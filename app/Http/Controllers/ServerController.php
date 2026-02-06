<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\Plan;
use App\Models\Node;
use App\Models\Core;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class ServerController extends Controller
{
    public function store(Request $request)
    {
        \Log::info('========== SERVER CREATION ==========');
        \Log::info('User:', ['id' => auth()->id(), 'name' => auth()->user()->name]);
        \Log::info('POST Data:', $request->all());
        
        try {
            // Валидация
            $request->validate([
                'name' => 'required|string|min:3|max:50',
                'plan_id' => 'required|integer',
                'node_id' => 'required|integer',
                'game_type' => 'required|in:java,bedrock',
                'period' => 'required|in:monthly,quarterly,half_year,yearly'
            ]);
            
            \Log::info('✅ Validation passed');
            
            $user = auth()->user();
            $plan = Plan::find($request->plan_id);
            $node = Node::find($request->node_id);
            
            if (!$plan) {
                throw new \Exception('Тариф не найден');
            }
            
            if (!$node) {
                throw new \Exception('Нода не найдена');
            }
            
            // Расчет цены
            $price = $this->calculatePrice($plan, $request->period);
            
            // Проверка баланса
            if ($user->balance < $price) {
                return back()->with('error', 'Недостаточно средств. Нужно: $' . $price . ', есть: $' . $user->balance)
                    ->withInput();
            }
            
            // Списываем средства
            $user->balance -= $price;
            $user->save();
            
            // Находим свободный порт
            $port = $this->findAvailablePort($node->id);
            
            // Создаем сервер в БД
            $server = Server::create([
                'name' => $request->name,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'node_id' => $node->id,
                'game_type' => $request->game_type,
                'memory' => $plan->memory,
                'disk_space' => $plan->disk_space,
                'player_slots' => $plan->player_slots,
                'ip_address' => $node->ip_address,
                'location' => $node->location,
                'status' => 'creating',
                'expires_at' => $this->calculateExpirationDate($request->period),
                'port' => $port,
                'settings' => [
                    'period' => $request->period,
                    'price_paid' => $price,
                    'created_via' => 'web_form'
                ]
            ]);
            
            \Log::info('✅ Server created in DB! ID: ' . $server->id);
            
            // ========== ВЫЗОВ PYTHON ДЕМОНА ==========
            $this->callDaemonToCreateServer($server, $plan, $node);
            
            return redirect()->route('servers.show', $server)
                ->with('success', 'Сервер создается! Это займет несколько минут. ID: ' . $server->id);
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation exception:', ['errors' => $e->errors()]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            \Log::error('Server creation error: ' . $e->getMessage());
            return back()->with('error', 'Ошибка: ' . $e->getMessage())
                ->withInput();
        }
    }
    

public function apiCheckStatus(Request $request, Server $server)
{
    // Проверяем права доступа
    if ($server->user_id !== auth()->id() && !auth()->user()->is_admin) {
        return response()->json(['error' => 'Forbidden'], 403);
    }
    
    try {
        // Получаем статус от демона
        $node = $server->node;
        
        $client = new Client(['timeout' => 5]);
        $response = $client->post("http://{$node->ip_address}:{$node->daemon_port}/api/server-status", [
            'json' => ['server_id' => $server->id],
            'headers' => [
                'Authorization' => 'Bearer ' . ($node->daemon_token ?? 'default_token'),
                'Content-Type' => 'application/json'
            ]
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Unknown error'
            ], 500);
        }
        
        // Обновляем статус в БД
        $this->updateServerStatusFromDaemon($server, $result);
        
        return response()->json([
            'success' => true,
            'status' => $result['status'],
            'managed_by_daemon' => $result['managed_by_daemon'] ?? false,
            'pid' => $result['pid'] ?? null,
            'port' => $result['port'] ?? $server->port,
            'uptime' => $result['uptime'] ?? $result['uptime_seconds'] ?? null,
            'server' => [
                'id' => $server->id,
                'name' => $server->name,
                'status' => $result['status'],
                'status_icon' => $this->getStatusIconForStatus($result['status']),
                'status_color' => $this->getStatusColorForStatus($result['status']),
                'status_text' => $this->getStatusTextForStatus($result['status']),
                'can_start' => $this->canServerBeStarted($server, $result['status']),
                'can_stop' => $this->canServerBeStopped($result['status']),
                'can_restart' => $this->canServerBeRestarted($server, $result['status']),
                'is_running' => $result['status'] === 'running',
                'uptime_formatted' => $this->formatUptime($result['uptime'] ?? $result['uptime_seconds'] ?? null)
            ]
        ]);
        
    } catch (ConnectException $e) {
        return response()->json([
            'success' => false,
            'error' => 'Daemon is not responding',
            'status' => 'daemon_offline'
        ], 503);
    } catch (\Exception $e) {
        \Log::error("API status check failed for server {$server->id}: " . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'status' => 'error'
        ], 500);
    }
}

/**
 * Обновление статуса сервера из ответа демона
 */
private function updateServerStatusFromDaemon(Server $server, array $daemonData)
{
    $settings = $server->settings ?? [];
    
    $updateData = [
        'settings' => array_merge($settings, [
            'real_status' => $daemonData['status'],
            'last_status_check' => now()->toDateTimeString(),
            'last_daemon_response' => $daemonData,
            'daemon_managed' => $daemonData['managed_by_daemon'] ?? false,
            'process_pid' => $daemonData['pid'] ?? null
        ])
    ];
    
    // Обновляем основной статус если он отличается
    $currentStatus = $server->status;
    $newStatus = $daemonData['status'];
    
    if ($newStatus !== $currentStatus) {
        $updateData['status'] = $newStatus;
        
        // Обновляем временные метки
        if ($newStatus === 'running') {
            $updateData['started_at'] = now();
            $updateData['stopped_at'] = null;
        } elseif ($newStatus === 'stopped') {
            $updateData['stopped_at'] = now();
        }
    }
    
    $server->update($updateData);
}

/**
 * Вспомогательные методы для определения статусов
 */
private function getStatusIconForStatus($status)
{
    $icons = [
        'running' => 'fa-play-circle',
        'stopped' => 'fa-stop-circle',
        'starting' => 'fa-spinner fa-spin',
        'stopping' => 'fa-stop',
        'restarting' => 'fa-redo',
        'creating' => 'fa-spinner fa-spin',
        'installing' => 'fa-download',
        'active' => 'fa-check-circle',
        'failed' => 'fa-exclamation-circle',
        'daemon_offline' => 'fa-plug'
    ];
    
    return $icons[$status] ?? 'fa-question-circle';
}

private function getStatusColorForStatus($status)
{
    $colors = [
        'running' => 'green',
        'stopped' => 'gray',
        'starting' => 'yellow',
        'stopping' => 'orange',
        'restarting' => 'yellow',
        'creating' => 'blue',
        'installing' => 'blue',
        'active' => 'green',
        'failed' => 'red',
        'daemon_offline' => 'red'
    ];
    
    return $colors[$status] ?? 'gray';
}

private function getStatusTextForStatus($status)
{
    $texts = [
        'running' => 'Запущен',
        'stopped' => 'Остановлен',
        'starting' => 'Запуск...',
        'stopping' => 'Остановка...',
        'restarting' => 'Перезагрузка...',
        'creating' => 'Создание...',
        'installing' => 'Установка...',
        'active' => 'Активен',
        'failed' => 'Ошибка',
        'daemon_offline' => 'Демон недоступен'
    ];
    
    return $texts[$status] ?? 'Неизвестно';
}

private function canServerBeStarted(Server $server, $status)
{
    return $status === 'stopped' && $server->core_id !== null;
}

private function canServerBeStopped($status)
{
    return $status === 'running';
}

private function canServerBeRestarted(Server $server, $status)
{
    return $this->canServerBeStarted($server, $status) && $this->canServerBeStopped($status);
}

private function formatUptime($uptime)
{
    if (!$uptime) return null;
    
    // Если uptime в формате "HH:MM" (как в вашем ответе: "00:26")
    if (is_string($uptime) && strpos($uptime, ':') !== false) {
        return $uptime;
    }
    
    // Если uptime в секундах
    if (is_numeric($uptime)) {
        $hours = floor($uptime / 3600);
        $minutes = floor(($uptime % 3600) / 60);
        
        if ($hours > 0) {
            return sprintf("%dч %dм", $hours, $minutes);
        } else {
            return sprintf("%dм", $minutes);
        }
    }
    
    return $uptime;
}
    /**
     * Расчет стоимости в зависимости от периода
     */
    private function calculatePrice($plan, $period)
    {
        $price = $plan->price_monthly;
        
        switch ($period) {
            case 'monthly': return $price;
            case 'quarterly': return $price * 3 * 0.9;
            case 'half_year': return $price * 6 * 0.85;
            case 'yearly': return $price * 12 * 0.8;
            default: return $price;
        }
    }
    
    /**
     * Расчет даты истечения
     */
    private function calculateExpirationDate($period)
    {
        $now = now();
        
        switch ($period) {
            case 'monthly': return $now->addMonth();
            case 'quarterly': return $now->addMonths(3);
            case 'half_year': return $now->addMonths(6);
            case 'yearly': return $now->addYear();
            default: return $now->addMonth();
        }
    }
    
    /**
     * Поиск свободного порта
     */
    private function findAvailablePort($nodeId)
    {
        // Получаем все занятые порты на ноде
        $usedPorts = Server::where('node_id', $nodeId)
            ->whereNotNull('port')
            ->pluck('port')
            ->toArray();
        
        // Начинаем с 25565 и ищем свободный
        $startPort = 25565;
        $maxPort = 26000;
        
        for ($port = $startPort; $port <= $maxPort; $port++) {
            if (!in_array($port, $usedPorts)) {
                return $port;
            }
        }
        
        // Если все порты заняты, используем случайный
        return rand($maxPort + 1, $maxPort + 1000);
    }
    
    /**
     * Вызов демона для создания сервера
     */
    private function callDaemonToCreateServer($server, $plan, $node)
    {
        try {
            \Log::info('Calling Python daemon for server: ' . $server->id);
            
            // Данные для отправки демону
            $daemonData = [
                'action' => 'create_server',
                'server_id' => $server->id,
                'server_name' => $server->name,
                'user_id' => $server->user_id,
                'game_type' => $server->game_type,
                'memory' => $server->memory,
                'disk_space' => $server->disk_space,
                'port' => $server->port,
                'node_ip' => $node->ip_address,
                'node_daemon_port' => $node->daemon_port ?? 8080,
                'node_token' => $node->daemon_token ?? 'default_token',
                'servers_path' => $node->servers_path ?? '/opt/minecraft-servers'
            ];
            
            // URL демона
            $daemonUrl = "http://{$node->ip_address}:{$node->daemon_port}/api/create-server";
            
            \Log::info('Sending to daemon:', [
                'url' => $daemonUrl,
                'data' => $daemonData
            ]);
            
            // Отправляем запрос к демону
            $client = new Client([
                'timeout' => 30,
                'connect_timeout' => 10,
                'verify' => false
            ]);
            
            $response = $client->post($daemonUrl, [
                'json' => $daemonData,
                'headers' => [
                    'Authorization' => 'Bearer ' . ($node->daemon_token ?? 'default_token'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            \Log::info('Daemon response:', $result);
            
            // Обновляем статус сервера
            if (isset($result['success']) && $result['success']) {
                $server->update([
                    'status' => 'active',
                    'settings' => array_merge($server->settings ?? [], [
                        'daemon_task_id' => $result['task_id'] ?? null,
                        'daemon_response' => $result,
                        'daemon_called_at' => now()
                    ])
                ]);
                \Log::info('✅ Daemon accepted task for server: ' . $server->id);
            } else {
                \Log::error('Daemon failed: ' . ($result['error'] ?? 'Unknown error'));
                $server->update([
                    'status' => 'failed',
                    'settings' => array_merge($server->settings ?? [], [
                        'daemon_error' => $result['error'] ?? 'Unknown error',
                        'daemon_called_at' => now()
                    ])
                ]);
            }
            
        } catch (ConnectException $e) {
            \Log::error('Daemon connection failed:', [
                'server_id' => $server->id,
                'error' => $e->getMessage(),
                'url' => $daemonUrl ?? 'unknown'
            ]);
            $server->update([
                'status' => 'daemon_offline',
                'settings' => array_merge($server->settings ?? [], [
                    'daemon_error' => 'Connection failed: ' . $e->getMessage(),
                    'daemon_called_at' => now()
                ])
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to call daemon:', [
                'server_id' => $server->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $server->update([
                'status' => 'daemon_error',
                'settings' => array_merge($server->settings ?? [], [
                    'daemon_error' => $e->getMessage(),
                    'daemon_called_at' => now()
                ])
            ]);
        }
    }
    
    public function kill(Server $server)
{
    \Log::info("Attempting to force kill server {$server->id}");
    
    if ($server->user_id != auth()->id() && !auth()->user()->is_admin) {
        abort(403, 'У вас нет доступа к этому серверу');
    }
    
    try {
        $node = $server->node;
        
        if (!$node) {
            throw new \Exception('Нода сервера не найдена');
        }
        
        // Форсированная остановка через демон
        $client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => false
        ]);
        
        $daemonUrl = "http://{$node->ip_address}:{$node->daemon_port}/api/kill-server";
        
        \Log::info("Sending force kill request to daemon: {$daemonUrl}");
        
        $response = $client->post($daemonUrl, [
            'json' => ['server_id' => $server->id],
            'headers' => [
                'Authorization' => 'Bearer ' . ($node->daemon_token ?? 'default_token'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        if (isset($result['success']) && $result['success']) {
            // Обновляем статус сервера
            $server->update([
                'status' => 'stopped',
                'stopped_at' => now(),
                'settings' => array_merge($server->settings ?? [], [
                    'last_kill_request' => now()->toDateTimeString(),
                    'kill_response' => $result
                ])
            ]);
            
            \Log::info("Server {$server->id} force killed successfully");
            
            return redirect()->route('servers.show', $server)
                ->with('success', 'Сервер форсированно остановлен');
        } else {
            throw new \Exception($result['error'] ?? 'Неизвестная ошибка демона');
        }
        
    } catch (ConnectException $e) {
        \Log::error("Daemon connection failed for force kill: " . $e->getMessage());
        
        // Если демон недоступен, попробуем убить процесс через системную команду
        if ($server->process_pid) {
            try {
                // Пробуем найти и убить процесс
                exec("kill -9 {$server->process_pid} 2>&1", $output, $returnCode);
                
                if ($returnCode === 0) {
                    $server->update([
                        'status' => 'stopped',
                        'stopped_at' => now(),
                        'settings' => array_merge($server->settings ?? [], [
                            'process_killed_locally' => true,
                            'kill_output' => $output,
                            'last_kill_request' => now()->toDateTimeString()
                        ])
                    ]);
                    
                    \Log::info("Server {$server->id} process killed locally: PID {$server->process_pid}");
                    
                    return redirect()->route('servers.show', $server)
                        ->with('success', 'Процесс сервера убит локально');
                }
            } catch (\Exception $localException) {
                \Log::error("Local kill failed: " . $localException->getMessage());
            }
        }
        
        return redirect()->route('servers.show', $server)
            ->with('error', 'Демон недоступен. Не удалось остановить сервер.');
            
    } catch (\Exception $e) {
        \Log::error("Force kill failed for server {$server->id}: " . $e->getMessage());
        
        return redirect()->route('servers.show', $server)
            ->with('error', 'Ошибка при форсированной остановке: ' . $e->getMessage());
    }
}
    public function checkBackgroundStatus(Server $server)
{
    try {
        $node = $server->node;
        $client = new Client(['timeout' => 5]);
        
        $response = $client->post("http://{$node->ip_address}:{$node->daemon_port}/api/server-status", [
            'json' => ['server_id' => $server->id],
            'headers' => [
                'Authorization' => 'Bearer ' . $node->daemon_token
            ]
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        if ($result['success']) {
            // Обновляем статус
            $server->update([
                'status' => $result['status'],
                'settings' => array_merge($server->settings ?? [], [
                    'last_status_check' => now()->toDateTimeString(),
                    'daemon_status_response' => $result,
                    'real_status' => $result['status'],
                    'daemon_managed' => $result['managed_by_daemon'] ?? false,
                    'process_pid' => $result['pid'] ?? null
                ])
            ]);
            
            \Log::info("Background status check for server {$server->id}: {$result['status']}");
        }
        
    } catch (\Exception $e) {
        \Log::warning("Background status check failed for server {$server->id}: " . $e->getMessage());
    }
}
    public function show(Server $server)
{
    // Проверяем права доступа
    if ($server->user_id !== auth()->id() && !auth()->user()->is_admin) {
        abort(403, 'Доступ запрещен');
    }
    
    // Загружаем связанные данные
    $server->load(['plan', 'node', 'core', 'user']);
    
    // Если сервер в статусе создания - проверяем статус
    if (in_array($server->status, ['creating', 'installing', 'core_installing'])) {
        // Запускаем фоновую проверку статуса
        dispatch(function () use ($server) {
            $this->checkBackgroundStatus($server);
        })->afterResponse();
    }
    
    // Получаем статистику сервера
    $serverStats = $this->getServerExtendedStats($server);
    
    // Определяем доступные действия
    $availableActions = $this->getAvailableActions($server);
    
    return view('dashboard.servers.show', [
        'server' => $server,
        'serverStats' => $serverStats,
        'availableActions' => $availableActions,
        'title' => 'Сервер ' . $server->name
    ]);
}
    
    /**
     * Запуск сервера
     */
    public function start(Request $request, Server $server)
{
    // Проверка прав
    if ($server->user_id !== auth()->id() && !auth()->user()->is_admin) {
        abort(403, 'Доступ запрещен');
    }
    
    // Проверяем, можно ли запустить сервер
    if (!$server->canBeStarted()) {
        return back()->with('error', 'Сервер не может быть запущен. Проверьте статус и наличие ядра.');
    }
    
    try {
        // Отправляем команду демону
        $result = $this->sendCommandToDaemon($server, 'start');
        
        // Обновляем статус в БД
        $server->update([
            'status' => 'starting',
            'started_at' => now(),
            'stopped_at' => null,
            'last_action' => now(),
            'settings' => array_merge($server->settings ?? [], [
                'last_start_attempt' => now(),
                'daemon_response' => $result
            ])
        ]);
        
        \Log::info("Server {$server->id} start command sent", ['result' => $result]);
        
        return back()->with('success', 'Сервер запускается... Это может занять несколько минут.');
        
    } catch (\Exception $e) {
        \Log::error("Failed to start server {$server->id}: " . $e->getMessage());
        
        $server->update([
            'status' => 'start_failed',
            'settings' => array_merge($server->settings ?? [], [
                'start_error' => $e->getMessage(),
                'last_error_at' => now()
            ])
        ]);
        
        return back()->with('error', 'Ошибка запуска: ' . $e->getMessage());
    }
}
    
    /**
     * Остановка сервера
     */
    public function stop(Request $request, Server $server)
{
    if ($server->user_id !== auth()->id() && !auth()->user()->is_admin) {
        abort(403, 'Доступ запрещен');
    }
    
    // Проверяем, можно ли остановить сервер
    if ($server->status !== 'running' && $server->status !== 'starting') {
        return back()->with('error', 'Сервер не запущен или уже останавливается.');
    }
    
    try {
        $result = $this->sendCommandToDaemon($server, 'stop');
        
        $server->update([
            'status' => 'stopping',
            'stopped_at' => now(),
            'last_action' => now(),
            'settings' => array_merge($server->settings ?? [], [
                'last_stop_attempt' => now(),
                'daemon_response' => $result
            ])
        ]);
        
        \Log::info("Server {$server->id} stop command sent", ['result' => $result]);
        
        return back()->with('success', 'Сервер останавливается...');
        
    } catch (\Exception $e) {
        \Log::error("Failed to stop server {$server->id}: " . $e->getMessage());
        
        $server->update([
            'status' => 'stop_failed',
            'settings' => array_merge($server->settings ?? [], [
                'stop_error' => $e->getMessage(),
                'last_error_at' => now()
            ])
        ]);
        
        return back()->with('error', 'Ошибка остановки: ' . $e->getMessage());
    }
}
    
    /**
     * Перезагрузка сервера
     */
    public function restart(Request $request, Server $server)
{
    if ($server->user_id !== auth()->id() && !auth()->user()->is_admin) {
        abort(403, 'Доступ запрещен');
    }
    
    if (!$server->canBeStarted()) {
        return back()->with('error', 'Сервер не может быть перезапущен. Проверьте статус и наличие ядра.');
    }
    
    try {
        $result = $this->sendCommandToDaemon($server, 'restart');
        
        $server->update([
            'status' => 'restarting',
            'last_action' => now(),
            'settings' => array_merge($server->settings ?? [], [
                'last_restart_attempt' => now(),
                'daemon_response' => $result
            ])
        ]);
        
        \Log::info("Server {$server->id} restart command sent", ['result' => $result]);
        
        return back()->with('success', 'Сервер перезагружается... Это может занять несколько минут.');
        
    } catch (\Exception $e) {
        \Log::error("Failed to restart server {$server->id}: " . $e->getMessage());
        
        $server->update([
            'status' => 'restart_failed',
            'settings' => array_merge($server->settings ?? [], [
                'restart_error' => $e->getMessage(),
                'last_error_at' => now()
            ])
        ]);
        
        return back()->with('error', 'Ошибка перезагрузки: ' . $e->getMessage());
    }
}
/**
 * Проверка реального статуса сервера через демон
 */
public function checkStatus(Request $request, Server $server)
{
    if ($server->user_id !== auth()->id() && !auth()->user()->is_admin) {
        abort(403, 'Доступ запрещен');
    }
    
    try {
        // Получаем реальный статус от демона
        $result = $this->sendCommandToDaemon($server, 'status');
        
        // Определяем новый статус на основе ответа демона
        $newStatus = $result['status'] ?? 'unknown';
        $managedByDaemon = $result['managed_by_daemon'] ?? false;
        $pid = $result['pid'] ?? null;
        
        // Обновляем статус в БД
        $updateData = [
            'last_status_check' => now(),
            'settings' => array_merge($server->settings ?? [], [
                'last_status_response' => $result,
                'real_status' => $newStatus,
                'daemon_managed' => $managedByDaemon,
                'process_pid' => $pid
            ])
        ];
        
        // Если демон говорит, что сервер работает, но у нас другой статус - исправляем
        if ($newStatus === 'running' && $server->status !== 'running') {
            $updateData['status'] = 'running';
            $updateData['started_at'] = now();
            $updateData['stopped_at'] = null;
        } elseif ($newStatus === 'stopped' && $server->status !== 'stopped') {
            $updateData['status'] = 'stopped';
            $updateData['stopped_at'] = now();
        }
        
        $server->update($updateData);
        
        \Log::info("Server {$server->id} status checked", [
            'real_status' => $newStatus,
            'db_status' => $server->status
        ]);
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'real_status' => $newStatus,
                'db_status' => $server->status,
                'managed_by_daemon' => $managedByDaemon,
                'pid' => $pid,
                'port' => $result['port'] ?? $server->port,
                'message' => "Реальный статус: {$newStatus}"
            ]);
        }
        
        return back()->with('info', "Реальный статус сервера: {$newStatus}");
        
    } catch (\Exception $e) {
        \Log::error("Failed to check status for server {$server->id}: " . $e->getMessage());
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
        
        return back()->with('warning', 'Не удалось проверить статус: ' . $e->getMessage());
    }
}    

private function getServerExtendedStats(Server $server)
{
    try {
        $result = $this->sendCommandToDaemon($server, 'status');
        
        return [
            'real_status' => $result['status'] ?? 'unknown',
            'managed_by_daemon' => $result['managed_by_daemon'] ?? false,
            'pid' => $result['pid'] ?? null,
            'port' => $result['port'] ?? $server->port,
            'uptime' => $result['uptime_seconds'] ?? null,
            'note' => $result['note'] ?? null,
            'daemon_info' => $result
        ];
    } catch (\Exception $e) {
        return [
            'real_status' => 'daemon_unavailable',
            'error' => $e->getMessage(),
            'db_status' => $server->status
        ];
    }
}

/**
 * Определение доступных действий для сервера
 */
private function getAvailableActions(Server $server)
{
    $actions = [
        'start' => false,
        'stop' => false,
        'restart' => false,
        'console' => false,
        'backup' => false,
        'files' => false
    ];
    
    // Базовые проверки
    $hasCore = $server->core_id !== null;
    $isPaid = !$server->expires_at || $server->expires_at->isFuture();
    
    // Проверяем статус
    $realStatus = $server->settings['real_status'] ?? $server->status;
    
    // Определяем доступность действий
    $actions['start'] = $hasCore && $isPaid && 
                       ($realStatus === 'stopped' || $realStatus === 'offline');
    
    $actions['stop'] = $realStatus === 'running' || $realStatus === 'starting';
    
    $actions['restart'] = $hasCore && $isPaid && 
                         ($realStatus === 'running' || $realStatus === 'starting');
    
    $actions['console'] = $realStatus === 'running' || $realStatus === 'starting';
    $actions['files'] = true; // Файлы всегда доступны
    $actions['backup'] = $realStatus === 'running' || $realStatus === 'starting';
    
    return $actions;
}

/**
 * Проверка возможности запуска сервера
 */
private function canBeStarted(Server $server)
{
    // Проверяем наличие ядра
    if (!$server->core_id) {
        return false;
    }
    
    // Проверяем срок действия
    if ($server->expires_at && $server->expires_at->isPast()) {
        return false;
    }
    
    // Проверяем текущий статус
    $currentStatus = $server->settings['real_status'] ?? $server->status;
    $blockedStatuses = ['creating', 'installing', 'starting', 'restarting', 'stopping'];
    
    if (in_array($currentStatus, $blockedStatuses)) {
        return false;
    }
    
    return true;
}
    /**
     * Создание бэкапа
     */
    public function backup(Request $request, Server $server)
    {
        if ($server->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещен');
        }
        
        try {
            $this->sendCommandToDaemon($server, 'backup');
            
            $server->update(['last_backup' => now()]);
            
            \Log::info("Server {$server->id} backup command sent");
            
            return back()->with('success', 'Создание бэкапа запущено...');
            
        } catch (\Exception $e) {
            \Log::error("Failed to backup server {$server->id}: " . $e->getMessage());
            return back()->with('error', 'Ошибка создания бэкапа: ' . $e->getMessage());
        }
    }
    
    /**
     * Отправка команды в консоль сервера
     */
    public function sendCommand(Request $request, Server $server)
    {
        $request->validate([
            'command' => 'required|string|max:255'
        ]);
        
        if ($server->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещен');
        }
        
        try {
            $node = $server->node;
            
            $data = [
                'action' => 'console_command',
                'server_id' => $server->id,
                'command' => $request->command
            ];
            
            $client = new Client(['timeout' => 10]);
            $response = $client->post("http://{$node->ip_address}:{$node->daemon_port}/api/console", [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $node->daemon_token,
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if ($result['success']) {
                return back()->with('success', 'Команда отправлена: ' . $request->command);
            } else {
                return back()->with('error', 'Ошибка: ' . ($result['error'] ?? 'Неизвестная ошибка'));
            }
            
        } catch (\Exception $e) {
            \Log::error("Failed to send command to server {$server->id}: " . $e->getMessage());
            return back()->with('error', 'Ошибка отправки команды: ' . $e->getMessage());
        }
    }
    
    /**
     * Страница управления файлами сервера
     */
    public function files(Server $server)
    {
        if ($server->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещен');
        }
        
        try {
            // Получаем список файлов через демон
            $files = $this->getServerFiles($server);
            
            return view('dashboard.servers.files', [
                'server' => $server,
                'files' => $files,
                'title' => 'Файлы сервера ' . $server->name
            ]);
            
        } catch (\Exception $e) {
            \Log::error("Failed to get files for server {$server->id}: " . $e->getMessage());
            return back()->with('error', 'Не удалось получить список файлов');
        }
    }
    
    /**
     * Страница консоли сервера
     */
    public function console(Server $server)
    {
        if ($server->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещен');
        }
        
        // Получаем последние логи
        $logs = $this->getServerLogs($server);
        
        return view('dashboard.servers.console', [
            'server' => $server,
            'logs' => $logs,
            'title' => 'Консоль сервера ' . $server->name
        ]);
    }
    
    /**
     * Настройки сервера
     */
    public function settings(Server $server)
{
    // Проверка прав
    if ($server->user_id !== auth()->id()) {
        abort(403, 'Доступ запрещен');
    }
    
    // Проверяем можно ли менять настройки
    if (!$server->canChangeCore() && $server->status !== 'core_installed') {
        return back()->with('error', 'Невозможно изменить настройки. Сервер должен быть остановлен и иметь установленное ядро.');
    }
    
    $availableCores = Core::where('game_type', $server->game_type)
                         ->where('is_active', true)
                         ->orderBy('name')
                         ->orderByDesc('version')
                         ->get();
    
    $title = 'Настройки сервера: ' . $server->name;
    
    return view('dashboard.servers.settings', [
        'server' => $server,
        'availableCores' => $availableCores,
        'title' => $title
    ]);
}
    
    /**
     * Обновление настроек сервера
     */
    public function updateSettings(Request $request, Server $server)
    {
        if ($server->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещен');
        }
        
        $request->validate([
            'name' => 'required|string|min:3|max:50',
            'core_id' => 'nullable|exists:cores,id',
            'auto_start' => 'boolean',
            'auto_backup' => 'boolean',
            'backup_interval' => 'nullable|integer|min:1|max:168'
        ]);
        
        try {
            $settings = $server->settings ?? [];
            
            // Обновляем основные настройки
            $server->update([
                'name' => $request->name,
                'settings' => array_merge($settings, [
                    'auto_start' => $request->boolean('auto_start', false),
                    'auto_backup' => $request->boolean('auto_backup', false),
                    'backup_interval' => $request->backup_interval ?? 24
                ])
            ]);
            
            // Если изменили ядро
            if ($request->core_id && $request->core_id != $server->core_id) {
                $core = Core::find($request->core_id);
                
                if ($core && $core->game_type == $server->game_type) {
                    // Устанавливаем новое ядро
                    $server->update([
                        'core_id' => $core->id,
                        'core_type' => $core->name,
                        'core_version' => $core->version,
                        'status' => 'core_installing'
                    ]);
                    
                    // Отправляем команду демону на установку ядра
                    $this->installCoreOnServer($server, $core);
                }
            }
            
            return back()->with('success', 'Настройки обновлены');
            
        } catch (\Exception $e) {
            \Log::error("Failed to update server settings {$server->id}: " . $e->getMessage());
            return back()->with('error', 'Ошибка обновления настроек: ' . $e->getMessage());
        }
    }
    
    /**
     * Установка ядра на сервер
     */
    private function installCoreOnServer(Server $server, Core $core)
    {
        try {
            $node = $server->node;
            
            $data = [
                'action' => 'install_core',
                'server_id' => $server->id,
                'core_id' => $core->id,
                'core_name' => $core->name,
                'core_version' => $core->version,
                'core_path' => $core->file_path,
                'core_file_name' => $core->file_name,
                'server_path' => $node->servers_path . '/' . $server->id
            ];
            
            $client = new Client(['timeout' => 30]);
            $response = $client->post("http://{$node->ip_address}:{$node->daemon_port}/api/install-core", [
                'json' => $data,
                'headers' => [
                    'Authorization' => 'Bearer ' . $node->daemon_token,
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            if ($result['success']) {
                \Log::info("Core installation started for server {$server->id}");
                return true;
            } else {
                throw new \Exception($result['error'] ?? 'Неизвестная ошибка');
            }
            
        } catch (\Exception $e) {
            \Log::error("Failed to install core on server {$server->id}: " . $e->getMessage());
            $server->update(['status' => 'core_install_failed']);
            throw $e;
        }
    }
    
    /**
     * Отправка команды демону
     */
    private function sendCommandToDaemon(Server $server, $action, $additionalData = [])
{
    try {
        $node = $server->node;
        
        // Определяем URL для разных действий
        $endpoints = [
            'start' => '/api/start-server',
            'stop' => '/api/stop-server',
            'restart' => '/api/restart-server',
            'status' => '/api/server-status',
            'console' => '/api/console'
        ];
        
        if (!isset($endpoints[$action])) {
            throw new \Exception("Неизвестное действие: {$action}");
        }
        
        $daemonUrl = "http://{$node->ip_address}:{$node->daemon_port}" . $endpoints[$action];
        
        $data = array_merge([
            'server_id' => $server->id
        ], $additionalData);
        
        \Log::info("Sending command to daemon", [
            'url' => $daemonUrl,
            'action' => $action,
            'server_id' => $server->id,
            'data' => $data
        ]);
        
        $client = new Client([
            'timeout' => 15,
            'connect_timeout' => 5,
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . ($node->daemon_token ?? 'default_token'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ]);
        
        $response = $client->post($daemonUrl, [
            'json' => $data
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        if (!$result['success']) {
            throw new \Exception($result['error'] ?? 'Неизвестная ошибка демона');
        }
        
        \Log::info("Daemon command successful", [
            'action' => $action,
            'result' => $result
        ]);
        
        return $result;
        
    } catch (ConnectException $e) {
        \Log::error("Daemon connection failed", [
            'server_id' => $server->id,
            'action' => $action,
            'error' => $e->getMessage()
        ]);
        throw new \Exception('Демон не отвечает: ' . $e->getMessage());
    } catch (\Exception $e) {
        \Log::error("Daemon command failed", [
            'server_id' => $server->id,
            'action' => $action,
            'error' => $e->getMessage()
        ]);
        throw new \Exception('Ошибка демона: ' . $e->getMessage());
    }
}
    
    /**
     * Получение статистики сервера
     */
    private function getServerStats(Server $server)
    {
        try {
            $node = $server->node;
            
            $client = new Client(['timeout' => 5]);
            $response = $client->post("http://{$node->ip_address}:{$node->daemon_port}/api/server-stats", [
                'json' => ['server_id' => $server->id],
                'headers' => [
                    'Authorization' => 'Bearer ' . $node->daemon_token
                ]
            ]);
            
            return json_decode($response->getBody(), true);
            
        } catch (\Exception $e) {
            \Log::info("Could not get stats for server {$server->id}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение списка файлов сервера
     */
    private function getServerFiles(Server $server)
    {
        try {
            $node = $server->node;
            
            $client = new Client(['timeout' => 10]);
            $response = $client->post("http://{$node->ip_address}:{$node->daemon_port}/api/list-files", [
                'json' => ['server_id' => $server->id],
                'headers' => [
                    'Authorization' => 'Bearer ' . $node->daemon_token
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            return $result['files'] ?? [];
            
        } catch (\Exception $e) {
            \Log::error("Failed to get files for server {$server->id}: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Получение логов сервера
     */
    private function getServerLogs(Server $server, $lines = 100)
    {
        try {
            $node = $server->node;
            
            $client = new Client(['timeout' => 10]);
            $response = $client->post("http://{$node->ip_address}:{$node->daemon_port}/api/get-logs", [
                'json' => [
                    'server_id' => $server->id,
                    'lines' => $lines
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $node->daemon_token
                ]
            ]);
            
            $result = json_decode($response->getBody(), true);
            
            return $result['logs'] ?? 'Логи недоступны';
            
        } catch (\Exception $e) {
            \Log::error("Failed to get logs for server {$server->id}: " . $e->getMessage());
            return 'Не удалось получить логи: ' . $e->getMessage();
        }
    }
    
    /**
     * Обновление статуса сервера (вызывается демоном)
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'server_id' => 'required|exists:servers,id',
            'status' => 'required|string',
            'message' => 'nullable|string'
        ]);
        
        try {
            $server = Server::findOrFail($request->server_id);
            
            $server->update([
                'status' => $request->status,
                'settings' => array_merge($server->settings ?? [], [
                    'last_status_update' => now(),
                    'status_message' => $request->message
                ])
            ]);
            
            \Log::info("Server {$server->id} status updated to: {$request->status}");
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            \Log::error("Failed to update server status: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Список серверов (для демона)
     */
    public function apiIndex()
    {
        if (!request()->has('token') || request()->token !== config('app.daemon_token')) {
            abort(403, 'Invalid token');
        }
        
        $servers = Server::with(['node', 'core'])
                        ->whereIn('status', ['active', 'starting', 'stopping', 'restarting'])
                        ->get()
                        ->map(function ($server) {
                            return [
                                'id' => $server->id,
                                'name' => $server->name,
                                'node_id' => $server->node_id,
                                'node_ip' => $server->node->ip_address,
                                'port' => $server->port,
                                'status' => $server->status,
                                'core_file' => $server->core ? $server->core->file_name : null,
                                'server_path' => $server->node->servers_path . '/' . $server->id
                            ];
                        });
        
        return response()->json(['servers' => $servers]);
    }
}

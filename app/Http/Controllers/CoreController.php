<?php

namespace App\Http\Controllers;

use App\Models\Core;
use App\Models\Server;
use App\Models\Node;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class CoreController extends Controller
{
    /**
     * Показать страницу выбора ядра для сервера
     */
    public function select(Server $server)
    {
        // Проверка прав доступа
        if ($server->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Доступ запрещен');
        }

        // Проверяем можно ли менять ядро
        if (!$server->canChangeCore()) {
            return redirect()->route('servers.show', $server)
                ->with('error', 'Невозможно изменить ядро. Сервер должен быть остановлен и иметь установленное ядро.');
        }

        $cores = Core::where('game_type', $server->game_type)
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->orderByRaw("
                        CAST(SUBSTRING_INDEX(version, '.', 1) AS UNSIGNED) DESC,
                        CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(version, '.', 2), '.', -1) AS UNSIGNED) DESC,
                        CAST(SUBSTRING_INDEX(version, '.', -1) AS UNSIGNED) DESC
                    ")
                    ->get()
                    ->groupBy('name');
        
        $currentCore = $server->core;
        $latestVersions = [];
        
        // Получаем последние версии для каждого типа ядра
        foreach ($cores as $name => $versionGroup) {
            $latestVersions[$name] = $versionGroup->first();
        }
        
        return view('dashboard.servers.select-core', [
            'server' => $server,
            'cores' => $cores,
            'currentCore' => $currentCore,
            'latestVersions' => $latestVersions,
            'title' => 'Выбор ядра для ' . $server->name
        ]);
    }
    
    /**
     * Установить новое ядро на сервер
     */
    public function install(Request $request, Server $server)
    {
        // Проверка прав доступа
        if ($server->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return redirect()->route('dashboard')->with('error', 'Доступ запрещен');
        }

        $request->validate([
            'core_id' => 'required|exists:cores,id'
        ]);
        
        $core = Core::findOrFail($request->core_id);
        
        // Проверка совместимости
        if ($core->game_type !== $server->game_type) {
            return back()->with('error', 'Это ядро не подходит для типа сервера');
        }
        
        // Проверяем можно ли менять ядро
        if (!$server->canChangeCore()) {
            return back()->with('error', 'Сервер не готов к смене ядра. Остановите сервер и попробуйте снова.');
        }
        
        // Обновляем сервер
        $server->update([
            'core_id' => $core->id,
            'core_type' => $core->name,
            'core_version' => $core->version,
            'status' => 'core_installing'
        ]);
        
        // Отправляем команду демону для установки ядра
        $success = $this->installCoreOnServer($server, $core);
        
        if ($success) {
            return redirect()->route('servers.show', $server)
                ->with('success', 'Ядро выбрано. Установка началась...');
        } else {
            // Если ошибка, возвращаем предыдущее ядро
            $server->update([
                'status' => 'core_install_failed'
            ]);
            
            return redirect()->route('servers.show', $server)
                ->with('error', 'Ошибка при установке ядра. Проверьте логи демона.');
        }
    }
    
    /**
     * Установка ядра на сервер через демон
     */
    private function installCoreOnServer(Server $server, Core $core)
    {
        try {
            $node = $server->node;
            
            if (!$node) {
                Log::error("Node not found for server {$server->id}");
                return false;
            }
            
            $client = new Client([
                'timeout' => 300,
                'verify' => false,
                'http_errors' => false
            ]);
            
            // Используем новый endpoint для установки ядра
            $response = $client->post("http://{$node->ip_address}:{$node->daemon_port}/api/install-core", [
                'json' => [
                    'server_id' => $server->id,
                    'core_name' => $core->name,
                    'core_version' => $core->version,
                    'game_type' => $core->game_type,
                    'file_name' => $core->file_name,
                    'server_path' => $server->getServerPath(),
                    'old_core_path' => $server->getServerCorePath() // Для бэкапа старого ядра
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . ($node->daemon_token ?? 'default_token'),
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            $statusCode = $response->getStatusCode();
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);
            
            Log::info("Core installation response: {$statusCode} - " . substr($body, 0, 200));
            
            if ($statusCode === 200 && isset($data['success']) && $data['success']) {
                // Успешная установка
                $server->update([
                    'status' => 'core_installed',
                    'installed_at' => now()
                ]);
                
                Log::info("Core {$core->name} {$core->version} installed successfully on server {$server->id}");
                return true;
            } else {
                $error = $data['error'] ?? $data['message'] ?? 'Unknown error';
                Log::error("Core installation failed: {$error}");
                return false;
            }
            
        } catch (\Exception $e) {
            Log::error("Failed to install core: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Проверить статус установки ядра (для AJAX)
     */
    public function checkInstallationStatus(Server $server)
    {
        if ($server->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        return response()->json([
            'status' => $server->status,
            'core_name' => $server->core_type,
            'core_version' => $server->core_version,
            'is_installed' => $server->hasCoreInstalled(),
            'installed_at' => $server->installed_at ? $server->installed_at->format('d.m.Y H:i') : null,
            'can_change' => $server->canChangeCore()
        ]);
    }
    
    /**
     * Получить информацию о доступных обновлениях ядра
     */
    public function checkUpdates(Server $server)
    {
        if ($server->user_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        if (!$server->core) {
            return response()->json(['updates_available' => false]);
        }
        
        // Получаем последнюю версию этого типа ядра
        $latestCore = Core::where('game_type', $server->game_type)
            ->where('name', $server->core->name)
            ->where('is_active', true)
            ->orderByDesc('version')
            ->first();
        
        $needsUpdate = $latestCore && $latestCore->id !== $server->core_id;
        
        return response()->json([
            'current' => [
                'id' => $server->core_id,
                'name' => $server->core_type,
                'version' => $server->core_version
            ],
            'latest' => $latestCore ? [
                'id' => $latestCore->id,
                'name' => $latestCore->name,
                'version' => $latestCore->version
            ] : null,
            'updates_available' => $needsUpdate,
            'can_update' => $server->canChangeCore() && $needsUpdate
        ]);
    }
    
    /**
     * API: Список ядер (для демона)
     */
    public function apiIndex()
    {
        $cores = Core::where('is_active', true)
            ->select(['id', 'name', 'game_type', 'version', 'file_name', 'file_path', 'file_size'])
            ->get();
        
        return response()->json([
            'success' => true,
            'cores' => $cores,
            'total' => $cores->count()
        ]);
    }
    
    /**
     * API: Скачивание ядра (для демона)
     */
    public function apiDownload(Core $core)
    {
        // Проверяем, доступен ли файл локально
        $localPath = storage_path('app/cores-backup/' . basename($core->file_path));
        
        if (file_exists($localPath)) {
            return response()->download($localPath, $core->file_name);
        }
        
        // Если нет локальной копии, возвращаем URL
        return response()->json([
            'success' => true,
            'download_url' => $core->download_url,
            'file_name' => $core->file_name,
            'file_size' => $core->file_size
        ]);
    }
}

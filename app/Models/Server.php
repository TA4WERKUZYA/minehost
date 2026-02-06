<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'plan_id',
        'node_id',
        'uuid',
        'ip_address',
        'port',
        'game_type',
        'core_type',      // Тип ядра: paper, spigot, vanilla, fabric
        'core_version',   // Версия ядра: 1.20.4, 1.19.4 и т.д.
        'core_id',        // ID конкретного ядра из таблицы cores
        'memory',
        'disk_space',
        'player_slots',   // Добавим это поле
        'status',
        'location',
        'expires_at',
        'settings',
        'installed_at',   // Когда было установлено ядро
        'started_at',     // Когда был запущен сервер
        'stopped_at',     // Когда был остановлен
        'last_backup'     // Дата последнего бэкапа
    ];

    protected $casts = [
        'settings' => 'array',
        'expires_at' => 'datetime',
        'installed_at' => 'datetime',
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'last_backup' => 'datetime',
        'memory' => 'integer',
        'disk_space' => 'integer',
        'player_slots' => 'integer',
        'port' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($server) {
            if (empty($server->uuid)) {
                $server->uuid = Str::uuid()->toString();
            }
            if (empty($server->port)) {
                // Генерация свободного порта
                $server->port = $server->findAvailablePort();
            }
            if (empty($server->status)) {
                $server->status = 'creating';
            }
            if (empty($server->ip_address)) {
                $node = Node::find($server->node_id);
                $server->ip_address = $node ? $node->ip_address : '127.0.0.1';
            }
            if (empty($server->location)) {
                $node = Node::find($server->node_id);
                $server->location = $node ? $node->location : 'Unknown';
            }
            if (empty($server->player_slots)) {
                $plan = Plan::find($server->plan_id);
                $server->player_slots = $plan ? $plan->player_slots : 20;
            }
            if (empty($server->core_type)) {
                $server->core_type = 'paper'; // По умолчанию Paper
            }
            if (empty($server->core_version)) {
                $server->core_version = '1.20.4'; // По умолчанию последняя версия
            }
        });

        static::created(function ($server) {
            // При создании сервера устанавливаем дефолтное ядро
            $server->assignDefaultCore();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function node()
    {
        return $this->belongsTo(Node::class);
    }

    public function core()
    {
        return $this->belongsTo(Core::class, 'core_id');
    }

    public function backups()
    {
        return $this->hasMany(Backup::class);
    }

    // Проверка активен ли сервер
    public function isActive()
    {
        return $this->status === 'active' && 
               ($this->expires_at === null || $this->expires_at > now());
    }

    // Проверка установлено ли ядро
    public function hasCoreInstalled()
    {
        return !empty($this->core_id) && $this->status === 'core_installed';
    }

    // Получить путь к ядру сервера
    public function getCorePath()
    {
        if ($this->core) {
            return $this->core->file_path;
        }
        
        // Fallback на дефолтное ядро
        $defaultCore = Core::where('game_type', $this->game_type)
                          ->where('is_default', true)
                          ->where('is_active', true)
                          ->first();
        
        return $defaultCore ? $defaultCore->file_path : null;
    }

    // Получить имя файла ядра
    public function getCoreFileName()
    {
        if ($this->core) {
            return $this->core->file_name;
        }
        
        // Генерируем имя на основе типа и версии
        if ($this->core_type === 'paper') {
            return "paper-{$this->core_version}.jar";
        } elseif ($this->core_type === 'vanilla') {
            return "server.jar";
        } elseif ($this->core_type === 'spigot') {
            return "spigot-{$this->core_version}.jar";
        } elseif ($this->core_type === 'fabric') {
            return "fabric-server-launch.jar";
        }
        
        return "server.jar";
    }

    // Получить путь к папке сервера
    public function getServerPath()
    {
        $node = $this->node;
        if ($node && $node->servers_path) {
            return rtrim($node->servers_path, '/') . '/' . $this->id;
        }
        
        return '/opt/minecraft-servers/' . $this->id;
    }

    // Получить полный путь к файлу ядра в папке сервера
    public function getServerCorePath()
    {
        return $this->getServerPath() . '/' . $this->getCoreFileName();
    }

    // Поиск свободного порта
    private function findAvailablePort()
    {
        $node = $this->node;
        if (!$node) {
            return rand(25566, 26000);
        }

        // Получаем все занятые порты на ноде
        $usedPorts = self::where('node_id', $node->id)
            ->where('id', '!=', $this->id ?? 0)
            ->whereNotNull('port')
            ->pluck('port')
            ->toArray();

        // Начинаем с 25565 и ищем свободный
        $startPort = 25565;
        $maxPort = 27000;

        for ($port = $startPort; $port <= $maxPort; $port++) {
            if (!in_array($port, $usedPorts)) {
                return $port;
            }
        }

        // Если все порты заняты, используем случайный
        return rand($maxPort + 1, $maxPort + 1000);
    }

    // Назначить дефолтное ядро
    private function assignDefaultCore()
    {
        $defaultCore = Core::where('game_type', $this->game_type)
                          ->where('is_default', true)
                          ->where('is_active', true)
                          ->first();

        if ($defaultCore) {
            $this->update([
                'core_id' => $defaultCore->id,
                'core_type' => $defaultCore->name,
                'core_version' => $defaultCore->version
            ]);
        }
    }

    // Получить статус в виде иконки
    public function getStatusIcon()
    {
        $statusIcons = [
            'creating' => 'fa-spinner fa-spin',
            'core_installing' => 'fa-cogs',
            'core_installed' => 'fa-check-circle',
            'installing' => 'fa-download',
            'active' => 'fa-play-circle',
            'stopped' => 'fa-stop-circle',
            'starting' => 'fa-play',
            'stopping' => 'fa-stop',
            'restarting' => 'fa-redo',
            'failed' => 'fa-exclamation-circle',
            'suspended' => 'fa-pause-circle',
            'daemon_offline' => 'fa-plug',
            'core_install_failed' => 'fa-times-circle'
        ];

        return $statusIcons[$this->status] ?? 'fa-question-circle';
    }

    // Получить цвет статуса
    public function getStatusColor()
    {
        $statusColors = [
            'creating' => 'blue',
            'core_installing' => 'yellow',
            'core_installed' => 'green',
            'installing' => 'blue',
            'active' => 'green',
            'stopped' => 'gray',
            'starting' => 'yellow',
            'stopping' => 'orange',
            'restarting' => 'yellow',
            'failed' => 'red',
            'suspended' => 'orange',
            'daemon_offline' => 'red',
            'core_install_failed' => 'red'
        ];

        return $statusColors[$this->status] ?? 'gray';
    }

    // Получить доступные действия для сервера
    /**
     * Получить доступные действия для сервера (с учетом реального статуса)
     */
    public function getAvailableActions()
{
    $actions = [];
    
    // Используем реальный статус
    $realStatus = $this->real_status;
    
    // Если статус "creating" или "installing" - показываем только проверку статуса
    if (in_array($realStatus, ['creating', 'installing', 'core_installing'])) {
        $actions[] = 'check_status';
        return $actions;
    }
    
    // Проверяем условия с использованием новых методов
    if ($this->canBeStarted()) {
        $actions[] = 'start';
    }
    
    if ($this->canBeStopped()) {
        $actions[] = 'stop';
    }
    
    if ($this->canBeRestarted()) {
        $actions[] = 'restart';
    }
    
    // Консоль доступна только если сервер реально работает
    if ($this->is_running) {
        $actions[] = 'console';
        $actions[] = 'backup';
    }
    
    // Файлы и настройки доступны всегда (кроме этапа создания)
    if (!in_array($realStatus, ['creating', 'installing'])) {
        $actions[] = 'files';
        $actions[] = 'settings';
    }
    
    // Проверка статуса доступна всегда
    $actions[] = 'check_status';
    
    return $actions;
}

    // Расчет цены в зависимости от периода
    public function calculatePrice($period = 'monthly')
    {
        $plan = $this->plan;
        if (!$plan) return 0;

        switch ($period) {
            case 'monthly':
                return $plan->price_monthly;
            case 'quarterly':
                return $plan->price_quarterly ?? $plan->price_monthly * 3 * 0.9; // 10% скидка
            case 'half_year':
                return $plan->price_half_year ?? $plan->price_monthly * 6 * 0.85; // 15% скидка
            case 'yearly':
                return $plan->price_yearly ?? $plan->price_monthly * 12 * 0.8; // 20% скидка
            default:
                return $plan->price_monthly;
        }
    }

    // Проверить нужно ли обновлять ядро
    public function needsCoreUpdate()
    {
        if (!$this->core) {
            return false;
        }

        $latestCore = Core::where('game_type', $this->game_type)
                         ->where('name', $this->core_type)
                         ->where('is_active', true)
                         ->orderBy('version', 'desc')
                         ->first();

        return $latestCore && $latestCore->id !== $this->core_id;
    }

    // Получить информацию о сроке действия
    public function getExpirationInfo()
    {
        if (!$this->expires_at) {
            return ['text' => 'Бессрочно', 'color' => 'green'];
        }

        $now = now();
        $expires = $this->expires_at;

        if ($expires->isPast()) {
            return ['text' => 'Истек', 'color' => 'red'];
        }

        $days = $now->diffInDays($expires, false);

        if ($days <= 0) {
            $hours = $now->diffInHours($expires, false);
            return [
                'text' => 'Истекает через ' . $hours . ' ч.',
                'color' => $hours < 24 ? 'red' : 'orange'
            ];
        } elseif ($days <= 7) {
            return ['text' => 'Истекает через ' . $days . ' дн.', 'color' => 'orange'];
        } else {
            return ['text' => $expires->format('d.m.Y'), 'color' => 'green'];
        }
    }

    public function canBeStarted()
{
    // 1. Проверяем наличие ядра
    if (!$this->core_id) {
        \Log::info("Server {$this->id} cannot start: no core");
        return false;
    }
    
    // 2. Проверяем текущий статус (реальный)
    $currentStatus = $this->real_status;
    
    // 3. Сервер можно запустить только если он остановлен или выключен
    $allowedStatuses = ['stopped', 'offline', 'core_installed', 'failed'];
    
    $canStart = in_array($currentStatus, $allowedStatuses);
    
    \Log::info("Server {$this->id} canBeStarted check", [
        'current_status' => $currentStatus,
        'allowed_statuses' => $allowedStatuses,
        'result' => $canStart
    ]);
    
    return $canStart;
}

    /**
     * Проверка возможности остановки сервера
     */
    public function canBeStopped()
    {
        $status = $this->getRealStatusAttribute();
        return in_array($status, ['running', 'starting']);
    }

    /**
     * Проверка возможности перезагрузки сервера
     */
    public function canBeRestarted()
    {
        return $this->canBeStarted() && $this->canBeStopped();
    }

    /**
     * Получить реальный статус (из демона или из БД)
     */
    public function getRealStatusAttribute()
    {
        return $this->settings['real_status'] ?? $this->status;
    }

    /**
     * Сервер работает (реально)
     */
    public function getIsRunningAttribute()
    {
        return $this->getRealStatusAttribute() === 'running';
    }

    /**
     * Сервер остановлен (реально)
     */
    public function getIsStoppedAttribute()
    {
        $status = $this->getRealStatusAttribute();
        return in_array($status, ['stopped', 'offline', 'core_installed']);
    }

    /**
     * Управляется ли сервер демоном
     */
    public function getIsManagedByDaemonAttribute()
    {
        return $this->settings['daemon_managed'] ?? false;
    }

    /**
     * Получить PID процесса сервера
     */
    public function getProcessPidAttribute()
    {
        return $this->settings['process_pid'] ?? null;
    }

    /**
     * Время работы сервера (в секундах)
     */
    public function getUptimeSecondsAttribute()
    {
        if (!$this->is_running) {
            return 0;
        }
        
        // Если есть время старта от демона
        if (isset($this->settings['started_at_daemon'])) {
            try {
                $started = Carbon::parse($this->settings['started_at_daemon']);
                return now()->diffInSeconds($started);
            } catch (\Exception $e) {
                // fall through
            }
        }
        
        // Иначе используем поле started_at
        if ($this->started_at) {
            return now()->diffInSeconds($this->started_at);
        }
        
        return 0;
    }

    /**
     * Получить красивое время работы
     */
    public function getUptimeFormattedAttribute()
    {
        $seconds = $this->uptime_seconds;
        
        if ($seconds <= 0) {
            return 'Не запущен';
        }
        
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        
        if ($days > 0) {
            return $days . 'д ' . $hours . 'ч';
        } elseif ($hours > 0) {
            return $hours . 'ч ' . $minutes . 'м';
        } else {
            return $minutes . 'м';
        }
    }

    /**
     * Получить информацию о занятой памяти (если доступно)
     */
    public function getMemoryUsageAttribute()
    {
        if (!isset($this->settings['memory_usage'])) {
            return null;
        }
        
        $usage = $this->settings['memory_usage'];
        $allocated = $this->memory;
        
        if (is_numeric($usage)) {
            $percentage = ($usage / $allocated) * 100;
            
            return [
                'used' => round($usage, 1),
                'allocated' => $allocated,
                'percentage' => round($percentage, 1),
                'formatted' => round($usage, 1) . ' MB / ' . $allocated . ' MB',
                'color' => $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : 'success')
            ];
        }
        
        return null;
    }

    /**
     * Получить информацию о дисковом пространстве
     */
    public function getDiskUsageAttribute()
    {
        if (!isset($this->settings['disk_usage'])) {
            return null;
        }
        
        $usage = $this->settings['disk_usage'];
        $allocated = $this->disk_space;
        
        if (is_numeric($usage)) {
            $percentage = ($usage / $allocated) * 100;
            
            return [
                'used' => round($usage, 1),
                'allocated' => $allocated,
                'percentage' => round($percentage, 1),
                'formatted' => round($usage, 1) . ' MB / ' . $allocated . ' MB',
                'color' => $percentage > 90 ? 'danger' : ($percentage > 70 ? 'warning' : 'success')
            ];
        }
        
        return null;
    }

    /**
     * Получить URL для подключения к серверу
     */
    public function getConnectionUrlAttribute()
    {
        if ($this->ip_address && $this->port) {
            return $this->ip_address . ':' . $this->port;
        }
        
        return null;
    }

    /**
     * Получить информацию о демоне
     */
    public function getDaemonInfoAttribute()
    {
        $node = $this->node;
        if (!$node) {
            return null;
        }
        
        return [
            'ip' => $node->ip_address,
            'port' => $node->daemon_port,
            'url' => 'http://' . $node->ip_address . ':' . $node->daemon_port,
            'token_configured' => !empty($node->daemon_token),
            'servers_path' => $node->servers_path
        ];
    }

    /**
     * Получить последний ответ от демона
     */
    public function getLastDaemonResponseAttribute()
    {
        return $this->settings['last_status_response'] ?? 
            $this->settings['daemon_response'] ?? 
            $this->settings['last_action_response'] ?? null;
    }

    /**
     * Получить информацию о последней ошибке
     */
    public function getLastErrorAttribute()
    {
        return [
            'message' => $this->settings['last_error'] ?? null,
            'type' => $this->settings['error_type'] ?? null,
            'time' => isset($this->settings['last_error_at']) ? 
                    Carbon::parse($this->settings['last_error_at']) : null
        ];
    }

    /**
     * Получить расширенные доступные действия (с учетом реального статуса)
     */
    public function getExtendedAvailableActions()
{
    $actions = [];
    
    // Определяем реальный статус
    $realStatus = $this->real_status;
    
    // Логируем для отладки
    \Log::info("Getting actions for server {$this->id}", [
        'status' => $this->status,
        'real_status' => $realStatus,
        'core_id' => $this->core_id,
        'is_running' => $this->is_running
    ]);
    
    // Запуск
    if ($this->canBeStarted()) {
        $actions['start'] = [
            'title' => 'Запустить',
            'icon' => 'fa-play',
            'color' => 'success',
            'disabled' => false,
            'tooltip' => 'Запустить сервер',
            'url' => route('servers.start', $this)
        ];
    }
    
    // Остановка
    if ($this->canBeStopped()) {
        $actions['stop'] = [
            'title' => 'Остановить',
            'icon' => 'fa-stop',
            'color' => 'danger',
            'disabled' => false,
            'tooltip' => 'Остановить сервер',
            'url' => route('servers.stop', $this)
        ];
    }
    
    // Перезагрузка
    if ($this->canBeRestarted()) {
        $actions['restart'] = [
            'title' => 'Перезагрузить',
            'icon' => 'fa-redo',
            'color' => 'warning',
            'disabled' => false,
            'tooltip' => 'Перезагрузить сервер',
            'url' => route('servers.restart', $this)
        ];
    }
    
    // Проверка статуса (всегда доступна)
    $actions['check_status'] = [
        'title' => 'Обновить статус',
        'icon' => 'fa-sync',
        'color' => 'info',
        'disabled' => false,
        'tooltip' => 'Проверить реальный статус',
        'url' => 'javascript:void(0)',
        'onclick' => 'checkServerStatus()'
    ];
    
    // Консоль (если сервер работает)
    if ($this->is_running) {
        $actions['console'] = [
            'title' => 'Консоль',
            'icon' => 'fa-terminal',
            'color' => 'primary',
            'disabled' => false,
            'tooltip' => 'Открыть консоль сервера',
            'url' => route('servers.console', $this)
        ];
    }
    
    // Файлы (всегда доступны)
    $actions['files'] = [
        'title' => 'Файлы',
        'icon' => 'fa-folder',
        'color' => 'secondary',
        'disabled' => false,
        'tooltip' => 'Управление файлами сервера',
        'url' => route('servers.files', $this)
    ];
    
    // Настройки (всегда доступны)
    $actions['settings'] = [
        'title' => 'Настройки',
        'icon' => 'fa-cog',
        'color' => 'secondary',
        'disabled' => false,
        'tooltip' => 'Настройки сервера',
        'url' => route('servers.settings', $this)
    ];
    
    return $actions;
}

    /**
     * Получить статусную информацию для отображения
     */
    public function getStatusInfoAttribute()
    {
        $realStatus = $this->real_status;
        $dbStatus = $this->status;
        
        $statusInfo = [
            'real_status' => $realStatus,
            'db_status' => $dbStatus,
            'icon' => $this->getStatusIcon(),
            'color' => $this->getStatusColor(),
            'text' => $this->getStatusText(),
            'is_synced' => $realStatus === $dbStatus || $realStatus === null,
            'needs_check' => !$this->is_synced || !isset($this->settings['last_status_check']),
            'last_check' => isset($this->settings['last_status_check']) ? 
                        Carbon::parse($this->settings['last_status_check'])->diffForHumans() : 'никогда'
        ];
        
        return $statusInfo;
    }

    /**
     * Получить текстовое описание статуса
     */
    public function getStatusText()
    {
        $statusTexts = [
            'creating' => 'Создание...',
            'core_installing' => 'Установка ядра...',
            'core_installed' => 'Ядро установлено',
            'installing' => 'Установка...',
            'active' => 'Активен',
            'running' => 'Запущен',
            'stopped' => 'Остановлен',
            'starting' => 'Запуск...',
            'stopping' => 'Остановка...',
            'restarting' => 'Перезагрузка...',
            'failed' => 'Ошибка',
            'suspended' => 'Приостановлен',
            'daemon_offline' => 'Демон недоступен',
            'core_install_failed' => 'Ошибка установки ядра',
            'daemon_unavailable' => 'Демон не отвечает',
            'unknown' => 'Неизвестно',
            'offline' => 'Выключен'
        ];
        
        return $statusTexts[$this->real_status] ?? $statusTexts[$this->status] ?? 'Неизвестно';
    }

    /**
     * Обновить реальный статус сервера
     */
    public function updateRealStatus($status, $data = [])
    {
        $settings = $this->settings ?? [];
        
        $updateData = [
            'settings' => array_merge($settings, [
                'real_status' => $status,
                'last_status_check' => now()->toDateTimeString(),
                'last_status_response' => $data
            ])
        ];
        
        // Если статус отличается от сохраненного, обновляем
        if ($status !== $this->status && 
            !in_array($status, ['unknown', 'daemon_unavailable'])) {
            $updateData['status'] = $status;
            
            // Обновляем временные метки
            if ($status === 'running') {
                $updateData['started_at'] = now();
                $updateData['stopped_at'] = null;
            } elseif ($status === 'stopped') {
                $updateData['stopped_at'] = now();
            }
        }
        
        // Сохраняем дополнительные данные
        if (isset($data['pid'])) {
            $updateData['settings']['process_pid'] = $data['pid'];
        }
        if (isset($data['managed_by_daemon'])) {
            $updateData['settings']['daemon_managed'] = $data['managed_by_daemon'];
        }
        if (isset($data['uptime_seconds'])) {
            $updateData['settings']['started_at_daemon'] = 
                now()->subSeconds($data['uptime_seconds'])->toDateTimeString();
        }
        
        $this->update($updateData);
        
        return $this;
    }
    public function getRconPortAttribute()
{
    return $this->settings['rcon_port'] ?? ($this->port + 1000);
}

    public function getRconEnabledAttribute()
    {
    return $this->settings['rcon_enabled'] ?? true;
}
}

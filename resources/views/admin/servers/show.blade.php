@extends('layouts.admin-app')

@section('title', 'Управление сервером: ' . $server->name)

@section('content')
<div class="container-fluid">
    <!-- Заголовок страницы -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-server mr-2"></i>Управление сервером: {{ $server->name }}
        </h1>
        <div>
            <a href="{{ route('admin.servers') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Назад к списку
            </a>
        </div>
    </div>

    <!-- Уведомления -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Навигация по вкладкам -->
    <div class="row">
        <div class="col-lg-3 mb-4">
            <!-- Карточка сервера -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Информация о сервере</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-server fa-4x text-primary mb-3"></i>
                        <h4>{{ $server->name }}</h4>
                        <p class="text-muted">ID: #{{ $server->id }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Статус</h6>
                        @switch($server->status)
                            @case('active')
                                <span class="badge bg-success">
                                    <i class="fas fa-play-circle me-1"></i>Активен
                                </span>
                                @break
                            @case('stopped')
                                <span class="badge bg-warning">
                                    <i class="fas fa-stop-circle me-1"></i>Остановлен
                                </span>
                                @break
                            @case('suspended')
                                <span class="badge bg-danger">
                                    <i class="fas fa-ban me-1"></i>Заблокирован
                                </span>
                                @break
                            @case('creating')
                            @case('installing')
                                <span class="badge bg-info">
                                    <i class="fas fa-spinner fa-spin me-1"></i>Создается
                                </span>
                                @break
                            @default
                                <span class="badge bg-secondary">{{ $server->status }}</span>
                        @endswitch
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Владелец</h6>
                        <div class="d-flex align-items-center">
                            @if($server->user->avatar)
                                <img src="{{ asset('storage/' . $server->user->avatar) }}" 
                                     alt="{{ $server->user->name }}" 
                                     class="rounded-circle me-2" width="30" height="30">
                            @else
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" 
                                     style="width: 30px; height: 30px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($server->user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('admin.users.edit', $server->user) }}" class="text-decoration-none">
                                    {{ $server->user->name }}
                                </a>
                                <div class="text-muted small">
                                    ID: {{ $server->user->id }}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Ресурсы</h6>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Память:</span>
                            <strong>{{ $server->memory }} MB</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Диск:</span>
                            <strong>{{ $server->disk_space }} MB</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Слоты:</span>
                            <strong>{{ $server->player_slots }}</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Подключение</h6>
                        <div class="mb-1">
                            <i class="fas fa-network-wired me-1"></i>
                            <span>{{ $server->node->name ?? 'Не назначена' }}</span>
                        </div>
                        @if($server->ip_address && $server->port)
                            <div class="mb-1">
                                <i class="fas fa-globe me-1"></i>
                                <code>{{ $server->ip_address }}:{{ $server->port }}</code>
                            </div>
                            <div class="text-center mt-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="copyToClipboard('{{ $server->ip_address }}:{{ $server->port }}')">
                                    <i class="fas fa-copy me-1"></i>Копировать IP
                                </button>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Срок действия</h6>
                        <div class="mb-1">
                            <i class="fas fa-calendar me-1"></i>
                            <span>Создан: {{ $server->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        @if($server->expires_at)
                            <div class="mb-1">
                                <i class="fas fa-clock me-1"></i>
                                <span>Истекает: {{ $server->expires_at->format('d.m.Y H:i') }}</span>
                            </div>
                            <div class="mb-1">
                                <i class="fas fa-hourglass-half me-1"></i>
                                <span>Осталось: {{ $server->expires_at->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Быстрые действия -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Быстрые действия</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($server->status == 'active')
    <!-- Форма остановки -->
    <form action="{{ route('admin.servers.manage', $server) }}" method="POST" class="d-grid">
        @csrf
        <input type="hidden" name="action" value="stop">
        <button type="submit" class="btn btn-warning">
            <i class="fas fa-stop me-2"></i>Остановить сервер
        </button>
    </form>
    
    <!-- Форма перезагрузки -->
    <form action="{{ route('admin.servers.manage', $server) }}" method="POST" class="d-grid">
        @csrf
        <input type="hidden" name="action" value="restart">
        <button type="submit" class="btn btn-info">
            <i class="fas fa-redo me-2"></i>Перезагрузить
        </button>
    </form>
    
@elseif($server->status == 'stopped')
    <!-- Форма запуска -->
    <form action="{{ route('admin.servers.manage', $server) }}" method="POST" class="d-grid">
        @csrf
        <input type="hidden" name="action" value="start">
        <button type="submit" class="btn btn-success">
            <i class="fas fa-play me-2"></i>Запустить сервер
        </button>
    </form>
    
@elseif($server->status == 'suspended')
    <!-- Форма разблокировки -->
    <form action="{{ route('admin.servers.manage', $server) }}" method="POST" class="d-grid">
        @csrf
        <input type="hidden" name="action" value="unsuspend">
        <button type="submit" class="btn btn-success">
            <i class="fas fa-unlock me-2"></i>Разблокировать
        </button>
    </form>
    
@else
    <button class="btn btn-secondary" disabled>
        <i class="fas fa-info-circle me-2"></i>Ожидание создания
    </button>
@endif
                        
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reinstallModal">
                            <i class="fas fa-redo-alt me-2"></i>Переустановить
                        </button>
                        
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#migrateModal">
                            <i class="fas fa-exchange-alt me-2"></i>Перенести на другую ноду
                        </button>
                        
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash me-2"></i>Удалить сервер
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Статистика -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Статистика</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Бэкапы</h6>
                        <div class="d-flex justify-content-between">
                            <span>Всего бэкапов:</span>
                            <strong>{{ $stats['totalBackups'] ?? 0 }}</strong>
                        </div>
                        @if($stats['lastBackup'])
                            <div class="d-flex justify-content-between mt-1">
                                <span>Последний:</span>
                                <span class="text-muted">{{ $stats['lastBackup']->created_at->diffForHumans() }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Даты</h6>
                        <div class="d-flex justify-content-between">
                            <span>Создан:</span>
                            <span class="text-muted">{{ $server->created_at->format('d.m.Y') }}</span>
                        </div>
                        @if($server->started_at)
                            <div class="d-flex justify-content-between mt-1">
                                <span>Запущен:</span>
                                <span class="text-muted">{{ $server->started_at->format('d.m.Y H:i') }}</span>
                            </div>
                        @endif
                        @if($server->stopped_at)
                            <div class="d-flex justify-content-between mt-1">
                                <span>Остановлен:</span>
                                <span class="text-muted">{{ $server->stopped_at->format('d.m.Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">Последняя активность</h6>
                        <div class="text-center">
                            <span class="badge bg-info">{{ $stats['lastActivity'] ?? 'Нет данных' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Основной контент -->
        <div class="col-lg-9">
            <!-- Навигация по вкладкам -->
            <ul class="nav nav-tabs mb-4" id="serverTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview" type="button">
                        <i class="fas fa-info-circle me-2"></i>Обзор
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings" type="button">
                        <i class="fas fa-cog me-2"></i>Настройки
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="backups-tab" data-bs-toggle="tab" data-bs-target="#backups" type="button">
                        <i class="fas fa-hdd me-2"></i>Бэкапы
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="console-tab" data-bs-toggle="tab" data-bs-target="#console" type="button">
                        <i class="fas fa-terminal me-2"></i>Консоль
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button">
                        <i class="fas fa-file-alt me-2"></i>Логи
                    </button>
                </li>
            </ul>
            
            <!-- Содержимое вкладок -->
            <div class="tab-content" id="serverTabsContent">
                <!-- Вкладка Обзор -->
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Основная информация</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                <td width="40%"><strong>ID сервера:</strong></td>
                                                <td>#{{ $server->id }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>UUID:</strong></td>
                                                <td><code>{{ $server->uuid }}</code></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Владелец:</strong></td>
                                                <td>
                                                    <a href="{{ route('admin.users.edit', $server->user) }}" class="text-decoration-none">
                                                        {{ $server->user->name }} (ID: {{ $server->user->id }})
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Тариф:</strong></td>
                                                <td>{{ $server->plan->name ?? 'Не указан' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Тип игры:</strong></td>
                                                <td>
                                                    @if($server->game_type == 'java')
                                                        <span class="badge bg-primary">Java Edition</span>
                                                    @elseif($server->game_type == 'bedrock')
                                                        <span class="badge bg-success">Bedrock Edition</span>
                                                    @else
                                                        {{ $server->game_type }}
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Версия:</strong></td>
                                                <td>{{ $server->core_version ?? 'Не указана' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Ядро:</strong></td>
                                                <td>{{ $server->core_type ?? 'Paper' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card shadow h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Ресурсы</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-6 mb-3">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body">
                                                    <i class="fas fa-memory fa-2x mb-2"></i>
                                                    <h5>{{ $server->memory }} MB</h5>
                                                    <p class="mb-0">Память</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card bg-success text-white">
                                                <div class="card-body">
                                                    <i class="fas fa-hdd fa-2x mb-2"></i>
                                                    <h5>{{ $server->disk_space }} MB</h5>
                                                    <p class="mb-0">Дисковое пространство</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card bg-info text-white">
                                                <div class="card-body">
                                                    <i class="fas fa-users fa-2x mb-2"></i>
                                                    <h5>{{ $server->player_slots }}</h5>
                                                    <p class="mb-0">Слотов игроков</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="card bg-warning text-white">
                                                <div class="card-body">
                                                    <i class="fas fa-microchip fa-2x mb-2"></i>
                                                    <h5>100%</h5>
                                                    <p class="mb-0">CPU Лимит</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-12 mb-4">
                            <div class="card shadow">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Информация от демона</h6>
                                </div>
                                <div class="card-body">
                                    @if($daemonInfo)
                                        <div class="alert alert-info">
                                            <h6><i class="fas fa-robot me-2"></i>Информация от серверного демона</h6>
                                            <pre class="mt-2 mb-0">{{ json_encode($daemonInfo, JSON_PRETTY_PRINT) }}</pre>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            Нет информации от серверного демона
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Вкладка Настройки -->
                <div class="tab-pane fade" id="settings" role="tabpanel">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Настройки сервера</h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('servers.settings.update', $server) }}" method="POST">
                                @csrf
                                
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="server_name" class="form-label">Название сервера</label>
                                        <input type="text" class="form-control" id="server_name" name="name" value="{{ $server->name }}">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="server_port" class="form-label">Порт</label>
                                        <input type="number" class="form-control" id="server_port" name="port" 
                                               value="{{ $server->port }}" min="1024" max="65535">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="server_memory" class="form-label">Память (MB)</label>
                                        <input type="number" class="form-control" id="server_memory" name="memory" 
                                               value="{{ $server->memory }}" min="512" max="16384">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="server_disk" class="form-label">Диск (MB)</label>
                                        <input type="number" class="form-control" id="server_disk" name="disk" 
                                               value="{{ $server->disk_space }}" min="1024" max="102400">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="server_slots" class="form-label">Слотов игроков</label>
                                        <input type="number" class="form-control" id="server_slots" name="player_slots" 
                                               value="{{ $server->player_slots }}" min="1" max="100">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="server_version" class="form-label">Версия Minecraft</label>
                                        <input type="text" class="form-control" id="server_version" name="version" 
                                               value="{{ $server->core_version }}">
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label for="server_description" class="form-label">Описание</label>
                                        <textarea class="form-control" id="server_description" name="description" rows="3">{{ $server->description ?? '' }}</textarea>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between">
                                    <button type="reset" class="btn btn-secondary">Сбросить</button>
                                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Вкладка Бэкапы -->
                <div class="tab-pane fade" id="backups" role="tabpanel">
                    <div class="card shadow">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Управление бэкапами</h6>
                            <form action="{{ route('servers.backup', $server) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Создать бэкап
                                </button>
                            </form>
                        </div>
                        <div class="card-body">
                            @if($server->backups->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Название</th>
                                                <th>Размер</th>
                                                <th>Дата создания</th>
                                                <th>Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($server->backups as $backup)
                                            <tr>
                                                <td>#{{ $backup->id }}</td>
                                                <td>{{ $backup->name }}</td>
                                                <td>{{ number_format($backup->size / 1024 / 1024, 2) }} MB</td>
                                                <td>{{ $backup->created_at->format('d.m.Y H:i') }}</td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="{{ route('admin.backups.show', $backup) }}" class="btn btn-info" title="Просмотр">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <form action="{{ route('backups.restore', $backup) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning" title="Восстановить">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.backups.delete', $backup) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            
                                                            <button type="submit" class="btn btn-danger" title="Удалить" onclick="return confirm('Удалить бэкап?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-hdd fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">Бэкапы не найдены</h5>
                                    <p class="text-muted">Создайте первый бэкап для этого сервера</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Вкладка Консоль -->
                <div class="tab-pane fade" id="console" role="tabpanel">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Консоль сервера</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Отправка команд доступна только для активных серверов
                            </div>
                            
                            @if($server->status == 'active')
                                <form action="{{ route('servers.command', $server) }}" method="POST" id="consoleForm">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="server_command" class="form-label">Команда</label>
                                        <div class="input-group">
                                            <span class="input-group-text">/</span>
                                            <input type="text" class="form-control" id="server_command" name="command" 
                                                   placeholder="help" required>
                                            <button type="submit" class="btn btn-primary">Отправить</button>
                                        </div>
                                    </div>
                                </form>
                                
                                <div class="mb-3">
                                    <h6>Быстрые команды:</h6>
                                    <div class="btn-group flex-wrap" role="group">
                                        <button type="button" class="btn btn-outline-secondary mb-1" onclick="setCommand('list')">
                                            list
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary mb-1" onclick="setCommand('say Привет игрокам!')">
                                            say
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary mb-1" onclick="setCommand('time set day')">
                                            time set day
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary mb-1" onclick="setCommand('weather clear')">
                                            weather clear
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary mb-1" onclick="setCommand('gamemode survival')">
                                            gamemode
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary mb-1" onclick="setCommand('stop')">
                                            stop
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-header bg-dark text-white">
                                        <h6 class="mb-0">Лог консоли</h6>
                                    </div>
                                    <div class="card-body bg-dark text-white" style="height: 300px; overflow-y: auto;">
                                        <pre id="consoleLog" class="mb-0 text-success">[{{ now()->format('H:i:s') }}] Консоль готова к использованию</pre>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Сервер не активен. Запустите сервер для использования консоли.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Вкладка Логи -->
                <div class="tab-pane fade" id="logs" role="tabpanel">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Логи сервера</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-4">
                                <i class="fas fa-info-circle me-2"></i>
                                Просмотр логов доступен через файловый менеджер
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-file-alt fa-3x text-primary mb-3"></i>
                                            <h5>Основной лог</h5>
                                            <p class="text-muted">server.log</p>
                                            <a href="{{ route('servers.files', $server) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-folder-open me-2"></i>Открыть в файловом менеджере
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                            <h5>Лог ошибок</h5>
                                            <p class="text-muted">logs/latest.log</p>
                                            <a href="{{ route('servers.files', $server) }}" class="btn btn-outline-warning">
                                                <i class="fas fa-folder-open me-2"></i>Открыть в файловом менеджере
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальные окна -->
@include('admin.servers.modals.suspend')
@include('admin.servers.modals.reinstall')
@include('admin.servers.modals.migrate')
@include('admin.servers.modals.delete')

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('IP адрес скопирован: ' + text);
        });
    }
    
    function setCommand(command) {
        document.getElementById('server_command').value = command;
    }
    
    // Обновление консоли
    @if($server->status == 'active')
        function updateConsoleLog() {
            // Здесь должна быть логика получения логов через AJAX
            // fetch('/api/v1/servers/{{ $server->id }}/console')
            //     .then(response => response.json())
            //     .then(data => {
            //         document.getElementById('consoleLog').textContent = data.log;
            //     });
        }
        
        // Обновляем консоль каждые 5 секунд
        // setInterval(updateConsoleLog, 5000);
    @endif
    
    // Подтверждение действий
    document.querySelectorAll('form').forEach(form => {
        if (form.querySelector('button[type="submit"]')?.classList.contains('btn-danger')) {
            form.addEventListener('submit', function(e) {
                if (!confirm('Вы уверены, что хотите выполнить это действие?')) {
                    e.preventDefault();
                }
            });
        }
    });
</script>
@endpush

<style>
    .nav-tabs .nav-link {
        border: 1px solid transparent;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
        padding: 0.75rem 1.25rem;
    }
    
    .nav-tabs .nav-link.active {
        color: #4e73df;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }
    
    .tab-content {
        padding: 20px;
        border: 1px solid #dee2e6;
        border-top: none;
        border-bottom-left-radius: 0.25rem;
        border-bottom-right-radius: 0.25rem;
    }
    
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        font-family: 'Courier New', monospace;
        font-size: 0.9rem;
    }
</style>
@endsection

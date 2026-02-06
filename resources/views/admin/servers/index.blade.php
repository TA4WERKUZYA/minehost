@extends('layouts.admin-app')

@section('title', 'Управление серверами')

@section('content')
<div class="container-fluid">
    <!-- Заголовок страницы -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-server mr-2"></i>Управление серверами
        </h1>
        <div class="d-flex">
            <button type="button" class="btn btn-primary mr-2" data-bs-toggle="modal" data-bs-target="#createServerModal">
                <i class="fas fa-plus mr-2"></i>Создать сервер
            </button>
            <a href="#" class="btn btn-outline-secondary" id="exportServersBtn">
                <i class="fas fa-download mr-2"></i>Экспорт
            </a>
        </div>
    </div>

    <!-- Статистика -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Всего серверов
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalServers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-server fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Активных
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeServers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-play-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Остановленных
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stoppedServers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-stop-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Заблокированных
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $suspendedServers }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Фильтры и поиск -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Фильтры и поиск</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.servers') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Поиск</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="ID, имя, IP..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <div class="col-md-2">
                    <label for="status" class="form-label">Статус</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Все</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Активные</option>
                        <option value="stopped" {{ request('status') == 'stopped' ? 'selected' : '' }}>Остановленные</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Заблокированные</option>
                        <option value="creating" {{ request('status') == 'creating' ? 'selected' : '' }}>Создающиеся</option>
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="node_id" class="form-label">Нода</label>
                    <select class="form-select" id="node_id" name="node_id">
                        <option value="">Все ноды</option>
                        @foreach($nodes as $node)
                            <option value="{{ $node->id }}" {{ request('node_id') == $node->id ? 'selected' : '' }}>
                                {{ $node->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-2">
                    <label for="user_id" class="form-label">Пользователь</label>
                    <select class="form-select" id="user_id" name="user_id">
                        <option value="">Все пользователи</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="sort" class="form-label">Сортировка</label>
                    <select class="form-select" id="sort" name="sort">
                        <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Новые</option>
                        <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Старые</option>
                        <option value="memory_desc" {{ request('sort') == 'memory_desc' ? 'selected' : '' }}>Память ↓</option>
                        <option value="memory_asc" {{ request('sort') == 'memory_asc' ? 'selected' : '' }}>Память ↑</option>
                        <option value="disk_desc" {{ request('sort') == 'disk_desc' ? 'selected' : '' }}>Диск ↓</option>
                        <option value="disk_asc" {{ request('sort') == 'disk_asc' ? 'selected' : '' }}>Диск ↑</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <div class="d-flex justify-content-between">
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search mr-2"></i>Применить
                            </button>
                            <a href="{{ route('admin.servers') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-redo mr-2"></i>Сбросить
                            </a>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showExpired" name="show_expired" 
                                   {{ request('show_expired') ? 'checked' : '' }}>
                            <label class="form-check-label" for="showExpired">
                                Показать истекшие
                            </label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Таблица серверов -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Список серверов</h6>
            <div class="text-muted">
                Показано {{ $servers->firstItem() ?? 0 }}-{{ $servers->lastItem() ?? 0 }} из {{ $servers->total() ?? 0 }}
            </div>
        </div>
        <div class="card-body">
            @if($servers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="serversTable">
                        <thead class="thead-light">
                            <tr>
                                <th width="60">ID</th>
                                <th>Сервер</th>
                                <th>Владелец</th>
                                <th>Нода / IP</th>
                                <th>Ресурсы</th>
                                <th>Статус</th>
                                <th>Дата создания</th>
                                <th width="140">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servers as $server)
                                <tr class="{{ $server->status == 'suspended' ? 'table-danger' : ($server->status == 'stopped' ? 'table-warning' : '') }}">
                                    <td class="text-center">
                                        <strong>#{{ $server->id }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="server-icon me-3">
                                                <i class="fas fa-server fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <strong>{{ $server->name }}</strong>
                                                <div class="text-muted small">
                                                    @if($server->plan)
                                                        <span class="badge bg-info">{{ $server->plan->name }}</span>
                                                    @endif
                                                    @if($server->game_type)
                                                        <span class="badge bg-secondary ms-1">{{ $server->game_type }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            <i class="fas fa-network-wired text-muted me-1"></i>
                                            <strong>{{ $server->node->name ?? 'Не назначена' }}</strong>
                                        </div>
                                        <div class="text-muted small">
                                            @if($server->ip_address && $server->port)
                                                <i class="fas fa-globe me-1"></i>
                                                {{ $server->ip_address }}:{{ $server->port }}
                                            @else
                                                <span class="text-warning">IP не назначен</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <div class="mb-1">
                                                <i class="fas fa-memory text-muted me-1"></i>
                                                <span class="badge bg-info">{{ $server->memory }} MB</span>
                                            </div>
                                            <div class="mb-1">
                                                <i class="fas fa-hdd text-muted me-1"></i>
                                                <span class="badge bg-secondary">{{ $server->disk }} MB</span>
                                            </div>
                                            <div>
                                                <i class="fas fa-microchip text-muted me-1"></i>
                                                <span class="badge bg-dark">{{ $server->cpu }}%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
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
                                                <span class="badge bg-info">
                                                    <i class="fas fa-spinner fa-spin me-1"></i>Создается
                                                </span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $server->status }}</span>
                                        @endswitch
                                        
                                        @if($server->expires_at && $server->expires_at->isPast())
                                            <div class="mt-1">
                                                <span class="badge bg-dark">
                                                    <i class="fas fa-clock me-1"></i>Истек
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $server->created_at->format('d.m.Y H:i') }}
                                        <div class="text-muted small">
                                            {{ $server->created_at->diffForHumans() }}
                                        </div>
                                        @if($server->expires_at)
                                            <div class="text-muted small mt-1">
                                                <i class="fas fa-calendar me-1"></i>
                                                До: {{ $server->expires_at->format('d.m.Y') }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-info" 
                                                    data-bs-toggle="modal" data-bs-target="#viewServerModal{{ $server->id }}"
                                                    title="Просмотр">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            <a href="{{ route('admin.servers.show', $server) }}" 
                                               class="btn btn-primary" title="Управление">
                                                <i class="fas fa-cog"></i>
                                            </a>
                                            
                                            @if($server->status == 'active')
                                                <form action="{{ route('servers.stop', $server) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-warning" title="Остановить">
                                                        <i class="fas fa-stop"></i>
                                                    </button>
                                                </form>
                                            @elseif($server->status == 'stopped')
                                                <form action="{{ route('servers.start', $server) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success" title="Запустить">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            @if($server->status == 'suspended')
                                                <form action="{{ route('admin.servers.manage', $server) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <input type="hidden" name="action" value="unsuspend">
                                                    <button type="submit" class="btn btn-success" title="Разблокировать">
                                                        <i class="fas fa-unlock"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" class="btn btn-danger"
                                                        onclick="if(confirm('Заблокировать сервер?')) document.getElementById('suspend-form-{{ $server->id }}').submit()"
                                                        title="Заблокировать">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                                <form id="suspend-form-{{ $server->id }}" 
                                                      action="{{ route('admin.servers.manage', $server) }}" 
                                                      method="POST" class="d-none">
                                                    @csrf
                                                    <input type="hidden" name="action" value="suspend">
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>

                                <!-- Модальное окно просмотра сервера -->
                                <div class="modal fade" id="viewServerModal{{ $server->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Информация о сервере #{{ $server->id }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6 class="text-muted mb-3">Основная информация</h6>
                                                        <div class="mb-3">
                                                            <strong>Название:</strong> {{ $server->name }}
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Идентификатор:</strong> {{ $server->uuid }}
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Владелец:</strong> 
                                                            <a href="{{ route('admin.users.edit', $server->user) }}" class="text-decoration-none">
                                                                {{ $server->user->name }} (ID: {{ $server->user->id }})
                                                            </a>
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Тариф:</strong> {{ $server->plan->name ?? 'Не указан' }}
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Тип игры:</strong> {{ $server->game_type ?? 'Java' }}
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <h6 class="text-muted mb-3">Ресурсы</h6>
                                                        <div class="mb-3">
                                                            <strong>Память:</strong> {{ $server->memory }} MB
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Диск:</strong> {{ $server->disk }} MB
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>CPU:</strong> {{ $server->cpu }}%
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Нода:</strong> {{ $server->node->name ?? 'Не назначена' }}
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>IP:Порт:</strong> 
                                                            @if($server->ip_address && $server->port)
                                                                {{ $server->ip_address }}:{{ $server->port }}
                                                            @else
                                                                <span class="text-warning">Не назначен</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <h6 class="text-muted mb-3">Статус</h6>
                                                        <div class="mb-3">
                                                            <strong>Статус:</strong>
                                                            @switch($server->status)
                                                                @case('active')
                                                                    <span class="badge bg-success">Активен</span>
                                                                    @break
                                                                @case('stopped')
                                                                    <span class="badge bg-warning">Остановлен</span>
                                                                    @break
                                                                @case('suspended')
                                                                    <span class="badge bg-danger">Заблокирован</span>
                                                                    @break
                                                                @default
                                                                    <span class="badge bg-secondary">{{ $server->status }}</span>
                                                            @endswitch
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Создан:</strong> {{ $server->created_at->format('d.m.Y H:i:s') }}
                                                        </div>
                                                        <div class="mb-3">
                                                            <strong>Обновлен:</strong> {{ $server->updated_at->format('d.m.Y H:i:s') }}
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="col-md-6">
                                                        <h6 class="text-muted mb-3">Срок действия</h6>
                                                        <div class="mb-3">
                                                            <strong>Создан:</strong> {{ $server->created_at->format('d.m.Y H:i') }}
                                                        </div>
                                                        @if($server->expires_at)
                                                            <div class="mb-3">
                                                                <strong>Истекает:</strong> {{ $server->expires_at->format('d.m.Y H:i') }}
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Осталось:</strong> 
                                                                @if($server->expires_at->isPast())
                                                                    <span class="text-danger">Истек</span>
                                                                @else
                                                                    {{ $server->expires_at->diffForHumans() }}
                                                                @endif
                                                            </div>
                                                        @else
                                                            <div class="mb-3">
                                                                <strong>Срок:</strong> <span class="text-success">Бессрочно</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
                                                <a href="{{ route('admin.servers.show', $server) }}" class="btn btn-primary">
                                                    <i class="fas fa-cog me-2"></i>Управление
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Пагинация -->
                @if($servers->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Показано {{ $servers->firstItem() }} - {{ $servers->lastItem() }} из {{ $servers->total() }}
                        </div>
                        {{ $servers->links() }}
                    </div>
                @endif
            @else
                <div class="text-center py-5">
                    <i class="fas fa-server fa-4x text-muted mb-3"></i>
                    <h5 class="text-muted">Серверы не найдены</h5>
                    <p class="text-muted">Попробуйте изменить параметры поиска или создайте первый сервер</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Модальное окно создания сервера -->
<div class="modal fade" id="createServerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Создание сервера</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.servers.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="server_name" class="form-label">Название сервера *</label>
                            <input type="text" class="form-control" id="server_name" name="name" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="server_user" class="form-label">Владелец *</label>
                            <select class="form-select" id="server_user" name="user_id" required>
                                <option value="">Выберите пользователя</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="server_plan" class="form-label">Тариф *</label>
                            <select class="form-select" id="server_plan" name="plan_id" required>
                                <option value="">Выберите тариф</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->memory }}MB / {{ $plan->disk }}MB)</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="server_node" class="form-label">Нода *</label>
                            <select class="form-select" id="server_node" name="node_id" required>
                                <option value="">Выберите ноду</option>
                                @foreach($nodes as $node)
                                    @if($node->is_active && $node->accept_new_servers)
                                        <option value="{{ $node->id }}">{{ $node->name }} ({{ $node->location }})</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="server_game_type" class="form-label">Тип игры</label>
                            <select class="form-select" id="server_game_type" name="game_type">
                                <option value="java">Java Edition</option>
                                <option value="bedrock">Bedrock Edition</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="server_version" class="form-label">Версия</label>
                            <input type="text" class="form-control" id="server_version" name="version" value="1.20.4">
                        </div>
                        
                        <div class="col-12 mb-3">
                            <label for="server_description" class="form-label">Описание</label>
                            <textarea class="form-control" id="server_description" name="description" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать сервер</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .server-icon i {
        transition: transform 0.3s;
    }
    .server-icon:hover i {
        transform: scale(1.1);
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.025);
    }
    .badge {
        font-size: 0.85em;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Очистка поиска
        document.getElementById('clearSearch')?.addEventListener('click', function() {
            document.getElementById('search').value = '';
            document.querySelector('form').submit();
        });

        // Экспорт серверов
        document.getElementById('exportServersBtn')?.addEventListener('click', function(e) {
            e.preventDefault();
            const params = new URLSearchParams(window.location.search);
            window.location.href = '/admin/servers/export?' + params.toString();
        });

        // Автоматическая отправка формы при изменении некоторых фильтров
        ['status', 'node_id', 'user_id', 'sort'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', function() {
                this.form.submit();
            });
        });

        // Подтверждение действий
        document.querySelectorAll('.btn-danger').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm('Вы уверены?')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@endpush

@endsection
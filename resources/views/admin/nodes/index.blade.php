@extends('layouts.admin-app')

@section('title', 'Управление нодами')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-1 text-gray-900">Управление нодами</h1>
            <p class="text-muted mb-0">Физические серверы для размещения Minecraft серверов</p>
        </div>
        <a href="{{ route('admin.nodes.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Добавить ноду
        </a>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="alert-heading mb-1">Успешно!</h5>
                    <p class="mb-0">{{ session('success') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="alert-heading mb-1">Ошибка!</h5>
                    <p class="mb-0">{{ session('error') }}</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-server me-2 text-primary"></i>Всего нод
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $nodes->count() }}</h2>
                        </div>
                        <div class="icon-circle bg-primary-light">
                            <i class="fas fa-server text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-check-circle me-2 text-success"></i>Активных нод
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $nodes->where('is_active', true)->count() }}</h2>
                        </div>
                        <div class="icon-circle bg-success-light">
                            <i class="fas fa-check-circle text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-microchip me-2 text-warning"></i>Всего серверов
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $nodes->sum('servers_count') }}</h2>
                        </div>
                        <div class="icon-circle bg-warning-light">
                            <i class="fas fa-microchip text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-globe-europe me-2 text-info"></i>Локаций
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $nodes->unique('location')->count() }}</h2>
                        </div>
                        <div class="icon-circle bg-info-light">
                            <i class="fas fa-globe-europe text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 py-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2 text-primary"></i>Список нод
                </h5>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm filter-btn active" data-filter="all">
                        Все ноды
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm filter-btn" data-filter="active">
                        Активные
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm filter-btn" data-filter="accepting">
                        Принимают
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="nodesTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4" style="width: 80px;">ID</th>
                            <th class="border-0">Нода</th>
                            <th class="border-0">Локация</th>
                            <th class="border-0">IP и порты</th>
                            <th class="border-0">Серверов</th>
                            <th class="border-0">Статус</th>
                            <th class="border-0 text-end pe-4" style="width: 150px;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($nodes as $node)
                            <tr class="node-row" data-node-id="{{ $node->id }}" 
                                data-is-active="{{ $node->is_active ? 'true' : 'false' }}"
                                data-accepts="{{ $node->accept_new_servers ? 'true' : 'false' }}">
                                <td class="ps-4">
                                    <div class="fw-bold text-primary">#{{ $node->id }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="node-icon me-3">
                                            <div class="icon-circle-sm {{ $node->is_active ? 'bg-success-light' : 'bg-danger-light' }}">
                                                <i class="fas fa-server {{ $node->is_active ? 'text-success' : 'text-danger' }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 fw-bold text-gray-900">{{ $node->name }}</h6>
                                                @if($node->is_default)
                                                    <span class="badge bg-primary-gradient ms-2">
                                                        <i class="fas fa-star me-1"></i>Основная
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-network-wired me-1"></i>{{ $node->hostname }}
                                            </div>
                                            <div class="mt-1">
                                                @if($node->is_cluster)
                                                    <span class="badge bg-info-gradient">
                                                        <i class="fas fa-cluster me-1"></i>Кластер
                                                    </span>
                                                @endif
                                                @if($node->is_sftp_enabled)
                                                    <span class="badge bg-secondary-gradient ms-1">
                                                        <i class="fas fa-file-upload me-1"></i>SFTP
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="location-info">
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <i class="fas fa-map-marker-alt text-danger"></i>
                                            </div>
                                            <span class="fw-medium">{{ $node->location }}</span>
                                        </div>
                                        <div class="text-muted small mt-1">
                                            <i class="fas fa-data-center me-1"></i>DC: {{ $node->datacenter ?? 'Не указан' }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="network-info">
                                        <div class="fw-medium text-gray-900">
                                            <i class="fas fa-globe me-1 text-primary"></i>{{ $node->ip_address }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-plug me-1"></i>SSH: {{ $node->ssh_port ?? 22 }}
                                        </div>
                                        <div class="text-muted small">
                                            <i class="fas fa-gamepad me-1"></i>Game: {{ $node->game_port_start ?? 25565 }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <div class="server-count mb-2">
                                            <span class="badge bg-primary-gradient">
                                                <i class="fas fa-server me-1"></i>{{ $node->servers_count ?? 0 }}
                                            </span>
                                            <div class="text-muted small mt-1">
                                                @if($node->max_servers)
                                                    Макс: {{ $node->max_servers }}
                                                @endif
                                            </div>
                                        </div>
                                        @if($node->server_load)
                                            <div class="progress" style="height: 6px; width: 100px;">
                                                <div class="progress-bar {{ $node->server_load > 80 ? 'bg-danger' : ($node->server_load > 60 ? 'bg-warning' : 'bg-success') }}" 
                                                     role="progressbar" 
                                                     style="width: {{ min($node->server_load, 100) }}%"
                                                     aria-valuenow="{{ $node->server_load }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100"></div>
                                            </div>
                                            <div class="text-muted small mt-1">
                                                Нагрузка: {{ $node->server_load }}%
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if($node->is_active)
                                            <span class="badge bg-success-gradient mb-1">
                                                <i class="fas fa-check-circle me-1"></i>Активна
                                            </span>
                                        @else
                                            <span class="badge bg-danger-gradient mb-1">
                                                <i class="fas fa-times-circle me-1"></i>Неактивна
                                            </span>
                                        @endif
                                        
                                        @if($node->accept_new_servers)
                                            <span class="badge bg-info-gradient mt-1">
                                                <i class="fas fa-plus-circle me-1"></i>Принимает серверы
                                            </span>
                                        @else
                                            <span class="badge bg-warning-gradient mt-1">
                                                <i class="fas fa-ban me-1"></i>Не принимает
                                            </span>
                                        @endif
                                        
                                        @if($node->is_maintenance)
                                            <span class="badge bg-secondary-gradient mt-1">
                                                <i class="fas fa-tools me-1"></i>Тех. работы
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.nodes.edit', $node) }}" 
                                           class="btn btn-outline-primary btn-action"
                                           data-bs-toggle="tooltip" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <button type="button" 
                                                class="btn btn-outline-{{ $node->is_active ? 'warning' : 'success' }} btn-action toggle-status-btn"
                                                data-bs-toggle="tooltip" 
                                                title="{{ $node->is_active ? 'Отключить' : 'Включить' }}"
                                                data-node-id="{{ $node->id }}"
                                                data-is-active="{{ $node->is_active ? 'true' : 'false' }}"
                                                data-node-name="{{ $node->name }}">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                        
                                        <form method="POST" action="{{ route('admin.nodes.delete', $node) }}" 
                                              class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-action delete-btn"
                                                    data-bs-toggle="tooltip" 
                                                    title="Удалить"
                                                    data-node-name="{{ $node->name }}"
                                                    data-has-servers="{{ ($node->servers_count ?? 0) > 0 ? 'true' : 'false' }}">
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
        </div>
        
        @if($nodes->isEmpty())
        <div class="card-body text-center py-5">
            <div class="empty-state">
                <div class="icon-circle bg-light mb-4 mx-auto">
                    <i class="fas fa-server text-muted fa-3x"></i>
                </div>
                <h4 class="text-muted mb-3">Ноды не найдены</h4>
                <p class="text-muted mb-4">Добавьте первую ноду для размещения серверов</p>
                <a href="{{ route('admin.nodes.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Добавить ноду
                </a>
            </div>
        </div>
        @endif
        
        @if($nodes->hasPages())
        <div class="card-footer bg-white border-top-0 py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Показано {{ $nodes->firstItem() }} - {{ $nodes->lastItem() }} из {{ $nodes->total() }} нод
                </div>
                <div class="pagination-container">
                    {{ $nodes->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    /* Иконки нод */
    .node-icon .icon-circle-sm {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid rgba(0,0,0,0.05);
    }
    
    /* Градиентные бейджи */
    .bg-primary-gradient { background: linear-gradient(135deg, #4e73df, #2e59d9); }
    .bg-success-gradient { background: linear-gradient(135deg, #1cc88a, #16a085); }
    .bg-info-gradient { background: linear-gradient(135deg, #36b9cc, #2d9ba9); }
    .bg-warning-gradient { background: linear-gradient(135deg, #f6c23e, #f39c12); }
    .bg-danger-gradient { background: linear-gradient(135deg, #e74a3b, #c0392b); }
    .bg-secondary-gradient { background: linear-gradient(135deg, #6c757d, #5a6268); }
    
    /* Светлые фоны */
    .bg-primary-light { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-light { background-color: rgba(28, 200, 138, 0.1); }
    .bg-info-light { background-color: rgba(54, 185, 204, 0.1); }
    .bg-warning-light { background-color: rgba(246, 194, 62, 0.1); }
    .bg-danger-light { background-color: rgba(231, 74, 59, 0.1); }
    
    /* Ряды таблицы */
    .node-row {
        transition: all 0.3s ease;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .node-row:hover {
        background-color: rgba(78, 115, 223, 0.03);
        transform: translateX(5px);
    }
    
    /* Кнопки действий */
    .btn-action {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 2px solid;
        transition: all 0.3s ease;
    }
    
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    
    .btn-outline-primary.btn-action:hover {
        background-color: #4e73df;
        color: white;
    }
    
    .btn-outline-success.btn-action:hover {
        background-color: #1cc88a;
        color: white;
    }
    
    .btn-outline-warning.btn-action:hover {
        background-color: #f6c23e;
        color: white;
    }
    
    .btn-outline-danger.btn-action:hover {
        background-color: #e74a3b;
        color: white;
    }
    
    /* Информация о сети */
    .network-info {
        min-width: 150px;
    }
    
    .location-info {
        min-width: 120px;
    }
    
    .server-count {
        min-width: 80px;
    }
    
    /* Прогресс бар */
    .progress {
        border-radius: 10px;
        background-color: #e3e6f0;
    }
    
    .progress-bar {
        border-radius: 10px;
    }
    
    /* Пустое состояние */
    .empty-state {
        max-width: 400px;
        margin: 0 auto;
    }
    
    .empty-state .icon-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Фильтры */
    .filter-btn.active {
        background-color: #4e73df;
        color: white;
        border-color: #4e73df;
    }
    
    /* Пагинация */
    .pagination-container .pagination {
        margin-bottom: 0;
    }
    
    .pagination-container .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .pagination-container .page-link {
        color: #4e73df;
        border-radius: 8px;
        margin: 0 3px;
        border: 1px solid #e3e6f0;
    }
    
    .pagination-container .page-link:hover {
        background-color: rgba(78, 115, 223, 0.1);
        border-color: #4e73df;
    }
    
    /* Анимации */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .node-row {
        animation: fadeIn 0.5s ease forwards;
        animation-delay: calc(var(--row-index) * 0.05s);
        opacity: 0;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Установка индексов строк для анимации
    const rows = document.querySelectorAll('.node-row');
    rows.forEach((row, index) => {
        row.style.setProperty('--row-index', index);
    });
    
    // Обработка удаления ноды
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const nodeName = this.getAttribute('data-node-name');
            const hasServers = this.getAttribute('data-has-servers') === 'true';
            const form = this.closest('.delete-form');
            
            let warningText = '';
            if (hasServers) {
                warningText = `<div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Внимание!</strong> На этой ноде есть серверы. Удаление ноды приведет к удалению всех связанных серверов.
                </div>`;
            }
            
            Swal.fire({
                title: 'Удалить ноду?',
                html: `<div class="text-start">
                    <p>Вы уверены, что хотите удалить ноду <strong>${nodeName}</strong>?</p>
                    ${warningText}
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-radiation-alt me-2"></i>
                        <strong>Это действие нельзя отменить!</strong>
                    </div>
                </div>`,
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#5a5c69',
                confirmButtonText: 'Да, удалить навсегда',
                cancelButtonText: 'Отмена',
                reverseButtons: true,
                width: 600
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    
    // Обработка переключения статуса
    document.querySelectorAll('.toggle-status-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const nodeId = this.getAttribute('data-node-id');
            const isActive = this.getAttribute('data-is-active') === 'true';
            const nodeName = this.getAttribute('data-node-name');
            const newStatus = !isActive;
            
            Swal.fire({
                title: newStatus ? 'Активировать ноду?' : 'Деактивировать ноду?',
                html: `<div class="text-start">
                    <p>Вы уверены, что хотите ${newStatus ? 'активировать' : 'деактивировать'} ноду <strong>${nodeName}</strong>?</p>
                    <div class="alert alert-${newStatus ? 'success' : 'warning'} mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        ${newStatus ? 
                            'Нода станет доступной для размещения новых серверов.' : 
                            'Нода перестанет принимать новые серверы. Существующие серверы продолжат работать.'
                        }
                    </div>
                </div>`,
                icon: newStatus ? 'success' : 'warning',
                showCancelButton: true,
                confirmButtonColor: newStatus ? '#1cc88a' : '#f6c23e',
                cancelButtonColor: '#5a5c69',
                confirmButtonText: newStatus ? 'Да, активировать' : 'Да, деактивировать',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Отправляем AJAX запрос для изменения статуса
                    fetch(`/admin/nodes/${nodeId}/toggle-status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Успешно!',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Ошибка!',
                                text: data.message || 'Произошла ошибка при изменении статуса'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Ошибка!',
                            text: 'Произошла ошибка при изменении статуса'
                        });
                    });
                }
            });
        });
    });
    
    // Фильтрация нод
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Обновление активной кнопки
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Показать/скрыть строки
            rows.forEach(row => {
                let show = true;
                const isActive = row.getAttribute('data-is-active') === 'true';
                const accepts = row.getAttribute('data-accepts') === 'true';
                
                if (filter === 'active') {
                    show = isActive;
                } else if (filter === 'accepting') {
                    show = isActive && accepts;
                }
                
                row.style.display = show ? '' : 'none';
            });
        });
    });
    
    // Поиск по нодам
    const searchInput = `
        <div class="input-group mb-4" style="max-width: 300px;">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="nodeSearch" placeholder="Поиск по названию или IP...">
        </div>
    `;
    
    const tableHeader = document.querySelector('.card-header .d-flex');
    tableHeader.insertAdjacentHTML('beforeend', searchInput);
    
    // Обработка поиска
    const searchInputEl = document.getElementById('nodeSearch');
    searchInputEl?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    // Сортировка по колонкам
    const tableHeaders = document.querySelectorAll('#nodesTable thead th[class="border-0"]');
    tableHeaders.forEach((header, index) => {
        if (index < tableHeaders.length - 1) { // Последняя колонка - действия
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortTable(index);
            });
        }
    });
    
    let sortDirection = true; // true = ascending, false = descending
    
    function sortTable(column) {
        const table = document.getElementById('nodesTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            const aText = a.cells[column].textContent.trim().toLowerCase();
            const bText = b.cells[column].textContent.trim().toLowerCase();
            
            // Пытаемся сравнить как числа
            const aNum = parseFloat(aText);
            const bNum = parseFloat(bText);
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return sortDirection ? aNum - bNum : bNum - aNum;
            }
            
            // Иначе как строки
            return sortDirection ? 
                aText.localeCompare(bText) : 
                bText.localeCompare(aText);
        });
        
        // Удаляем старые строки
        while (tbody.firstChild) {
            tbody.removeChild(tbody.firstChild);
        }
        
        // Добавляем отсортированные
        rows.forEach(row => tbody.appendChild(row));
        
        sortDirection = !sortDirection;
        
        // Обновляем индексы для анимации
        const newRows = document.querySelectorAll('.node-row');
        newRows.forEach((row, index) => {
            row.style.setProperty('--row-index', index);
        });
    }
});
</script>

@push('scripts')
<!-- SweetAlert2 для красивых уведомлений -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection
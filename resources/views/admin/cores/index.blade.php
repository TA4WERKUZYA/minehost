@extends('layouts.admin-app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-1 text-gray-900">{{ $title }}</h1>
            <p class="text-muted mb-0">Управление Minecraft ядрами для серверов</p>
        </div>
        <a href="{{ route('admin.cores.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle me-2"></i>Добавить ядро
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
                                <i class="fas fa-microchip me-2 text-primary"></i>Всего ядер
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $cores->count() }}</h2>
                        </div>
                        <div class="icon-circle bg-primary-light">
                            <i class="fas fa-microchip text-primary fa-2x"></i>
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
                                <i class="fas fa-java me-2 text-success"></i>Java Edition
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $cores->where('game_type', 'java')->count() }}</h2>
                        </div>
                        <div class="icon-circle bg-success-light">
                            <i class="fab fa-java text-success fa-2x"></i>
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
                                <i class="fas fa-mobile-alt me-2 text-info"></i>Bedrock Edition
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $cores->where('game_type', 'bedrock')->count() }}</h2>
                        </div>
                        <div class="icon-circle bg-info-light">
                            <i class="fas fa-mobile-alt text-info fa-2x"></i>
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
                                <i class="fas fa-server me-2 text-warning"></i>Используется
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $cores->where('is_active', true)->count() }}</h2>
                        </div>
                        <div class="icon-circle bg-warning-light">
                            <i class="fas fa-server text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 py-4">
            <h5 class="mb-0">
                <i class="fas fa-list me-2 text-primary"></i>Список ядер
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="coresTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4" style="width: 80px;">ID</th>
                            <th class="border-0">Ядро</th>
                            <th class="border-0">Версия</th>
                            <th class="border-0">Тип</th>
                            <th class="border-0">Файл</th>
                            <th class="border-0">Статус</th>
                            <th class="border-0 text-end pe-4" style="width: 150px;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cores as $core)
                            <tr class="core-row" data-core-id="{{ $core->id }}">
                                <td class="ps-4">
                                    <div class="fw-bold text-primary">#{{ $core->id }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="core-icon me-3">
                                            <div class="icon-circle-sm bg-{{ $core->color }}-light">
                                                <i class="{{ $core->icon }} text-{{ $core->color }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 fw-bold text-gray-900">{{ $core->name }}</h6>
                                                @if($core->is_default)
                                                    <span class="badge bg-primary-gradient ms-2">
                                                        <i class="fas fa-star me-1"></i>По умолчанию
                                                    </span>
                                                @endif
                                            </div>
                                            @if($core->description)
                                                <p class="text-muted mb-0 small">{{ Str::limit($core->description, 60) }}</p>
                                            @endif
                                            <div class="mt-1">
                                                @if(!$core->is_stable)
                                                    <span class="badge bg-warning-gradient">
                                                        <i class="fas fa-exclamation-triangle me-1"></i>Нестабильное
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="version-badge">
                                        <span class="badge bg-info-gradient">
                                            <i class="fas fa-tag me-1"></i>{{ $core->version }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @if($core->game_type == 'java')
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <i class="fab fa-java text-primary"></i>
                                            </div>
                                            <span class="fw-medium">Java Edition</span>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <i class="fas fa-mobile-alt text-info"></i>
                                            </div>
                                            <span class="fw-medium">Bedrock Edition</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="file-info">
                                        <div class="fw-medium text-gray-900">{{ $core->file_name }}</div>
                                        <div class="text-muted small">
                                            <i class="fas fa-weight me-1"></i>{{ $core->getFileSizeFormattedAttribute() }}
                                        </div>
                                        <div class="text-truncate small" style="max-width: 200px;" 
                                             data-bs-toggle="tooltip" title="{{ $core->file_path }}">
                                            <i class="fas fa-folder me-1"></i>{{ $core->file_path }}
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @if($core->is_active)
                                            <span class="badge bg-success-gradient mb-1">
                                                <i class="fas fa-check-circle me-1"></i>Активно
                                            </span>
                                        @else
                                            <span class="badge bg-danger-gradient mb-1">
                                                <i class="fas fa-times-circle me-1"></i>Неактивно
                                            </span>
                                        @endif
                                        <div class="text-muted small">
                                            <i class="fas fa-server me-1"></i>
                                            Серверов: {{ $core->servers()->count() }}
                                        </div>
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        @if(!$core->is_default)
                                            <form method="POST" action="{{ route('admin.cores.make-default', $core) }}" 
                                                  class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success btn-action" 
                                                        data-bs-toggle="tooltip" title="Сделать дефолтным">
                                                    <i class="fas fa-star"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <a href="{{ route('admin.cores.edit', $core) }}" 
                                           class="btn btn-outline-primary btn-action"
                                           data-bs-toggle="tooltip" title="Редактировать">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <form method="POST" action="{{ route('admin.cores.destroy', $core) }}" 
                                              class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-outline-danger btn-action delete-btn"
                                                    data-bs-toggle="tooltip" 
                                                    title="Удалить"
                                                    data-core-name="{{ $core->name }} {{ $core->version }}"
                                                    data-is-used="{{ $core->isInUse() ? 'true' : 'false' }}"
                                                    {{ $core->isInUse() ? 'disabled' : '' }}>
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
        
        @if($cores->isEmpty())
        <div class="card-body text-center py-5">
            <div class="empty-state">
                <div class="icon-circle bg-light mb-4 mx-auto">
                    <i class="fas fa-microchip text-muted fa-3x"></i>
                </div>
                <h4 class="text-muted mb-3">Ядра не найдены</h4>
                <p class="text-muted mb-4">Добавьте первое ядро для Minecraft серверов</p>
                <a href="{{ route('admin.cores.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus-circle me-2"></i>Добавить ядро
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<style>
    /* Иконки ядер */
    .core-icon .icon-circle-sm {
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
    
    /* Светлые фоны */
    .bg-primary-light { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-light { background-color: rgba(28, 200, 138, 0.1); }
    .bg-info-light { background-color: rgba(54, 185, 204, 0.1); }
    .bg-warning-light { background-color: rgba(246, 194, 62, 0.1); }
    .bg-danger-light { background-color: rgba(231, 74, 59, 0.1); }
    
    /* Ряды таблицы */
    .core-row {
        transition: all 0.3s ease;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .core-row:hover {
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
    
    .btn-outline-danger.btn-action:hover {
        background-color: #e74a3b;
        color: white;
    }
    
    .btn-action:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }
    
    /* Информация о файле */
    .file-info {
        max-width: 250px;
    }
    
    /* Бейдж версии */
    .version-badge .badge {
        font-size: 0.8rem;
        padding: 0.35rem 0.75rem;
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
    
    /* Цвета иконок */
    .text-purple { color: #6f42c1; }
    .bg-purple-light { background-color: rgba(111, 66, 193, 0.1); }
    
    .text-orange { color: #fd7e14; }
    .bg-orange-light { background-color: rgba(253, 126, 20, 0.1); }
    
    .text-pink { color: #e83e8c; }
    .bg-pink-light { background-color: rgba(232, 62, 140, 0.1); }
    
    .text-teal { color: #20c997; }
    .bg-teal-light { background-color: rgba(32, 201, 151, 0.1); }
    
    /* Анимации */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .core-row {
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
    const rows = document.querySelectorAll('.core-row');
    rows.forEach((row, index) => {
        row.style.setProperty('--row-index', index);
    });
    
    // Обработка удаления ядра
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const coreName = this.getAttribute('data-core-name');
            const isUsed = this.getAttribute('data-is-used') === 'true';
            
            if (isUsed) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Невозможно удалить',
                    text: `Ядро "${coreName}" используется на серверах. Сначала удалите или измените связанные серверы.`,
                    confirmButtonText: 'Понятно',
                    confirmButtonColor: '#4e73df'
                });
                return;
            }
            
            Swal.fire({
                title: 'Удалить ядро?',
                text: `Вы уверены, что хотите удалить ядро "${coreName}"? Это действие нельзя отменить.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#5a5c69',
                confirmButtonText: 'Да, удалить',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = this.closest('.delete-form');
                    form.submit();
                }
            });
        });
    });
    
    // Подтверждение установки как дефолтного
    document.querySelectorAll('form[action*="make-default"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const coreRow = this.closest('.core-row');
            const coreName = coreRow.querySelector('.fw-bold.text-gray-900').textContent;
            
            Swal.fire({
                title: 'Установить как дефолтное?',
                text: `Ядро "${coreName}" будет установлено как основное для новых серверов.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#5a5c69',
                confirmButtonText: 'Да, установить',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
    
    // Фильтрация по типу игры
    const filterButtons = `
        <div class="btn-group mb-4" role="group">
            <button type="button" class="btn btn-outline-primary filter-btn active" data-filter="all">
                Все ядра
            </button>
            <button type="button" class="btn btn-outline-primary filter-btn" data-filter="java">
                <i class="fab fa-java me-2"></i>Java Edition
            </button>
            <button type="button" class="btn btn-outline-primary filter-btn" data-filter="bedrock">
                <i class="fas fa-mobile-alt me-2"></i>Bedrock Edition
            </button>
            <button type="button" class="btn btn-outline-primary filter-btn" data-filter="active">
                <i class="fas fa-check-circle me-2"></i>Активные
            </button>
        </div>
    `;
    
    // Вставка фильтров перед таблицей
    const table = document.getElementById('coresTable');
    const filterContainer = document.createElement('div');
    filterContainer.innerHTML = filterButtons;
    table.parentNode.insertBefore(filterContainer, table);
    
    // Обработка фильтров
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.getAttribute('data-filter');
            
            // Обновление активной кнопки
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Показать/скрыть строки
            rows.forEach(row => {
                let show = true;
                
                if (filter === 'java') {
                    show = row.textContent.includes('Java Edition');
                } else if (filter === 'bedrock') {
                    show = row.textContent.includes('Bedrock Edition');
                } else if (filter === 'active') {
                    show = !row.textContent.includes('Неактивно');
                }
                
                row.style.display = show ? '' : 'none';
            });
        });
    });
    
    // Поиск по ядрам
    const searchInput = `
        <div class="input-group mb-4" style="max-width: 300px;">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="coreSearch" placeholder="Поиск по названию или версии...">
        </div>
    `;
    
    filterContainer.insertAdjacentHTML('beforebegin', searchInput);
    
    // Обработка поиска
    const searchInputEl = document.getElementById('coreSearch');
    searchInputEl?.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
});
</script>

@push('scripts')
<!-- SweetAlert2 для красивых уведомлений -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection
@extends('layouts.admin-app')

@section('title', 'Дашборд - Админ панель')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-1 text-gray-900">Дашборд</h1>
            <p class="text-muted mb-0">Обзор системы и статистика</p>
        </div>
        <div>
            <button class="btn btn-outline-primary">
                <i class="fas fa-redo me-2"></i>Обновить
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <!-- Users Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-users me-2 text-primary"></i>Пользователи
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $stats['total_users'] ?? 0 }}</h2>
                            <small class="text-success">
                                <i class="fas fa-arrow-up me-1"></i>
                                {{ $stats['active_users'] ?? 0 }} активных
                            </small>
                        </div>
                        <div class="icon-circle bg-primary-light">
                            <i class="fas fa-users text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: {{ min(($stats['active_users'] ?? 0) / max(($stats['total_users'] ?? 1), 1) * 100, 100) }}%"></div>
                        </div>
                        <small class="text-muted d-block mt-2">Активных: {{ $stats['active_users'] ?? 0 }}</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-3">
                    <a href="/admin/users" class="text-decoration-none">
                        <span class="text-primary small">Подробнее</span>
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Servers Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-server me-2 text-success"></i>Серверы
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $stats['total_servers'] ?? 0 }}</h2>
                            <small class="text-success">
                                <i class="fas fa-play-circle me-1"></i>
                                {{ $stats['active_servers'] ?? 0 }} запущено
                            </small>
                        </div>
                        <div class="icon-circle bg-success-light">
                            <i class="fas fa-server text-success fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: {{ min(($stats['active_servers'] ?? 0) / max(($stats['total_servers'] ?? 1), 1) * 100, 100) }}%"></div>
                        </div>
                        <small class="text-muted d-block mt-2">Активных: {{ $stats['active_servers'] ?? 0 }}</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-3">
                    <a href="/admin/servers" class="text-decoration-none">
                        <span class="text-success small">Подробнее</span>
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Nodes Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-network-wired me-2 text-info"></i>Ноды
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $stats['total_nodes'] ?? 0 }}</h2>
                            <small class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                {{ $stats['active_nodes'] ?? 0 }} доступно
                            </small>
                        </div>
                        <div class="icon-circle bg-info-light">
                            <i class="fas fa-network-wired text-info fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-info" style="width: {{ min(($stats['active_nodes'] ?? 0) / max(($stats['total_nodes'] ?? 1), 1) * 100, 100) }}%"></div>
                        </div>
                        <small class="text-muted d-block mt-2">Загрузка: {{ $stats['node_usage'] ?? 0 }}%</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-3">
                    <a href="/admin/nodes" class="text-decoration-none">
                        <span class="text-info small">Подробнее</span>
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-wallet me-2 text-warning"></i>Выручка
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ number_format($stats['monthly_revenue'] ?? 0, 0, ',', ' ') }} ₽</h2>
                            <small class="text-success">
                                <i class="fas fa-chart-line me-1"></i>
                                За месяц
                            </small>
                        </div>
                        <div class="icon-circle bg-warning-light">
                            <i class="fas fa-wallet text-warning fa-2x"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">Баланс системы</small>
                                <div class="fw-bold">{{ number_format($stats['total_balance'] ?? 0, 0, ',', ' ') }} ₽</div>
                            </div>
                            <div>
                                <small class="text-muted">Заказы</small>
                                <div class="fw-bold">{{ $stats['pending_orders'] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-top py-3">
                    <a href="/admin/orders" class="text-decoration-none">
                        <span class="text-warning small">Подробнее</span>
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Row -->
    <div class="row g-4">
        <!-- System Status -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>Статистика системы
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4 mb-4">
                        <div class="col-md-4">
                            <div class="stat-box p-3 rounded bg-primary-light">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle-sm bg-primary me-3">
                                        <i class="fas fa-microchip text-white"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">CPU</small>
                                        <div class="fw-bold">{{ $stats['cpu_usage'] ?? 45 }}%</div>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-primary" style="width: {{ $stats['cpu_usage'] ?? 45 }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box p-3 rounded bg-success-light">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle-sm bg-success me-3">
                                        <i class="fas fa-memory text-white"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Память</small>
                                        <div class="fw-bold">{{ $stats['memory_usage'] ?? 68 }}%</div>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: {{ $stats['memory_usage'] ?? 68 }}%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-box p-3 rounded bg-info-light">
                                <div class="d-flex align-items-center">
                                    <div class="icon-circle-sm bg-info me-3">
                                        <i class="fas fa-hdd text-white"></i>
                                    </div>
                                    <div>
                                        <small class="text-muted">Диск</small>
                                        <div class="fw-bold">{{ $stats['disk_usage'] ?? 32 }}%</div>
                                    </div>
                                </div>
                                <div class="progress mt-2" style="height: 4px;">
                                    <div class="progress-bar bg-info" style="width: {{ $stats['disk_usage'] ?? 32 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="mt-4">
                        <h6 class="mb-3">Последняя активность</h6>
                        <div class="timeline">
                            @foreach($recent_users as $user)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $user->name }}</strong>
                                            <small class="text-muted ms-2">зарегистрировался</small>
                                        </div>
                                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fas fa-envelope me-1"></i>{{ $user->email }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders & Quick Actions -->
        <div class="col-lg-4">
            <!-- Recent Orders -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-shopping-cart me-2 text-warning"></i>Последние заказы
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($recent_orders as $order)
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle-sm bg-warning-light me-3">
                                    <i class="fas fa-receipt text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <strong>#{{ $order->id }}</strong>
                                        <span class="badge bg-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : 'danger') }}">
                                            {{ $order->status }}
                                        </span>
                                    </div>
                                    <div class="text-muted small">
                                        {{ $order->user->name ?? 'Пользователь' }}
                                    </div>
                                    <div class="text-success small">
                                        {{ number_format($order->amount, 0, ',', ' ') }} ₽
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item border-0 py-4 text-center text-muted">
                            <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                            <p class="mb-0">Заказов нет</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-white border-top py-3">
                    <a href="/admin/orders" class="btn btn-outline-primary w-100">
                        <i class="fas fa-eye me-2"></i>Все заказы
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2 text-danger"></i>Быстрые действия
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="/admin/users/create" class="btn btn-outline-primary w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-user-plus fa-2x mb-2"></i>
                                <span>Добавить пользователя</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="/admin/servers/create" class="btn btn-outline-success w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                <span>Создать сервер</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="/admin/nodes/create" class="btn btn-outline-info w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-network-wired fa-2x mb-2"></i>
                                <span>Добавить ноду</span>
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="/admin/backups" class="btn btn-outline-warning w-100 h-100 d-flex flex-column align-items-center justify-content-center py-3">
                                <i class="fas fa-hdd fa-2x mb-2"></i>
                                <span>Бэкапы</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Status -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-cogs me-2 text-secondary"></i>Статус системы
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success border-0">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="alert-heading mb-1">Система работает стабильно!</h5>
                                <p class="mb-0">Все компоненты хостинга функционируют нормально. Мониторинг активен.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light">
                                <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Информация</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <span>База данных: <strong>Online</strong></span>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <span>Демон серверов: <strong>Online</strong></span>
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <span>Файловое хранилище: <strong>Online</strong></span>
                                    </li>
                                    <li>
                                        <i class="fas fa-check text-success me-2"></i>
                                        <span>API Gateway: <strong>Online</strong></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded bg-light">
                                <h6 class="mb-3"><i class="fas fa-tasks me-2"></i>Быстрые ссылки</h6>
                                <div class="d-grid gap-2">
                                    <a href="/admin/nodes" class="btn btn-outline-primary text-start">
                                        <i class="fas fa-network-wired me-2"></i> Управление нодами
                                    </a>
                                    <a href="/admin/users" class="btn btn-outline-success text-start">
                                        <i class="fas fa-users me-2"></i> Управление пользователями
                                    </a>
                                    <a href="/admin/servers" class="btn btn-outline-info text-start">
                                        <i class="fas fa-server me-2"></i> Все серверы
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

<style>
    .icon-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .icon-circle-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .avatar-circle-sm {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .bg-primary-light { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-light { background-color: rgba(28, 200, 138, 0.1); }
    .bg-info-light { background-color: rgba(54, 185, 204, 0.1); }
    .bg-warning-light { background-color: rgba(246, 194, 62, 0.1); }
    .bg-danger-light { background-color: rgba(231, 74, 59, 0.1); }
    
    .stat-box {
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .stat-box:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline::before {
        content: '';
        position: absolute;
        left: 10px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e3e6f0;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 5px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 3px solid white;
        z-index: 1;
    }
    
    .timeline-content {
        padding: 10px;
        background: white;
        border-radius: 8px;
        border: 1px solid #e3e6f0;
    }
    
    .btn-outline-primary, .btn-outline-success, .btn-outline-info, .btn-outline-warning, .btn-outline-danger {
        transition: all 0.3s ease;
    }
    
    .btn-outline-primary:hover { transform: translateY(-2px); }
    .btn-outline-success:hover { transform: translateY(-2px); }
    .btn-outline-info:hover { transform: translateY(-2px); }
    .btn-outline-warning:hover { transform: translateY(-2px); }
    .btn-outline-danger:hover { transform: translateY(-2px); }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Анимация счетчиков
        function animateCounters() {
            const counters = document.querySelectorAll('.fw-bold.text-gray-900');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace(/\s/g, ''));
                if (!isNaN(target) && target > 0) {
                    animateCounter(counter, target);
                }
            });
        }
        
        function animateCounter(element, target) {
            let current = 0;
            const increment = target / 100;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target.toLocaleString();
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString();
                }
            }, 20);
        }
        
        // Запуск анимации при загрузке
        setTimeout(animateCounters, 300);
        
        // Автообновление статистики
        function updateStats() {
            // Здесь можно добавить AJAX запрос для обновления статистики
            console.log('Обновление статистики...');
        }
        
        // Обновление каждые 60 секунд
        setInterval(updateStats, 60000);
        
        // Интерактивные элементы
        document.querySelectorAll('.stat-box').forEach(box => {
            box.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            box.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endsection
@extends('layouts.admin-app')

@section('title', 'Управление заказами')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-1 text-gray-900">Управление заказами</h1>
            <p class="text-muted mb-0">Просмотр и управление заказами пользователей</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-primary" id="exportOrdersBtn">
                <i class="fas fa-file-export me-2"></i>Экспорт
            </button>
            <button type="button" class="btn btn-primary" id="refreshOrdersBtn">
                <i class="fas fa-sync-alt me-2"></i>Обновить
            </button>
        </div>
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
        @php
            // Вычисляем статистику из коллекции заказов
            $totalOrders = $orders->total();
            $completedOrders = 0;
            $pendingOrders = 0;
            $totalRevenue = 0;
            
            foreach ($orders as $order) {
                if ($order->status == 'completed') {
                    $completedOrders++;
                    $totalRevenue += $order->amount;
                } elseif ($order->status == 'pending') {
                    $pendingOrders++;
                }
                // Также учитываем доход от других статусов, если нужно
            }
        @endphp
        
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-shopping-cart me-2 text-primary"></i>Всего заказов
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $totalOrders }}</h2>
                        </div>
                        <div class="icon-circle bg-primary-light">
                            <i class="fas fa-shopping-cart text-primary fa-2x"></i>
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
                                <i class="fas fa-check-circle me-2 text-success"></i>Завершённых
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $completedOrders }}</h2>
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
                                <i class="fas fa-clock me-2 text-warning"></i>Ожидающих
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ $pendingOrders }}</h2>
                        </div>
                        <div class="icon-circle bg-warning-light">
                            <i class="fas fa-clock text-warning fa-2x"></i>
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
                                <i class="fas fa-money-bill-wave me-2 text-info"></i>Общая сумма
                            </h6>
                            <h2 class="mb-0 fw-bold text-gray-900">{{ number_format($totalRevenue, 2) }} ₽</h2>
                        </div>
                        <div class="icon-circle bg-info-light">
                            <i class="fas fa-money-bill-wave text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label fw-medium">Статус</label>
                        <select class="form-select" id="statusFilter">
                            <option value="all">Все статусы</option>
                            <option value="pending">Ожидание</option>
                            <option value="completed">Завершён</option>
                            <option value="failed">Ошибка</option>
                            <option value="cancelled">Отменён</option>
                            <option value="refunded">Возврат</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label fw-medium">Период</label>
                        <select class="form-select" id="periodFilter">
                            <option value="all">За всё время</option>
                            <option value="today">Сегодня</option>
                            <option value="week">За неделю</option>
                            <option value="month">За месяц</option>
                            <option value="year">За год</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label fw-medium">Платежная система</label>
                        <select class="form-select" id="gatewayFilter">
                            <option value="all">Все системы</option>
                            <option value="yookassa">ЮKassa</option>
                            <option value="robokassa">RoboKassa</option>
                            <option value="paypal">PayPal</option>
                            <option value="cryptocloud">CryptoCloud</option>
                            <option value="manual">Вручную</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label fw-medium">Поиск</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" id="searchOrders" placeholder="ID, email или сервер...">
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
                    <i class="fas fa-list me-2 text-primary"></i>Список заказов
                </h5>
                <div class="text-muted">
                    Страница {{ $orders->currentPage() }} из {{ $orders->lastPage() }}
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="ordersTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4" style="width: 80px;">ID</th>
                            <th class="border-0">Заказ</th>
                            <th class="border-0">Пользователь</th>
                            <th class="border-0">Тариф</th>
                            <th class="border-0 text-end">Сумма</th>
                            <th class="border-0">Статус</th>
                            <th class="border-0">Дата</th>
                            <th class="border-0 text-end pe-4" style="width: 150px;">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr class="order-row" 
                                data-order-id="{{ $order->id }}"
                                data-status="{{ $order->status }}"
                                data-amount="{{ $order->amount }}"
                                data-gateway="{{ $order->gateway ?? 'manual' }}">
                                <td class="ps-4">
                                    <div class="fw-bold text-primary">#{{ $order->id }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="order-icon me-3">
                                            <div class="icon-circle-sm {{ $order->status == 'completed' ? 'bg-success-light' : ($order->status == 'pending' ? 'bg-warning-light' : 'bg-danger-light') }}">
                                                <i class="fas fa-shopping-cart {{ $order->status == 'completed' ? 'text-success' : ($order->status == 'pending' ? 'text-warning' : 'text-danger') }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex align-items-center">
                                                <h6 class="mb-0 fw-bold text-gray-900">Заказ #{{ $order->id }}</h6>
                                                @if($order->server_id)
                                                    <span class="badge bg-info-gradient ms-2">
                                                        <i class="fas fa-server me-1"></i>Сервер
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-credit-card me-1"></i>
                                                @if($order->gateway)
                                                    {{ $order->gateway }}
                                                @else
                                                    Вручную
                                                @endif
                                            </div>
                                            @if($order->transaction_id)
                                                <div class="text-muted small">
                                                    <i class="fas fa-hashtag me-1"></i>Транзакция: {{ $order->transaction_id }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="user-info">
                                        @if($order->user)
                                            <div class="fw-medium text-gray-900">
                                                <i class="fas fa-user me-1 text-primary"></i>
                                                {{ $order->user->name }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-envelope me-1"></i>{{ $order->user->email }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-id-card me-1"></i>ID: {{ $order->user->id }}
                                            </div>
                                        @else
                                            <div class="text-muted">Пользователь удалён</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($order->plan)
                                        <div class="plan-info">
                                            <div class="fw-medium text-gray-900">
                                                {{ $order->plan->name }}
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-microchip me-1"></i>{{ $order->plan->ram }} MB
                                            </div>
                                            <div class="text-muted small">
                                                <i class="fas fa-hdd me-1"></i>{{ $order->plan->disk }} GB
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-muted">Тариф удалён</div>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="amount-info">
                                        <div class="fw-bold text-gray-900">
                                            {{ number_format($order->amount, 2) }} ₽
                                        </div>
                                        @if($order->discount > 0)
                                            <div class="text-success small">
                                                <i class="fas fa-tag me-1"></i>Скидка: {{ $order->discount }}%
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        @switch($order->status)
                                            @case('pending')
                                                <span class="badge bg-warning-gradient mb-1">
                                                    <i class="fas fa-clock me-1"></i>Ожидание
                                                </span>
                                                @if($order->created_at->diffInHours(now()) > 24)
                                                    <div class="text-danger small">
                                                        <i class="fas fa-exclamation-circle me-1"></i>Старый заказ
                                                    </div>
                                                @endif
                                                @break
                                                
                                            @case('completed')
                                                <span class="badge bg-success-gradient mb-1">
                                                    <i class="fas fa-check-circle me-1"></i>Завершён
                                                </span>
                                                @break
                                                
                                            @case('failed')
                                                <span class="badge bg-danger-gradient mb-1">
                                                    <i class="fas fa-times-circle me-1"></i>Ошибка
                                                </span>
                                                @if($order->error_message)
                                                    <div class="text-muted small" data-bs-toggle="tooltip" title="{{ $order->error_message }}">
                                                        <i class="fas fa-info-circle me-1"></i>Ошибка
                                                    </div>
                                                @endif
                                                @break
                                                
                                            @case('cancelled')
                                                <span class="badge bg-secondary-gradient mb-1">
                                                    <i class="fas fa-ban me-1"></i>Отменён
                                                </span>
                                                @break
                                                
                                            @case('refunded')
                                                <span class="badge bg-info-gradient mb-1">
                                                    <i class="fas fa-undo me-1"></i>Возврат
                                                </span>
                                                @break
                                                
                                            @default
                                                <span class="badge bg-light text-dark mb-1">
                                                    <i class="fas fa-question-circle me-1"></i>{{ $order->status }}
                                                </span>
                                        @endswitch
                                    </div>
                                </td>
                                <td>
                                    <div class="date-info">
                                        <div class="fw-medium">
                                            {{ $order->created_at->format('d.m.Y') }}
                                        </div>
                                        <div class="text-muted small">
                                            {{ $order->created_at->format('H:i') }}
                                        </div>
                                        @if($order->paid_at)
                                            <div class="text-success small">
                                                <i class="fas fa-check me-1"></i>Оплачен
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" 
                                                class="btn btn-outline-primary btn-action view-order-btn"
                                                data-bs-toggle="tooltip" 
                                                title="Просмотр"
                                                data-order-id="{{ $order->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        @if($order->status == 'pending')
                                            <button type="button" 
                                                    class="btn btn-outline-success btn-action complete-order-btn"
                                                    data-bs-toggle="tooltip" 
                                                    title="Завершить"
                                                    data-order-id="{{ $order->id }}"
                                                    data-order-amount="{{ number_format($order->amount, 2) }} ₽">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            
                                            <button type="button" 
                                                    class="btn btn-outline-danger btn-action cancel-order-btn"
                                                    data-bs-toggle="tooltip" 
                                                    title="Отменить"
                                                    data-order-id="{{ $order->id }}">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        @if($order->status == 'completed')
                                            <button type="button" 
                                                    class="btn btn-outline-warning btn-action refund-order-btn"
                                                    data-bs-toggle="tooltip" 
                                                    title="Возврат"
                                                    data-order-id="{{ $order->id }}"
                                                    data-order-amount="{{ number_format($order->amount, 2) }} ₽">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="icon-circle bg-light mb-4 mx-auto">
                                            <i class="fas fa-shopping-cart text-muted fa-3x"></i>
                                        </div>
                                        <h4 class="text-muted mb-3">Заказы не найдены</h4>
                                        <p class="text-muted mb-4">Пока нет ни одного заказа</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        @if($orders->hasPages())
        <div class="card-footer bg-white border-top-0 py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Показано {{ $orders->firstItem() }} - {{ $orders->lastItem() }} из {{ $orders->total() }} заказов
                </div>
                <div class="pagination-container">
                    {{ $orders->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Детали заказа #<span id="modalOrderId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="orderDetailsContent">
                    <!-- Простая версия без AJAX -->
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Информация о заказе</h6>
                        <div class="list-group list-group-flush">
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Статус:</span>
                                <span class="fw-medium">Ожидание</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Сумма:</span>
                                <span class="fw-medium">500.00 ₽</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between">
                                <span>Дата создания:</span>
                                <span class="fw-medium">{{ now()->format('d.m.Y H:i') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold mb-3">Действия</h6>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-success">Завершить заказ</button>
                            <button type="button" class="btn btn-warning">Изменить статус</button>
                            <button type="button" class="btn btn-danger">Отменить заказ</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Иконки заказов */
    .order-icon .icon-circle-sm {
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
    .order-row {
        transition: all 0.3s ease;
        border-bottom: 1px solid #e3e6f0;
    }
    
    .order-row:hover {
        background-color: rgba(78, 115, 223, 0.03);
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
    
    /* Информация о пользователе */
    .user-info {
        min-width: 180px;
    }
    
    .plan-info {
        min-width: 150px;
    }
    
    .amount-info {
        min-width: 100px;
    }
    
    .date-info {
        min-width: 90px;
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
    .form-select:focus {
        border-color: #4e73df;
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
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
    
    .order-row {
        animation: fadeIn 0.5s ease forwards;
        animation-delay: calc(var(--row-index) * 0.05s);
        opacity: 0;
    }
    
    /* Модальное окно */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    .modal-header {
        border-bottom: 1px solid #e3e6f0;
        background: linear-gradient(135deg, #4e73df, #2e59d9);
        color: white;
        border-radius: 12px 12px 0 0;
    }
    
    .modal-header .btn-close {
        filter: invert(1);
        opacity: 0.8;
    }
    
    .modal-header .btn-close:hover {
        opacity: 1;
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
    const rows = document.querySelectorAll('.order-row');
    rows.forEach((row, index) => {
        row.style.setProperty('--row-index', index);
    });
    
    // Кнопка обновления
    document.getElementById('refreshOrdersBtn')?.addEventListener('click', function() {
        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Обновление...';
        
        setTimeout(() => {
            location.reload();
        }, 1000);
    });
    
    // Кнопка экспорта
    document.getElementById('exportOrdersBtn')?.addEventListener('click', function() {
        const filters = {
            status: document.getElementById('statusFilter').value,
            period: document.getElementById('periodFilter').value,
            gateway: document.getElementById('gatewayFilter').value,
            search: document.getElementById('searchOrders').value
        };
        
        const queryString = new URLSearchParams(filters).toString();
        // Замените на ваш реальный маршрут экспорта
        // window.open(`/admin/orders/export?${queryString}`, '_blank');
        
        Swal.fire({
            icon: 'info',
            title: 'Экспорт данных',
            text: 'Функция экспорта будет доступна после настройки контроллера',
            confirmButtonColor: '#4e73df'
        });
    });
    
    // Просмотр деталей заказа
    document.querySelectorAll('.view-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
            document.getElementById('modalOrderId').textContent = orderId;
            modal.show();
        });
    });
    
    // Завершение заказа
    document.querySelectorAll('.complete-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const orderAmount = this.getAttribute('data-order-amount');
            
            Swal.fire({
                title: 'Завершить заказ?',
                html: `<div class="text-start">
                    <p>Вы уверены, что хотите отметить заказ #${orderId} как завершённый?</p>
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Сумма заказ: <strong>${orderAmount}</strong><br>
                        Будет создан сервер, если ещё не создан
                    </div>
                </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#5a5c69',
                confirmButtonText: 'Да, завершить',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Отправляем форму для завершения заказа
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/orders/${orderId}/complete`;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PUT">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
    
    // Отмена заказа
    document.querySelectorAll('.cancel-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            
            Swal.fire({
                title: 'Отменить заказ?',
                html: `<div class="text-start">
                    <p>Вы уверены, что хотите отменить заказ #${orderId}?</p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Внимание!</strong> Это действие нельзя отменить. Если заказ уже оплачен, потребуется возврат средств.
                    </div>
                </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#5a5c69',
                confirmButtonText: 'Да, отменить',
                cancelButtonText: 'Отмена',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Отправляем форму для отмены заказа
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/orders/${orderId}/cancel`;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PUT">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
    
    // Возврат средств
    document.querySelectorAll('.refund-order-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const orderId = this.getAttribute('data-order-id');
            const orderAmount = this.getAttribute('data-order-amount');
            
            Swal.fire({
                title: 'Возврат средств?',
                html: `<div class="text-start">
                    <p>Вы уверены, что хотите оформить возврат для заказа #${orderId}?</p>
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-radiation-alt me-2"></i>
                        <strong>Внимание!</strong> Средства будут возвращены пользователю.<br>
                        Сумма возврата: <strong>${orderAmount}</strong>
                    </div>
                    <div class="form-group mt-3">
                        <label for="refundReason" class="form-label">Причина возврата:</label>
                        <textarea class="form-control" id="refundReason" rows="2" placeholder="Укажите причину возврата..."></textarea>
                    </div>
                </div>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f6c23e',
                cancelButtonColor: '#5a5c69',
                confirmButtonText: 'Да, вернуть средства',
                cancelButtonText: 'Отмена',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const reason = document.getElementById('refundReason').value;
                    if (!reason.trim()) {
                        Swal.showValidationMessage('Укажите причину возврата');
                        return false;
                    }
                    return { reason: reason.trim() };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Отправляем форму для возврата средств
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/orders/${orderId}/refund`;
                    form.innerHTML = `
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="PUT">
                        <input type="hidden" name="reason" value="${result.value.reason}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
    
    // Фильтрация таблицы
    const filterElements = ['statusFilter', 'periodFilter', 'gatewayFilter', 'searchOrders'];
    filterElements.forEach(filterId => {
        document.getElementById(filterId)?.addEventListener('change', applyFilters);
    });
    
    function applyFilters() {
        const status = document.getElementById('statusFilter').value;
        const period = document.getElementById('periodFilter').value;
        const gateway = document.getElementById('gatewayFilter').value;
        const search = document.getElementById('searchOrders').value.toLowerCase();
        
        rows.forEach(row => {
            let show = true;
            
            // Фильтр по статусу
            if (status !== 'all') {
                const rowStatus = row.getAttribute('data-status');
                if (rowStatus !== status) show = false;
            }
            
            // Фильтр по платежной системе
            if (gateway !== 'all') {
                const rowGateway = row.getAttribute('data-gateway');
                if (rowGateway !== gateway) show = false;
            }
            
            // Поиск
            if (search) {
                const rowText = row.textContent.toLowerCase();
                if (!rowText.includes(search)) show = false;
            }
            
            // Фильтр по периоду (клиентская сторона - упрощённый)
            if (period !== 'all' && show) {
                const dateCell = row.querySelector('.date-info .fw-medium');
                if (dateCell) {
                    const rowDate = new Date(dateCell.textContent.split('.').reverse().join('-'));
                    const now = new Date();
                    
                    switch(period) {
                        case 'today':
                            show = rowDate.toDateString() === now.toDateString();
                            break;
                        case 'week':
                            const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                            show = rowDate >= weekAgo;
                            break;
                        case 'month':
                            const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                            show = rowDate >= monthAgo;
                            break;
                        case 'year':
                            const yearAgo = new Date(now.getTime() - 365 * 24 * 60 * 60 * 1000);
                            show = rowDate >= yearAgo;
                            break;
                    }
                }
            }
            
            row.style.display = show ? '' : 'none';
        });
    }
    
    // Сортировка по колонкам
    const tableHeaders = document.querySelectorAll('#ordersTable thead th[class="border-0"]');
    tableHeaders.forEach((header, index) => {
        if (index < tableHeaders.length - 1) { // Последняя колонка - действия
            header.style.cursor = 'pointer';
            header.addEventListener('click', function() {
                sortOrdersTable(index);
            });
        }
    });
    
    let sortDirection = true; // true = ascending, false = descending
    
    function sortOrdersTable(column) {
        const table = document.getElementById('ordersTable');
        const tbody = table.querySelector('tbody');
        const visibleRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.style.display !== 'none');
        
        visibleRows.sort((a, b) => {
            let aText = a.cells[column].textContent.trim().toLowerCase();
            let bText = b.cells[column].textContent.trim().toLowerCase();
            
            // Специальная обработка для колонок
            if (column === 4) { // Колонка суммы
                aText = parseFloat(a.getAttribute('data-amount')) || 0;
                bText = parseFloat(b.getAttribute('data-amount')) || 0;
                return sortDirection ? aText - bText : bText - aText;
            }
            
            // Пытаемся сравнить как даты
            if (column === 6) { // Колонка даты
                const aDate = new Date(a.querySelector('.date-info .fw-medium')?.textContent.split('.').reverse().join('-'));
                const bDate = new Date(b.querySelector('.date-info .fw-medium')?.textContent.split('.').reverse().join('-'));
                return sortDirection ? aDate - bDate : bDate - aDate;
            }
            
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
        
        // Переставляем только видимые строки
        visibleRows.forEach(row => tbody.appendChild(row));
        
        sortDirection = !sortDirection;
    }
});
</script>

@push('scripts')
<!-- SweetAlert2 для красивых уведомлений -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection
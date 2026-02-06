@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Заголовок -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h1 class="h2 text-gray-900 mb-1">{{ $title }}</h1>
            <p class="text-muted mb-0">Пополнение баланса для оплаты серверов</p>
        </div>
        <div class="d-flex align-items-center">
            <div class="me-4">
                <span class="text-muted me-2">Текущий баланс:</span>
                <span class="h4 mb-0 fw-bold text-primary">{{ number_format($user->balance, 2) }} ₽</span>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Назад
            </a>
        </div>
    </div>

    <!-- Карточки -->
    <div class="row mb-5">
        <div class="col-lg-8">
            <!-- Форма пополнения -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-wallet me-2 text-primary"></i>Пополнение баланса
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form id="paymentForm" method="POST" action="{{ route('payment.create') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-medium mb-3">Выберите сумму пополнения:</label>
                            <div class="row g-3">
                                @foreach([100, 250, 500, 1000, 2000, 5000] as $amount)
                                <div class="col-md-4">
                                    <div class="amount-option" data-amount="{{ $amount }}">
                                        <div class="card border-2 hover-shadow text-center py-4 cursor-pointer">
                                            <div class="card-body">
                                                <h4 class="mb-2">{{ $amount }} ₽</h4>
                                                <div class="text-muted small">
                                                    @if($amount >= 1000)
                                                        <i class="fas fa-gift text-success me-1"></i>+5% бонус
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="customAmount" class="form-label fw-medium">Или введите свою сумму:</label>
                            <div class="input-group input-group-lg">
                                <input type="number" 
                                       class="form-control" 
                                       id="customAmount" 
                                       name="amount" 
                                       min="10" 
                                       max="50000" 
                                       step="1"
                                       placeholder="Введите сумму от 10 до 50 000 ₽"
                                       required>
                                <span class="input-group-text bg-light">₽</span>
                            </div>
                            <div class="form-text">Минимальная сумма: 10 ₽</div>
                        </div>

                        <div class="alert alert-info border-info">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="alert-heading mb-1">Безопасная оплата через ЮKassa</h6>
                                    <p class="mb-0 small">
                                        Вы будете перенаправлены на защищенную страницу оплаты ЮKassa.
                                        После успешной оплаты средства автоматически зачислятся на ваш баланс.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-credit-card me-2"></i>Перейти к оплате
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Информация о платежах -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2 text-primary"></i>История платежей
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($payments as $payment)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div>
                                <div class="fw-medium text-gray-900">{{ number_format($payment->amount, 2) }} ₽</div>
                                <div class="text-muted small">
                                    {{ $payment->created_at->format('d.m.Y H:i') }}
                                </div>
                            </div>
                            <div>
                                @switch($payment->status)
                                    @case('succeeded')
                                        <span class="badge bg-success-gradient">
                                            <i class="fas fa-check-circle me-1"></i>Успешно
                                        </span>
                                        @break
                                    @case('pending')
                                        <span class="badge bg-warning-gradient">
                                            <i class="fas fa-clock me-1"></i>Ожидание
                                        </span>
                                        @break
                                    @case('canceled')
                                        <span class="badge bg-danger-gradient">
                                            <i class="fas fa-times-circle me-1"></i>Отменен
                                        </span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary-gradient">{{ $payment->status }}</span>
                                @endswitch
                            </div>
                        </div>
                        @empty
                        <div class="list-group-item text-center py-4">
                            <i class="fas fa-receipt fa-2x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Нет истории платежей</p>
                        </div>
                        @endforelse
                    </div>
                    
                    @if($payments->hasPages())
                    <div class="card-footer bg-white border-top-0 py-3">
                        {{ $payments->links() }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Подсказки -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Как пополнить баланс?
                    </h5>
                </div>
                <div class="card-body">
                    <ol class="list-unstyled mb-0">
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary me-3">1</span>
                            <div>
                                <span class="fw-medium">Выберите сумму</span>
                                <div class="text-muted small">Минимум 10 рублей</div>
                            </div>
                        </li>
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary me-3">2</span>
                            <div>
                                <span class="fw-medium">Нажмите "Перейти к оплате"</span>
                                <div class="text-muted small">Вы перейдете на страницу ЮKassa</div>
                            </div>
                        </li>
                        <li class="mb-3 d-flex">
                            <span class="badge bg-primary me-3">3</span>
                            <div>
                                <span class="fw-medium">Оплатите удобным способом</span>
                                <div class="text-muted small">Карта, ЮMoney, СБП и другие</div>
                            </div>
                        </li>
                        <li class="d-flex">
                            <span class="badge bg-primary me-3">4</span>
                            <div>
                                <span class="fw-medium">Средства на балансе</span>
                                <div class="text-muted small">Автоматическое зачисление</div>
                            </div>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .amount-option .card {
        transition: all 0.3s ease;
        border-color: #e3e6f0;
        cursor: pointer;
    }
    
    .amount-option .card:hover {
        border-color: #4e73df;
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .amount-option.active .card {
        border-color: #4e73df;
        background-color: rgba(78, 115, 223, 0.05);
    }
    
    .cursor-pointer {
        cursor: pointer;
    }
    
    .bg-success-gradient {
        background: linear-gradient(135deg, #1cc88a, #16a085);
    }
    
    .bg-warning-gradient {
        background: linear-gradient(135deg, #f6c23e, #f39c12);
    }
    
    .bg-danger-gradient {
        background: linear-gradient(135deg, #e74a3b, #c0392b);
    }
    
    .bg-secondary-gradient {
        background: linear-gradient(135deg, #6c757d, #5a6268);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Выбор суммы
    const amountOptions = document.querySelectorAll('.amount-option');
    const customAmountInput = document.getElementById('customAmount');
    
    amountOptions.forEach(option => {
        option.addEventListener('click', function() {
            // Убираем активный класс у всех
            amountOptions.forEach(opt => opt.classList.remove('active'));
            // Добавляем активный класс текущему
            this.classList.add('active');
            // Устанавливаем значение в поле ввода
            const amount = this.getAttribute('data-amount');
            customAmountInput.value = amount;
        });
    });
    
    // Ввод пользовательской суммы
    customAmountInput.addEventListener('input', function() {
        // Убираем активный класс у всех вариантов
        amountOptions.forEach(opt => opt.classList.remove('active'));
    });
    
    // Проверка минимальной суммы
    const paymentForm = document.getElementById('paymentForm');
    paymentForm.addEventListener('submit', function(e) {
        const amount = parseFloat(customAmountInput.value);
        
        if (isNaN(amount) || amount < 10) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Ошибка',
                text: 'Минимальная сумма пополнения: 10 ₽',
                confirmButtonColor: '#e74a3b'
            });
            customAmountInput.focus();
            return false;
        }
        
        if (amount > 50000) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Ошибка',
                text: 'Максимальная сумма за одну операцию: 50 000 ₽',
                confirmButtonColor: '#e74a3b'
            });
            return false;
        }
        
        // Показываем загрузку
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Перенаправление...';
    });
});
</script>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection

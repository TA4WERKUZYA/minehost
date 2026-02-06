@extends('layouts.app')

@section('title', $title)

@section('styles')
<style>
    .amount-option {
        transition: all 0.3s ease;
        border: 2px solid #e5e7eb;
        cursor: pointer;
    }
    
    .amount-option:hover {
        border-color: #3b82f6;
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .amount-option.active {
        border-color: #3b82f6;
        background-color: rgba(59, 130, 246, 0.05);
    }
    
    .bg-gradient-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    }
    
    .bg-gradient-green {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    }
    
    .bg-gradient-yellow {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }
    
    .bg-gradient-purple {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    }
</style>
@endsection

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto">
        <!-- Заголовок и баланс -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $title }}</h1>
                    <p class="text-gray-600">Пополняйте баланс для оплаты игровых серверов</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="bg-gradient-blue text-white px-6 py-4 rounded-xl shadow-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-blue-100 mb-1">Текущий баланс</div>
                                <div class="text-3xl font-bold">{{ number_format($user->balance, 2) }} ₽</div>
                            </div>
                            <div class="text-4xl">
                                <i class="fas fa-coins text-yellow-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Левая колонка: Форма пополнения -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-blue px-6 py-4">
                        <h2 class="text-xl font-semibold text-white">
                            <i class="fas fa-credit-card mr-2"></i>Пополнить баланс
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        @if(session('success'))
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-check-circle text-green-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-circle text-red-500"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <form id="paymentForm" method="POST" action="{{ route('balance.create') }}">
                            @csrf
                            
                            <!-- Выбор суммы -->
                            <div class="mb-8">
                                <label class="block text-sm font-medium text-gray-700 mb-4">
                                    Выберите сумму пополнения:
                                </label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach([100, 250, 500, 1000, 2000, 5000] as $amount)
                                    <div class="amount-option rounded-xl p-4 text-center" data-amount="{{ $amount }}">
                                        <div class="text-2xl font-bold text-gray-900 mb-1">{{ $amount }} ₽</div>
                                        <div class="text-xs text-gray-500">
                                            @if($amount >= 1000)
                                            <i class="fas fa-gift text-green-500 mr-1"></i>+5% бонус
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Поле для своей суммы -->
                            <div class="mb-8">
                                <label for="customAmount" class="block text-sm font-medium text-gray-700 mb-2">
                                    Или введите свою сумму:
                                </label>
                                <div class="relative">
                                    <input type="number" 
                                           id="customAmount" 
                                           name="amount"
                                           min="10"
                                           max="50000"
                                           step="1"
                                           required
                                           class="block w-full pl-12 pr-4 py-3 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Введите сумму">
                                    <div class="absolute left-0 inset-y-0 flex items-center pl-4">
                                        <span class="text-gray-500">₽</span>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Минимальная сумма: 10 ₽ • Максимальная: 50 000 ₽</p>
                            </div>

                            <!-- Информация -->
                            <div class="mb-8 bg-blue-50 border border-blue-200 rounded-xl p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-shield-alt text-blue-500 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">Безопасная оплата через ЮKassa</h3>
                                        <div class="mt-1 text-sm text-blue-700">
                                            <p class="mb-1">• Защищенное соединение SSL</p>
                                            <p class="mb-1">• Мгновенное зачисление средств</p>
                                            <p>• Поддержка карт, ЮMoney, СБП и других способов</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Кнопка оплаты -->
                            <button type="submit" 
                                    id="submitBtn"
                                    class="w-full bg-gradient-blue text-white py-4 px-6 rounded-xl font-semibold text-lg hover:opacity-90 transition duration-300 focus:outline-none focus:ring-4 focus:ring-blue-300">
                                <i class="fas fa-lock mr-2"></i>Перейти к оплате
                            </button>
                        </form>
                    </div>
                </div>

                <!-- История платежей -->
                <div class="mt-8 bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-purple px-6 py-4">
                        <h2 class="text-xl font-semibold text-white">
                            <i class="fas fa-history mr-2"></i>Последние платежи
                        </h2>
                    </div>
                    <div class="p-6">
                        @if($payments->count() > 0)
                        <div class="space-y-4">
                            @foreach($payments as $payment)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        @if($payment->status === 'succeeded')
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-check text-green-600"></i>
                                        </div>
                                        @elseif($payment->status === 'pending')
                                        <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-clock text-yellow-600"></i>
                                        </div>
                                        @else
                                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-times text-red-600"></i>
                                        </div>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-gray-900">
                                            {{ number_format($payment->amount, 2) }} ₽
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $payment->created_at->format('d.m.Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    @if($payment->status === 'succeeded')
                                    <span class="px-3 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full">
                                        Успешно
                                    </span>
                                    @elseif($payment->status === 'pending')
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">
                                        Ожидание
                                    </span>
                                    @else
                                    <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-medium rounded-full">
                                        Отменен
                                    </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-center py-8">
                            <i class="fas fa-receipt text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">Нет платежей</p>
                            <p class="text-sm text-gray-400 mt-1">После пополнения баланса здесь появится история</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Правая колонка: Информация -->
            <div class="space-y-8">
                <!-- Способы оплаты -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-green px-6 py-4">
                        <h2 class="text-xl font-semibold text-white">
                            <i class="fas fa-money-check-alt mr-2"></i>Способы оплаты
                        </h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fab fa-cc-visa text-blue-600"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Банковские карты</div>
                                    <div class="text-sm text-gray-500">Visa, Mastercard, Мир</div>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-mobile-alt text-purple-600"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">СБП (Сбербанк)</div>
                                    <div class="text-sm text-gray-500">Быстрый платеж через QR</div>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-wallet text-yellow-600"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">ЮMoney</div>
                                    <div class="text-sm text-gray-500">Кошелек ЮMoney</div>
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <div class="flex-shrink-0 w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-qrcode text-red-600"></i>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Другие способы</div>
                                    <div class="text-sm text-gray-500">Qiwi, терминалы и др.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Как это работает -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-yellow px-6 py-4">
                        <h2 class="text-xl font-semibold text-white">
                            <i class="fas fa-question-circle mr-2"></i>Как это работает?
                        </h2>
                    </div>
                    <div class="p-6">
                        <ol class="space-y-4">
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">1</span>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Выберите сумму</div>
                                    <div class="text-sm text-gray-500">От 10 до 50 000 рублей</div>
                                </div>
                            </li>
                            
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">2</span>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Перейдите к оплате</div>
                                    <div class="text-sm text-gray-500">Вы будете перенаправлены на ЮKassa</div>
                                </div>
                            </li>
                            
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">3</span>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Оплатите удобным способом</div>
                                    <div class="text-sm text-gray-500">Карта, ЮMoney, СБП или другой</div>
                                </div>
                            </li>
                            
                            <li class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">4</span>
                                </div>
                                <div class="ml-3">
                                    <div class="font-medium text-gray-900">Средства на балансе</div>
                                    <div class="text-sm text-gray-500">Автоматическое зачисление</div>
                                </div>
                            </li>
                        </ol>
                    </div>
                </div>

                <!-- Поддержка -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gray-800 px-6 py-4">
                        <h2 class="text-xl font-semibold text-white">
                            <i class="fas fa-headset mr-2"></i>Нужна помощь?
                        </h2>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">
                            Если возникли проблемы с оплатой или средства не зачислились на баланс, обратитесь в поддержку.
                        </p>
                        <a href="mailto:support@allyhost.ru" 
                           class="inline-flex items-center px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition duration-300">
                            <i class="fas fa-envelope mr-2"></i>
                            Написать в поддержку
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Понятно'
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
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'Понятно'
            });
            return false;
        }
        
        // Показываем загрузку
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Перенаправление на ЮKassa...';
    });
    
    // Автоматический выбор 500 рублей при загрузке
    setTimeout(() => {
        if (!customAmountInput.value) {
            customAmountInput.value = 500;
            amountOptions[2]?.classList.add('active'); // 500 рублей
        }
    }, 100);
});
</script>
@endsection

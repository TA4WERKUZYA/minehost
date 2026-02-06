@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto">
        <div class="text-center">
            <!-- Иконка успеха -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-green-600 text-5xl"></i>
                </div>
            </div>
            
            <!-- Заголовок -->
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Оплата успешно завершена! 🎉</h1>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                Средства зачислены на ваш баланс. Теперь вы можете создать новый сервер или продлить существующий.
            </p>
            
            <!-- Детали платежа -->
            @if($payment)
            <div class="bg-white rounded-2xl shadow-xl p-8 max-w-lg mx-auto mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                    <i class="fas fa-receipt mr-2 text-blue-500"></i>Детали платежа
                </h2>
                
                <div class="space-y-6">
                    <div class="flex justify-between items-center pb-4 border-b">
                        <div class="text-gray-600">Сумма:</div>
                        <div class="text-3xl font-bold text-green-600">{{ number_format($payment->amount, 2) }} ₽</div>
                    </div>
                    
                    <div class="flex justify-between items-center pb-4 border-b">
                        <div class="text-gray-600">Статус:</div>
                        <div>
                            <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold">
                                <i class="fas fa-check-circle mr-2"></i>Успешно
                            </span>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center pb-4 border-b">
                        <div class="text-gray-600">Дата:</div>
                        <div class="font-medium">{{ $payment->paid_at->format('d.m.Y H:i') }}</div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <div class="text-gray-600">ID платежа:</div>
                        <div class="font-mono text-sm text-gray-500">{{ $payment->yookassa_id }}</div>
                    </div>
                </div>
            </div>
            @endif
            
            <!-- Новый баланс -->
            <div class="mb-8">
                <div class="inline-flex items-center px-6 py-3 bg-gradient-blue text-white rounded-xl">
                    <i class="fas fa-wallet text-2xl mr-3"></i>
                    <div class="text-left">
                        <div class="text-sm font-medium opacity-90">Новый баланс</div>
                        <div class="text-2xl font-bold">{{ number_format(Auth::user()->balance, 2) }} ₽</div>
                    </div>
                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('dashboard.create') }}" 
                   class="btn-primary inline-flex items-center justify-center px-8 py-4 text-lg">
                    <i class="fas fa-plus-circle mr-2"></i>
                    Создать сервер
                </a>
                
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center justify-center px-8 py-4 text-lg border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition duration-300">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Вернуться в панель
                </a>
                
                <a href="{{ route('balance.index') }}" 
                   class="inline-flex items-center justify-center px-8 py-4 text-lg border-2 border-blue-200 text-blue-600 rounded-xl hover:bg-blue-50 transition duration-300">
                    <i class="fas fa-redo mr-2"></i>
                    Пополнить еще
                </a>
            </div>
            
            <!-- Подсказка -->
            <div class="mt-12 pt-8 border-t">
                <p class="text-gray-500">
                    <i class="fas fa-info-circle mr-2 text-blue-500"></i>
                    Если средства не появились на балансе в течение 5 минут, пожалуйста, обратитесь в поддержку.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

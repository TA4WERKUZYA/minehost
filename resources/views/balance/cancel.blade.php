@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto">
        <div class="text-center">
            <!-- Иконка отмены -->
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full">
                    <i class="fas fa-times-circle text-red-600 text-5xl"></i>
                </div>
            </div>
            
            <!-- Заголовок -->
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Платеж отменен</h1>
            <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                Оплата не была завершена. Средства не списаны с вашей карты.
                Вы можете попробовать снова или выбрать другой способ оплаты.
            </p>
            
            <!-- Предупреждение -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 max-w-2xl mx-auto mb-8">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-lg font-medium text-yellow-800">Внимание!</h3>
                        <div class="mt-2 text-yellow-700">
                            <p>Если деньги были списаны, но платеж отменен, средства вернутся на вашу карту в течение 1-10 рабочих дней.</p>
                            <p class="mt-2">Для уточнения статуса платежа обратитесь в поддержку вашего банка.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Кнопки действий -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('balance.index') }}" 
                   class="btn-primary inline-flex items-center justify-center px-8 py-4 text-lg">
                    <i class="fas fa-redo mr-2"></i>
                    Попробовать снова
                </a>
                
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center justify-center px-8 py-4 text-lg border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition duration-300">
                    <i class="fas fa-home mr-2"></i>
                    На главную
                </a>
            </div>
            
            <!-- Помощь -->
            <div class="mt-12 pt-8 border-t">
                <div class="flex flex-col md:flex-row items-center justify-center gap-6">
                    <div class="text-left">
                        <h3 class="font-semibold text-gray-900 mb-2">Нужна помощь?</h3>
                        <p class="text-gray-600">Если у вас возникли проблемы с оплатой, свяжитесь с нашей поддержкой.</p>
                    </div>
                    <a href="mailto:support@allyhost.ru" 
                       class="inline-flex items-center px-6 py-3 bg-gray-800 text-white rounded-lg hover:bg-gray-700 transition duration-300">
                        <i class="fas fa-headset mr-2"></i>
                        Связаться с поддержкой
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="text-center py-5">
                <div class="icon-circle-lg bg-success-light mb-4 mx-auto">
                    <i class="fas fa-check-circle text-success fa-4x"></i>
                </div>
                
                <h1 class="h2 mb-3 text-gray-900">Оплата успешно завершена!</h1>
                <p class="lead text-muted mb-4">
                    Средства зачислены на ваш баланс. Теперь вы можете создать новый сервер или продлить существующий.
                </p>
                
                @if($payment)
                <div class="card border-success mb-4">
                    <div class="card-body text-center">
                        <h5 class="card-title text-success">
                            <i class="fas fa-credit-card me-2"></i>Детали платежа
                        </h5>
                        <div class="row mt-3">
                            <div class="col-md-6 mb-3">
                                <div class="text-muted">Сумма:</div>
                                <div class="h4 text-gray-900">{{ number_format($payment->amount, 2) }} ₽</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="text-muted">Статус:</div>
                                <div class="h4">
                                    <span class="badge bg-success-gradient">Успешно</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-muted small">
                            ID платежа: {{ $payment->yookassa_id }}<br>
                            Дата: {{ $payment->paid_at->format('d.m.Y H:i') }}
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="d-grid gap-2 d-md-flex justify-content-center">
                    <a href="{{ route('dashboard.create') }}" class="btn btn-primary btn-lg me-md-2">
                        <i class="fas fa-plus-circle me-2"></i>Создать сервер
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-tachometer-alt me-2"></i>Вернуться в панель
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .icon-circle-lg {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 4px solid rgba(28, 200, 138, 0.2);
    }
    
    .bg-success-light {
        background-color: rgba(28, 200, 138, 0.1);
    }
    
    .bg-success-gradient {
        background: linear-gradient(135deg, #1cc88a, #16a085);
    }
</style>
@endsection

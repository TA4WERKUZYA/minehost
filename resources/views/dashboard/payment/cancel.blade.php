@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="text-center py-5">
                <div class="icon-circle-lg bg-danger-light mb-4 mx-auto">
                    <i class="fas fa-times-circle text-danger fa-4x"></i>
                </div>
                
                <h1 class="h2 mb-3 text-gray-900">Платеж отменен</h1>
                <p class="lead text-muted mb-4">
                    Оплата не была завершена. Средства не списаны с вашей карты.
                    Вы можете попробовать снова или выбрать другой способ оплаты.
                </p>
                
                <div class="alert alert-warning mb-4">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <p class="mb-0">Если деньги были списаны, но платеж отменен, средства вернутся на вашу карту в течение 1-10 рабочих дней.</p>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-center">
                    <a href="{{ route('payment.index') }}" class="btn btn-primary btn-lg me-md-2">
                        <i class="fas fa-redo me-2"></i>Попробовать снова
                    </a>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-home me-2"></i>На главную
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
        border: 4px solid rgba(231, 74, 59, 0.2);
    }
    
    .bg-danger-light {
        background-color: rgba(231, 74, 59, 0.1);
    }
</style>
@endsection

@extends('layouts.admin-app')

@section('title', 'Заказы пользователя ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-shopping-cart mr-2"></i>Заказы пользователя: {{ $user->name }}
        </h1>
        <div>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>К списку пользователей
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="fas fa-user-edit mr-2"></i>Редактировать пользователя
            </a>
        </div>
    </div>

    @if($orders->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Список заказов ({{ $orders->total() }})</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Тариф</th>
                                <th>Сумма</th>
                                <th>Статус</th>
                                <th>Дата создания</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($orders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>{{ $order->plan->name ?? 'Не указан' }}</td>
                                <td>{{ number_format($order->amount, 2) }} ₽</td>
                                <td>
                                    @switch($order->status)
                                        @case('completed')
                                            <span class="badge bg-success">Завершен</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-warning">Ожидание</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Отменен</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $order->status }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $order->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($orders->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Показано {{ $orders->firstItem() }} - {{ $orders->lastItem() }} из {{ $orders->total() }}
                        </div>
                        {{ $orders->links() }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">У пользователя нет заказов</h5>
                <p class="text-muted">{{ $user->name }} еще не сделал ни одного заказа.</p>
            </div>
        </div>
    @endif
</div>
@endsection

@extends('layouts.admin-app')

@section('title', 'Транзакции пользователя ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-exchange-alt mr-2"></i>Транзакции пользователя: {{ $user->name }}
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

    @if($transactions->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">История транзакций ({{ $transactions->total() }})</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Тип</th>
                                <th>Сумма</th>
                                <th>Описание</th>
                                <th>Баланс до</th>
                                <th>Баланс после</th>
                                <th>Администратор</th>
                                <th>Дата</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>#{{ $transaction->id }}</td>
                                <td>
                                    @switch($transaction->type)
                                        @case('deposit')
                                            <span class="badge bg-success">Пополнение</span>
                                            @break
                                        @case('withdrawal')
                                            <span class="badge bg-danger">Списание</span>
                                            @break
                                        @case('payment')
                                            <span class="badge bg-info">Оплата</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $transaction->type }}</span>
                                    @endswitch
                                </td>
                                <td class="{{ $transaction->amount > 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $transaction->amount > 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }} ₽
                                </td>
                                <td>{{ $transaction->description }}</td>
                                <td>{{ number_format($transaction->old_balance, 2) }} ₽</td>
                                <td>{{ number_format($transaction->new_balance, 2) }} ₽</td>
                                <td>
                                    @if($transaction->admin_id)
                                        {{ $transaction->admin->name ?? 'Администратор' }}
                                    @else
                                        <span class="text-muted">Система</span>
                                    @endif
                                </td>
                                <td>{{ $transaction->created_at->format('d.m.Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($transactions->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Показано {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} из {{ $transactions->total() }}
                        </div>
                        {{ $transactions->links() }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-exchange-alt fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">У пользователя нет транзакций</h5>
                <p class="text-muted">{{ $user->name }} еще не совершал транзакций.</p>
            </div>
        </div>
    @endif
</div>
@endsection

@extends('layouts.admin-app')

@section('title', 'Серверы пользователя ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-server mr-2"></i>Серверы пользователя: {{ $user->name }}
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

    @if($servers->count() > 0)
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Список серверов ({{ $servers->total() }})</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Название</th>
                                <th>Нода</th>
                                <th>Тариф</th>
                                <th>Статус</th>
                                <th>Дата создания</th>
                                <th>Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servers as $server)
                            <tr>
                                <td>#{{ $server->id }}</td>
                                <td>{{ $server->name }}</td>
                                <td>{{ $server->node->name ?? 'Не указана' }}</td>
                                <td>{{ $server->plan->name ?? 'Не указан' }}</td>
                                <td>
                                    @switch($server->status)
                                        @case('active')
                                            <span class="badge bg-success">Активен</span>
                                            @break
                                        @case('stopped')
                                            <span class="badge bg-warning">Остановлен</span>
                                            @break
                                        @case('suspended')
                                            <span class="badge bg-danger">Заблокирован</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $server->status }}</span>
                                    @endswitch
                                </td>
                                <td>{{ $server->created_at->format('d.m.Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.servers.show', $server) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($servers->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="text-muted">
                            Показано {{ $servers->firstItem() }} - {{ $servers->lastItem() }} из {{ $servers->total() }}
                        </div>
                        {{ $servers->links() }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="card shadow">
            <div class="card-body text-center py-5">
                <i class="fas fa-server fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">У пользователя нет серверов</h5>
                <p class="text-muted">{{ $user->name }} еще не создал ни одного сервера.</p>
            </div>
        </div>
    @endif
</div>
@endsection

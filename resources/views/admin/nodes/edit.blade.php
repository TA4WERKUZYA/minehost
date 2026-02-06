@extends('layouts.admin-app')

@section('title', 'Редактирование ноды')

@section('content')
<div class="container-fluid">
    <!-- Заголовок страницы -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit mr-2"></i>Редактирование ноды #{{ $node->id }}
        </h1>
        <a href="/admin/nodes" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Назад к списку
        </a>
    </div>

    <!-- Форма редактирования -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Основная информация</h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="/admin/nodes/{{ $node->id }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="name" class="form-label">Название ноды <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="{{ old('name', $node->name) }}" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="hostname" class="form-label">Хостнейм <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="hostname" name="hostname" 
                               value="{{ old('hostname', $node->hostname) }}" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ip_address" class="form-label">IP адрес <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ip_address" name="ip_address" 
                               value="{{ old('ip_address', $node->ip_address) }}" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="location" class="form-label">Локация <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="location" name="location" 
                               value="{{ old('location', $node->location) }}" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="api_url" class="form-label">API URL демона <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="api_url" name="api_url" 
                               value="{{ old('api_url', $node->api_url ?? 'http://' . $node->ip_address . ':' . ($node->daemon_port ?? 8080)) }}" required>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="api_token" class="form-label">API токен <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="api_token" name="api_token" 
                               value="{{ old('api_token', $node->api_token ?? $node->daemon_token) }}" required>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="total_memory" class="form-label">Общая память (MB) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="total_memory" name="total_memory" 
                               value="{{ old('total_memory', $node->total_memory) }}" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="total_disk" class="form-label">Общий диск (GB) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="total_disk" name="total_disk" 
                               value="{{ old('total_disk', $node->total_disk) }}" required>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label for="total_cpu" class="form-label">Всего ядер CPU</label>
                        <input type="number" class="form-control" id="total_cpu" name="total_cpu" 
                               value="{{ old('total_cpu', $node->total_cpu) }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="daemon_port" class="form-label">Порт демона</label>
                        <input type="number" class="form-control" id="daemon_port" name="daemon_port" 
                               value="{{ old('daemon_port', $node->daemon_port ?? 8080) }}">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Описание</label>
                        <textarea class="form-control" id="description" name="description" rows="2">{{ old('description', $node->description) }}</textarea>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $node->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Нода активна</label>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="accept_new_servers" name="accept_new_servers" value="1" 
                                   {{ old('accept_new_servers', $node->accept_new_servers) ? 'checked' : '' }}>
                            <label class="form-check-label" for="accept_new_servers">Принимать новые серверы</label>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        <i class="fas fa-times mr-2"></i>Отмена
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>Сохранить изменения
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

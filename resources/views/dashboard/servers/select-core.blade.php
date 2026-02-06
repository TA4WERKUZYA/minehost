@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ $title }}</h5>
                </div>
                <div class="card-body">
                    
                    <!-- Информация о текущем ядре -->
                    @if($server->core)
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Текущее ядро:</h6>
                        <div class="d-flex align-items-center mt-2">
                            <div class="me-3">
                                <i class="fas fa-cube fa-2x text-primary"></i>
                            </div>
                            <div>
                                <h5 class="mb-1">
                                    <span class="badge bg-{{ $server->core->color }} me-2">
                                        {{ strtoupper($server->core->name) }}
                                    </span>
                                    {{ $server->core->version }}
                                </h5>
                                <p class="mb-0 text-muted">
                                    <small>
                                        <i class="far fa-clock"></i> Установлено: 
                                        {{ $server->installed_at ? $server->installed_at->format('d.m.Y H:i') : 'Не установлено' }}
                                    </small>
                                </p>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Предупреждение о необходимости остановки сервера -->
                    @if(!$server->canChangeCore())
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle"></i> Внимание!</h6>
                        <p class="mb-0">
                            Для смены ядра необходимо остановить сервер. 
                            Текущий статус: <strong>{{ $server->status }}</strong>
                        </p>
                    </div>
                    @endif
                    
                    <!-- Доступные ядра -->
                    <div class="row mt-4">
                        @foreach($cores as $coreName => $coreVersions)
                        <div class="col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">
                                            <i class="fas {{ $coreVersions->first()->icon }} me-2 text-{{ $coreVersions->first()->color }}"></i>
                                            {{ ucfirst($coreName) }}
                                        </h6>
                                        @if(isset($latestVersions[$coreName]) && 
                                            $server->core && 
                                            $server->core->name === $coreName &&
                                            $server->needsCoreUpdate())
                                        <span class="badge bg-warning">Доступно обновление</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="list-group list-group-flush">
                                        @foreach($coreVersions as $core)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">
                                                        Версия {{ $core->version }}
                                                        @if($core->is_stable)
                                                        <span class="badge bg-success ms-2">Стабильная</span>
                                                        @endif
                                                        @if($core->is_default)
                                                        <span class="badge bg-primary ms-2">По умолчанию</span>
                                                        @endif
                                                    </h6>
                                                    <p class="mb-0 text-muted small">
                                                        <i class="fas fa-hdd"></i> {{ $core->file_size_formatted }}
                                                        @if($core->is_active)
                                                        <span class="ms-2 text-success">
                                                            <i class="fas fa-check-circle"></i> Доступно
                                                        </span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div>
                                                    @if($server->canChangeCore())
                                                    <form action="{{ route('servers.core.install', $server) }}" method="POST" 
                                                          onsubmit="return confirm('Установить ядро {{ $core->name }} {{ $core->version }}?')">
                                                        @csrf
                                                        <input type="hidden" name="core_id" value="{{ $core->id }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                                            @if($server->core && $server->core->id === $core->id)
                                                            <i class="fas fa-check"></i> Установлено
                                                            @else
                                                            <i class="fas fa-download"></i> Установить
                                                            @endif
                                                        </button>
                                                    </form>
                                                    @else
                                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                                        <i class="fas fa-ban"></i> Недоступно
                                                    </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Кнопки навигации -->
                    <div class="mt-4">
                        <a href="{{ route('servers.show', $server) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Назад к серверу
                        </a>
                        
                        @if($server->needsCoreUpdate() && $server->canChangeCore())
                        <button class="btn btn-warning ms-2" data-bs-toggle="modal" data-bs-target="#updateModal">
                            <i class="fas fa-sync-alt"></i> Обновить до последней версии
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно для обновления -->
@if($server->needsCoreUpdate() && $server->canChangeCore())
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Обновление ядра</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Вы действительно хотите обновить ядро с 
                   <strong>{{ $server->core->version }}</strong> 
                   на 
                   <strong>{{ $latestVersions[$server->core_type]->version }}</strong>?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Сервер будет остановлен на время обновления!
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <form action="{{ route('servers.core.install', $server) }}" method="POST">
                    @csrf
                    <input type="hidden" name="core_id" value="{{ $latestVersions[$server->core_type]->id }}">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-sync-alt"></i> Обновить
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

<!-- JavaScript для обновления статуса -->
@if($server->status === 'core_installing')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Периодически проверяем статус установки
    let checkInterval = setInterval(function() {
        fetch('{{ route("servers.core.status", $server) }}')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'core_installed' || data.status === 'core_install_failed') {
                    clearInterval(checkInterval);
                    // Обновляем страницу через 3 секунды
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                }
            });
    }, 3000); // Проверяем каждые 3 секунды
});
</script>
@endif
@endsection

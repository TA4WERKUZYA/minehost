@extends('layouts.admin-app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>{{ $title }}</h2>
        </div>
    </div>
    
    <!-- Общая статистика -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Всего ядер</h5>
                            <h2 class="mb-0">{{ $totalCores }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-cube fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">Активных</h5>
                            <h2 class="mb-0">{{ $activeCores }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-check-circle fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title">По умолчанию</h5>
                            <h2 class="mb-0">{{ $defaultCores }}</h2>
                        </div>
                        <div>
                            <i class="fas fa-star fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Распределение по типам -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-gamepad"></i> Распределение по типам игр</h5>
                </div>
                <div class="card-body">
                    <canvas id="gameTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list"></i> Распределение по названиям</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th>Количество</th>
                                    <th>Процент</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byName as $core)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ $core->color }} me-2">
                                            {{ strtoupper($core->name) }}
                                        </span>
                                    </td>
                                    <td>{{ $core->count }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-{{ $core->color }}" 
                                                 style="width: {{ ($core->count / $totalCores) * 100 }}%">
                                                {{ round(($core->count / $totalCores) * 100, 1) }}%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Самые используемые ядра -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-server"></i> Самые используемые ядра</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Ядро</th>
                                    <th>Тип игры</th>
                                    <th>Версия</th>
                                    <th>Серверов</th>
                                    <th>Статус</th>
                                    <th>Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mostUsedCores as $core)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas {{ $core->icon }} fa-2x me-3 text-{{ $core->color }}"></i>
                                            <div>
                                                <h6 class="mb-0">{{ ucfirst($core->name) }}</h6>
                                                <small class="text-muted">{{ $core->file_name }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $core->game_type }}</span>
                                    </td>
                                    <td>{{ $core->version }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $core->servers_count }}</span>
                                    </td>
                                    <td>
                                        @if($core->is_active)
                                        <span class="badge bg-success">Активно</span>
                                        @else
                                        <span class="badge bg-danger">Неактивно</span>
                                        @endif
                                        
                                        @if($core->is_default)
                                        <span class="badge bg-warning ms-1">По умолчанию</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.cores.edit', $core) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.cores.index') }}?filter={{ $core->name }}" class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // График распределения по типам игр
    const gameTypeData = {
        labels: {!! json_encode(array_keys($byGameType->toArray())) !!},
        datasets: [{
            data: {!! json_encode(array_values($byGameType->toArray())) !!},
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 99, 132, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    };
    
    const gameTypeCtx = document.getElementById('gameTypeChart').getContext('2d');
    new Chart(gameTypeCtx, {
        type: 'pie',
        data: gameTypeData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            label += context.parsed + ' ядер';
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection

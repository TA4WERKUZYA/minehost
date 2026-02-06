@extends('layouts.admin-app')

@section('title', 'Создание ноды')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-1 text-gray-900">Создание ноды</h1>
            <p class="text-muted mb-0">Добавление нового физического сервера для размещения Minecraft серверов</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.nodes') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Назад к списку
            </a>
        </div>
    </div>

    <!-- Alerts -->
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="alert-heading mb-1">Ошибки в форме</h5>
                    <ul class="mb-0 ps-0" style="list-style: none;">
                        @foreach($errors->all() as $error)
                            <li><i class="fas fa-times-circle text-danger me-2"></i>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>Новая нода
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.nodes.store') }}" id="createNodeForm">
                        @csrf
                        
                        <div class="row g-4">
                            <!-- Основная информация -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label fw-medium">
                                        <i class="fas fa-font me-1 text-muted"></i>
                                        Название ноды <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Например: Node-01, Germany-DE1"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Уникальное имя для идентификации ноды
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hostname" class="form-label fw-medium">
                                        <i class="fas fa-network-wired me-1 text-muted"></i>
                                        Хостнейм <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('hostname') is-invalid @enderror" 
                                           id="hostname" 
                                           name="hostname" 
                                           value="{{ old('hostname') }}" 
                                           placeholder="Например: node01.example.com"
                                           required>
                                    @error('hostname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Доменное имя или FQDN сервера
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Сетевая информация -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ip_address" class="form-label fw-medium">
                                        <i class="fas fa-globe me-1 text-muted"></i>
                                        IP адрес <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('ip_address') is-invalid @enderror" 
                                           id="ip_address" 
                                           name="ip_address" 
                                           value="{{ old('ip_address') }}" 
                                           placeholder="Например: 192.168.1.100"
                                           required>
                                    @error('ip_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Основной IP адрес сервера
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location" class="form-label fw-medium">
                                        <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                                        Локация <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('location') is-invalid @enderror" 
                                           id="location" 
                                           name="location" 
                                           value="{{ old('location') }}" 
                                           placeholder="Например: Москва, Германия, США"
                                           required>
                                    @error('location')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Географическое расположение сервера
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="datacenter" class="form-label fw-medium">
                                        <i class="fas fa-data-center me-1 text-muted"></i>
                                        Датацентр
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('datacenter') is-invalid @enderror" 
                                           id="datacenter" 
                                           name="datacenter" 
                                           value="{{ old('datacenter') }}" 
                                           placeholder="Например: DC-1, Hetzner, OVH">
                                    @error('datacenter')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- API конфигурация -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="api_url" class="form-label fw-medium">
                                        <i class="fas fa-plug me-1 text-muted"></i>
                                        API URL <span class="text-danger">*</span>
                                    </label>
                                    <input type="url" 
                                           class="form-control @error('api_url') is-invalid @enderror" 
                                           id="api_url" 
                                           name="api_url" 
                                           value="{{ old('api_url') }}" 
                                           placeholder="http://ip:8080"
                                           required>
                                    @error('api_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        URL для подключения к демону (Pterodactyl)
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="api_token" class="form-label fw-medium">
                                        <i class="fas fa-key me-1 text-muted"></i>
                                        API токен <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('api_token') is-invalid @enderror" 
                                           id="api_token" 
                                           name="api_token" 
                                           value="{{ old('api_token') }}" 
                                           required>
                                    @error('api_token')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Токен для аутентификации API
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Порта и SSH -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="daemon_port" class="form-label fw-medium">
                                        <i class="fas fa-plug me-1 text-muted"></i>
                                        Порт демона
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('daemon_port') is-invalid @enderror" 
                                           id="daemon_port" 
                                           name="daemon_port" 
                                           value="{{ old('daemon_port', 8080) }}"
                                           min="1" max="65535">
                                    @error('daemon_port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ssh_port" class="form-label fw-medium">
                                        <i class="fas fa-terminal me-1 text-muted"></i>
                                        SSH порт
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('ssh_port') is-invalid @enderror" 
                                           id="ssh_port" 
                                           name="ssh_port" 
                                           value="{{ old('ssh_port', 22) }}"
                                           min="1" max="65535">
                                    @error('ssh_port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="game_port_start" class="form-label fw-medium">
                                        <i class="fas fa-gamepad me-1 text-muted"></i>
                                        Стартовый порт
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('game_port_start') is-invalid @enderror" 
                                           id="game_port_start" 
                                           name="game_port_start" 
                                           value="{{ old('game_port_start', 25565) }}"
                                           min="1024" max="65535">
                                    @error('game_port_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Первый порт для игровых серверов
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Ресурсы -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_memory" class="form-label fw-medium">
                                        <i class="fas fa-memory me-1 text-muted"></i>
                                        Память (MB) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('total_memory') is-invalid @enderror" 
                                           id="total_memory" 
                                           name="total_memory" 
                                           value="{{ old('total_memory', 16384) }}" 
                                           min="1024" max="524288"
                                           required>
                                    @error('total_memory')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Доступная оперативная память
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_disk" class="form-label fw-medium">
                                        <i class="fas fa-hdd me-1 text-muted"></i>
                                        Диск (GB) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('total_disk') is-invalid @enderror" 
                                           id="total_disk" 
                                           name="total_disk" 
                                           value="{{ old('total_disk', 500) }}" 
                                           min="10" max="10000"
                                           required>
                                    @error('total_disk')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Доступное дисковое пространство
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total_cpu" class="form-label fw-medium">
                                        <i class="fas fa-microchip me-1 text-muted"></i>
                                        Ядра CPU
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('total_cpu') is-invalid @enderror" 
                                           id="total_cpu" 
                                           name="total_cpu" 
                                           value="{{ old('total_cpu', 8) }}"
                                           min="1" max="128">
                                    @error('total_cpu')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Количество ядер процессора
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="max_servers" class="form-label fw-medium">
                                        <i class="fas fa-server me-1 text-muted"></i>
                                        Макс. серверов
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('max_servers') is-invalid @enderror" 
                                           id="max_servers" 
                                           name="max_servers" 
                                           value="{{ old('max_servers', 50) }}"
                                           min="1" max="1000">
                                    @error('max_servers')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Описание -->
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="description" class="form-label fw-medium">
                                        <i class="fas fa-align-left me-1 text-muted"></i>
                                        Описание
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="4"
                                              placeholder="Описание ноды, дополнительные заметки...">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Настройки -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-medium">
                                        <i class="fas fa-cogs me-1 text-muted"></i>
                                        Настройки ноды
                                    </label>
                                    <div class="card bg-light border-0 p-3">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="is_active" 
                                                           name="is_active" 
                                                           value="1"
                                                           {{ old('is_active', true) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-medium" for="is_active">
                                                        <i class="fas fa-power-off me-2 text-success"></i>
                                                        Нода активна
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="accept_new_servers" 
                                                           name="accept_new_servers" 
                                                           value="1"
                                                           {{ old('accept_new_servers', true) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-medium" for="accept_new_servers">
                                                        <i class="fas fa-plus-circle me-2 text-info"></i>
                                                        Принимать новые серверы
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="is_default" 
                                                           name="is_default" 
                                                           value="1"
                                                           {{ old('is_default', false) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-medium" for="is_default">
                                                        <i class="fas fa-star me-2 text-warning"></i>
                                                        Нода по умолчанию
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="is_sftp_enabled" 
                                                           name="is_sftp_enabled" 
                                                           value="1"
                                                           {{ old('is_sftp_enabled', true) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-medium" for="is_sftp_enabled">
                                                        <i class="fas fa-file-upload me-2 text-secondary"></i>
                                                        Включить SFTP
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="is_cluster" 
                                                           name="is_cluster" 
                                                           value="1"
                                                           {{ old('is_cluster', false) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-medium" for="is_cluster">
                                                        <i class="fas fa-cluster me-2 text-primary"></i>
                                                        Кластерная нода
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Блок подсказок -->
                            <div class="col-md-6">
                                <div class="card border-0 bg-light">
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-3">
                                            <i class="fas fa-lightbulb me-2 text-warning"></i>Полезные подсказки:
                                        </h6>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <small>API токен должен иметь права на чтение и запись</small>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <small>Убедитесь что демон доступен по указанному URL</small>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <small>Для SSH подключения должны быть открыты порты</small>
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                                <small>Диапазон игровых портов должен быть свободен</small>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                            <a href="{{ route('admin.nodes') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-times me-2"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="saveButton">
                                <i class="fas fa-plus-circle me-2"></i>Создать ноду
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar Info -->
        <div class="col-lg-4">
            <!-- Requirements Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>Требования
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-info border-info mb-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-server fa-2x"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="alert-heading mb-1">Демон Pterodactyl</h6>
                                <p class="mb-0 small">Требуется установленный и настроенный Wings демон</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex align-items-center py-3">
                            <i class="fas fa-check-circle text-success me-3"></i>
                            <div>
                                <span class="fw-medium">Docker</span>
                                <div class="text-muted small">Должен быть установлен и запущен</div>
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex align-items-center py-3">
                            <i class="fas fa-check-circle text-success me-3"></i>
                            <div>
                                <span class="fw-medium">Внешний IP</span>
                                <div class="text-muted small">Сервер должен быть доступен извне</div>
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex align-items-center py-3">
                            <i class="fas fa-check-circle text-success me-3"></i>
                            <div>
                                <span class="fw-medium">Открытые порты</span>
                                <div class="text-muted small">Необходимо открыть порты демона и SSH</div>
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex align-items-center py-3">
                            <i class="fas fa-check-circle text-success me-3"></i>
                            <div>
                                <span class="fw-medium">API токен</span>
                                <div class="text-muted small">Должен быть сгенерирован в панели Pterodactyl</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2 text-primary"></i>Быстрая настройка
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-block" onclick="fillStandardConfig()">
                            <i class="fas fa-bolt me-2"></i>Стандартная конфигурация
                        </button>
                        
                        <button type="button" class="btn btn-outline-success btn-block" onclick="fillHighPerformance()">
                            <i class="fas fa-rocket me-2"></i>Высокая производительность
                        </button>
                        
                        <button type="button" class="btn btn-outline-warning btn-block" onclick="fillTestConfig()">
                            <i class="fas fa-flask me-2"></i>Тестовая конфигурация
                        </button>
                        
                        <div class="text-center mt-3">
                            <a href="https://pterodactyl.io/wings/1.0/installing.html" 
                               target="_blank" 
                               class="text-decoration-none">
                                <i class="fas fa-external-link-alt me-1"></i>
                                <small>Документация по установке</small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Стили из общей темы */
    .form-control-lg {
        height: calc(3.5rem + 2px);
        font-size: 1rem;
    }
    
    .form-check-input:checked {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
    }
    
    .list-group-item {
        border-color: rgba(0,0,0,0.05);
        transition: background-color 0.3s ease;
    }
    
    .list-group-item:hover {
        background-color: rgba(78, 115, 223, 0.03);
    }
    
    /* Анимация кнопки сохранения */
    #saveButton {
        transition: all 0.3s ease;
    }
    
    #saveButton:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
    }
    
    /* Иконки в списке */
    .fas.fa-check-circle.text-success {
        font-size: 1.2rem;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Валидация формы перед отправкой
    const createForm = document.getElementById('createNodeForm');
    createForm?.addEventListener('submit', function(e) {
        let hasErrors = false;
        
        // Очистка предыдущих ошибок
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
        
        // Проверка обязательных полей
        const requiredFields = ['name', 'hostname', 'ip_address', 'location', 'api_url', 'api_token', 'total_memory', 'total_disk'];
        requiredFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && !field.value.trim()) {
                showError(field, `Поле обязательно для заполнения`);
                hasErrors = true;
            }
        });
        
        // Проверка IP адреса
        const ipField = document.getElementById('ip_address');
        if (ipField.value.trim() && !isValidIP(ipField.value)) {
            showError(ipField, 'Введите корректный IP адрес');
            hasErrors = true;
        }
        
        // Проверка URL
        const urlField = document.getElementById('api_url');
        if (urlField.value.trim() && !isValidUrl(urlField.value)) {
            showError(urlField, 'Введите корректный URL (начинается с http:// или https://)');
            hasErrors = true;
        }
        
        // Проверка портов
        const portFields = ['daemon_port', 'ssh_port', 'game_port_start'];
        portFields.forEach(fieldName => {
            const field = document.getElementById(fieldName);
            if (field && field.value) {
                const port = parseInt(field.value);
                if (port < 1 || port > 65535) {
                    showError(field, 'Порт должен быть в диапазоне 1-65535');
                    hasErrors = true;
                }
            }
        });
        
        // Проверка ресурсов
        const memoryField = document.getElementById('total_memory');
        if (memoryField.value) {
            const memory = parseInt(memoryField.value);
            if (memory < 1024) {
                showError(memoryField, 'Минимальная память: 1024 MB (1GB)');
                hasErrors = true;
            }
            if (memory > 524288) {
                showError(memoryField, 'Максимальная память: 524288 MB (512GB)');
                hasErrors = true;
            }
        }
        
        const diskField = document.getElementById('total_disk');
        if (diskField.value) {
            const disk = parseInt(diskField.value);
            if (disk < 10) {
                showError(diskField, 'Минимальный диск: 10 GB');
                hasErrors = true;
            }
            if (disk > 10000) {
                showError(diskField, 'Максимальный диск: 10000 GB');
                hasErrors = true;
            }
        }
        
        if (hasErrors) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Ошибки в форме',
                text: 'Пожалуйста, исправьте ошибки в форме',
                confirmButtonColor: '#e74a3b'
            });
        }
    });
    
    // Функция для отображения ошибок
    function showError(input, message) {
        input.classList.add('is-invalid');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
        input.focus();
    }
    
    // Функция проверки IP адреса
    function isValidIP(ip) {
        const ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
        if (!ipPattern.test(ip)) return false;
        
        const parts = ip.split('.');
        for (let part of parts) {
            const num = parseInt(part);
            if (num < 0 || num > 255) return false;
            if (part.length > 1 && part[0] === '0') return false; // No leading zeros
        }
        return true;
    }
    
    // Функция проверки URL
    function isValidUrl(string) {
        try {
            new URL(string);
            return string.startsWith('http://') || string.startsWith('https://');
        } catch (_) {
            return false;
        }
    }
    
    // Функции для быстрой настройки
    window.fillStandardConfig = function() {
        document.getElementById('total_memory').value = 16384; // 16GB
        document.getElementById('total_disk').value = 500; // 500GB
        document.getElementById('total_cpu').value = 8;
        document.getElementById('max_servers').value = 50;
        document.getElementById('daemon_port').value = 8080;
        document.getElementById('ssh_port').value = 22;
        document.getElementById('game_port_start').value = 25565;
        
        Swal.fire({
            icon: 'success',
            title: 'Стандартная конфигурация',
            text: 'Параметры стандартной конфигурации установлены',
            timer: 2000,
            showConfirmButton: false
        });
    };
    
    window.fillHighPerformance = function() {
        document.getElementById('total_memory').value = 32768; // 32GB
        document.getElementById('total_disk').value = 1000; // 1TB
        document.getElementById('total_cpu').value = 16;
        document.getElementById('max_servers').value = 100;
        document.getElementById('daemon_port').value = 8080;
        document.getElementById('ssh_port').value = 2222;
        document.getElementById('game_port_start').value = 27000;
        
        Swal.fire({
            icon: 'success',
            title: 'Высокая производительность',
            text: 'Параметры для высоконагруженной ноды установлены',
            timer: 2000,
            showConfirmButton: false
        });
    };
    
    window.fillTestConfig = function() {
        document.getElementById('total_memory').value = 4096; // 4GB
        document.getElementById('total_disk').value = 100; // 100GB
        document.getElementById('total_cpu').value = 4;
        document.getElementById('max_servers').value = 10;
        document.getElementById('daemon_port').value = 8081;
        document.getElementById('ssh_port').value = 2223;
        document.getElementById('game_port_start').value = 28000;
        
        Swal.fire({
            icon: 'success',
            title: 'Тестовая конфигурация',
            text: 'Параметры для тестовой ноды установлены',
            timer: 2000,
            showConfirmButton: false
        });
    };
    
    // Автозаполнение портов по умолчанию
    const portsConfig = {
        daemon_port: 8080,
        ssh_port: 22,
        game_port_start: 25565
    };
    
    Object.keys(portsConfig).forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field && !field.value) {
            field.value = portsConfig[fieldName];
        }
    });
    
    // Автозаполнение ресурсов по умолчанию
    const resourcesConfig = {
        total_memory: 16384,
        total_disk: 500,
        total_cpu: 8,
        max_servers: 50
    };
    
    Object.keys(resourcesConfig).forEach(fieldName => {
        const field = document.getElementById(fieldName);
        if (field && !field.value) {
            field.value = resourcesConfig[fieldName];
        }
    });
    
    // Генерация hostname на основе имени
    const nameField = document.getElementById('name');
    const hostnameField = document.getElementById('hostname');
    
    nameField?.addEventListener('blur', function() {
        if (nameField.value && !hostnameField.value) {
            const hostname = nameField.value.toLowerCase()
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            hostnameField.value = hostname + '.example.com';
        }
    });
});
</script>

@push('scripts')
<!-- SweetAlert2 для красивых уведомлений -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection
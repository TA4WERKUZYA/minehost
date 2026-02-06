@extends('layouts.admin-app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-1 text-gray-900">{{ $title }}</h1>
            <p class="text-muted mb-0">Редактирование ядра Minecraft</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.cores.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Назад к списку
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="{{ $core->icon }} me-2 text-primary"></i>Тип ядра
                            </h6>
                            <h4 class="mb-0 fw-bold text-gray-900">
                                {{ $core->game_type == 'java' ? 'Java Edition' : 'Bedrock Edition' }}
                            </h4>
                        </div>
                        <div class="icon-circle bg-primary-light">
                            <i class="{{ $core->icon }} text-primary fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-tag me-2 text-success"></i>Версия
                            </h6>
                            <h4 class="mb-0 fw-bold text-gray-900">{{ $core->version }}</h4>
                        </div>
                        <div class="icon-circle bg-success-light">
                            <i class="fas fa-tag text-success fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-server me-2 text-warning"></i>Серверов
                            </h6>
                            <h4 class="mb-0 fw-bold text-gray-900">{{ $core->servers()->count() }}</h4>
                        </div>
                        <div class="icon-circle bg-warning-light">
                            <i class="fas fa-server text-warning fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase text-muted mb-2">
                                <i class="fas fa-weight me-2 text-info"></i>Размер файла
                            </h6>
                            <h4 class="mb-0 fw-bold text-gray-900">{{ $core->getFileSizeFormattedAttribute() }}</h4>
                        </div>
                        <div class="icon-circle bg-info-light">
                            <i class="fas fa-weight text-info fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2 text-primary"></i>Редактирование ядра
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('admin.cores.update', $core) }}" id="editCoreForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-label fw-medium">
                                        <i class="fas fa-font me-1 text-muted"></i>
                                        Название ядра <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $core->name) }}" 
                                           placeholder="Например: PaperMC, Spigot, Vanilla"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Имя, которое будет отображаться пользователям
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-medium">
                                        <i class="fas fa-gamepad me-1 text-muted"></i>
                                        Тип игры
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input type="text" 
                                               class="form-control bg-light" 
                                               value="{{ $core->game_type == 'java' ? 'Java Edition' : 'Bedrock Edition' }}" 
                                               readonly>
                                        <span class="input-group-text bg-light border-0">
                                            <i class="{{ $core->icon }} text-primary"></i>
                                        </span>
                                    </div>
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Тип игры нельзя изменить
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-medium">
                                        <i class="fas fa-code-branch me-1 text-muted"></i>
                                        Версия Minecraft
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input type="text" 
                                               class="form-control bg-light" 
                                               value="{{ $core->version }}" 
                                               readonly>
                                        <span class="input-group-text bg-light border-0">
                                            <i class="fas fa-lock text-muted"></i>
                                        </span>
                                    </div>
                                    <div class="form-text text-warning">
                                        <i class="fas fa-exclamation-circle me-1"></i>
                                        Версию нельзя изменить
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-medium">
                                        <i class="fas fa-file me-1 text-muted"></i>
                                        Файл ядра
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <input type="text" 
                                               class="form-control bg-light" 
                                               value="{{ $core->file_name }}" 
                                               readonly>
                                        <span class="input-group-text bg-light border-0">
                                            {{ $core->getFileSizeFormattedAttribute() }}
                                        </span>
                                    </div>
                                    <div class="form-text text-truncate" data-bs-toggle="tooltip" title="{{ $core->file_path }}">
                                        <i class="fas fa-folder me-1"></i>{{ $core->file_path }}
                                    </div>
                                </div>
                            </div>
                            
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
                                              placeholder="Опишите особенности этого ядра...">{{ old('description', $core->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Описание будет видно пользователям при выборе ядра
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="download_url" class="form-label fw-medium">
                                        <i class="fas fa-download me-1 text-muted"></i>
                                        URL для скачивания
                                    </label>
                                    <input type="url" 
                                           class="form-control @error('download_url') is-invalid @enderror" 
                                           id="download_url" 
                                           name="download_url" 
                                           value="{{ old('download_url', $core->download_url) }}"
                                           placeholder="https://example.com/core.jar">
                                    @error('download_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Ссылка для автоматического обновления ядра
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-medium">
                                        <i class="fas fa-cogs me-1 text-muted"></i>
                                        Настройки ядра
                                    </label>
                                    <div class="card bg-light border-0 p-3">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="is_default" 
                                                           name="is_default" 
                                                           value="1"
                                                           {{ old('is_default', $core->is_default) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-medium" for="is_default">
                                                        <i class="fas fa-star me-2 text-warning"></i>
                                                        Ядро по умолчанию
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="is_stable" 
                                                           name="is_stable" 
                                                           value="1"
                                                           {{ old('is_stable', $core->is_stable) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-medium" for="is_stable">
                                                        <i class="fas fa-shield-alt me-2 text-success"></i>
                                                        Стабильная версия
                                                    </label>
                                                </div>
                                            </div>
                                            
                                            <div class="col-12">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" 
                                                           type="checkbox" 
                                                           id="is_active" 
                                                           name="is_active" 
                                                           value="1"
                                                           {{ old('is_active', $core->is_active) ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-medium" for="is_active">
                                                        <i class="fas fa-power-off me-2 text-primary"></i>
                                                        Активное ядро
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Warning Alert if Core is in Use -->
                        @if($core->isInUse())
                        <div class="alert alert-warning alert-dismissible fade show mt-4" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="alert-heading mb-1">
                                        <i class="fas fa-server me-2"></i>Внимание!
                                    </h5>
                                    <p class="mb-0">
                                        Это ядро используется <strong>{{ $core->servers()->count() }}</strong> серверами. 
                                        Изменение настроек может повлиять на их работу.
                                    </p>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                        @endif
                        
                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                            <a href="{{ route('admin.cores.index') }}" class="btn btn-outline-secondary px-4">
                                <i class="fas fa-times me-2"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-primary px-4" id="saveButton">
                                <i class="fas fa-save me-2"></i>Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Core Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2 text-info"></i>Информация о ядре
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="text-center p-4 bg-light border-bottom">
                        <div class="icon-circle-lg bg-primary-light mb-3 mx-auto">
                            <i class="{{ $core->icon }} text-primary fa-3x"></i>
                        </div>
                        <h4 class="mb-1 fw-bold text-gray-900">{{ $core->name }}</h4>
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <span class="badge bg-info-gradient">
                                <i class="fas fa-tag me-1"></i>{{ $core->version }}
                            </span>
                            @if($core->is_default)
                            <span class="badge bg-warning-gradient">
                                <i class="fas fa-star me-1"></i>По умолчанию
                            </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-plus me-3 text-muted"></i>
                                <span>Дата добавления</span>
                            </div>
                            <span class="fw-medium">{{ $core->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-edit me-3 text-muted"></i>
                                <span>Последнее обновление</span>
                            </div>
                            <span class="fw-medium">{{ $core->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-database me-3 text-muted"></i>
                                <span>ID в системе</span>
                            </div>
                            <span class="badge bg-dark">#{{ $core->id }}</span>
                        </div>
                        
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-hdd me-3 text-muted"></i>
                                <span>Путь к файлу</span>
                            </div>
                            <button type="button" 
                                    class="btn btn-sm btn-outline-secondary copy-path-btn" 
                                    data-path="{{ $core->file_path }}">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Danger Zone -->
            <div class="card border-0 shadow-sm border-danger">
                <div class="card-header bg-white border-bottom-0 py-4 border-danger">
                    <h5 class="mb-0 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Опасная зона
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="alert alert-danger border-danger mb-4">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-radiation-alt fa-2x"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="alert-heading mb-1">Внимание!</h6>
                                <p class="mb-0 small">Эти действия нельзя отменить</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('admin.cores.destroy', $core) }}" 
                              class="d-grid delete-form">
                            @csrf
                            @method('DELETE')
                            <button type="button" 
                                    class="btn btn-outline-danger btn-block delete-btn"
                                    data-core-name="{{ $core->name }} {{ $core->version }}"
                                    data-is-used="{{ $core->isInUse() ? 'true' : 'false' }}"
                                    {{ $core->isInUse() ? 'disabled' : '' }}>
                                <i class="fas fa-trash me-2"></i>Удалить ядро
                            </button>
                        </form>
                        
                        @if(!$core->is_default)
                        <form method="POST" action="{{ route('admin.cores.make-default', $core) }}" 
                              class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-outline-success btn-block">
                                <i class="fas fa-star me-2"></i>Сделать дефолтным
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Стили из страницы списка */
    .icon-circle-lg {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 3px solid rgba(78, 115, 223, 0.2);
    }
    
    .bg-primary-light { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-light { background-color: rgba(28, 200, 138, 0.1); }
    .bg-warning-light { background-color: rgba(246, 194, 62, 0.1); }
    .bg-info-light { background-color: rgba(54, 185, 204, 0.1); }
    .bg-danger-light { background-color: rgba(231, 74, 59, 0.1); }
    
    .bg-info-gradient { background: linear-gradient(135deg, #36b9cc, #2d9ba9); }
    .bg-warning-gradient { background: linear-gradient(135deg, #f6c23e, #f39c12); }
    .bg-dark { background-color: #5a5c69 !important; }
    
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
    
    /* Стили для disabled кнопок */
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    /* Стили для опасной зоны */
    .border-danger {
        border-color: #e74a3b !important;
        border-width: 2px !important;
    }
    
    .alert-danger.border-danger {
        border-color: #e74a3b !important;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });
    
    // Обработка кнопки удаления
    const deleteBtn = document.querySelector('.delete-btn');
    deleteBtn?.addEventListener('click', function() {
        const coreName = this.getAttribute('data-core-name');
        const isUsed = this.getAttribute('data-is-used') === 'true';
        const form = this.closest('.delete-form');
        
        if (isUsed) {
            Swal.fire({
                icon: 'warning',
                title: 'Невозможно удалить',
                text: `Ядро "${coreName}" используется на серверах. Сначала удалите или измените связанные серверы.`,
                confirmButtonText: 'Понятно',
                confirmButtonColor: '#4e73df'
            });
            return;
        }
        
        Swal.fire({
            title: 'Удалить ядро?',
            html: `<div class="text-start">
                <p>Вы уверены, что хотите удалить ядро <strong>${coreName}</strong>?</p>
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Это действие нельзя отменить!</strong>
                </div>
            </div>`,
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#5a5c69',
            confirmButtonText: 'Да, удалить навсегда',
            cancelButtonText: 'Отмена',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
    
    // Подтверждение установки как дефолтного
    document.querySelectorAll('form[action*="make-default"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const coreName = "{{ $core->name }} {{ $core->version }}";
            
            Swal.fire({
                title: 'Установить как дефолтное?',
                html: `<div class="text-start">
                    <p>Ядро <strong>${coreName}</strong> будет установлено как основное для новых серверов.</p>
                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-info-circle me-2"></i>
                        Текущее ядро по умолчанию будет сброшено
                    </div>
                </div>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#5a5c69',
                confirmButtonText: 'Да, установить',
                cancelButtonText: 'Отмена'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });
    });
    
    // Валидация формы перед отправкой
    const editForm = document.getElementById('editCoreForm');
    editForm?.addEventListener('submit', function(e) {
        const nameInput = document.getElementById('name');
        const downloadUrlInput = document.getElementById('download_url');
        let hasErrors = false;
        
        // Очистка предыдущих ошибок
        document.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(el => {
            el.remove();
        });
        
        // Проверка имени
        if (!nameInput.value.trim()) {
            showError(nameInput, 'Название ядра обязательно');
            hasErrors = true;
        }
        
        // Проверка URL (если заполнен)
        if (downloadUrlInput.value.trim() && !isValidUrl(downloadUrlInput.value)) {
            showError(downloadUrlInput, 'Введите корректный URL');
            hasErrors = true;
        }
        
        if (hasErrors) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Ошибка в форме',
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
    
    // Функция проверки URL
    function isValidUrl(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }
    
    // Копирование пути к файлу
    document.querySelectorAll('.copy-path-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const path = this.getAttribute('data-path');
            navigator.clipboard.writeText(path).then(() => {
                // Временно меняем иконку на успех
                const icon = this.querySelector('i');
                const originalClass = icon.className;
                icon.className = 'fas fa-check text-success';
                
                // Меняем текст на кнопке
                this.innerHTML = '<i class="fas fa-check text-success"></i>';
                
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-copy"></i>';
                }, 2000);
            }).catch(err => {
                console.error('Ошибка копирования: ', err);
            });
        });
    });
});
</script>

@push('scripts')
<!-- SweetAlert2 для красивых уведомлений -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush

@endsection
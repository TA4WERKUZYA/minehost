@extends('layouts.admin-app')

@section('title', $title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <div>
            <h1 class="h2 mb-1 text-gray-900">{{ $title }}</h1>
            <p class="text-muted mb-0">Добавление нового ядра Minecraft</p>
        </div>
        <a href="{{ route('admin.cores.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Назад к списку
        </a>
    </div>

    <div class="row g-4">
        <!-- Основная форма -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-upload me-2 text-primary"></i>Загрузка нового ядра
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.cores.store') }}" 
                          enctype="multipart/form-data" id="upload-form">
                        @csrf
                        
                        <!-- Информационное сообщение -->
                        <div class="alert alert-info border-0 mb-4">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle fa-2x"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="alert-heading mb-1">Информация о загрузке</h6>
                                    <p class="mb-0"><strong>Максимальный размер файла: 100MB</strong><br>
                                    Поддерживаемые форматы: .jar, .zip, .bin</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Основные поля -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">
                                    <i class="fas fa-tag me-2 text-primary"></i>Название ядра *
                                </label>
                                <select class="form-select" id="name" name="name" required>
                                    <option value="">Выберите тип ядра</option>
                                    <option value="paper" {{ old('name') == 'paper' ? 'selected' : '' }}>PaperMC</option>
                                    <option value="spigot" {{ old('name') == 'spigot' ? 'selected' : '' }}>Spigot</option>
                                    <option value="vanilla" {{ old('name') == 'vanilla' ? 'selected' : '' }}>Vanilla</option>
                                    <option value="fabric" {{ old('name') == 'fabric' ? 'selected' : '' }}>Fabric</option>
                                    <option value="bedrock" {{ old('name') == 'bedrock' ? 'selected' : '' }}>Bedrock</option>
                                </select>
                                <div class="form-text text-muted">
                                    Это имя будет отображаться пользователям
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="game_type" class="form-label fw-medium">
                                    <i class="fas fa-gamepad me-2 text-primary"></i>Тип игры *
                                </label>
                                <select class="form-select" id="game_type" name="game_type" required>
                                    <option value="">Выберите тип игры</option>
                                    <option value="java" {{ old('game_type') == 'java' ? 'selected' : '' }}>
                                        <i class="fab fa-java me-2"></i>Java Edition
                                    </option>
                                    <option value="bedrock" {{ old('game_type') == 'bedrock' ? 'selected' : '' }}>
                                        <i class="fas fa-mobile-alt me-2"></i>Bedrock Edition
                                    </option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="version" class="form-label fw-medium">
                                    <i class="fas fa-code-branch me-2 text-primary"></i>Версия Minecraft *
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-hashtag text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control" id="version" name="version" 
                                           value="{{ old('version') }}" required
                                           placeholder="Например: 1.20.4">
                                </div>
                                <div class="form-text text-muted">
                                    Формат: X.X.X
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="file_name" class="form-label fw-medium">
                                    <i class="fas fa-file me-2 text-primary"></i>Имя файла (опционально)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-file-alt text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control" id="file_name" name="file_name" 
                                           value="{{ old('file_name') }}"
                                           placeholder="paper-1.20.4.jar">
                                </div>
                                <div class="form-text text-muted">
                                    Будет использовано имя загружаемого файла если не указано
                                </div>
                            </div>
                        </div>
                        
                        <!-- Описание -->
                        <div class="mb-4">
                            <label for="description" class="form-label fw-medium">
                                <i class="fas fa-align-left me-2 text-primary"></i>Описание
                            </label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3" placeholder="Краткое описание ядра...">{{ old('description') }}</textarea>
                        </div>
                        
                        <!-- Загрузка файла -->
                        <div class="mb-4">
                            <label for="core_file" class="form-label fw-medium">
                                <i class="fas fa-cloud-upload-alt me-2 text-primary"></i>Файл ядра *
                            </label>
                            <div class="file-upload-area p-4 border-2 border-dashed rounded-3 bg-light text-center mb-3">
                                <div class="mb-3">
                                    <i class="fas fa-file-upload fa-3x text-muted"></i>
                                </div>
                                <h5 class="mb-2">Перетащите файл сюда</h5>
                                <p class="text-muted mb-3">или</p>
                                <div class="input-group" style="max-width: 300px; margin: 0 auto;">
                                    <input type="file" class="form-control" id="core_file" name="core_file" 
                                           accept=".jar,.zip,.bin" required>
                                </div>
                                <p class="text-muted small mt-3">
                                    Максимальный размер: 100MB
                                </p>
                            </div>
                            
                            <div id="file-info" class="mt-3" style="display: none;">
                                <div class="alert alert-light border">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-file text-primary fa-2x"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-medium" id="file-name"></div>
                                            <div class="text-muted small" id="file-size"></div>
                                        </div>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="clearFile()">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-text text-muted">
                                Файл будет сохранен в: <code>/opt/minecraft-cores/</code>
                            </div>
                        </div>
                        
                        <!-- URL для скачивания -->
                        <div class="mb-4">
                            <label for="download_url" class="form-label fw-medium">
                                <i class="fas fa-link me-2 text-primary"></i>URL для скачивания (опционально)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-globe text-muted"></i>
                                </span>
                                <input type="url" class="form-control" id="download_url" name="download_url" 
                                       value="{{ old('download_url') }}" 
                                       placeholder="https://example.com/server.jar">
                            </div>
                            <div class="form-text text-muted">
                                Если ядро должно скачиваться автоматически с внешнего источника
                            </div>
                        </div>
                        
                        <!-- Настройки -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="form-check-card p-3 border rounded-3">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" class="form-check-input" id="is_default" 
                                               name="is_default" value="1" role="switch" {{ old('is_default') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="is_default">
                                            Ядро по умолчанию
                                        </label>
                                    </div>
                                    <div class="form-text text-muted small">
                                        <i class="fas fa-star me-1"></i>
                                        Будет автоматически устанавливаться на новые серверы
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check-card p-3 border rounded-3">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" class="form-check-input" id="is_stable" 
                                               name="is_stable" value="1" role="switch" {{ old('is_stable', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="is_stable">
                                            Стабильная версия
                                        </label>
                                    </div>
                                    <div class="form-text text-muted small">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Отметьте для production использования
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-check-card p-3 border rounded-3">
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" class="form-check-input" id="is_active" 
                                               name="is_active" value="1" role="switch" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="is_active">
                                            Активное ядро
                                        </label>
                                    </div>
                                    <div class="form-text text-muted small">
                                        <i class="fas fa-check-circle me-1"></i>
                                        Будет доступно для выбора пользователями
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Кнопки -->
                        <div class="d-flex justify-content-between pt-3 border-top">
                            <button type="submit" class="btn btn-primary px-4" id="submit-btn">
                                <i class="fas fa-save me-2"></i>Загрузить ядро
                            </button>
                            <a href="{{ route('admin.cores.index') }}" class="btn btn-outline-secondary px-4">
                                Отмена
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Информационная панель -->
        <div class="col-lg-4">
            <!-- Рекомендации -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2 text-warning"></i>Рекомендуемые ядра
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle-sm bg-primary-light me-3">
                                    <i class="fab fa-java text-primary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium">PaperMC</div>
                                    <div class="text-muted small">Для большинства серверов</div>
                                </div>
                                <span class="badge bg-primary">Рекомендуется</span>
                            </div>
                        </div>
                        
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle-sm bg-success-light me-3">
                                    <i class="fas fa-plug text-success"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium">Spigot</div>
                                    <div class="text-muted small">Для плагинов</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle-sm bg-info-light me-3">
                                    <i class="fas fa-gem text-info"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium">Vanilla</div>
                                    <div class="text-muted small">Чистая версия</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-center">
                                <div class="icon-circle-sm bg-warning-light me-3">
                                    <i class="fas fa-mobile-alt text-warning"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium">Bedrock</div>
                                    <div class="text-muted small">Для мобильных устройств</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Источники -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-external-link-alt me-2 text-info"></i>Где скачать ядра
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="https://papermc.io/downloads" target="_blank" 
                           class="btn btn-outline-primary text-start">
                            <i class="fab fa-java me-2"></i>
                            <div class="d-flex flex-column">
                                <span class="fw-medium">PaperMC</span>
                                <small class="text-muted">papermc.io</small>
                            </div>
                        </a>
                        
                        <a href="https://getbukkit.org/download/spigot" target="_blank" 
                           class="btn btn-outline-success text-start">
                            <i class="fas fa-plug me-2"></i>
                            <div class="d-flex flex-column">
                                <span class="fw-medium">Spigot</span>
                                <small class="text-muted">getbukkit.org</small>
                            </div>
                        </a>
                        
                        <a href="https://www.minecraft.net/download/server" target="_blank" 
                           class="btn btn-outline-info text-start">
                            <i class="fas fa-gem me-2"></i>
                            <div class="d-flex flex-column">
                                <span class="fw-medium">Vanilla</span>
                                <small class="text-muted">minecraft.net</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Внимание -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Внимание
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning border-0 mb-0">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-0">
                                    <strong>Загружайте только проверенные файлы.</strong><br>
                                    Нестабильные ядра могут привести к сбоям серверов.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Прогресс загрузки -->
            <div class="card border-0 shadow-sm mt-4" id="progress-card" style="display: none;">
                <div class="card-header bg-white border-bottom-0 py-4">
                    <h5 class="mb-0">
                        <i class="fas fa-spinner fa-spin me-2 text-primary"></i>Загрузка...
                    </h5>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="progress-bar"></div>
                    </div>
                    <p class="mb-0 small text-center" id="progress-text">
                        Подготовка к загрузке...
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* File upload area */
    .file-upload-area {
        border-color: #dee2e6 !important;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #f8f9fc 0%, #f1f3f9 100%);
    }
    
    .file-upload-area:hover {
        border-color: #4e73df !important;
        background: linear-gradient(135deg, #f1f3f9 0%, #e9ecf7 100%);
    }
    
    /* Form check cards */
    .form-check-card {
        transition: all 0.3s ease;
        background: white;
    }
    
    .form-check-card:hover {
        border-color: #4e73df;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    /* Custom switches */
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
        background-color: #e3e6f0;
        border-color: #e3e6f0;
    }
    
    .form-switch .form-check-input:checked {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    /* Icon circles */
    .icon-circle-sm {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    /* Light backgrounds */
    .bg-primary-light { background-color: rgba(78, 115, 223, 0.1); }
    .bg-success-light { background-color: rgba(28, 200, 138, 0.1); }
    .bg-info-light { background-color: rgba(54, 185, 204, 0.1); }
    .bg-warning-light { background-color: rgba(246, 194, 62, 0.1); }
    .bg-danger-light { background-color: rgba(231, 74, 59, 0.1); }
    
    /* Form labels */
    .form-label {
        color: #5a5c69;
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    
    /* Form text */
    .form-text {
        font-size: 0.85rem;
    }
    
    /* Buttons in sidebar */
    .btn-outline-primary:hover {
        background: linear-gradient(135deg, #4e73df, #2e59d9);
        color: white;
    }
    
    .btn-outline-success:hover {
        background: linear-gradient(135deg, #1cc88a, #16a085);
        color: white;
    }
    
    .btn-outline-info:hover {
        background: linear-gradient(135deg, #36b9cc, #2d9ba9);
        color: white;
    }
    
    /* Input groups */
    .input-group-text {
        border-right: none;
    }
    
    .input-group .form-control {
        border-left: none;
    }
    
    /* Alerts */
    .alert {
        border: none;
        border-left: 4px solid;
    }
    
    .alert-info {
        border-left-color: #36b9cc;
    }
    
    .alert-warning {
        border-left-color: #f6c23e;
    }
    
    /* Progress bar */
    .progress {
        height: 10px;
        border-radius: 5px;
        overflow: hidden;
    }
    
    .progress-bar {
        border-radius: 5px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('core_file');
    const fileInfo = document.getElementById('file-info');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const progressCard = document.getElementById('progress-card');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const submitBtn = document.getElementById('submit-btn');
    const uploadForm = document.getElementById('upload-form');
    const uploadArea = document.querySelector('.file-upload-area');
    
    // Drag and drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.style.borderColor = '#4e73df';
        this.style.background = 'linear-gradient(135deg, #e9ecf7 0%, #dee4ff 100%)';
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.style.borderColor = '#dee2e6';
        this.style.background = 'linear-gradient(135deg, #f8f9fc 0%, #f1f3f9 100%)';
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.style.borderColor = '#dee2e6';
        this.style.background = 'linear-gradient(135deg, #f8f9fc 0%, #f1f3f9 100%)';
        
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            handleFileSelect(e.dataTransfer.files[0]);
        }
    });
    
    // Отображение информации о файле
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            handleFileSelect(file);
        }
    });
    
    function handleFileSelect(file) {
        fileInfo.style.display = 'block';
        fileName.textContent = file.name;
        
        // Автозаполнение имени файла если поле пустое
        const fileNameInput = document.getElementById('file_name');
        if (!fileNameInput.value) {
            fileNameInput.value = file.name;
        }
        
        // Форматирование размера
        const size = file.size;
        let sizeText = '';
        if (size >= 1024 * 1024) {
            sizeText = (size / (1024 * 1024)).toFixed(2) + ' MB';
        } else if (size >= 1024) {
            sizeText = (size / 1024).toFixed(2) + ' KB';
        } else {
            sizeText = size + ' bytes';
        }
        fileSize.textContent = `Размер: ${sizeText}`;
        
        // Проверка размера
        if (size > 100 * 1024 * 1024) { // 100MB
            fileSize.innerHTML = `<span class="text-danger fw-medium">(Слишком большой! Максимум 100MB)</span>`;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-ban me-2"></i>Файл слишком большой';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-danger');
        } else {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Загрузить ядро';
            submitBtn.classList.remove('btn-danger');
            submitBtn.classList.add('btn-primary');
        }
    }
    
    // Очистка файла
    window.clearFile = function() {
        fileInput.value = '';
        fileInfo.style.display = 'none';
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save me-2"></i>Загрузить ядро';
        submitBtn.classList.remove('btn-danger');
        submitBtn.classList.add('btn-primary');
    };
    
    // Прогресс загрузки
    uploadForm.addEventListener('submit', function(e) {
        const file = fileInput.files[0];
        if (!file) {
            e.preventDefault();
            alert('Пожалуйста, выберите файл для загрузки');
            return;
        }
        
        if (file.size > 100 * 1024 * 1024) {
            e.preventDefault();
            alert('Файл слишком большой. Максимальный размер: 100MB');
            return;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Загрузка...';
        progressCard.style.display = 'block';
        
        // Имитация прогресса
        let progress = 0;
        const interval = setInterval(function() {
            progress += Math.random() * 15;
            if (progress > 100) progress = 100;
            progressBar.style.width = progress + '%';
            
            if (progress >= 100) {
                clearInterval(interval);
                progressText.textContent = 'Загрузка завершена!';
                progressBar.classList.remove('progress-bar-animated');
                progressBar.style.width = '100%';
            } else if (progress >= 80) {
                progressText.textContent = 'Обработка файла...';
            } else if (progress >= 40) {
                progressText.textContent = 'Загрузка: ' + Math.round(progress) + '%';
            }
        }, 300);
    });
    
    // Автозаполнение описания при выборе типа ядра
    const nameSelect = document.getElementById('name');
    const descriptionTextarea = document.getElementById('description');
    
    nameSelect.addEventListener('change', function(e) {
        const descriptions = {
            'paper': 'Оптимизированный сервер PaperMC с улучшенной производительностью и поддержкой плагинов. Идеально для большинства серверов.',
            'spigot': 'Сервер Spigot с полной поддержкой плагинов и кастомизации. Подходит для серверов с плагинами.',
            'vanilla': 'Официальный сервер Minecraft от Mojang без модификаций. Чистый опыт игры.',
            'fabric': 'Легковесный сервер Fabric для модификаций с минимальным воздействием на производительность.',
            'bedrock': 'Сервер для Bedrock Edition (Windows 10, мобильные устройства, консоли). Поддерживает кроссплатформенную игру.'
        };
        
        if (descriptions[e.target.value] && !descriptionTextarea.value) {
            descriptionTextarea.value = descriptions[e.target.value];
        }
    });
    
    // Автозаполнение версии при выборе типа
    const gameTypeSelect = document.getElementById('game_type');
    const versionInput = document.getElementById('version');
    
    gameTypeSelect.addEventListener('change', function(e) {
        if (e.target.value === 'java' && !versionInput.value) {
            versionInput.value = '1.20.4';
        } else if (e.target.value === 'bedrock' && !versionInput.value) {
            versionInput.value = '1.20.80';
        }
    });
    
    // Автоматическое заполнение имени файла
    nameSelect.addEventListener('change', function() {
        const version = versionInput.value;
        const fileNameInput = document.getElementById('file_name');
        
        if (!fileNameInput.value && version) {
            const name = this.value;
            if (name) {
                fileNameInput.value = `${name}-${version}.jar`;
            }
        }
    });
    
    versionInput.addEventListener('input', function() {
        const name = nameSelect.value;
        const fileNameInput = document.getElementById('file_name');
        
        if (!fileNameInput.value && name && this.value) {
            fileNameInput.value = `${name}-${this.value}.jar`;
        }
    });
});
</script>

@endsection
@extends('layouts.app')

@section('title', 'Создание сервера')

@section('content')
<div class="container py-5">
    <div class="server-create-wrapper">
        <!-- Заголовок -->
        <div class="create-header text-center mb-5">
            <div class="header-icon">
                <i class="fas fa-server"></i>
            </div>
            <h1 class="create-title">Создание нового сервера</h1>
            <p class="create-subtitle">Настройте свой Minecraft сервер за несколько минут</p>
        </div>

        <!-- Прогресс-бар -->
        <div class="create-progress mb-5">
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Основное</div>
                </div>
                <div class="step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Тариф</div>
                </div>
                <div class="step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Настройки</div>
                </div>
                <div class="step" data-step="4">
                    <div class="step-number">4</div>
                    <div class="step-label">Подтверждение</div>
                </div>
            </div>
            <div class="progress-line">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
        </div>

        @if (session('error'))
            <div class="alert-message error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (isset($user) && $user)
            <form method="POST" action="{{ route('dashboard.store') }}" id="server-create-form">
                @csrf
                
                <!-- Шаг 1: Основная информация -->
                <div class="create-step active" id="step-1">
                    <div class="step-header">
                        <h2><i class="fas fa-info-circle"></i> Основная информация</h2>
                        <p>Задайте основные параметры сервера</p>
                    </div>
                    
                    <div class="form-grid">
                        <!-- Имя сервера -->
                        <div class="form-group">
                            <label for="server_name" class="form-label">
                                <i class="fas fa-tag"></i> Имя сервера *
                            </label>
                            <div class="input-with-action">
                                <input type="text" 
                                       id="server_name" 
                                       name="name" 
                                       class="form-input"
                                       value="{{ old('name') ?: 'Мой Minecraft Сервер' }}"
                                       required
                                       minlength="3"
                                       maxlength="50"
                                       placeholder="Введите имя сервера">
                                <button type="button" class="input-action-btn" id="generate-name-btn" title="Сгенерировать имя">
                                    <i class="fas fa-random"></i>
                                </button>
                            </div>
                            <div class="form-hint">
                                <i class="fas fa-lightbulb"></i> Латинские буквы, цифры, пробелы и дефисы
                            </div>
                        </div>
                        
                        <!-- Платформа -->
                        <div class="form-group">
                            <label for="game_type" class="form-label">
                                <i class="fas fa-gamepad"></i> Платформа *
                            </label>
                            <div class="platform-selector">
                                <label class="platform-option active" data-platform="java">
                                    <input type="radio" name="game_type" value="java" 
                                           {{ old('game_type') == 'java' ? 'checked' : 'checked' }} hidden>
                                    <div class="platform-card">
                                        <div class="platform-icon">
                                            <i class="fab fa-java"></i>
                                        </div>
                                        <div class="platform-info">
                                            <h4>Java Edition</h4>
                                            <p>Для ПК, Mac, Linux</p>
                                            <div class="platform-badge">Популярно</div>
                                        </div>
                                    </div>
                                </label>
                                <label class="platform-option" data-platform="bedrock">
                                    <input type="radio" name="game_type" value="bedrock" 
                                           {{ old('game_type') == 'bedrock' ? 'checked' : '' }} hidden>
                                    <div class="platform-card">
                                        <div class="platform-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <div class="platform-info">
                                            <h4>Bedrock Edition</h4>
                                            <p>Для консолей и телефонов</p>
                                            <div class="platform-badge">Кроссплатформа</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-actions">
                        <button type="button" class="btn btn-next" data-next="2">
                            Далее <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Шаг 2: Выбор тарифа -->
                <div class="create-step" id="step-2">
                    <div class="step-header">
                        <h2><i class="fas fa-cubes"></i> Выбор тарифа</h2>
                        <p>Выберите подходящий тариф для вашего сервера</p>
                    </div>
                    
                    <div class="plans-container" id="plans-container">
                        <!-- Тарифы будут загружены динамически -->
                        <div class="plans-loading">
                            <div class="loading-spinner"></div>
                            <p>Загрузка доступных тарифов...</p>
                        </div>
                    </div>
                    
                    <div class="step-actions">
                        <button type="button" class="btn btn-prev" data-prev="1">
                            <i class="fas fa-arrow-left"></i> Назад
                        </button>
                        <button type="button" class="btn btn-next" data-next="3">
                            Далее <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Шаг 3: Дополнительные настройки -->
                <div class="create-step" id="step-3">
                    <div class="step-header">
                        <h2><i class="fas fa-cog"></i> Дополнительные настройки</h2>
                        <p>Настройте расположение и срок аренды</p>
                    </div>
                    
                    <div class="form-grid">
                        <!-- Выбор ноды -->
                        <div class="form-group">
                            <label for="node_id" class="form-label">
                                <i class="fas fa-globe"></i> Расположение сервера *
                            </label>
                            <select id="node_id" name="node_id" class="form-select" required>
                                <option value="">Выберите расположение</option>
                                @foreach($nodes as $node)
                                    @if($node->is_active && $node->accept_new_servers)
                                        <option value="{{ $node->id }}" {{ old('node_id') == $node->id ? 'selected' : '' }}>
                                            {{ $node->name }} ({{ $node->location }})
                                            @if($node->total_memory - $node->used_memory > 0)
                                                - Доступно {{ $node->total_memory - $node->used_memory }}MB RAM
                                            @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <div class="form-hint">
                                <i class="fas fa-map-marker-alt"></i> Выберите ближайшее к вам расположение
                            </div>
                        </div>
                        
                        <!-- Период аренды -->
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt"></i> Период аренды *
                            </label>
                            <div class="period-selector">
                                @php
                                    $periods = [
                                        'monthly' => ['label' => 'Ежемесячно', 'discount' => 0],
                                        'quarterly' => ['label' => 'Квартал', 'discount' => 10],
                                        'half_year' => ['label' => 'Полгода', 'discount' => 15],
                                        'yearly' => ['label' => 'Год', 'discount' => 20],
                                    ];
                                @endphp
                                @foreach($periods as $value => $data)
                                    <label class="period-option {{ old('period') == $value ? 'selected' : ($loop->first ? 'selected' : '') }}">
                                        <input type="radio" name="period" value="{{ $value }}" 
                                               {{ old('period') == $value ? 'checked' : ($loop->first ? 'checked' : '') }} hidden>
                                        <div class="period-card">
                                            <div class="period-name">{{ $data['label'] }}</div>
                                            @if($data['discount'] > 0)
                                                <div class="period-discount">-{{ $data['discount'] }}%</div>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="form-hint">
                                <i class="fas fa-percentage"></i> Чем дольше период - тем больше экономия
                            </div>
                        </div>
                    </div>
                    
                    <!-- Предварительная сводка -->
                    <div class="preview-summary">
                        <h3><i class="fas fa-receipt"></i> Предварительная сводка</h3>
                        <div class="summary-grid">
                            <div class="summary-item">
                                <span class="summary-label">Имя сервера:</span>
                                <span class="summary-value" id="preview-name">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Платформа:</span>
                                <span class="summary-value" id="preview-platform">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Тариф:</span>
                                <span class="summary-value" id="preview-plan">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Расположение:</span>
                                <span class="summary-value" id="preview-node">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Период:</span>
                                <span class="summary-value" id="preview-period">-</span>
                            </div>
                            <div class="summary-item total">
                                <span class="summary-label">Итого к оплате:</span>
                                <span class="summary-value" id="preview-total">$0.00</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-actions">
                        <button type="button" class="btn btn-prev" data-prev="2">
                            <i class="fas fa-arrow-left"></i> Назад
                        </button>
                        <button type="button" class="btn btn-next" data-next="4">
                            Далее <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Шаг 4: Подтверждение -->
                <div class="create-step" id="step-4">
                    <div class="step-header">
                        <h2><i class="fas fa-check-circle"></i> Подтверждение заказа</h2>
                        <p>Проверьте информацию и создайте сервер</p>
                    </div>
                    
                    <div class="order-summary">
                        <div class="summary-card">
                            <div class="summary-header">
                                <h3>Детали заказа</h3>
                            </div>
                            <div class="summary-body">
                                <div class="summary-section">
                                    <h4>Информация о сервере</h4>
                                    <div class="summary-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Имя сервера:</span>
                                            <span class="detail-value" id="final-name">-</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Платформа:</span>
                                            <span class="detail-value" id="final-platform">-</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Тариф:</span>
                                            <span class="detail-value" id="final-plan">-</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Расположение:</span>
                                            <span class="detail-value" id="final-node">-</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="summary-section">
                                    <h4>Стоимость</h4>
                                    <div class="price-breakdown">
                                        <div class="price-item">
                                            <span>Ежемесячная цена:</span>
                                            <span id="final-monthly-price">$0.00</span>
                                        </div>
                                        <div class="price-item">
                                            <span>Скидка:</span>
                                            <span class="text-discount" id="final-discount">0%</span>
                                        </div>
                                        <div class="price-item total">
                                            <span>Итого к оплате:</span>
                                            <span class="total-price" id="final-total-price">$0.00</span>
                                        </div>
                                        <div class="price-item">
                                            <span>Срок действия:</span>
                                            <span id="final-expires">-</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="summary-section">
                                    <h4>Ваш баланс</h4>
                                    <div class="balance-info {{ $user->balance < 1 ? 'insufficient' : 'sufficient' }}">
                                        <div class="balance-amount">
                                            <i class="fas fa-wallet"></i>
                                            <span>${{ number_format($user->balance, 2) }}</span>
                                        </div>
                                        <div class="balance-status" id="balance-status">
                                            @if($user->balance < 1)
                                                <span class="status-badge insufficient">
                                                    <i class="fas fa-exclamation-triangle"></i> Недостаточно средств
                                                </span>
                                            @else
                                                <span class="status-badge sufficient">
                                                    <i class="fas fa-check-circle"></i> Достаточно средств
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="step-actions">
                        <button type="button" class="btn btn-prev" data-prev="3">
                            <i class="fas fa-arrow-left"></i> Назад
                        </button>
                        <button type="submit" class="btn btn-submit" id="submit-btn" {{ $user->balance < 1 ? 'disabled' : '' }}>
                            <i class="fas fa-rocket"></i> Создать сервер
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Ошибка: пользователь не найден</h3>
                <p>Пожалуйста, войдите в систему заново</p>
                <a href="{{ route('login') }}" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i> Войти
                </a>
            </div>
        @endif
    </div>
</div>

<script>
// Данные тарифов из Blade
const plansData = @json($plans);
const userBalance = {{ $user->balance ?? 0 }};

// Генератор имен сервера
const serverNames = [
    'Epic Minecraft World',
    'Creative Build Paradise',
    'Survival Adventure',
    'Fantasy Realm',
    'Pixel Universe',
    'Block Kingdom',
    'Magic Valley',
    'Dragon\'s Lair',
    'Sky Empire',
    'Ocean Temple',
    'Nether Fortress',
    'End City',
    'Redstone Lab',
    'Enchantment Tower',
    'Village Haven'
];

document.addEventListener('DOMContentLoaded', function() {
    // Инициализация
    initProgressBar();
    initFormSteps();
    initPlatformSelector();
    initNameGenerator();
    loadPlans('java'); // По умолчанию Java Edition
    initPeriodSelector();
    initFormValidation();
    
    // Обновление предпросмотра
    updatePreview();
});

// Прогресс-бар
function initProgressBar() {
    const steps = document.querySelectorAll('.step');
    const progressFill = document.getElementById('progress-fill');
    
    function updateProgress() {
        const activeStep = document.querySelector('.create-step.active');
        const stepNumber = parseInt(activeStep.id.split('-')[1]);
        const progress = ((stepNumber - 1) / 3) * 100;
        
        progressFill.style.width = `${progress}%`;
        
        steps.forEach((step, index) => {
            if (index + 1 <= stepNumber) {
                step.classList.add('active');
            } else {
                step.classList.remove('active');
            }
        });
    }
    
    updateProgress();
    
    // Обновление при смене шага
    document.addEventListener('stepChange', updateProgress);
}

// Навигация по шагам
function initFormSteps() {
    const steps = document.querySelectorAll('.create-step');
    const nextButtons = document.querySelectorAll('.btn-next');
    const prevButtons = document.querySelectorAll('.btn-prev');
    
    // Показать конкретный шаг
    function showStep(stepNumber) {
        steps.forEach(step => {
            step.classList.remove('active');
            if (step.id === `step-${stepNumber}`) {
                step.classList.add('active');
            }
        });
        
        // Генерируем событие смены шага
        document.dispatchEvent(new CustomEvent('stepChange', { detail: { step: stepNumber } }));
        
        // Обновляем предпросмотр
        updatePreview();
    }
    
    // Кнопки "Далее"
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            const nextStep = parseInt(this.dataset.next);
            if (validateStep(nextStep - 1)) {
                showStep(nextStep);
            }
        });
    });
    
    // Кнопки "Назад"
    prevButtons.forEach(button => {
        button.addEventListener('click', function() {
            const prevStep = parseInt(this.dataset.prev);
            showStep(prevStep);
        });
    });
}

// Валидация шага
function validateStep(stepNumber) {
    switch(stepNumber) {
        case 1:
            const name = document.getElementById('server_name').value.trim();
            const platform = document.querySelector('input[name="game_type"]:checked').value;
            
            if (!name || name.length < 3) {
                showError('Пожалуйста, введите корректное имя сервера (минимум 3 символа)');
                return false;
            }
            
            if (!platform) {
                showError('Пожалуйста, выберите платформу');
                return false;
            }
            
            return true;
            
        case 2:
            const selectedPlan = document.querySelector('.plan-card.selected');
            if (!selectedPlan) {
                showError('Пожалуйста, выберите тариф');
                return false;
            }
            return true;
            
        case 3:
            const node = document.getElementById('node_id').value;
            const period = document.querySelector('input[name="period"]:checked').value;
            
            if (!node) {
                showError('Пожалуйста, выберите расположение сервера');
                return false;
            }
            
            if (!period) {
                showError('Пожалуйста, выберите период аренды');
                return false;
            }
            
            return true;
            
        default:
            return true;
    }
}

// Выбор платформы
function initPlatformSelector() {
    const platformOptions = document.querySelectorAll('.platform-option');
    
    platformOptions.forEach(option => {
        option.addEventListener('click', function() {
            const platform = this.dataset.platform;
            
            // Обновляем классы
            platformOptions.forEach(opt => opt.classList.remove('active'));
            this.classList.add('active');
            
            // Обновляем радио-кнопку
            const radio = this.querySelector('input[type="radio"]');
            radio.checked = true;
            
            // Загружаем соответствующие тарифы
            loadPlans(platform);
            
            // Обновляем предпросмотр
            updatePreview();
        });
    });
}

// Загрузка тарифов
function loadPlans(platform) {
    const container = document.getElementById('plans-container');
    
    // Фильтруем тарифы по платформе
    const filteredPlans = plansData.filter(plan => 
        (plan.game_type === platform) || (!plan.game_type && platform === 'java')
    );
    
    if (filteredPlans.length === 0) {
        container.innerHTML = `
            <div class="no-plans-message">
                <i class="fas fa-info-circle"></i>
                <h3>Нет доступных тарифов</h3>
                <p>Для выбранной платформы пока нет доступных тарифов</p>
            </div>
        `;
        return;
    }
    
    // Очищаем контейнер
    container.innerHTML = '';
    
    // Создаем карточки тарифов
    filteredPlans.forEach(plan => {
        const planCard = document.createElement('div');
        planCard.className = 'plan-card';
        planCard.dataset.planId = plan.id;
        planCard.dataset.memory = plan.memory;
        planCard.dataset.disk = plan.disk_space;
        planCard.dataset.slots = plan.player_slots;
        planCard.dataset.price = plan.price_monthly;
        
        planCard.innerHTML = `
            <div class="plan-header">
                <h3>${plan.name}</h3>
                <div class="plan-price">$${plan.price_monthly}<span>/мес</span></div>
            </div>
            <div class="plan-features">
                <div class="feature">
                    <i class="fas fa-memory"></i>
                    <span>${plan.memory}MB RAM</span>
                </div>
                <div class="feature">
                    <i class="fas fa-hdd"></i>
                    <span>${plan.disk_space}MB Диск</span>
                </div>
                <div class="feature">
                    <i class="fas fa-users"></i>
                    <span>${plan.player_slots} игроков</span>
                </div>
                <div class="feature">
                    <i class="fas fa-microchip"></i>
                    <span>100% CPU</span>
                </div>
            </div>
            <button type="button" class="btn-select-plan">
                <i class="fas fa-check"></i> Выбрать
            </button>
        `;
        
        // Обработчик выбора тарифа
        planCard.querySelector('.btn-select-plan').addEventListener('click', function() {
            // Снимаем выделение со всех карточек
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Выделяем выбранную карточку
            planCard.classList.add('selected');
            
            // Устанавливаем значение в скрытое поле
            const planIdInput = document.querySelector('input[name="plan_id"]');
            if (!planIdInput) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'plan_id';
                input.value = plan.id;
                document.getElementById('server-create-form').appendChild(input);
            } else {
                planIdInput.value = plan.id;
            }
            
            // Обновляем предпросмотр
            updatePreview();
        });
        
        container.appendChild(planCard);
    });
}

// Генератор имен
function initNameGenerator() {
    const generateBtn = document.getElementById('generate-name-btn');
    const nameInput = document.getElementById('server_name');
    
    function generateRandomName() {
        const randomIndex = Math.floor(Math.random() * serverNames.length);
        return serverNames[randomIndex];
    }
    
    generateBtn.addEventListener('click', function() {
        nameInput.value = generateRandomName();
        updatePreview();
    });
}

// Выбор периода
function initPeriodSelector() {
    const periodOptions = document.querySelectorAll('.period-option');
    
    periodOptions.forEach(option => {
        option.addEventListener('click', function() {
            periodOptions.forEach(opt => opt.classList.remove('selected'));
            this.classList.add('selected');
            updatePreview();
        });
    });
}

// Обновление предпросмотра
function updatePreview() {
    // Основная информация
    document.getElementById('preview-name').textContent = 
    document.getElementById('final-name').textContent = 
        document.getElementById('server_name').value;
    
    // Платформа
    const platform = document.querySelector('input[name="game_type"]:checked').value;
    const platformText = platform === 'java' ? 'Java Edition' : 'Bedrock Edition';
    document.getElementById('preview-platform').textContent = 
    document.getElementById('final-platform').textContent = platformText;
    
    // Тариф
    const selectedPlan = document.querySelector('.plan-card.selected');
    if (selectedPlan) {
        const planName = selectedPlan.querySelector('h3').textContent;
        const planPrice = parseFloat(selectedPlan.dataset.price);
        
        document.getElementById('preview-plan').textContent = 
        document.getElementById('final-plan').textContent = planName;
        document.getElementById('final-monthly-price').textContent = `$${planPrice.toFixed(2)}`;
    }
    
    // Расположение
    const nodeSelect = document.getElementById('node_id');
    const selectedNode = nodeSelect.options[nodeSelect.selectedIndex];
    document.getElementById('preview-node').textContent = 
    document.getElementById('final-node').textContent = selectedNode.text;
    
    // Период
    const selectedPeriod = document.querySelector('input[name="period"]:checked');
    const periodCard = selectedPeriod.closest('.period-option');
    const periodName = periodCard.querySelector('.period-name').textContent;
    const discount = periodCard.querySelector('.period-discount')?.textContent || '0%';
    
    document.getElementById('preview-period').textContent = periodName;
    document.getElementById('final-discount').textContent = discount;
    
    // Расчет стоимости
    if (selectedPlan) {
        const planPrice = parseFloat(selectedPlan.dataset.price);
        const period = selectedPeriod.value;
        let totalPrice = planPrice;
        let expiresText = '1 месяц';
        
        switch(period) {
            case 'monthly':
                totalPrice = planPrice;
                expiresText = '1 месяц';
                break;
            case 'quarterly':
                totalPrice = planPrice * 3 * 0.9;
                expiresText = '3 месяца';
                break;
            case 'half_year':
                totalPrice = planPrice * 6 * 0.85;
                expiresText = '6 месяцев';
                break;
            case 'yearly':
                totalPrice = planPrice * 12 * 0.8;
                expiresText = '1 год';
                break;
        }
        
        document.getElementById('preview-total').textContent = 
        document.getElementById('final-total-price').textContent = `$${totalPrice.toFixed(2)}`;
        document.getElementById('final-expires').textContent = expiresText;
        
        // Проверка баланса
        const canAfford = userBalance >= totalPrice;
        const submitBtn = document.getElementById('submit-btn');
        const balanceStatus = document.getElementById('balance-status');
        
        if (canAfford) {
            submitBtn.disabled = false;
            balanceStatus.innerHTML = `
                <span class="status-badge sufficient">
                    <i class="fas fa-check-circle"></i> Достаточно средств
                </span>
            `;
        } else {
            submitBtn.disabled = true;
            balanceStatus.innerHTML = `
                <span class="status-badge insufficient">
                    <i class="fas fa-exclamation-triangle"></i> Недостаточно средств
                </span>
            `;
        }
    }
}

// Валидация формы
function initFormValidation() {
    const form = document.getElementById('server-create-form');
    
    form.addEventListener('submit', function(e) {
        if (!validateStep(1) || !validateStep(2) || !validateStep(3)) {
            e.preventDefault();
            showError('Пожалуйста, заполните все обязательные поля корректно');
            return false;
        }
        
        // Проверка баланса
        const totalPrice = parseFloat(
            document.getElementById('final-total-price').textContent.replace('$', '')
        );
        
        if (userBalance < totalPrice) {
            e.preventDefault();
            showError('Недостаточно средств на балансе. Пожалуйста, пополните счет.');
            return false;
        }
        
        return true;
    });
}

// Показать ошибку
function showError(message) {
    // Создаем элемент ошибки
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert-message error';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-circle"></i>
        <span>${message}</span>
    `;
    
    // Вставляем в начало формы
    const form = document.getElementById('server-create-form');
    form.parentNode.insertBefore(errorDiv, form);
    
    // Автоматически скрываем через 5 секунд
    setTimeout(() => {
        errorDiv.remove();
    }, 5000);
}
</script>

<style>
/* Основные стили */
:root {
    --primary-color: #667eea;
    --primary-dark: #5a67d8;
    --secondary-color: #764ba2;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --dark-color: #1f2937;
    --light-color: #f9fafb;
    --border-color: #e5e7eb;
    --shadow: 0 10px 25px rgba(0,0,0,0.1);
    --radius: 12px;
    --transition: all 0.3s ease;
}

.server-create-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Заголовок */
.create-header {
    margin-bottom: 3rem;
}

.header-icon {
    font-size: 4rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.create-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.create-subtitle {
    font-size: 1.1rem;
    color: #6b7280;
}

/* Прогресс-бар */
.create-progress {
    margin: 3rem 0;
}

.progress-steps {
    display: flex;
    justify-content: space-between;
    position: relative;
    z-index: 1;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
    z-index: 2;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e5e7eb;
    color: #9ca3af;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin-bottom: 0.5rem;
    transition: var(--transition);
}

.step.active .step-number {
    background: var(--primary-color);
    color: white;
    transform: scale(1.1);
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.2);
}

.step-label {
    font-size: 0.9rem;
    color: #9ca3af;
    font-weight: 500;
}

.step.active .step-label {
    color: var(--primary-color);
    font-weight: 600;
}

.progress-line {
    height: 3px;
    background: #e5e7eb;
    position: relative;
    top: -22px;
    z-index: 0;
}

.progress-fill {
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    width: 0;
    transition: width 0.5s ease;
    border-radius: 2px;
}

/* Шаги формы */
.create-step {
    display: none;
    animation: fadeIn 0.5s ease;
}

.create-step.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.step-header {
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--border-color);
}

.step-header h2 {
    font-size: 1.5rem;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.step-header h2 i {
    color: var(--primary-color);
}

.step-header p {
    color: #6b7280;
    font-size: 1rem;
}

/* Сетка форм */
.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.75rem;
    font-size: 1rem;
}

.form-label i {
    color: var(--primary-color);
    margin-right: 8px;
}

.form-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    font-size: 1rem;
    transition: var(--transition);
    background: white;
}

.form-input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.input-with-action {
    position: relative;
}

.input-action-btn {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: 6px;
    width: 36px;
    height: 36px;
    cursor: pointer;
    transition: var(--transition);
}

.input-action-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-50%) scale(1.1);
}

.form-hint {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.form-hint i {
    color: var(--primary-color);
}

/* Селектор платформы */
.platform-selector {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.platform-option {
    cursor: pointer;
}

.platform-option input[type="radio"]:checked + .platform-card {
    border-color: var(--primary-color);
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.platform-card {
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    padding: 1.5rem;
    transition: var(--transition);
    background: white;
}

.platform-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
}

.platform-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.platform-info h4 {
    font-size: 1.25rem;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.platform-info p {
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.platform-badge {
    display: inline-block;
    padding: 4px 12px;
    background: var(--primary-color);
    color: white;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

/* Контейнер тарифов */
.plans-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.plan-card {
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    padding: 1.5rem;
    transition: var(--transition);
    background: white;
    position: relative;
}

.plan-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.plan-card.selected {
    border-color: var(--primary-color);
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
    transform: translateY(-5px);
    box-shadow: var(--shadow);
}

.plan-header {
    margin-bottom: 1.5rem;
}

.plan-header h3 {
    font-size: 1.25rem;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.plan-price {
    font-size: 2rem;
    font-weight: 800;
    color: var(--primary-color);
}

.plan-price span {
    font-size: 1rem;
    color: #6b7280;
    font-weight: normal;
}

.plan-features {
    margin-bottom: 1.5rem;
}

.feature {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 0.75rem;
    color: #4b5563;
}

.feature i {
    color: var(--primary-color);
    width: 20px;
}

.btn-select-plan {
    width: 100%;
    padding: 12px;
    background: var(--primary-color);
    color: white;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-select-plan:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

/* Селектор периода */
.period-selector {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 1rem;
}

.period-option {
    cursor: pointer;
}

.period-option input[type="radio"]:checked + .period-card {
    border-color: var(--primary-color);
    background: var(--primary-color);
    color: white;
}

.period-card {
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    padding: 1rem;
    text-align: center;
    transition: var(--transition);
}

.period-card:hover {
    border-color: var(--primary-color);
}

.period-name {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.period-discount {
    font-size: 0.875rem;
    color: var(--success-color);
    font-weight: 600;
}

.period-option input[type="radio"]:checked + .period-card .period-discount {
    color: white;
}

/* Предпросмотр */
.preview-summary {
    background: var(--light-color);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
    border: 2px solid var(--border-color);
}

.preview-summary h3 {
    font-size: 1.25rem;
    color: var(--dark-color);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.preview-summary h3 i {
    color: var(--primary-color);
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.summary-item {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border-color);
}

.summary-item.total {
    border-bottom: none;
    font-weight: 700;
    color: var(--primary-color);
}

.summary-label {
    color: #6b7280;
    font-size: 0.875rem;
}

.summary-value {
    color: var(--dark-color);
    font-weight: 500;
    float: right;
}

/* Итоговая сводка */
.order-summary {
    margin-bottom: 2rem;
}

.summary-card {
    background: white;
    border-radius: var(--radius);
    border: 2px solid var(--border-color);
    overflow: hidden;
}

.summary-header {
    background: var(--primary-color);
    color: white;
    padding: 1.5rem;
}

.summary-header h3 {
    margin: 0;
    font-size: 1.25rem;
}

.summary-body {
    padding: 1.5rem;
}

.summary-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid var(--border-color);
}

.summary-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.summary-section h4 {
    font-size: 1.1rem;
    color: var(--dark-color);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.summary-section h4 i {
    color: var(--primary-color);
}

.summary-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.detail-label {
    color: #6b7280;
}

.detail-value {
    color: var(--dark-color);
    font-weight: 500;
}

.price-breakdown {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.price-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
}

.price-item.total {
    border-top: 2px solid var(--border-color);
    border-bottom: 2px solid var(--border-color);
    padding: 1rem 0;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-color);
}

.text-discount {
    color: var(--success-color);
    font-weight: 600;
}

.total-price {
    color: var(--primary-color);
    font-weight: 700;
    font-size: 1.5rem;
}

/* Информация о балансе */
.balance-info {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border-radius: var(--radius);
    background: var(--light-color);
}

.balance-info.sufficient {
    border: 2px solid var(--success-color);
}

.balance-info.insufficient {
    border: 2px solid var(--danger-color);
}

.balance-amount {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark-color);
}

.balance-amount i {
    color: var(--primary-color);
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-badge.sufficient {
    background: var(--success-color);
    color: white;
}

.status-badge.insufficient {
    background: var(--danger-color);
    color: white;
}

/* Кнопки навигации */
.step-actions {
    display: flex;
    justify-content: space-between;
    margin-top: 3rem;
    padding-top: 2rem;
    border-top: 2px solid var(--border-color);
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: var(--radius);
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 1rem;
}

.btn-prev {
    background: white;
    color: var(--dark-color);
    border: 2px solid var(--border-color);
}

.btn-prev:hover {
    background: var(--light-color);
    border-color: var(--primary-color);
    color: var(--primary-color);
}

.btn-next,
.btn-submit {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.btn-next:hover,
.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-submit:disabled:hover {
    transform: none;
    box-shadow: none;
}

/* Сообщения об ошибках */
.alert-message {
    padding: 1rem 1.5rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 10px;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.alert-message.error {
    background: #fee2e2;
    color: var(--danger-color);
    border: 2px solid #fecaca;
}

.alert-message i {
    font-size: 1.25rem;
}

/* Сообщение об ошибке пользователя */
.error-message {
    text-align: center;
    padding: 3rem;
    background: var(--light-color);
    border-radius: var(--radius);
    border: 2px solid var(--border-color);
}

.error-message i {
    font-size: 3rem;
    color: var(--danger-color);
    margin-bottom: 1rem;
}

.error-message h3 {
    font-size: 1.5rem;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.error-message p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

.btn-login {
    padding: 12px 24px;
    background: var(--primary-color);
    color: white;
    border-radius: var(--radius);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: var(--transition);
}

.btn-login:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
}

/* Адаптивность */
@media (max-width: 768px) {
    .create-title {
        font-size: 2rem;
    }
    
    .progress-steps {
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .platform-selector {
        grid-template-columns: 1fr;
    }
    
    .plans-container {
        grid-template-columns: 1fr;
    }
    
    .period-selector {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .step-actions {
        flex-direction: column;
        gap: 1rem;
    }
    
    .btn {
        width: 100%;
    }
}
</style>
@endsection
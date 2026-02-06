@extends('layouts.admin-app')

@section('title', 'Управление тарифами')

@section('content')
<div class="container py-5">
    <div class="plans-management-wrapper">
        <!-- Заголовок -->
        <div class="management-header">
            <div class="header-icon">
                <i class="fas fa-cubes"></i>
            </div>
            <h1 class="management-title">Управление тарифами</h1>
            <p class="management-subtitle">Создавайте и редактируйте тарифы для Minecraft серверов</p>
        </div>

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-cube"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $plans->count() }}</h3>
                    <p>Всего тарифов</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-toggle-on"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $plans->where('is_active', true)->count() }}</h3>
                    <p>Активных</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fab fa-java"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $plans->where('game_type', 'java')->count() }}</h3>
                    <p>Для Java</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $plans->where('game_type', 'bedrock')->count() }}</h3>
                    <p>Для Bedrock</p>
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="action-buttons">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPlanModal">
                <i class="fas fa-plus me-2"></i>Создать тариф
            </button>
            <div class="filter-controls">
                <select id="filterGameType" class="form-select" style="width: auto;">
                    <option value="">Все типы</option>
                    <option value="java">Java Edition</option>
                    <option value="bedrock">Bedrock Edition</option>
                </select>
                <select id="filterStatus" class="form-select" style="width: auto;">
                    <option value="">Все статусы</option>
                    <option value="active">Активные</option>
                    <option value="inactive">Неактивные</option>
                </select>
            </div>
        </div>

        <!-- Таблица тарифов -->
        <div class="table-container">
            <table class="plans-table">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Название</th>
                        <th>Платформа</th>
                        <th>Ресурсы</th>
                        <th>Цена</th>
                        <th>Статус</th>
                        <th width="150">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    <tr class="plan-row" data-game-type="{{ $plan->game_type ?? 'java' }}" data-status="{{ $plan->is_active ? 'active' : 'inactive' }}">
                        <td class="text-center">
                            <span class="plan-id">#{{ $plan->id }}</span>
                        </td>
                        <td>
                            <div class="plan-name">
                                <strong>{{ $plan->name }}</strong>
                                @if($plan->description)
                                    <div class="plan-description">{{ $plan->description }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="platform-badge {{ $plan->game_type == 'bedrock' ? 'bedrock' : 'java' }}">
                                <i class="fas fa-{{ $plan->game_type == 'bedrock' ? 'mobile-alt' : 'desktop' }} me-1"></i>
                                {{ $plan->game_type == 'bedrock' ? 'Bedrock' : 'Java Edition' }}
                            </div>
                        </td>
                        <td>
                            <div class="plan-resources">
                                <div class="resource-item">
                                    <i class="fas fa-memory text-primary"></i>
                                    <span>{{ $plan->memory }}MB RAM</span>
                                </div>
                                <div class="resource-item">
                                    <i class="fas fa-hdd text-primary"></i>
                                    <span>{{ $plan->disk_space }}MB Диск</span>
                                </div>
                                <div class="resource-item">
                                    <i class="fas fa-users text-primary"></i>
                                    <span>{{ $plan->player_slots }} игроков</span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="plan-price">
                                <div class="price-main">
                                    ${{ number_format($plan->price_monthly, 2) }}<span>/мес</span>
                                </div>
                                @if($plan->price_quarterly)
                                    <div class="price-secondary">${{ number_format($plan->price_quarterly, 2) }}/квартал</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="status-badge {{ $plan->is_active ? 'active' : 'inactive' }}">
                                <i class="fas fa-{{ $plan->is_active ? 'check-circle' : 'times-circle' }} me-1"></i>
                                {{ $plan->is_active ? 'Активен' : 'Неактивен' }}
                            </div>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" class="btn-action btn-edit" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editPlanModal"
                                        data-plan-id="{{ $plan->id }}"
                                        data-plan-name="{{ $plan->name }}"
                                        data-plan-description="{{ $plan->description ?? '' }}"
                                        data-plan-game-type="{{ $plan->game_type ?? 'java' }}"
                                        data-plan-memory="{{ $plan->memory }}"
                                        data-plan-disk="{{ $plan->disk_space }}"
                                        data-plan-slots="{{ $plan->player_slots }}"
                                        data-plan-price-monthly="{{ $plan->price_monthly }}"
                                        data-plan-price-quarterly="{{ $plan->price_quarterly ?? '' }}"
                                        data-plan-price-half-year="{{ $plan->price_half_year ?? '' }}"
                                        data-plan-price-yearly="{{ $plan->price_yearly ?? '' }}"
                                        data-plan-is-active="{{ $plan->is_active }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn-action btn-toggle" 
                                        onclick="togglePlanStatus({{ $plan->id }})"
                                        title="{{ $plan->is_active ? 'Деактивировать' : 'Активировать' }}">
                                    <i class="fas fa-{{ $plan->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                </button>
                                <form action="{{ route('admin.plans.delete', $plan) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-delete" 
                                            onclick="return confirm('Удалить тариф {{ $plan->name }}?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="empty-state">
                                <i class="fas fa-cube fa-3x text-muted mb-3"></i>
                                <h4>Тарифы не найдены</h4>
                                <p class="text-muted">Создайте первый тариф для ваших серверов</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        @if($plans->hasPages())
            <div class="pagination-wrapper">
                {{ $plans->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Модальное окно создания тарифа -->
<div class="modal fade" id="createPlanModal" tabindex="-1" aria-labelledby="createPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createPlanModalLabel">
                    <i class="fas fa-plus me-2"></i>Создание нового тарифа
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.plans.store') }}" method="POST" id="createPlanForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- Основная информация -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plan_name" class="form-label">Название тарифа *</label>
                                <input type="text" class="form-control" id="plan_name" name="name" required>
                                <div class="form-text">Например: "Стартовый", "Профессиональный" и т.д.</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="plan_game_type" class="form-label">Платформа *</label>
                                <select class="form-select" id="plan_game_type" name="game_type" required>
                                    <option value="java">Java Edition</option>
                                    <option value="bedrock">Bedrock Edition</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="form-group">
                                <label for="plan_description" class="form-label">Описание</label>
                                <textarea class="form-control" id="plan_description" name="description" rows="2"></textarea>
                            </div>
                        </div>
                        
                        <!-- Ресурсы -->
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="plan_memory" class="form-label">Оперативная память (MB) *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="plan_memory" name="memory" 
                                           min="512" max="16384" step="512" value="1024" required>
                                    <span class="input-group-text">MB</span>
                                </div>
                                <div class="form-text">Минимум 512MB, максимум 16GB</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="plan_disk" class="form-label">Дисковое пространство (MB) *</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="plan_disk" name="disk_space" 
                                           min="1024" max="102400" step="1024" value="10240" required>
                                    <span class="input-group-text">MB</span>
                                </div>
                                <div class="form-text">Минимум 1GB, максимум 100GB</div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="plan_slots" class="form-label">Количество слотов *</label>
                                <input type="number" class="form-control" id="plan_slots" name="player_slots" 
                                       min="1" max="100" value="10" required>
                            </div>
                        </div>
                        
                        <!-- Цены -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="plan_price_monthly" class="form-label">Цена за месяц ($) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="plan_price_monthly" name="price_monthly" 
                                           min="1" max="100" step="0.01" value="5.00" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="plan_price_quarterly" class="form-label">Цена за квартал ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="plan_price_quarterly" name="price_quarterly" 
                                           min="1" max="300" step="0.01">
                                    <span class="input-group-text" id="quarterly-discount"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="plan_price_half_year" class="form-label">Цена за полгода ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="plan_price_half_year" name="price_half_year" 
                                           min="1" max="600" step="0.01">
                                    <span class="input-group-text" id="half-year-discount"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="plan_price_yearly" class="form-label">Цена за год ($)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="plan_price_yearly" name="price_yearly" 
                                           min="1" max="1200" step="0.01">
                                    <span class="input-group-text" id="yearly-discount"></span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Дополнительные настройки -->
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="plan_is_active" name="is_active" value="1" checked>
                                <label class="form-check-label" for="plan_is_active">
                                    Тариф активен и доступен для покупки
                                </label>
                            </div>
                        </div>
                        
                        <!-- Предпросмотр тарифа -->
                        <div class="col-12">
                            <div class="preview-card">
                                <h6>Предпросмотр тарифа:</h6>
                                <div class="preview-content">
                                    <div class="preview-header">
                                        <h4 id="preview-name">Новый тариф</h4>
                                        <div class="preview-price">
                                            $<span id="preview-price">5.00</span><span>/мес</span>
                                        </div>
                                    </div>
                                    <div class="preview-resources">
                                        <div class="preview-resource">
                                            <i class="fas fa-memory"></i>
                                            <span id="preview-memory">1024MB RAM</span>
                                        </div>
                                        <div class="preview-resource">
                                            <i class="fas fa-hdd"></i>
                                            <span id="preview-disk">10240MB Диск</span>
                                        </div>
                                        <div class="preview-resource">
                                            <i class="fas fa-users"></i>
                                            <span id="preview-slots">10 игроков</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Создать тариф</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно редактирования тарифа -->
<div class="modal fade" id="editPlanModal" tabindex="-1" aria-labelledby="editPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPlanModalLabel">
                    <i class="fas fa-edit me-2"></i>Редактирование тарифа
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.plans.update', ['plan' => 'PLAN_ID']) }}" method="POST" id="editPlanForm">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Содержимое такое же как в создании, но с подставленными значениями -->
                    <div id="editPlanFormContent">
                        <!-- Форма будет загружена динамически -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Фильтрация таблицы
    const filterGameType = document.getElementById('filterGameType');
    const filterStatus = document.getElementById('filterStatus');
    const planRows = document.querySelectorAll('.plan-row');
    
    function filterTable() {
        const gameType = filterGameType.value;
        const status = filterStatus.value;
        
        planRows.forEach(row => {
            const rowGameType = row.dataset.gameType;
            const rowStatus = row.dataset.status;
            
            const showByGameType = !gameType || rowGameType === gameType;
            const showByStatus = !status || rowStatus === status;
            
            row.style.display = (showByGameType && showByStatus) ? '' : 'none';
        });
    }
    
    filterGameType.addEventListener('change', filterTable);
    filterStatus.addEventListener('change', filterTable);
    
    // Предпросмотр в модальном окне создания
    const monthlyPriceInput = document.getElementById('plan_price_monthly');
    const quarterlyPriceInput = document.getElementById('plan_price_quarterly');
    const halfYearPriceInput = document.getElementById('plan_price_half_year');
    const yearlyPriceInput = document.getElementById('plan_price_yearly');
    
    function calculateDiscount(periodPrice, months) {
        if (!monthlyPriceInput.value) return '';
        const monthlyPrice = parseFloat(monthlyPriceInput.value);
        const total = periodPrice;
        const expected = monthlyPrice * months;
        const discount = Math.round((1 - total/expected) * 100);
        return discount > 0 ? `-${discount}%` : '';
    }
    
    function updatePreview() {
        // Основная информация
        const name = document.getElementById('plan_name').value || 'Новый тариф';
        const memory = document.getElementById('plan_memory').value + 'MB RAM';
        const disk = document.getElementById('plan_disk').value + 'MB Диск';
        const slots = document.getElementById('plan_slots').value + ' игроков';
        const price = document.getElementById('plan_price_monthly').value || '5.00';
        
        document.getElementById('preview-name').textContent = name;
        document.getElementById('preview-price').textContent = price;
        document.getElementById('preview-memory').textContent = memory;
        document.getElementById('preview-disk').textContent = disk;
        document.getElementById('preview-slots').textContent = slots;
        
        // Расчет скидок
        if (quarterlyPriceInput.value) {
            const discount = calculateDiscount(quarterlyPriceInput.value, 3);
            document.getElementById('quarterly-discount').textContent = discount;
        }
        
        if (halfYearPriceInput.value) {
            const discount = calculateDiscount(halfYearPriceInput.value, 6);
            document.getElementById('half-year-discount').textContent = discount;
        }
        
        if (yearlyPriceInput.value) {
            const discount = calculateDiscount(yearlyPriceInput.value, 12);
            document.getElementById('yearly-discount').textContent = discount;
        }
    }
    
    // Обновление предпросмотра при изменении полей
    ['plan_name', 'plan_memory', 'plan_disk', 'plan_slots', 'plan_price_monthly', 
     'plan_price_quarterly', 'plan_price_half_year', 'plan_price_yearly'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updatePreview);
    });
    
    // Инициализация предпросмотра
    updatePreview();
    
    // Редактирование тарифа
    const editModal = document.getElementById('editPlanModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const planId = button.dataset.planId;
            
            // Обновляем action формы
            const form = document.getElementById('editPlanForm');
            form.action = form.action.replace('PLAN_ID', planId);
            
            // Загружаем данные в форму
            const formContent = document.getElementById('editPlanFormContent');
            formContent.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Название тарифа *</label>
                            <input type="text" class="form-control" name="name" value="${button.dataset.planName}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Платформа *</label>
                            <select class="form-select" name="game_type" required>
                                <option value="java" ${button.dataset.planGameType === 'java' ? 'selected' : ''}>Java Edition</option>
                                <option value="bedrock" ${button.dataset.planGameType === 'bedrock' ? 'selected' : ''}>Bedrock Edition</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="2">${button.dataset.planDescription}</textarea>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Оперативная память (MB) *</label>
                            <input type="number" class="form-control" name="memory" 
                                   value="${button.dataset.planMemory}" min="512" max="16384" step="512" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Дисковое пространство (MB) *</label>
                            <input type="number" class="form-control" name="disk_space" 
                                   value="${button.dataset.planDisk}" min="1024" max="102400" step="1024" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Количество слотов *</label>
                            <input type="number" class="form-control" name="player_slots" 
                                   value="${button.dataset.planSlots}" min="1" max="100" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Цена за месяц ($) *</label>
                            <input type="number" class="form-control" name="price_monthly" 
                                   value="${button.dataset.planPriceMonthly}" min="1" max="100" step="0.01" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Цена за квартал ($)</label>
                            <input type="number" class="form-control" name="price_quarterly" 
                                   value="${button.dataset.planPriceQuarterly || ''}" min="1" max="300" step="0.01">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Цена за полгода ($)</label>
                            <input type="number" class="form-control" name="price_half_year" 
                                   value="${button.dataset.planPriceHalfYear || ''}" min="1" max="600" step="0.01">
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Цена за год ($)</label>
                            <input type="number" class="form-control" name="price_yearly" 
                                   value="${button.dataset.planPriceYearly || ''}" min="1" max="1200" step="0.01">
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" 
                                   ${button.dataset.planIsActive === '1' ? 'checked' : ''}>
                            <label class="form-check-label">
                                Тариф активен и доступен для покупки
                            </label>
                        </div>
                    </div>
                </div>
            `;
        });
    }
});

// Переключение статуса тарифа
function togglePlanStatus(planId) {
    if (!confirm('Изменить статус тарифа?')) return;
    
    fetch(`/admin/plans/${planId}/toggle`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Ошибка при изменении статуса');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка при изменении статуса');
    });
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

.plans-management-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Заголовок */
.management-header {
    text-align: center;
    margin-bottom: 3rem;
}

.header-icon {
    font-size: 4rem;
    color: var(--primary-color);
    margin-bottom: 1rem;
}

.management-title {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.management-subtitle {
    font-size: 1.1rem;
    color: #6b7280;
}

/* Статистика */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    border-radius: var(--radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    border: 2px solid var(--border-color);
    transition: var(--transition);
}

.stat-card:hover {
    border-color: var(--primary-color);
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.stat-info h3 {
    font-size: 2rem;
    font-weight: 800;
    color: var(--dark-color);
    margin: 0;
    line-height: 1;
}

.stat-info p {
    color: #6b7280;
    margin: 0;
    font-size: 0.9rem;
}

/* Кнопки действий */
.action-buttons {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
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
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.filter-controls {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.form-select {
    padding: 8px 16px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    background: white;
    color: var(--dark-color);
    font-size: 0.9rem;
    transition: var(--transition);
}

.form-select:focus {
    outline: none;
    border-color: var(--primary-color);
}

/* Таблица */
.table-container {
    background: white;
    border-radius: var(--radius);
    border: 2px solid var(--border-color);
    overflow: hidden;
    margin-bottom: 2rem;
}

.plans-table {
    width: 100%;
    border-collapse: collapse;
}

.plans-table thead {
    background: var(--light-color);
}

.plans-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--dark-color);
    border-bottom: 2px solid var(--border-color);
}

.plans-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: top;
}

.plans-table tbody tr:hover {
    background: var(--light-color);
}

.plans-table tbody tr:last-child td {
    border-bottom: none;
}

/* Элементы таблицы */
.plan-id {
    font-weight: 700;
    color: var(--primary-color);
}

.plan-name strong {
    display: block;
    margin-bottom: 4px;
    color: var(--dark-color);
}

.plan-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.platform-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.platform-badge.java {
    background: rgba(102, 126, 234, 0.1);
    color: var(--primary-color);
    border: 1px solid rgba(102, 126, 234, 0.3);
}

.platform-badge.bedrock {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success-color);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.plan-resources {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.resource-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    color: #4b5563;
}

.resource-item i {
    width: 16px;
}

.plan-price .price-main {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--primary-color);
}

.plan-price .price-main span {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: normal;
}

.plan-price .price-secondary {
    font-size: 0.75rem;
    color: #6b7280;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.active {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success-color);
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.status-badge.inactive {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger-color);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

/* Кнопки действий в таблице */
.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-action {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: white;
}

.btn-edit {
    background: var(--primary-color);
}

.btn-edit:hover {
    background: var(--primary-dark);
    transform: scale(1.1);
}

.btn-toggle {
    background: var(--warning-color);
}

.btn-toggle:hover {
    background: #d97706;
    transform: scale(1.1);
}

.btn-delete {
    background: var(--danger-color);
}

.btn-delete:hover {
    background: #dc2626;
    transform: scale(1.1);
}

/* Состояние "пусто" */
.empty-state {
    padding: 3rem;
    text-align: center;
}

.empty-state h4 {
    font-size: 1.25rem;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

/* Пагинация */
.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}

/* Модальные окна */
.modal-content {
    border: none;
    border-radius: var(--radius);
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-bottom: none;
    padding: 1.5rem 2rem;
}

.modal-title {
    font-weight: 600;
    display: flex;
    align-items: center;
}

.btn-close {
    filter: brightness(0) invert(1);
    opacity: 0.8;
}

.btn-close:hover {
    opacity: 1;
}

.modal-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.9rem;
    transition: var(--transition);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
}

.form-text {
    font-size: 0.75rem;
    color: #6b7280;
    margin-top: 4px;
}

.input-group .form-control {
    border-radius: 8px 0 0 8px;
}

.input-group-text {
    background: var(--light-color);
    border: 2px solid var(--border-color);
    border-left: none;
    color: #6b7280;
}

.input-group .form-control:focus + .input-group-text {
    border-color: var(--primary-color);
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* Предпросмотр тарифа */
.preview-card {
    background: var(--light-color);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-top: 1rem;
    border: 2px solid var(--border-color);
}

.preview-card h6 {
    color: var(--dark-color);
    margin-bottom: 1rem;
    font-weight: 600;
}

.preview-content {
    background: white;
    border-radius: 8px;
    padding: 1rem;
    border: 1px solid var(--border-color);
}

.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border-color);
}

.preview-header h4 {
    margin: 0;
    font-size: 1.25rem;
    color: var(--dark-color);
}

.preview-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
}

.preview-price span {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: normal;
}

.preview-resources {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.preview-resource {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #4b5563;
}

.preview-resource i {
    color: var(--primary-color);
}

/* Адаптивность */
@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    
    .filter-controls {
        flex-direction: column;
    }
    
    .form-select {
        width: 100% !important;
    }
    
    .plans-table {
        display: block;
        overflow-x: auto;
    }
    
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
}
</style>
@endsection
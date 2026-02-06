@extends('layouts.admin-app')

@section('title', 'Управление пользователями')

@section('content')
<div class="container py-5">
    <div class="users-management-wrapper">
        <!-- Заголовок -->
        <div class="management-header">
            <div class="header-icon">
                <i class="fas fa-users"></i>
            </div>
            <h1 class="management-title">Управление пользователями</h1>
            <p class="management-subtitle">Администрирование пользователей и их балансов</p>
        </div>

        <!-- Статистика -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $totalUsers }}</h3>
                    <p>Всего пользователей</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $activeUsers }}</h3>
                    <p>Активных</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-server"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ $totalServers }}</h3>
                    <p>Серверов</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-info">
                    <h3>{{ number_format($totalBalance, 2) }} ₽</h3>
                    <p>Общий баланс</p>
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <div class="action-buttons">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                <i class="fas fa-user-plus me-2"></i>Добавить пользователя
            </button>
            <div class="filter-controls">
                <input type="text" id="searchInput" class="form-control" 
                       placeholder="Поиск по имени, email или ID..." 
                       style="width: 250px;">
                <select id="filterStatus" class="form-select">
                    <option value="">Все статусы</option>
                    <option value="active">Активные</option>
                    <option value="banned">Заблокированные</option>
                    <option value="unverified">Не подтвержденные</option>
                    <option value="deleted">Удаленные</option>
                </select>
                <select id="filterRole" class="form-select">
                    <option value="">Все роли</option>
                    <option value="user">Пользователь</option>
                    <option value="moderator">Модератор</option>
                    <option value="admin">Администратор</option>
                </select>
            </div>
        </div>

        <!-- Таблица пользователей -->
        <div class="table-container">
            <table class="users-table">
                <thead>
                    <tr>
                        <th width="60">ID</th>
                        <th>Пользователь</th>
                        <th>Контакты</th>
                        <th>Статистика</th>
                        <th>Баланс</th>
                        <th>Статус</th>
                        <th>Дата регистрации</th>
                        <th width="140">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr class="user-row" 
                        data-status="{{ $user->deleted_at ? 'deleted' : ($user->is_banned ? 'banned' : ($user->email_verified_at ? 'active' : 'unverified')) }}"
                        data-role="{{ $user->role }}"
                        data-search="{{ strtolower($user->name . ' ' . $user->email . ' ' . $user->id) }}">
                        <td class="text-center">
                            <span class="user-id">#{{ $user->id }}</span>
                        </td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" 
                                             alt="{{ $user->name }}" 
                                             class="avatar-img">
                                    @else
                                        <div class="avatar-placeholder">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="user-details">
                                    <div class="user-name">{{ $user->name }}</div>
                                    <div class="user-role-badge role-{{ $user->role }}">
                                        @switch($user->role)
                                            @case('admin')
                                                <i class="fas fa-crown me-1"></i>Админ
                                                @break
                                            @case('moderator')
                                                <i class="fas fa-shield-alt me-1"></i>Модератор
                                                @break
                                            @default
                                                <i class="fas fa-user me-1"></i>Пользователь
                                        @endswitch
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="user-contacts">
                                <div class="contact-item">
                                    <i class="fas fa-envelope"></i>
                                    <a href="mailto:{{ $user->email }}" class="contact-link">{{ $user->email }}</a>
                                </div>
                                @if($user->discord_id)
                                    <div class="contact-item">
                                        <i class="fab fa-discord"></i>
                                        <span>{{ $user->discord_id }}</span>
                                    </div>
                                @endif
                                @if($user->telegram_id)
                                    <div class="contact-item">
                                        <i class="fab fa-telegram"></i>
                                        <span>{{ $user->telegram_id }}</span>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="user-stats">
                                <div class="stat-item">
                                    <i class="fas fa-server"></i>
                                    <span>{{ $user->servers_count ?? 0 }} серверов</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-shopping-cart"></i>
                                    <span>{{ $user->orders_count ?? 0 }} заказов</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <span>
                                        @if($user->last_login_at)
                                            {{ $user->last_login_at->diffForHumans() }}
                                        @else
                                            Никогда
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="user-balance">
                                <div class="balance-amount {{ $user->balance > 0 ? 'positive' : ($user->balance < 0 ? 'negative' : 'zero') }}">
                                    {{ number_format($user->balance, 2) }} ₽
                                </div>
                                @if($user->balance > 0)
                                    <button type="button" class="btn-withdraw" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#withdrawModal{{ $user->id }}">
                                        <i class="fas fa-minus-circle"></i> Списать
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="user-status">
                                @if($user->deleted_at)
                                    <span class="status-badge deleted">
                                        <i class="fas fa-trash"></i> Удален
                                    </span>
                                @elseif($user->is_banned)
                                    <span class="status-badge banned">
                                        <i class="fas fa-ban"></i> Заблокирован
                                    </span>
                                    @if($user->ban_reason)
                                        <div class="ban-reason" title="{{ $user->ban_reason }}">
                                            <i class="fas fa-info-circle"></i>
                                        </div>
                                    @endif
                                @elseif($user->email_verified_at)
                                    <span class="status-badge active">
                                        <i class="fas fa-check-circle"></i> Активен
                                    </span>
                                @else
                                    <span class="status-badge unverified">
                                        <i class="fas fa-exclamation-circle"></i> Не подтвержден
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="user-registration">
                                <div class="registration-date">{{ $user->created_at->format('d.m.Y H:i') }}</div>
                                <div class="registration-ago">{{ $user->created_at->diffForHumans() }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="user-actions">
                                <button type="button" class="btn-action btn-view" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#viewUserModal{{ $user->id }}"
                                        title="Просмотр">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="{{ route('admin.users.edit', $user->id) }}" 
                                   class="btn-action btn-edit" title="Редактировать">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                @if($user->deleted_at)
                                    <form action="{{ route('admin.users.restore', $user->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn-action btn-restore" title="Восстановить">
                                            <i class="fas fa-trash-restore"></i>
                                        </button>
                                    </form>
                                @elseif($user->is_banned)
                                    <form action="{{ route('admin.users.unban', $user->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn-action btn-unban" title="Разблокировать">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    </form>
                                @else
                                    <button type="button" class="btn-action btn-ban" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#banUserModal{{ $user->id }}"
                                            title="Заблокировать">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @endif
                                
                                @if(!$user->deleted_at)
                                    <button type="button" class="btn-action btn-delete"
                                            onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                            title="Удалить">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $user->id }}" 
                                          action="{{ route('admin.users.delete', $user->id) }}" 
                                          method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Модальные окна для каждого пользователя -->
                    @include('admin.users.modals.view', ['user' => $user])
                    @include('admin.users.modals.ban', ['user' => $user])
                    @include('admin.users.modals.withdraw', ['user' => $user])
                    
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <div class="empty-state">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h4>Пользователи не найдены</h4>
                                <p class="text-muted">Попробуйте изменить параметры поиска</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Пагинация -->
        @if($users->hasPages())
            <div class="pagination-wrapper">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Модальное окно создания пользователя -->
@include('admin.users.modals.create')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Фильтрация таблицы
    const searchInput = document.getElementById('searchInput');
    const filterStatus = document.getElementById('filterStatus');
    const filterRole = document.getElementById('filterRole');
    const userRows = document.querySelectorAll('.user-row');
    
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value;
        const roleFilter = filterRole.value;
        
        userRows.forEach(row => {
            const searchData = row.dataset.search || '';
            const status = row.dataset.status;
            const role = row.dataset.role;
            
            const showBySearch = !searchTerm || searchData.includes(searchTerm);
            const showByStatus = !statusFilter || status === statusFilter;
            const showByRole = !roleFilter || role === roleFilter;
            
            row.style.display = (showBySearch && showByStatus && showByRole) ? '' : 'none';
        });
    }
    
    searchInput.addEventListener('input', filterTable);
    filterStatus.addEventListener('change', filterTable);
    filterRole.addEventListener('change', filterTable);
    
    // Удаление пользователя
    window.deleteUser = function(userId, userName) {
        if (confirm(`Вы уверены, что хотите удалить пользователя "${userName}"?`)) {
            document.getElementById(`delete-form-${userId}`).submit();
        }
    };
    
    // Экспорт пользователей
    document.getElementById('exportUsersBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const params = new URLSearchParams(window.location.search);
        window.location.href = '/admin/users/export?' + params.toString();
    });
    
    // Автоматический фокус на поле поиска
    searchInput.focus();
});
</script>

<style>
:root {
    --primary-color: #667eea;
    --primary-dark: #5a67d8;
    --secondary-color: #764ba2;
    --success-color: #10b981;
    --danger-color: #ef4444;
    --warning-color: #f59e0b;
    --info-color: #3b82f6;
    --dark-color: #1f2937;
    --light-color: #f9fafb;
    --border-color: #e5e7eb;
    --shadow: 0 10px 25px rgba(0,0,0,0.1);
    --radius: 12px;
    --transition: all 0.3s ease;
}

.users-management-wrapper {
    max-width: 1400px;
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

.form-control, .form-select {
    padding: 8px 16px;
    border: 2px solid var(--border-color);
    border-radius: var(--radius);
    background: white;
    color: var(--dark-color);
    font-size: 0.9rem;
    transition: var(--transition);
}

.form-control:focus, .form-select:focus {
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

.users-table {
    width: 100%;
    border-collapse: collapse;
}

.users-table thead {
    background: var(--light-color);
}

.users-table th {
    padding: 1rem;
    text-align: left;
    font-weight: 600;
    color: var(--dark-color);
    border-bottom: 2px solid var(--border-color);
}

.users-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    vertical-align: top;
}

.users-table tbody tr:hover {
    background: var(--light-color);
}

.users-table tbody tr:last-child td {
    border-bottom: none;
}

/* Элементы таблицы */
.user-id {
    font-weight: 700;
    color: var(--primary-color);
    font-size: 0.9rem;
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.user-avatar {
    position: relative;
}

.avatar-img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--primary-color);
}

.avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1.2rem;
    border: 3px solid var(--primary-color);
}

.user-details {
    flex: 1;
}

.user-name {
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 4px;
}

.user-role-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.role-admin {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger-color);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.role-moderator {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning-color);
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.role-user {
    background: rgba(102, 126, 234, 0.1);
    color: var(--primary-color);
    border: 1px solid rgba(102, 126, 234, 0.3);
}

.user-contacts {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.contact-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    color: #4b5563;
}

.contact-item i {
    width: 16px;
    color: var(--primary-color);
}

.contact-link {
    color: var(--primary-color);
    text-decoration: none;
}

.contact-link:hover {
    text-decoration: underline;
}

.user-stats {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.875rem;
    color: #4b5563;
}

.stat-item i {
    width: 16px;
    color: var(--primary-color);
}

.user-balance {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.balance-amount {
    font-size: 1.25rem;
    font-weight: 700;
}

.balance-amount.positive {
    color: var(--success-color);
}

.balance-amount.negative {
    color: var(--danger-color);
}

.balance-amount.zero {
    color: #6b7280;
}

.btn-withdraw {
    background: var(--warning-color);
    color: white;
    border: none;
    border-radius: 6px;
    padding: 4px 12px;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.btn-withdraw:hover {
    background: #d97706;
    transform: translateY(-1px);
}

.user-status {
    display: flex;
    align-items: center;
    gap: 6px;
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

.status-badge.banned {
    background: rgba(239, 68, 68, 0.1);
    color: var(--danger-color);
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.status-badge.unverified {
    background: rgba(245, 158, 11, 0.1);
    color: var(--warning-color);
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.status-badge.deleted {
    background: rgba(31, 41, 55, 0.1);
    color: var(--dark-color);
    border: 1px solid rgba(31, 41, 55, 0.3);
}

.ban-reason i {
    color: var(--danger-color);
    cursor: help;
}

.user-registration {
    display: flex;
    flex-direction: column;
}

.registration-date {
    font-weight: 500;
    color: var(--dark-color);
    margin-bottom: 2px;
}

.registration-ago {
    font-size: 0.75rem;
    color: #6b7280;
}

/* Кнопки действий */
.user-actions {
    display: flex;
    gap: 6px;
}

.btn-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: var(--transition);
    color: white;
}

.btn-view {
    background: var(--info-color);
}

.btn-view:hover {
    background: #2563eb;
    transform: scale(1.1);
}

.btn-edit {
    background: var(--primary-color);
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-edit:hover {
    background: var(--primary-dark);
    transform: scale(1.1);
}

.btn-ban {
    background: var(--danger-color);
}

.btn-ban:hover {
    background: #dc2626;
    transform: scale(1.1);
}

.btn-unban {
    background: var(--success-color);
}

.btn-unban:hover {
    background: #059669;
    transform: scale(1.1);
}

.btn-restore {
    background: var(--warning-color);
}

.btn-restore:hover {
    background: #d97706;
    transform: scale(1.1);
}

.btn-delete {
    background: var(--dark-color);
}

.btn-delete:hover {
    background: #374151;
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
    
    .form-control, .form-select {
        width: 100% !important;
    }
    
    .users-table {
        display: block;
        overflow-x: auto;
    }
    
    .user-actions {
        flex-wrap: wrap;
    }
    
    .btn-action {
        width: 28px;
        height: 28px;
    }
}
</style>

<!-- Создайте файлы для модальных окон -->
@push('modals')
@include('admin.users.modals.create')
@endpush
@endsection
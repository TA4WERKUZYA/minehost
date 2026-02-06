@extends('layouts.admin-app')

@section('title', 'Редактирование пользователя')

@section('content')
<div class="container-fluid">
    <!-- Заголовок страницы -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-edit mr-2"></i>Редактирование пользователя
        </h1>
        <div>
            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Назад к списку
            </a>
        </div>
    </div>

    <!-- Уведомления -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Ошибки:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Основной контент -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Основная информация</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Имя пользователя *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="role" class="form-label">Роль *</label>
                                <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Пользователь</option>
                                    <option value="moderator" {{ old('role', $user->role) == 'moderator' ? 'selected' : '' }}>Модератор</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Администратор</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="balance" class="form-label">Баланс (₽)</label>
                                <input type="number" step="0.01" class="form-control @error('balance') is-invalid @enderror" 
                                       id="balance" name="balance" value="{{ old('balance', $user->balance) }}">
                                @error('balance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="discord_id" class="form-label">Discord ID</label>
                                <input type="text" class="form-control @error('discord_id') is-invalid @enderror" 
                                       id="discord_id" name="discord_id" value="{{ old('discord_id', $user->discord_id) }}">
                                @error('discord_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="telegram_id" class="form-label">Telegram ID</label>
                                <input type="text" class="form-control @error('telegram_id') is-invalid @enderror" 
                                       id="telegram_id" name="telegram_id" value="{{ old('telegram_id', $user->telegram_id) }}">
                                @error('telegram_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="country" class="form-label">Страна</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" value="{{ old('country', $user->country) }}">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="timezone" class="form-label">Часовой пояс</label>
                                <select class="form-select @error('timezone') is-invalid @enderror" id="timezone" name="timezone">
                                    <option value="">Выберите часовой пояс</option>
                                    <option value="Europe/Moscow" {{ old('timezone', $user->timezone) == 'Europe/Moscow' ? 'selected' : '' }}>Москва (UTC+3)</option>
                                    <option value="Europe/Kaliningrad" {{ old('timezone', $user->timezone) == 'Europe/Kaliningrad' ? 'selected' : '' }}>Калининград (UTC+2)</option>
                                    <option value="Asia/Yekaterinburg" {{ old('timezone', $user->timezone) == 'Asia/Yekaterinburg' ? 'selected' : '' }}>Екатеринбург (UTC+5)</option>
                                    <option value="Asia/Novosibirsk" {{ old('timezone', $user->timezone) == 'Asia/Novosibirsk' ? 'selected' : '' }}>Новосибирск (UTC+7)</option>
                                    <option value="Asia/Vladivostok" {{ old('timezone', $user->timezone) == 'Asia/Vladivostok' ? 'selected' : '' }}>Владивосток (UTC+10)</option>
                                </select>
                                @error('timezone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="is_banned" name="is_banned" 
                                       value="1" {{ old('is_banned', $user->is_banned) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_banned">
                                    Заблокировать пользователя
                                </label>
                            </div>
                            
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="stop_servers" name="stop_servers" value="1">
                                <label class="form-check-label" for="stop_servers">
                                    Остановить все серверы при блокировке
                                </label>
                            </div>
                            
                            <div id="banReasonSection" style="{{ old('is_banned', $user->is_banned) ? '' : 'display: none;' }}">
                                <label for="ban_reason" class="form-label">Причина блокировки</label>
                                <textarea class="form-control @error('ban_reason') is-invalid @enderror" 
                                          id="ban_reason" name="ban_reason" rows="3">{{ old('ban_reason', $user->ban_reason) }}</textarea>
                                @error('ban_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="mb-4">

                        <h6 class="mb-3">Сменить пароль</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Новый пароль</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Подтверждение пароля</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
                                <div class="form-text">Оставьте пустым, если не хотите менять пароль</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-2"></i>Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Сохранить изменения
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Боковая панель с информацией -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Информация о пользователе</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" 
                                 alt="{{ $user->name }}" 
                                 class="rounded-circle mb-3" width="120" height="120">
                        @else
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto mb-3" 
                                 style="width: 120px; height: 120px; font-size: 3rem;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                        <h5>{{ $user->name }}</h5>
                        <p class="text-muted">{{ $user->email }}</p>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Статус</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>ID:</span>
                            <strong>#{{ $user->id }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Роль:</span>
                            @switch($user->role)
                                @case('admin')
                                    <span class="badge bg-danger">Администратор</span>
                                    @break
                                @case('moderator')
                                    <span class="badge bg-warning">Модератор</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">Пользователь</span>
                            @endswitch
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Статус:</span>
                            @if($user->is_banned)
                                <span class="badge bg-danger">Заблокирован</span>
                            @else
                                <span class="badge bg-success">Активен</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Email подтвержден:</span>
                            @if($user->email_verified_at)
                                <span class="badge bg-success">Да</span>
                            @else
                                <span class="badge bg-warning">Нет</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Баланс:</span>
                            <strong class="{{ $user->balance > 0 ? 'text-success' : 'text-muted' }}">
                                {{ number_format($user->balance, 2) }} ₽
                            </strong>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted mb-3">Даты</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Регистрация:</span>
                            <span>{{ $user->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Последнее обновление:</span>
                            <span>{{ $user->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        @if($user->last_login_at)
                            <div class="d-flex justify-content-between">
                                <span>Последний вход:</span>
                                <span>{{ $user->last_login_at->format('d.m.Y H:i') }}</span>
                            </div>
                        @endif
                        @if($user->banned_at)
                            <div class="d-flex justify-content-between">
                                <span>Заблокирован:</span>
                                <span>{{ $user->banned_at->format('d.m.Y H:i') }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4">
                        <h6 class="text-muted mb-3">Действия</h6>
                        <div class="d-grid gap-2">
                            @if($user->is_banned)
                                <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="d-grid">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-unlock mr-2"></i>Разблокировать
                                    </button>
                                </form>
                            @else
                                <button type="button" class="btn btn-warning mb-2" data-bs-toggle="modal" data-bs-target="#banModal">
                                    <i class="fas fa-ban mr-2"></i>Заблокировать
                                </button>
                            @endif

                            <button type="button" class="btn btn-info mb-2" data-bs-toggle="modal" data-bs-target="#addBalanceModal">
                                <i class="fas fa-plus-circle mr-2"></i>Пополнить баланс
                            </button>

                            <button type="button" class="btn btn-danger mb-2" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                                <i class="fas fa-minus-circle mr-2"></i>Списать баланс
                            </button>

                            <a href="{{ route('admin.users.servers', $user) }}" class="btn btn-outline-primary mb-2">
                                <i class="fas fa-server mr-2"></i>Серверы
                            </a>

                            <a href="{{ route('admin.users.orders', $user) }}" class="btn btn-outline-secondary mb-2">
                                <i class="fas fa-shopping-cart mr-2"></i>Заказы
                            </a>

                            @if(class_exists('App\Models\Transaction'))
                                <a href="{{ route('admin.users.transactions', $user) }}" class="btn btn-outline-info mb-2">
                                    <i class="fas fa-exchange-alt mr-2"></i>Транзакции
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно блокировки -->
<div class="modal fade" id="banModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Блокировка пользователя</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.ban', $user) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Вы собираетесь заблокировать пользователя <strong>{{ $user->name }}</strong>.</p>
                    <div class="mb-3">
                        <label for="modal_ban_reason" class="form-label">Причина блокировки</label>
                        <textarea class="form-control" id="modal_ban_reason" name="ban_reason" 
                                  rows="3" placeholder="Укажите причину блокировки..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="modal_ban_duration" class="form-label">Срок блокировки</label>
                        <select class="form-select" id="modal_ban_duration" name="ban_duration">
                            <option value="1">1 день</option>
                            <option value="7">7 дней</option>
                            <option value="30">30 дней</option>
                            <option value="365">1 год</option>
                            <option value="permanent" selected>Навсегда</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="modal_stop_servers" name="stop_servers" checked>
                        <label class="form-check-label" for="modal_stop_servers">
                            Остановить все серверы пользователя
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Заблокировать</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно пополнения баланса -->
<div class="modal fade" id="addBalanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Пополнение баланса</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.add-balance', $user) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Текущий баланс пользователя <strong>{{ $user->name }}</strong>: {{ number_format($user->balance, 2) }} ₽</p>
                    <div class="mb-3">
                        <label for="add_amount" class="form-label">Сумма пополнения</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="add_amount" name="amount" 
                                   step="0.01" min="0.01" value="100" required>
                            <span class="input-group-text">₽</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="add_reason" class="form-label">Причина пополнения</label>
                        <input type="text" class="form-control" id="add_reason" name="reason" 
                               placeholder="Например: Бонус за регистрацию" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-success">Пополнить</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Модальное окно списания баланса -->
<div class="modal fade" id="withdrawModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Списание баланса</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.withdraw', $user) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Текущий баланс пользователя <strong>{{ $user->name }}</strong>: {{ number_format($user->balance, 2) }} ₽</p>
                    <div class="mb-3">
                        <label for="withdraw_amount" class="form-label">Сумма списания</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="withdraw_amount" name="amount" 
                                   step="0.01" min="0.01" max="{{ $user->balance }}" 
                                   value="{{ min(100, $user->balance) }}" required>
                            <span class="input-group-text">₽</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="withdraw_reason" class="form-label">Причина списания</label>
                        <input type="text" class="form-control" id="withdraw_reason" name="reason" 
                               placeholder="Например: Оплата сервера" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Списать</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Показать/скрыть поле причины блокировки
        const banCheckbox = document.getElementById('is_banned');
        const banReasonSection = document.getElementById('banReasonSection');
        
        if (banCheckbox && banReasonSection) {
            banCheckbox.addEventListener('change', function() {
                banReasonSection.style.display = this.checked ? 'block' : 'none';
            });
        }
        
        // Валидация пароля
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        
        function validatePassword() {
            if (passwordInput.value !== passwordConfirmInput.value) {
                passwordConfirmInput.setCustomValidity('Пароли не совпадают');
            } else {
                passwordConfirmInput.setCustomValidity('');
            }
        }
        
        if (passwordInput && passwordConfirmInput) {
            passwordInput.addEventListener('input', validatePassword);
            passwordConfirmInput.addEventListener('input', validatePassword);
        }
        
        // Подтверждение действий
        document.querySelectorAll('form').forEach(form => {
            if (form.querySelector('button[type="submit"]')?.classList.contains('btn-danger')) {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Вы уверены, что хотите выполнить это действие?')) {
                        e.preventDefault();
                    }
                });
            }
        });
    });
</script>
@endpush

<style>
    .form-label {
        font-weight: 600;
    }
    
    .badge {
        font-size: 0.85em;
    }
</style>
@endsection

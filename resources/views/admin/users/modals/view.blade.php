@foreach($users as $user)
<div class="modal fade" id="viewUserModal{{ $user->id }}" tabindex="-1" 
     aria-labelledby="viewUserModalLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel{{ $user->id }}">
                    <i class="fas fa-user me-2"></i>Просмотр пользователя
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Левая колонка - основная информация -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body text-center">
                                <!-- Аватар -->
                                <div class="avatar-container mb-3">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" 
                                             alt="{{ $user->name }}"
                                             class="rounded-circle border border-4 border-primary"
                                             style="width: 120px; height: 120px; object-fit: cover;">
                                    @else
                                        <div class="rounded-circle border border-4 border-primary d-flex align-items-center justify-content-center mx-auto bg-gradient-primary text-white"
                                             style="width: 120px; height: 120px; font-size: 2.5rem;">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Имя и ID -->
                                <h4 class="mb-1">{{ $user->name }}</h4>
                                <p class="text-muted mb-3">ID: #{{ $user->id }}</p>
                                
                                <!-- Роль -->
                                <span class="badge bg-{{ $user->role == 'admin' ? 'danger' : ($user->role == 'moderator' ? 'warning' : 'primary') }} fs-6 px-3 py-2 mb-3">
                                    @switch($user->role)
                                        @case('admin')
                                            <i class="fas fa-crown me-1"></i>Администратор
                                            @break
                                        @case('moderator')
                                            <i class="fas fa-shield-alt me-1"></i>Модератор
                                            @break
                                        @default
                                            <i class="fas fa-user me-1"></i>Пользователь
                                    @endswitch
                                </span>
                            </div>
                        </div>
                        
                        <!-- Контактная информация -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-envelope me-2"></i>Контактная информация</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-2">
                                    <i class="fas fa-envelope text-primary me-2"></i>
                                    <strong>Email:</strong> 
                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none">
                                        {{ $user->email }}
                                    </a>
                                    @if($user->email_verified_at)
                                        <span class="badge bg-success ms-2">
                                            <i class="fas fa-check"></i> Подтвержден
                                        </span>
                                    @endif
                                </div>
                                
                                @if($user->discord_id)
                                <div class="mb-2">
                                    <i class="fab fa-discord text-primary me-2"></i>
                                    <strong>Discord:</strong> {{ $user->discord_id }}
                                </div>
                                @endif
                                
                                @if($user->telegram_id)
                                <div class="mb-2">
                                    <i class="fab fa-telegram text-primary me-2"></i>
                                    <strong>Telegram:</strong> {{ $user->telegram_id }}
                                </div>
                                @endif
                                
                                @if($user->phone)
                                <div class="mb-2">
                                    <i class="fas fa-phone text-primary me-2"></i>
                                    <strong>Телефон:</strong> {{ $user->phone }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Правая колонка - статистика и активность -->
                    <div class="col-md-8">
                        <!-- Статус и баланс -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="fas fa-info-circle me-2"></i>Статус аккаунта
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            @if($user->deleted_at)
                                                <span class="badge bg-dark fs-6">
                                                    <i class="fas fa-trash me-1"></i> Удален
                                                </span>
                                            @elseif($user->is_banned)
                                                <span class="badge bg-danger fs-6">
                                                    <i class="fas fa-ban me-1"></i> Заблокирован
                                                </span>
                                                @if($user->ban_reason)
                                                    <div class="ms-2" data-bs-toggle="tooltip" 
                                                         title="Причина: {{ $user->ban_reason }}">
                                                        <i class="fas fa-info-circle text-danger"></i>
                                                    </div>
                                                @endif
                                            @elseif($user->email_verified_at)
                                                <span class="badge bg-success fs-6">
                                                    <i class="fas fa-check-circle me-1"></i> Активен
                                                </span>
                                            @else
                                                <span class="badge bg-warning fs-6">
                                                    <i class="fas fa-exclamation-circle me-1"></i> Не подтвержден
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <h6 class="card-title text-muted mb-3">
                                            <i class="fas fa-wallet me-2"></i>Баланс
                                        </h6>
                                        <h3 class="mb-0 {{ $user->balance > 0 ? 'text-success' : ($user->balance < 0 ? 'text-danger' : 'text-secondary') }}">
                                            {{ number_format($user->balance, 2) }} ₽
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Статистика -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm text-center">
                                    <div class="card-body">
                                        <h2 class="text-primary">{{ $user->servers_count ?? 0 }}</h2>
                                        <p class="text-muted mb-0">Серверов</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm text-center">
                                    <div class="card-body">
                                        <h2 class="text-success">{{ $user->orders_count ?? 0 }}</h2>
                                        <p class="text-muted mb-0">Заказов</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm text-center">
                                    <div class="card-body">
                                        <h2 class="text-info">{{ $user->transactions_count ?? 0 }}</h2>
                                        <p class="text-muted mb-0">Транзакций</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm text-center">
                                    <div class="card-body">
                                        <h2 class="text-warning">{{ $user->backups_count ?? 0 }}</h2>
                                        <p class="text-muted mb-0">Бэкапов</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Даты и активность -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-calendar me-2"></i>Даты</h6>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-2">
                                            <strong>Регистрация:</strong><br>
                                            {{ $user->created_at->format('d.m.Y H:i') }}
                                            <small class="text-muted">({{ $user->created_at->diffForHumans() }})</small>
                                        </p>
                                        
                                        @if($user->email_verified_at)
                                        <p class="mb-2">
                                            <strong>Email подтвержден:</strong><br>
                                            {{ $user->email_verified_at->format('d.m.Y H:i') }}
                                        </p>
                                        @endif
                                        
                                        @if($user->last_login_at)
                                        <p class="mb-2">
                                            <strong>Последний вход:</strong><br>
                                            {{ $user->last_login_at->format('d.m.Y H:i') }}
                                            <small class="text-muted">({{ $user->last_login_at->diffForHumans() }})</small>
                                        </p>
                                        @else
                                        <p class="mb-2">
                                            <strong>Последний вход:</strong><br>
                                            <span class="text-muted">Никогда</span>
                                        </p>
                                        @endif
                                        
                                        @if($user->deleted_at)
                                        <p class="mb-0">
                                            <strong>Удален:</strong><br>
                                            {{ $user->deleted_at->format('d.m.Y H:i') }}
                                        </p>
                                        @endif
                                        
                                        @if($user->is_banned && $user->banned_at)
                                        <p class="mb-0">
                                            <strong>Заблокирован:</strong><br>
                                            {{ $user->banned_at->format('d.m.Y H:i') }}
                                        </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Быстрые действия</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            @if(!$user->deleted_at)
                                                @if($user->is_banned)
                                                    <form action="{{ route('admin.users.unban', $user->id) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success w-100">
                                                            <i class="fas fa-unlock me-1"></i> Разблокировать
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button" class="btn btn-danger w-100" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#banUserModal{{ $user->id }}">
                                                        <i class="fas fa-ban me-1"></i> Заблокировать
                                                    </button>
                                                @endif
                                                
                                                @if($user->balance > 0)
                                                    <button type="button" class="btn btn-warning w-100"
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#withdrawModal{{ $user->id }}">
                                                        <i class="fas fa-minus-circle me-1"></i> Списать средства
                                                    </button>
                                                @endif
                                                
                                                <button type="button" class="btn btn-info w-100"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#addBalanceModal{{ $user->id }}">
                                                    <i class="fas fa-plus-circle me-1"></i> Пополнить баланс
                                                </button>
                                            @else
                                                <form action="{{ route('admin.users.restore', $user->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success w-100">
                                                        <i class="fas fa-trash-restore me-1"></i> Восстановить
                                                    </button>
                                                </form>
                                                
                                                <button type="button" class="btn btn-danger w-100"
                                                        onclick="forceDeleteUser({{ $user->id }}, '{{ $user->name }}')">
                                                    <i class="fas fa-trash-alt me-1"></i> Удалить навсегда
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Примечания администратора -->
                        @if($user->notes)
                        <div class="card border-0 shadow-sm mt-4">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Примечания администратора</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $user->notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Закрыть
                </button>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i>Редактировать
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
function forceDeleteUser(userId, userName) {
    if (confirm(`ВНИМАНИЕ! Вы собираетесь БЕЗВОЗВРАТНО удалить пользователя "${userName}"!\n\nВсе связанные данные (серверы, заказы, транзакции) также будут удалены!\n\nЭто действие нельзя отменить!`)) {
        const reason = prompt('Укажите причину удаления:', 'Нарушение правил');
        if (reason !== null) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${userId}/force-delete`;
            form.style.display = 'none';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = reason;
            
            form.appendChild(csrfToken);
            form.appendChild(methodInput);
            form.appendChild(reasonInput);
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
@endpush
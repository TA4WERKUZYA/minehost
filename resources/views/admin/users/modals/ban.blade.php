@foreach($users as $user)
@if(!$user->deleted_at && !$user->is_banned)
<div class="modal fade" id="banUserModal{{ $user->id }}" tabindex="-1" 
     aria-labelledby="banUserModalLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.ban', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="banUserModalLabel{{ $user->id }}">
                        <i class="fas fa-ban me-2"></i>Блокировка пользователя
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading">Вы собираетесь заблокировать пользователя:</h6>
                                <p class="mb-0"><strong>{{ $user->name }}</strong> (ID: {{ $user->id }})</p>
                                <p class="mb-0"><small>Email: {{ $user->email }}</small></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ban_reason{{ $user->id }}" class="form-label fw-bold">
                            <i class="fas fa-comment-dots me-1"></i>Причина блокировки *
                        </label>
                        <textarea class="form-control" id="ban_reason{{ $user->id }}" 
                                  name="ban_reason" rows="4" required 
                                  placeholder="Подробно опишите причину блокировки пользователя..."></textarea>
                        <div class="form-text">Эта причина будет показана пользователю при попытке входа</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="ban_duration{{ $user->id }}" class="form-label fw-bold">
                            <i class="fas fa-clock me-1"></i>Срок блокировки *
                        </label>
                        <select class="form-select" id="ban_duration{{ $user->id }}" name="ban_duration" required>
                            <option value="">Выберите срок</option>
                            <option value="0">Навсегда</option>
                            <option value="1">1 день</option>
                            <option value="3">3 дня</option>
                            <option value="7">7 дней</option>
                            <option value="30">30 дней</option>
                            <option value="custom">Указать дату разблокировки</option>
                        </select>
                    </div>
                    
                    <div class="mb-3 d-none" id="customDateContainer{{ $user->id }}">
                        <label for="ban_until{{ $user->id }}" class="form-label">
                            <i class="fas fa-calendar me-1"></i>Дата разблокировки
                        </label>
                        <input type="datetime-local" class="form-control" id="ban_until{{ $user->id }}" 
                               name="ban_until">
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="delete_servers{{ $user->id }}" 
                                   name="delete_servers" value="1">
                            <label class="form-check-label" for="delete_servers{{ $user->id }}">
                                <i class="fas fa-server me-1"></i>Остановить все серверы пользователя
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="send_notification{{ $user->id }}" 
                                   name="send_notification" value="1" checked>
                            <label class="form-check-label" for="send_notification{{ $user->id }}">
                                <i class="fas fa-bell me-1"></i>Отправить уведомление пользователю
                            </label>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Пользователь не сможет войти в систему до снятия блокировки.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Отмена
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban me-1"></i>Заблокировать пользователя
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const banDurationSelect{{ $user->id }} = document.getElementById('ban_duration{{ $user->id }}');
    const customDateContainer{{ $user->id }} = document.getElementById('customDateContainer{{ $user->id }}');
    
    if (banDurationSelect{{ $user->id }} && customDateContainer{{ $user->id }}) {
        banDurationSelect{{ $user->id }}.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateContainer{{ $user->id }}.classList.remove('d-none');
                
                // Установить минимальную дату - завтра
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const minDate = tomorrow.toISOString().slice(0, 16);
                document.getElementById('ban_until{{ $user->id }}').min = minDate;
                
                // Установить значение по умолчанию - через неделю
                const nextWeek = new Date();
                nextWeek.setDate(nextWeek.getDate() + 7);
                const defaultDate = nextWeek.toISOString().slice(0, 16);
                document.getElementById('ban_until{{ $user->id }}').value = defaultDate;
                
                // Сделать поле обязательным
                document.getElementById('ban_until{{ $user->id }}').required = true;
            } else {
                customDateContainer{{ $user->id }}.classList.add('d-none');
                document.getElementById('ban_until{{ $user->id }}').required = false;
            }
        });
        
        // Валидация формы
        const form = document.querySelector('#banUserModal{{ $user->id }} form');
        form.addEventListener('submit', function(e) {
            const banReason = document.getElementById('ban_reason{{ $user->id }}').value.trim();
            const banDuration = document.getElementById('ban_duration{{ $user->id }}').value;
            
            if (!banReason) {
                e.preventDefault();
                alert('Пожалуйста, укажите причину блокировки');
                return false;
            }
            
            if (!banDuration) {
                e.preventDefault();
                alert('Пожалуйста, выберите срок блокировки');
                return false;
            }
            
            if (banDuration === 'custom') {
                const banUntil = document.getElementById('ban_until{{ $user->id }}').value;
                if (!banUntil) {
                    e.preventDefault();
                    alert('Пожалуйста, укажите дату разблокировки');
                    return false;
                }
            }
        });
    }
});
</script>
@endpush
@endif
@endforeach
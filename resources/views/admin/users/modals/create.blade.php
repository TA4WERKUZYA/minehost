<div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createUserModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Добавить нового пользователя
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Имя пользователя *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Пароль *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="togglePassword('password')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Минимум 8 символов</div>
                                @error('password')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Подтверждение пароля *</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password_confirmation" 
                                           name="password_confirmation" required>
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="togglePassword('password_confirmation')">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Роль *</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Выберите роль</option>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Пользователь</option>
                                    <option value="moderator" {{ old('role') == 'moderator' ? 'selected' : '' }}>Модератор</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Администратор</option>
                                </select>
                                @error('role')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="balance" class="form-label">Начальный баланс</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" id="balance" 
                                           name="balance" value="{{ old('balance', 0) }}">
                                    <span class="input-group-text">₽</span>
                                </div>
                                <div class="form-text">Можно указать отрицательное значение</div>
                                @error('balance')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="discord_id" class="form-label">Discord ID</label>
                                <input type="text" class="form-control" id="discord_id" name="discord_id" 
                                       value="{{ old('discord_id') }}" placeholder="@username или ID">
                                @error('discord_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="telegram_id" class="form-label">Telegram ID</label>
                                <input type="text" class="form-control" id="telegram_id" name="telegram_id" 
                                       value="{{ old('telegram_id') }}" placeholder="@username или ID">
                                @error('telegram_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="email_verified" 
                                           name="email_verified" {{ old('email_verified') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="email_verified">
                                        Email подтвержден
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Аватар</label>
                        <input type="file" class="form-control" id="avatar" name="avatar" 
                               accept="image/*">
                        <div class="form-text">Максимальный размер: 2MB. Разрешены: JPG, PNG, GIF, WebP</div>
                        @error('avatar')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label">Примечания (только для администраторов)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                        <div class="form-text">Эти заметки видны только администраторам</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Отмена
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>Создать пользователя
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const button = input.parentNode.querySelector('button');
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Показать/скрыть дополнительные поля
document.addEventListener('DOMContentLoaded', function() {
    const balanceInput = document.getElementById('balance');
    if (balanceInput) {
        balanceInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            const group = this.parentNode;
            
            // Удаляем предыдущие классы
            group.classList.remove('border-success', 'border-danger', 'border-warning');
            
            if (value > 0) {
                group.classList.add('border-success');
            } else if (value < 0) {
                group.classList.add('border-danger');
            } else if (value === 0) {
                group.classList.add('border-warning');
            }
        });
    }
});
</script>
@endpush
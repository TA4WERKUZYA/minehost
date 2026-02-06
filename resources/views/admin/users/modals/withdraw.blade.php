@foreach($users as $user)
@if(!$user->deleted_at)
<div class="modal fade" id="addBalanceModal{{ $user->id }}" tabindex="-1" 
     aria-labelledby="addBalanceModalLabel{{ $user->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.users.add-balance', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="addBalanceModalLabel{{ $user->id }}">
                        <i class="fas fa-plus-circle me-2"></i>Пополнение баланса
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-user-circle fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="alert-heading">Пополнение баланса пользователя:</h6>
                                <p class="mb-0"><strong>{{ $user->name }}</strong> (ID: {{ $user->id }})</p>
                                <p class="mb-0">
                                    <small>Текущий баланс: <strong>{{ number_format($user->balance, 2) }} ₽</strong></small>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="input-group input-group-lg mb-2">
                            <span class="input-group-text bg-success text-white">
                                <i class="fas fa-plus"></i>
                            </span>
                            <input type="number" class="form-control" id="add_amount{{ $user->id }}" 
                                   name="amount" step="0.01" min="0.01" max="100000" 
                                   placeholder="0.00" required 
                                   oninput="updateAddPreview{{ $user->id }}(this.value)">
                            <span class="input-group-text">₽</span>
                        </div>
                        
                        <div class="form-text text-center">
                            <div class="btn-group btn-group-sm mt-2" role="group">
                                <button type="button" class="btn btn-outline-success" 
                                        onclick="setAmount{{ $user->id }}(50)">+50 ₽</button>
                                <button type="button" class="btn btn-outline-success" 
                                        onclick="setAmount{{ $user->id }}(100)">+100 ₽</button>
                                <button type="button" class="btn btn-outline-success" 
                                        onclick="setAmount{{ $user->id }}(500)">+500 ₽</button>
                                <button type="button" class="btn btn-outline-success" 
                                        onclick="setAmount{{ $user->id }}(1000)">+1000 ₽</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_reason{{ $user->id }}" class="form-label fw-bold">
                            <i class="fas fa-comment-dots me-1"></i>Причина пополнения *
                        </label>
                        <select class="form-select" id="add_reason{{ $user->id }}" name="reason" required>
                            <option value="">Выберите причину</option>
                            <option value="bonus">Бонусная выплата</option>
                            <option value="refund">Возврат средств</option>
                            <option value="correction">Корректировка баланса</option>
                            <option value="compensation">Компенсация</option>
                            <option value="manual">Ручное пополнение</option>
                            <option value="promo">Промо-акция</option>
                            <option value="other">Другая причина</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="add_description{{ $user->id }}" class="form-label">
                            <i class="fas fa-file-alt me-1"></i>Описание операции
                        </label>
                        <textarea class="form-control" id="add_description{{ $user->id }}" 
                                  name="description" rows="2" 
                                  placeholder="Дополнительная информация о пополнении..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="add_notify_user{{ $user->id }}" 
                                   name="notify_user" value="1" checked>
                            <label class="form-check-label" for="add_notify_user{{ $user->id }}">
                                <i class="fas fa-bell me-1"></i>Уведомить пользователя по email
                            </label>
                        </div>
                        
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="create_transaction{{ $user->id }}" 
                                   name="create_transaction" value="1" checked>
                            <label class="form-check-label" for="create_transaction{{ $user->id }}">
                                <i class="fas fa-exchange-alt me-1"></i>Создать запись о транзакции
                            </label>
                        </div>
                    </div>
                    
                    <!-- Превью операции -->
                    <div class="alert alert-light border mt-3">
                        <h6 class="alert-heading">
                            <i class="fas fa-eye me-1"></i>Предпросмотр операции
                        </h6>
                        <div class="row">
                            <div class="col-6">
                                <small class="text-muted">Текущий баланс:</small><br>
                                <span class="fw-bold">{{ number_format($user->balance, 2) }} ₽</span>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted">Будет добавлено:</small><br>
                                <span id="addAmount{{ $user->id }}" class="fw-bold text-success">
                                    0.00 ₽
                                </span>
                            </div>
                        </div>
                        <hr class="my-2">
                        <div class="row">
                            <div class="col-12">
                                <small class="text-muted">Новый баланс:</small><br>
                                <span id="newAddBalance{{ $user->id }}" class="fw-bold fs-5 text-success">
                                    {{ number_format($user->balance, 2) }} ₽
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Отмена
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-plus-circle me-1"></i>Подтвердить пополнение
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function updateAddPreview{{ $user->id }}(amount) {
    const currentBalance = parseFloat({{ $user->balance }});
    const addAmount = parseFloat(amount) || 0;
    
    // Обновить предпросмотр
    document.getElementById('addAmount{{ $user->id }}').textContent = 
        addAmount.toFixed(2) + ' ₽';
    
    const newBalance = currentBalance + addAmount;
    document.getElementById('newAddBalance{{ $user->id }}').textContent = 
        newBalance.toFixed(2) + ' ₽';
}

function setAmount{{ $user->id }}(amount) {
    const input = document.getElementById('add_amount{{ $user->id }}');
    input.value = amount;
    updateAddPreview{{ $user->id }}(amount);
}
</script>
@endpush
@endif
@endforeach
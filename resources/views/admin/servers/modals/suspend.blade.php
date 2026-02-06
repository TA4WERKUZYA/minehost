<!-- Модальное окно блокировки -->
<div class="modal fade" id="suspendModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Блокировка сервера</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.servers.manage', $server) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="suspend">
                
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Вы собираетесь заблокировать сервер <strong>{{ $server->name }}</strong> (ID: #{{ $server->id }}).
                    </div>
                    
                    <div class="mb-3">
                        <label for="suspend_reason" class="form-label">Причина блокировки</label>
                        <textarea class="form-control" id="suspend_reason" name="reason" 
                                  rows="3" placeholder="Укажите причину блокировки сервера..." required></textarea>
                        <div class="form-text">
                            Причина будет видна владельцу сервера
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="stop_server" name="stop_server" checked>
                        <label class="form-check-label" for="stop_server">
                            Остановить сервер перед блокировкой
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_user" name="notify_user" checked>
                        <label class="form-check-label" for="notify_user">
                            Уведомить пользователя по email
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger">Заблокировать сервер</button>
                </div>
            </form>
        </div>
    </div>
</div>

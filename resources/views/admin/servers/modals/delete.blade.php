<!-- Модальное окно удаления -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удаление сервера</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.servers.manage', $server) }}" method="POST">
                @csrf
                <!-- УДАЛИТЬ ЭТУ СТРОКУ: @method('PUT') -->
                
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Внимание! Это действие необратимо.</strong>
                    </div>
                    
                    <p>Вы собираетесь удалить сервер <strong>{{ $server->name }}</strong> (ID: #{{ $server->id }}).</p>
                    
                    <input type="hidden" name="action" value="force_delete">
                    
                    <div class="mb-3">
                        <label for="delete_reason" class="form-label">Причина удаления</label>
                        <textarea class="form-control" id="delete_reason" name="reason" 
                                  rows="2" placeholder="Укажите причину удаления..." required></textarea>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="delete_backups" name="delete_backups">
                        <label class="form-check-label" for="delete_backups">
                            Удалить все бэкапы сервера
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_user_delete" name="notify_user" checked>
                        <label class="form-check-label" for="notify_user_delete">
                            Уведомить пользователя
                        </label>
                    </div>
                    
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6>Информация о сервере:</h6>
                        <ul class="mb-0">
                            <li>Владелец: {{ $server->user->name }}</li>
                            <li>Создан: {{ $server->created_at->format('d.m.Y H:i') }}</li>
                            <li>Память: {{ $server->memory }}MB</li>
                            <li>Диск: {{ $server->disk_space }}MB</li>
                            <li>Бэкапов: {{ $server->backups->count() }}</li>
                        </ul>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Вы уверены, что хотите удалить сервер? Это действие необратимо!')">
                        Удалить сервер
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
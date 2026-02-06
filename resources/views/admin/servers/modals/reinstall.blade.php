<!-- Модальное окно переустановки -->
<div class="modal fade" id="reinstallModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Переустановка сервера</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.servers.manage', $server) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="reinstall">
                
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Внимание!</strong> Переустановка сервера удалит все файлы и настройки.
                    </div>
                    
                    <div class="mb-3">
                        <label for="reinstall_type" class="form-label">Тип переустановки</label>
                        <select class="form-select" id="reinstall_type" name="reinstall_type" required>
                            <option value="full">Полная переустановка (удалить всё)</option>
                            <option value="soft">Мягкая переустановка (сохранить мир)</option>
                            <option value="core_only">Только замена ядра</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reinstall_version" class="form-label">Версия Minecraft</label>
                        <select class="form-select" id="reinstall_version" name="version">
                            <option value="">Текущая версия ({{ $server->core_version ?? '1.20.4' }})</option>
                            <option value="1.21">1.21</option>
                            <option value="1.20.4">1.20.4</option>
                            <option value="1.20.1">1.20.1</option>
                            <option value="1.19.4">1.19.4</option>
                            <option value="1.18.2">1.18.2</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="reinstall_core" class="form-label">Ядро</label>
                        <select class="form-select" id="reinstall_core" name="core">
                            <option value="">Текущее ядро ({{ $server->core_type ?? 'Paper' }})</option>
                            <option value="paper">Paper</option>
                            <option value="purpur">Purpur</option>
                            <option value="spigot">Spigot</option>
                            <option value="craftbukkit">CraftBukkit</option>
                            <option value="fabric">Fabric</option>
                            <option value="forge">Forge</option>
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="create_backup" name="create_backup" checked>
                        <label class="form-check-label" for="create_backup">
                            Создать бэкап перед переустановкой
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="notify_user_reinstall" name="notify_user" checked>
                        <label class="form-check-label" for="notify_user_reinstall">
                            Уведомить пользователя
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-warning">Переустановить сервер</button>
                </div>
            </form>
        </div>
    </div>
</div>

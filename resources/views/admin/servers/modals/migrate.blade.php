<!-- Модальное окно переноса -->
<div class="modal fade" id="migrateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Перенос сервера на другую ноду</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.servers.manage', $server) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="action" value="migrate">
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Текущая нода: <strong>{{ $server->node->name ?? 'Не назначена' }}</strong>
                    </div>
                    
                    <div class="mb-3">
                        <label for="target_node" class="form-label">Целевая нода</label>
                        <select class="form-select" id="target_node" name="node_id" required>
                            <option value="">Выберите ноду</option>
                            @foreach($nodes as $node)
                                @if($node->id != $server->node_id && $node->is_active)
                                    <option value="{{ $node->id }}">
                                        {{ $node->name }} ({{ $node->location }})
                                        - Свободно: {{ $node->total_memory - $node->used_memory }}MB / {{ $node->total_disk - $node->used_disk }}MB
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="migration_type" class="form-label">Тип переноса</label>
                        <select class="form-select" id="migration_type" name="migration_type" required>
                            <option value="live">Live-перенос (без остановки)</option>
                            <option value="stop_copy">Остановить → Копировать → Запустить</option>
                            <option value="copy_only">Только копирование файлов</option>
                        </select>
                        <div class="form-text">
                            Live-перенос доступен только для Paper/Purpur серверов
                        </div>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="keep_ip" name="keep_ip">
                        <label class="form-check-label" for="keep_ip">
                            Сохранить текущий IP-адрес (если возможно)
                        </label>
                    </div>
                    
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="verify_disk_space" name="verify_disk_space" checked>
                        <label class="form-check-label" for="verify_disk_space">
                            Проверить свободное место на целевой ноде
                        </label>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                    <button type="submit" class="btn btn-primary">Начать перенос</button>
                </div>
            </form>
        </div>
    </div>
</div>

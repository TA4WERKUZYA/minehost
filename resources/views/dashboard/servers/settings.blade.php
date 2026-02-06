@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Заголовок -->
        <div class="mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $title }}</h1>
                    <p class="text-gray-600 mt-2">Измените параметры вашего сервера</p>
                </div>
                <a href="{{ route('servers.show', $server) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>Назад к серверу
                </a>
            </div>
        </div>
        
        <!-- Сообщения об ошибках/успехе -->
        @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400 mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif
        
        @if (session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Форма настроек -->
        <div class="bg-white rounded-xl shadow p-6">
            <form action="{{ route('servers.settings.update', $server) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Базовые настройки -->
                <div class="mb-8">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Основные настройки</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Имя сервера -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Имя сервера
                            </label>
                            <input type="text" name="name" value="{{ old('name', $server->name) }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Порт сервера -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Порт ({{ $server->port }})
                            </label>
                            <input type="number" name="port" value="{{ old('port', $server->port) }}"
                                   min="25565" max="27000"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Оставьте пустым для автоматического">
                            <p class="mt-1 text-sm text-gray-500">Измените только если необходимо</p>
                            @error('port')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Память -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Память (MB)
                            </label>
                            <input type="number" name="memory" value="{{ old('memory', $server->memory) }}"
                                   min="512" max="{{ $server->plan->max_memory ?? 16384 }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <p class="mt-1 text-sm text-gray-500">
                                Максимум по тарифу: {{ $server->plan->max_memory ?? 16384 }} MB
                            </p>
                            @error('memory')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Слоты игроков -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Слоты игроков
                            </label>
                            <input type="number" name="player_slots" value="{{ old('player_slots', $server->player_slots) }}"
                                   min="1" max="100"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            @error('player_slots')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <!-- Настройки ядра -->
                <div class="mb-8 pt-8 border-t border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Настройки ядра</h2>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Текущее ядро
                        </label>
                        <div class="p-4 bg-gray-50 rounded-lg">
                            @if($server->core)
                            <div class="flex items-center">
                                <i class="fas {{ $server->core->icon }} text-2xl text-{{ $server->core->color }} mr-3"></i>
                                <div>
                                    <h3 class="font-bold text-gray-900">
                                        {{ ucfirst($server->core->name) }} {{ $server->core->version }}
                                    </h3>
                                    <p class="text-sm text-gray-600">{{ $server->core->file_name }}</p>
                                </div>
                            </div>
                            @else
                            <p class="text-red-600">Ядро не установлено</p>
                            @endif
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Выберите новое ядро
                        </label>
                        <select name="core_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Не менять ядро --</option>
                            @foreach($availableCores as $core)
                            <option value="{{ $core->id }}" 
                                    {{ old('core_id') == $core->id ? 'selected' : '' }}
                                    data-version="{{ $core->version }}"
                                    data-size="{{ $core->file_size_formatted }}">
                                {{ ucfirst($core->name) }} {{ $core->version }} 
                                @if($core->is_stable) (Стабильная) @endif
                                @if($core->is_default) (По умолчанию) @endif
                                - {{ $core->file_size_formatted }}
                            </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-sm text-gray-500">
                            Изменение ядра переустановит сервер. Убедитесь, что сделали бэкап!
                        </p>
                    </div>
                </div>
                
                <!-- Автоматизация -->
                <div class="mb-8 pt-8 border-t border-gray-200">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Автоматизация</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="auto_start" id="auto_start" value="1"
                                   {{ old('auto_start', $server->settings['auto_start'] ?? false) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="auto_start" class="ml-3 text-sm text-gray-700">
                                Автозапуск после перезагрузки сервера
                            </label>
                        </div>
                        
                        <div class="flex items-center">
                            <input type="checkbox" name="auto_backup" id="auto_backup" value="1"
                                   {{ old('auto_backup', $server->settings['auto_backup'] ?? false) ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="auto_backup" class="ml-3 text-sm text-gray-700">
                                Автоматическое создание бэкапов
                            </label>
                        </div>
                        
                        <div class="ml-7">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Интервал бэкапов (часы)
                            </label>
                            <input type="number" name="backup_interval" 
                                   value="{{ old('backup_interval', $server->settings['backup_interval'] ?? 24) }}"
                                   min="1" max="168"
                                   class="w-32 px-3 py-1 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>
                
                <!-- Кнопки -->
                <div class="pt-8 border-t border-gray-200">
                    <div class="flex justify-between">
                        <a href="{{ route('servers.core.select', $server) }}" 
                           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            <i class="fas fa-cogs mr-2"></i>Выбор ядра
                        </a>
                        
                        <div class="space-x-4">
                            <button type="reset" 
                                    class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                Сбросить
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                                <i class="fas fa-save mr-2"></i>Сохранить настройки
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Опасная зона -->
        @if(auth()->user()->is_admin)
        <div class="mt-8 bg-red-50 border border-red-200 rounded-xl p-6">
            <h2 class="text-xl font-bold text-red-800 mb-4">
                <i class="fas fa-exclamation-triangle mr-2"></i>Опасная зона
            </h2>
            
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-red-700 mb-2">Переустановка сервера (удалит все данные!)</p>
                    <form action="{{ route('admin.servers.manage', $server) }}" method="POST" 
                          onsubmit="return confirm('ВНИМАНИЕ: Все данные будут удалены!')">
                        @csrf
                        <input type="hidden" name="action" value="reinstall">
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm">
                            <i class="fas fa-bomb mr-2"></i>Переустановить сервер
                        </button>
                    </form>
                </div>
                
                <div>
                    <p class="text-sm text-red-700 mb-2">Удаление сервера (необратимо!)</p>
                    <form action="{{ route('admin.servers.manage', $server) }}" method="POST" 
                          onsubmit="return confirm('ВНИМАНИЕ: Сервер будет удален без возможности восстановления!')">
                        @csrf
                        <input type="hidden" name="action" value="delete">
                        <button type="submit" class="px-4 py-2 bg-red-800 text-white rounded-lg hover:bg-red-900 text-sm">
                            <i class="fas fa-trash mr-2"></i>Удалить сервер
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Предупреждение при выборе нового ядра
    const coreSelect = document.querySelector('select[name="core_id"]');
    if (coreSelect) {
        coreSelect.addEventListener('change', function() {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const version = selectedOption.dataset.version;
                const size = selectedOption.dataset.size;
                
                if (confirm(`Вы собираетесь установить новое ядро.\n\nВерсия: ${version}\nРазмер: ${size}\n\nУбедитесь, что сделали бэкап! Продолжить?`)) {
                    return true;
                } else {
                    this.value = '';
                    return false;
                }
            }
        });
    }
});
</script>
@endsection

@extends('layouts.app')

@section('title', $title ?? $server->name)

@section('content')
<div class="py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Уведомления -->
        @include('partials.alerts')
        
        <!-- Заголовок и основная информация -->
        <div class="mb-8">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $server->name }}</h1>
                    <div class="flex items-center gap-3 mt-2">
                        <div class="flex items-center">
                            <div class="relative">
                                <div class="w-3 h-3 rounded-full bg-{{ $server->getStatusColor() }}-500 animate-pulse"></div>
                                <div class="w-3 h-3 rounded-full bg-{{ $server->getStatusColor() }}-500 absolute top-0 opacity-75"></div>
                            </div>
                            <span class="ml-2 px-3 py-1 rounded-full text-sm font-semibold
                                bg-{{ $server->getStatusColor() }}-100 text-{{ $server->getStatusColor() }}-800">
                                <i class="fas {{ $server->getStatusIcon() }} mr-1"></i>
                                {{ $server->getStatusText() }}
                            </span>
                        </div>
                        
                        @if($server->is_running)
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                            <i class="fas fa-clock mr-1"></i> {{ $server->uptime_formatted }}
                        </span>
                        @endif
                        
                        @if($server->is_managed_by_daemon)
                        <span class="px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                            <i class="fas fa-server mr-1"></i> Демон
                        </span>
                        @endif
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('dashboard') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Назад
                    </a>
                    <button onclick="checkServerStatus(true)" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center gap-2">
                        <i class="fas fa-sync-alt"></i> Обновить статус
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Индикатор загрузки -->
        <div id="status-loading" class="hidden mb-6">
            <div class="flex items-center justify-center p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <i class="fas fa-spinner fa-spin text-blue-600 mr-3 text-lg"></i>
                <span class="text-blue-800 font-medium">Проверяем статус сервера...</span>
            </div>
        </div>
        
        <!-- Основной контент -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Левая колонка: Информация и управление -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Карточка подключения -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">Подключение к серверу</h2>
                        <div class="flex gap-2">
                            <button onclick="copyToClipboard('{{ $server->ip_address }}:{{ $server->port }}')" 
                                    class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium flex items-center gap-1">
                                <i class="fas fa-copy"></i> Адрес
                            </button>
                            <button onclick="copyToClipboard('{{ $server->ip_address }}')" 
                                    class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm font-medium flex items-center gap-1">
                                <i class="fas fa-copy"></i> IP
                            </button>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Основной адрес -->
                        <div class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-blue-700">Основной адрес</span>
                                <i class="fas fa-server text-blue-400"></i>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold font-mono text-gray-900 mb-1">
                                    {{ $server->ip_address }}:{{ $server->port }}
                                </p>
                                <p class="text-sm text-blue-600">Minecraft {{ $server->game_type == 'java' ? 'Java' : 'Bedrock' }} Edition</p>
                            </div>
                        </div>
                        
                        <!-- RCON информация -->
                        <div class="p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl border border-emerald-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-emerald-700">RCON доступ</span>
                                <i class="fas fa-terminal text-emerald-400"></i>
                            </div>
                            <div class="text-center">
                                @php
                                    $rconPort = $server->settings['rcon_port'] ?? ($server->port + 1000);
                                    $rconEnabled = $server->settings['rcon_enabled'] ?? true;
                                @endphp
                                <div class="flex items-center justify-center gap-2 mb-1">
                                    <span class="text-lg font-bold font-mono text-gray-900">{{ $server->ip_address }}:{{ $rconPort }}</span>
                                    @if($rconEnabled)
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-xs font-semibold rounded-full">
                                        Активен
                                    </span>
                                    @else
                                    <span class="px-2 py-0.5 bg-red-100 text-red-800 text-xs font-semibold rounded-full">
                                        Выключен
                                    </span>
                                    @endif
                                </div>
                                @if($rconEnabled && isset($server->settings['rcon_password']))
                                <button onclick="showRconInfo()" 
                                        class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">
                                    <i class="fas fa-key mr-1"></i> Показать RCON пароль
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                    
                    <!-- Кнопка быстрого подключения -->
                    <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900">Быстрое подключение</h3>
                                <p class="text-sm text-gray-600">Скопируйте для игры</p>
                            </div>
                            <button onclick="copyMinecraftCommand()" 
                                    class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition font-medium flex items-center gap-2">
                                <i class="fas fa-gamepad"></i>
                                Копировать для игры
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Панель управления сервером -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Управление сервером</h2>
                    
                    <!-- Кнопки управления -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <!-- Старт -->
                        @if($server->canBeStarted())
                        <form action="{{ route('servers.start', $server) }}" method="POST" class="h-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full h-full p-5 bg-gradient-to-r from-emerald-500 to-green-600 text-white rounded-xl hover:opacity-90 transition-all duration-200 font-bold text-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-play text-2xl mb-2"></i>
                                    <span>Запустить</span>
                                </div>
                            </button>
                        </form>
                        @else
                        <button class="w-full p-5 bg-gradient-to-r from-gray-300 to-gray-400 text-gray-600 rounded-xl cursor-not-allowed opacity-70">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-play text-2xl mb-2"></i>
                                <span>Запустить</span>
                            </div>
                        </button>
                        @endif
                        
                        <!-- Стоп -->
                        @if($server->canBeStopped())
                        <form action="{{ route('servers.stop', $server) }}" method="POST" class="h-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full h-full p-5 bg-gradient-to-r from-red-500 to-pink-600 text-white rounded-xl hover:opacity-90 transition-all duration-200 font-bold text-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-stop text-2xl mb-2"></i>
                                    <span>Остановить</span>
                                </div>
                            </button>
                        </form>
                        @else
                        <button class="w-full p-5 bg-gradient-to-r from-gray-300 to-gray-400 text-gray-600 rounded-xl cursor-not-allowed opacity-70">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-stop text-2xl mb-2"></i>
                                <span>Остановить</span>
                            </div>
                        </button>
                        @endif
                        
                        <!-- Рестарт -->
                        @if($server->canBeRestarted())
                        <form action="{{ route('servers.restart', $server) }}" method="POST" class="h-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full h-full p-5 bg-gradient-to-r from-amber-500 to-orange-600 text-white rounded-xl hover:opacity-90 transition-all duration-200 font-bold text-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-redo text-2xl mb-2"></i>
                                    <span>Перезагрузить</span>
                                </div>
                            </button>
                        </form>
                        @else
                        <button class="w-full p-5 bg-gradient-to-r from-gray-300 to-gray-400 text-gray-600 rounded-xl cursor-not-allowed opacity-70">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-redo text-2xl mb-2"></i>
                                <span>Перезагрузить</span>
                            </div>
                        </button>
                        @endif
                        
                        <!-- Форсированная остановка -->
                        @if($server->is_running)
                        <form action="{{ route('servers.kill', $server) }}" method="POST" class="h-full">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('Вы уверены? Это может повредить файлы сервера!')"
                                    class="w-full h-full p-5 bg-gradient-to-r from-rose-600 to-pink-700 text-white rounded-xl hover:opacity-90 transition-all duration-200 font-bold text-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-skull-crossbones text-2xl mb-2"></i>
                                    <span class="text-sm">Форсировано</span>
                                </div>
                            </button>
                        </form>
                        @else
                        <button class="w-full p-5 bg-gradient-to-r from-gray-300 to-gray-400 text-gray-600 rounded-xl cursor-not-allowed opacity-70">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-skull-crossbones text-2xl mb-2"></i>
                                <span class="text-sm">Форсировано</span>
                            </div>
                        </button>
                        @endif
                    </div>
                    
                    <!-- Индикатор состояния -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Состояние сервера</h3>
                        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <div class="flex flex-wrap gap-4">
                                <div class="flex-1 min-w-[200px]">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm text-gray-600">Процесс</span>
                                        <span class="text-sm font-bold text-gray-900" id="process-info">
                                            {{ $server->process_pid ? 'PID: ' . $server->process_pid : 'Не запущен' }}
                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-{{ $server->is_running ? 'emerald' : 'gray' }}-500 h-2 rounded-full transition-all duration-500"
                                             style="width: {{ $server->is_running ? '100%' : '0%' }}"></div>
                                    </div>
                                </div>
                                
                                <div class="flex-1 min-w-[200px]">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm text-gray-600">Порт</span>
                                        <span class="text-sm font-bold text-gray-900">{{ $server->port }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-{{ $server->is_running ? 'blue' : 'gray' }}-500 h-2 rounded-full transition-all duration-500"
                                             style="width: {{ $server->is_running ? '100%' : '0%' }}"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Быстрые действия -->
                    <h3 class="text-lg font-semibold text-gray-800 mb-3">Быстрые действия</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <a href="{{ route('servers.console', $server) }}" 
                           class="p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl hover:border-blue-300 transition-all duration-200 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition">
                                    <i class="fas fa-terminal text-blue-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Консоль</h4>
                                    <p class="text-sm text-gray-600">Управление командой</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="{{ route('servers.files', $server) }}" 
                           class="p-4 bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-100 rounded-xl hover:border-emerald-300 transition-all duration-200 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center group-hover:bg-emerald-200 transition">
                                    <i class="fas fa-folder text-emerald-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Файлы</h4>
                                    <p class="text-sm text-gray-600">Управление файлами</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="{{ route('servers.settings', $server) }}" 
                           class="p-4 bg-gradient-to-r from-purple-50 to-violet-50 border border-purple-100 rounded-xl hover:border-purple-300 transition-all duration-200 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition">
                                    <i class="fas fa-cog text-purple-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Настройки</h4>
                                    <p class="text-sm text-gray-600">Конфигурация</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="{{ route('servers.backup', $server) }}" 
                           class="p-4 bg-gradient-to-r from-amber-50 to-yellow-50 border border-amber-100 rounded-xl hover:border-amber-300 transition-all duration-200 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition">
                                    <i class="fas fa-save text-amber-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Бэкап</h4>
                                    <p class="text-sm text-gray-600">Создать резервную копию</p>
                                </div>
                            </div>
                        </a>
                        
                        <a href="#" onclick="showServerCommands()" 
                           class="p-4 bg-gradient-to-r from-cyan-50 to-blue-50 border border-cyan-100 rounded-xl hover:border-cyan-300 transition-all duration-200 group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center group-hover:bg-cyan-200 transition">
                                    <i class="fas fa-code text-cyan-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Команды</h4>
                                    <p class="text-sm text-gray-600">Полезные команды</p>
                                </div>
                            </div>
                        </a>
                        
                        <button onclick="checkServerStatus(true)" 
                                class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 rounded-xl hover:border-gray-300 transition-all duration-200 group text-left">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-gray-200 transition">
                                    <i class="fas fa-search text-gray-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-gray-900">Диагностика</h4>
                                    <p class="text-sm text-gray-600">Проверить состояние</p>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
                
                <!-- Мониторинг ресурсов -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Мониторинг ресурсов</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Память -->
                        <div class="p-5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Оперативная память</h3>
                                    <p class="text-sm text-gray-600">Использование RAM</p>
                                </div>
                                <i class="fas fa-memory text-2xl text-blue-400"></i>
                            </div>
                            <div class="mb-3">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700">Использовано</span>
                                    <span class="font-bold text-gray-900">
                                        @if($server->memory_usage)
                                            {{ round($server->memory_usage['used'], 1) }} MB
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="w-full bg-blue-100 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-3 rounded-full transition-all duration-1000 ease-out"
                                         id="memory-bar"
                                         style="width: {{ $server->memory_usage ? min($server->memory_usage['percentage'], 100) : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>0 MB</span>
                                <span class="font-semibold">{{ $server->memory }} MB</span>
                            </div>
                        </div>
                        
                        <!-- Диск -->
                        <div class="p-5 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl border border-emerald-100">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Дисковое пространство</h3>
                                    <p class="text-sm text-gray-600">Использование диска</p>
                                </div>
                                <i class="fas fa-hdd text-2xl text-emerald-400"></i>
                            </div>
                            <div class="mb-3">
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="text-gray-700">Использовано</span>
                                    <span class="font-bold text-gray-900">
                                        @if($server->disk_usage)
                                            {{ round($server->disk_usage['used'], 1) }} MB
                                        @else
                                            —
                                        @endif
                                    </span>
                                </div>
                                <div class="w-full bg-emerald-100 rounded-full h-3">
                                    <div class="bg-gradient-to-r from-emerald-500 to-green-600 h-3 rounded-full transition-all duration-1000 ease-out"
                                         id="disk-bar"
                                         style="width: {{ $server->disk_usage ? min($server->disk_usage['percentage'], 100) : 0 }}%"></div>
                                </div>
                            </div>
                            <div class="flex justify-between text-sm text-gray-600">
                                <span>0 MB</span>
                                <span class="font-semibold">{{ $server->disk_space }} MB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Правая колонка: Дополнительная информация -->
            <div class="space-y-8">
                <!-- Информация о версии -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Версия сервера</h2>
                    
                    <div class="space-y-4">
                        <div class="p-4 bg-gradient-to-r from-violet-50 to-purple-50 rounded-xl border border-violet-100">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-violet-700">Ядро</span>
                                <i class="fas fa-microchip text-violet-400"></i>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900 mb-1">{{ $server->core_type }}</p>
                                <p class="text-sm text-violet-600">Тип ядра</p>
                            </div>
                        </div>
                        
                        <div class="p-4 bg-gradient-to-r from-amber-50 to-yellow-50 rounded-xl border border-amber-100">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-medium text-amber-700">Версия</span>
                                <i class="fas fa-code-branch text-amber-400"></i>
                            </div>
                            <div class="text-center">
                                <p class="text-3xl font-bold text-gray-900 mb-1">{{ $server->core_version ?? '1.20.4' }}</p>
                                <p class="text-sm text-amber-600">Minecraft {{ $server->game_type == 'java' ? 'Java' : 'Bedrock' }}</p>
                            </div>
                        </div>
                        
                        <div class="p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Игроки онлайн</span>
                                <i class="fas fa-users text-gray-400"></i>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-gray-900">0</p>
                                    <p class="text-sm text-gray-600">из {{ $server->player_slots }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                        <div class="bg-gray-400 h-2 rounded-full" style="width: 0%"></div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Нет данных</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Техническая информация -->
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Техническая информация</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Нода:</span>
                            <span class="font-semibold text-gray-900">{{ $server->node->name ?? 'Неизвестно' }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Расположение:</span>
                            <span class="font-semibold text-gray-900">{{ $server->location }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Создан:</span>
                            <span class="font-semibold text-gray-900">{{ $server->created_at->format('d.m.Y H:i') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-600">Обновлен:</span>
                            <span class="font-semibold text-gray-900">{{ $server->updated_at->format('d.m.Y H:i') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">UUID:</span>
                            <button onclick="copyToClipboard('{{ $server->uuid }}')" 
                                    class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Тарифный план -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-white mb-6">Тарифный план</h2>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-blue-200">Тариф:</span>
                            <span class="text-lg font-bold text-white">{{ $server->plan->name ?? 'Базовый' }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-blue-200">Стоимость:</span>
                            <span class="text-2xl font-bold text-white">${{ $server->plan->price_monthly ?? '0.00' }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-blue-200">Следующее списание:</span>
                            <span class="font-semibold text-white">
                                @if($server->expires_at)
                                    {{ $server->expires_at->format('d.m.Y') }}
                                @else
                                    Бессрочно
                                @endif
                            </span>
                        </div>
                        
                        <div class="mt-6">
                            <a href="#" 
   class="block w-full py-3 bg-white text-blue-600 rounded-lg font-bold hover:bg-gray-100 transition text-center"
   onclick="alert('Функция смены тарифа скоро будет доступна')">
    <i class="fas fa-sync-alt mr-2"></i>Изменить тариф
</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Модальное окно RCON информации -->
<div id="rcon-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-xl bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold text-gray-900">RCON доступ</h3>
            <button onclick="hideRconInfo()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">IP адрес:</label>
                <div class="flex">
                    <input type="text" readonly 
                           value="{{ $server->ip_address }}" 
                           class="flex-1 p-2 bg-gray-50 border border-gray-300 rounded-l-lg font-mono">
                    <button onclick="copyToClipboard('{{ $server->ip_address }}')" 
                            class="px-3 bg-gray-200 hover:bg-gray-300 border border-l-0 border-gray-300 rounded-r-lg">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Порт RCON:</label>
                <div class="flex">
                    <input type="text" readonly 
                           value="{{ $server->settings['rcon_port'] ?? ($server->port + 1000) }}" 
                           class="flex-1 p-2 bg-gray-50 border border-gray-300 rounded-l-lg font-mono">
                    <button onclick="copyToClipboard('{{ $server->settings['rcon_port'] ?? ($server->port + 1000) }}')" 
                            class="px-3 bg-gray-200 hover:bg-gray-300 border border-l-0 border-gray-300 rounded-r-lg">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Пароль RCON:</label>
                <div class="flex">
                    <input type="password" readonly 
                           value="{{ $server->settings['rcon_password'] ?? '' }}" 
                           id="rcon-password"
                           class="flex-1 p-2 bg-gray-50 border border-gray-300 rounded-l-lg font-mono">
                    <button onclick="toggleRconPassword()" 
                            class="px-3 bg-gray-200 hover:bg-gray-300 border border-l-0 border-gray-300">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button onclick="copyToClipboard('{{ $server->settings['rcon_password'] ?? '' }}')" 
                            class="px-3 bg-gray-200 hover:bg-gray-300 border border-l-0 border-gray-300 rounded-r-lg">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
            <div class="text-sm text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Используйте для подключения через RCON клиенты (mcrcon и т.д.)
            </div>
        </div>
    </div>
</div>

<script>
// Функция копирования в буфер обмена
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Скопировано: ' + text, 'success');
    }).catch(err => {
        showNotification('Ошибка копирования: ' + err, 'error');
    });
}

// Копирование команды для Minecraft
function copyMinecraftCommand() {
    const command = `{{ $server->ip_address }}:{{ $server->port }}`;
    const message = `Добавьте сервер в Minecraft:\n${command}`;
    
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(command).then(() => {
            showNotification('Адрес сервера скопирован в буфер обмена!', 'success');
        });
    } else {
        // Fallback для старых браузеров
        const textArea = document.createElement('textarea');
        textArea.value = command;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Адрес сервера скопирован в буфер обмена!', 'success');
    }
}

// Показать/скрыть RCON информацию
function showRconInfo() {
    document.getElementById('rcon-modal').classList.remove('hidden');
}

function hideRconInfo() {
    document.getElementById('rcon-modal').classList.add('hidden');
}

function toggleRconPassword() {
    const input = document.getElementById('rcon-password');
    if (input.type === 'password') {
        input.type = 'text';
    } else {
        input.type = 'password';
    }
}

// Проверка статуса сервера
async function checkServerStatus(showDetails = false) {
    const loadingEl = document.getElementById('status-loading');
    const refreshBtn = document.querySelector('button[onclick*="checkServerStatus"]');
    
    if (loadingEl) loadingEl.classList.remove('hidden');
    if (refreshBtn) {
        refreshBtn.disabled = true;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Проверка...';
    }
    
    try {
        const response = await fetch(`/dashboard/servers/{{ $server->id }}/api/status`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            updateServerStatus(data);
            showNotification('Статус сервера обновлен!', 'success');
            
            // Обновляем информацию о процессе
            const processInfo = document.getElementById('process-info');
            if (processInfo && data.pid) {
                processInfo.textContent = 'PID: ' + data.pid;
            }
        } else {
            throw new Error(data.error || 'Unknown error');
        }
    } catch (error) {
        console.error('Error checking server status:', error);
        showNotification('Ошибка проверки статуса: ' + error.message, 'error');
    } finally {
        if (loadingEl) loadingEl.classList.add('hidden');
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Обновить статус';
        }
    }
}

// Обновление статуса на странице
function updateServerStatus(data) {
    console.log('Updating server status:', data);
    
    if (!data.server) {
        console.error('No server data in response:', data);
        return;
    }
    
    // Обновляем статус в заголовке
    const statusElements = document.querySelectorAll('[class*="status-badge"]');
    if (statusElements.length > 0) {
        statusElements.forEach(el => {
            // Сохраняем оригинальные классы кроме цветовых
            const originalClasses = el.className.split(' ').filter(cls => 
                !cls.includes('bg-') && !cls.includes('text-') && !cls.startsWith('bg-')
            );
            
            // Добавляем новые цветовые классы
            const newClasses = [
                ...originalClasses,
                `bg-${data.server.status_color}-100`,
                `text-${data.server.status_color}-800`
            ];
            
            el.className = newClasses.join(' ');
            
            // Обновляем иконку и текст
            const icon = el.querySelector('i');
            if (icon) {
                icon.className = `fas ${data.server.status_icon} mr-1`;
            }
            
            const textSpan = Array.from(el.childNodes).find(node => 
                node.nodeType === 3 && node.textContent.trim()
            );
            if (textSpan) {
                textSpan.textContent = ` ${data.server.status_text}`;
            }
        });
    }
    
    // Обновляем индикатор процесса
    const processBars = document.querySelectorAll('.bg-gray-200 .bg-\\[.*\\]-500, .bg-gray-200 [class*="bg-"]');
    if (processBars.length > 0 && data.server) {
        processBars.forEach(bar => {
            const isRunning = data.server.status === 'running';
            bar.className = `h-2 rounded-full transition-all duration-500 ${
                isRunning ? 'bg-emerald-500' : 'bg-gray-500'
            }`;
            bar.style.width = isRunning ? '100%' : '0%';
        });
    }
    
    // Обновляем анимацию точки статуса
    const statusDots = document.querySelectorAll('.animate-pulse');
    if (statusDots.length > 0 && data.server) {
        statusDots.forEach(dot => {
            dot.className = `w-3 h-3 rounded-full bg-${data.server.status_color}-500 animate-pulse`;
        });
    }
    
    // Обновляем информацию о процессе
    const processInfo = document.getElementById('process-info');
    if (processInfo && data.pid) {
        processInfo.textContent = 'PID: ' + data.pid;
    }
    
    // Обновляем кнопки управления на основе новых статусов
    updateActionButtons(data.server);
}

// Обновление кнопок действий
function updateActionButtons(serverData) {
    console.log('Updating action buttons:', serverData);
    
    // Находим формы с действиями
    const startForms = document.querySelectorAll('form[action*="/start"]');
    const stopForms = document.querySelectorAll('form[action*="/stop"]');
    const restartForms = document.querySelectorAll('form[action*="/restart"]');
    
    // Логика для кнопки Start
    if (startForms.length > 0) {
        const canStart = serverData.can_start !== undefined ? serverData.can_start : 
                        (serverData.status === 'stopped' || serverData.status === 'offline');
        
        startForms.forEach(form => {
            const button = form.querySelector('button');
            if (button) {
                button.disabled = !canStart;
                button.classList.toggle('opacity-50', !canStart);
                button.classList.toggle('cursor-not-allowed', !canStart);
            }
        });
    }
    
    // Логика для кнопки Stop
    if (stopForms.length > 0) {
        const canStop = serverData.can_stop !== undefined ? serverData.can_stop : 
                       (serverData.status === 'running' || serverData.status === 'starting');
        
        stopForms.forEach(form => {
            const button = form.querySelector('button');
            if (button) {
                button.disabled = !canStop;
                button.classList.toggle('opacity-50', !canStop);
                button.classList.toggle('cursor-not-allowed', !canStop);
            }
        });
    }
    
    // Логика для кнопки Restart
    if (restartForms.length > 0) {
        const canRestart = serverData.can_restart !== undefined ? serverData.can_restart : 
                          (serverData.status === 'running');
        
        restartForms.forEach(form => {
            const button = form.querySelector('button');
            if (button) {
                button.disabled = !canRestart;
                button.classList.toggle('opacity-50', !canRestart);
                button.classList.toggle('cursor-not-allowed', !canRestart);
            }
        });
    }
}

// Показать полезные команды
function showServerCommands() {
    const commands = [
        { name: 'Перезагрузка мира', command: 'reload' },
        { name: 'Сохранить мир', command: 'save-all' },
        { name: 'Список игроков', command: 'list' },
        { name: 'Остановить с сообщением', command: 'stop Сервер выключается...' },
        { name: 'Чистка предметов', command: 'kill @e[type=item]' }
    ];
    
    let html = '<div class="space-y-3">';
    commands.forEach(cmd => {
        html += `
            <div class="p-3 bg-gray-50 rounded-lg">
                <div class="text-sm text-gray-600 mb-1">${cmd.name}</div>
                <div class="flex gap-2">
                    <input type="text" readonly value="${cmd.command}" 
                           class="flex-1 p-2 bg-white border border-gray-300 rounded font-mono text-sm">
                    <button onclick="copyToClipboard('${cmd.command}')" 
                            class="px-3 bg-gray-200 hover:bg-gray-300 rounded">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        `;
    });
    html += '</div>';
    
    Swal.fire({
        title: 'Полезные команды',
        html: html,
        icon: 'info',
        confirmButtonText: 'Закрыть',
        confirmButtonColor: '#3b82f6'
    });
}

// Показать уведомление
function showNotification(message, type = 'info') {
    const icon = {
        success: 'check-circle',
        error: 'exclamation-circle',
        info: 'info-circle',
        warning: 'exclamation-triangle'
    };
    
    const color = {
        success: 'emerald',
        error: 'red',
        info: 'blue',
        warning: 'amber'
    };
    
    // Создаем уведомление
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 bg-${color[type]}-50 border border-${color[type]}-200 rounded-xl shadow-lg z-50 max-w-md animate-slide-in`;
    notification.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-${icon[type]} text-${color[type]}-500 text-lg"></i>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm text-${color[type]}-800 font-medium">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" 
                    class="ml-4 text-${color[type]}-400 hover:text-${color[type]}-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    // Добавляем стиль анимации
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slide-in {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        .animate-slide-in { animation: slide-in 0.3s ease-out; }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(notification);
    
    // Автоматическое скрытие через 5 секунд
    setTimeout(() => {
        if (notification.parentElement) {
            notification.style.animation = 'slide-in 0.3s ease-out reverse';
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Инициализация
document.addEventListener('DOMContentLoaded', function() {
    // Добавляем обработчики подтверждения для кнопок
    document.querySelectorAll('form[action*="/start"], form[action*="/stop"], form[action*="/restart"]').forEach(form => {
        form.addEventListener('submit', function(e) {
            const action = this.action.includes('start') ? 'запустить' :
                          this.action.includes('stop') ? 'остановить' :
                          'перезагрузить';
            
            if (!confirm(`Вы уверены, что хотите ${action} сервер?`)) {
                e.preventDefault();
            } else {
                showNotification(`Команда "${action}" отправлена серверу`, 'info');
            }
        });
    });
    
    // Автоматическая проверка статуса при загрузке
    setTimeout(() => checkServerStatus(), 1500);
    
    // Периодическая проверка статуса
    setInterval(() => {
        if (!document.hidden) {
            checkServerStatus();
        }
    }, 60000);
    
    // Анимация индикаторов использования
    setTimeout(() => {
        const memoryBar = document.getElementById('memory-bar');
        const diskBar = document.getElementById('disk-bar');
        
        if (memoryBar) {
            const width = parseFloat(memoryBar.style.width) || 0;
            memoryBar.style.width = width + '%';
        }
        
        if (diskBar) {
            const width = parseFloat(diskBar.style.width) || 0;
            diskBar.style.width = width + '%';
        }
    }, 100);
});

// Обработка Esc для модальных окон
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideRconInfo();
    }
});
</script>

<style>
/* Дополнительные стили */
.bg-gradient-to-r {
    background-size: 200% 200%;
    animation: gradient 3s ease infinite;
}

@keyframes gradient {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

.hover\:shadow-lg {
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.hover\:-translate-y-0\.5 {
    transition: transform 0.3s ease;
}

/* Анимация для индикатора статуса */
@keyframes ping {
    75%, 100% {
        transform: scale(2);
        opacity: 0;
    }
}

.animate-ping {
    animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>
@endsection
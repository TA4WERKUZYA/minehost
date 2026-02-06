@if (session('success'))
<div class="mb-8 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-check-circle text-emerald-400 text-lg"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" 
                class="ml-auto text-emerald-400 hover:text-emerald-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if (session('error'))
<div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-xl">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400 text-lg"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" 
                class="ml-auto text-red-400 hover:text-red-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if (session('info'))
<div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-xl">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-blue-400 text-lg"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-blue-800">{{ session('info') }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" 
                class="ml-auto text-blue-400 hover:text-blue-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if (session('warning'))
<div class="mb-8 p-4 bg-amber-50 border border-amber-200 rounded-xl">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-amber-400 text-lg"></i>
        </div>
        <div class="ml-3">
            <p class="text-sm font-medium text-amber-800">{{ session('warning') }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" 
                class="ml-auto text-amber-400 hover:text-amber-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@if ($errors->any())
<div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-xl">
    <div class="flex">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-circle text-red-400 mt-0.5"></i>
        </div>
        <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800 mb-2">
                @if ($errors->count() > 1)
                    Найдено {{ $errors->count() }} ошибки
                @else
                    Найдена {{ $errors->count() }} ошибка
                @endif
            </h3>
            <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button onclick="this.parentElement.parentElement.remove()" 
                class="ml-auto text-red-400 hover:text-red-600 self-start">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

@php
    // Проверка статуса демона
    $server = $server ?? null;
@endphp

@if($server && (!$server->is_managed_by_daemon || $server->status === 'daemon_offline'))
<div class="mb-8 p-4 bg-amber-50 border border-amber-200 rounded-xl">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-amber-400 text-lg"></i>
        </div>
        <div class="ml-3 flex-1">
            <h4 class="text-sm font-medium text-amber-800">Проблема с демоном</h4>
            <p class="text-sm text-amber-700 mt-1">
                Сервер не управляется демоном. Некоторые функции могут быть недоступны.
            </p>
            @if($server->status === 'daemon_offline')
            <p class="text-xs text-amber-600 mt-2">
                <i class="fas fa-plug mr-1"></i> Демон недоступен по адресу: {{ $server->node->ip_address ?? '127.0.0.1' }}:{{ $server->node->daemon_port ?? 8080 }}
            </p>
            @endif
        </div>
        <button onclick="this.parentElement.parentElement.remove()" 
                class="ml-auto text-amber-400 hover:text-amber-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

<!-- Уведомление о необходимости обновления ядра -->
@if($server && $server->needsCoreUpdate())
<div class="mb-8 p-4 bg-indigo-50 border border-indigo-200 rounded-xl">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <i class="fas fa-arrow-alt-circle-up text-indigo-400 text-lg"></i>
        </div>
        <div class="ml-3 flex-1">
            <h4 class="text-sm font-medium text-indigo-800">Доступно обновление</h4>
            <p class="text-sm text-indigo-700 mt-1">
                Доступна новая версия ядра для вашего сервера.
            </p>
        </div>
        <a href="{{ route('servers.core.select', $server) }}" 
           class="ml-4 px-3 py-1 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            Обновить
        </a>
        <button onclick="this.parentElement.parentElement.remove()" 
                class="ml-2 text-indigo-400 hover:text-indigo-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
@endif

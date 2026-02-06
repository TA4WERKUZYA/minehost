@extends('layouts.app')

@section('title', 'Мои сервера')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Мои сервера</h1>
    <p class="text-gray-600">Управляйте вашими Minecraft серверами</p>
</div>

@if($servers->isEmpty())
<div class="bg-white rounded-2xl shadow-xl p-8 text-center">
    <div class="mb-6">
        <div class="w-24 h-24 bg-blue-100 rounded-full flex items-center justify-center mx-auto">
            <i class="fas fa-server text-blue-600 text-4xl"></i>
        </div>
    </div>
    <h2 class="text-2xl font-bold text-gray-800 mb-4">У вас нет серверов</h2>
    <p class="text-gray-600 mb-6 max-w-md mx-auto">
        Создайте ваш первый Minecraft сервер и начните играть с друзьями
    </p>
    <a href="{{ route('dashboard.create') }}" 
       class="btn-primary inline-flex">
        <i class="fas fa-plus mr-2"></i> Заказать сервер
    </a>
</div>
@else
<!-- Список серверов -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($servers as $server)
    <div class="server-card bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
        <!-- Заголовок сервера -->
        <div class="p-6">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">{{ $server->name }}</h3>
                    <p class="text-sm text-gray-500">ID: #{{ $server->id }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold 
                    {{ $server->status === 'running' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $server->status === 'running' ? '🟢 Запущен' : '🔴 Остановлен' }}
                </span>
            </div>
            
            <!-- Изображение сервера -->
            <div class="mb-6 relative rounded-lg overflow-hidden">
                <div class="w-full h-48 bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center">
                    <i class="fas fa-server text-white text-6xl opacity-50"></i>
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>
                <div class="absolute bottom-4 left-4 text-white">
                    <p class="font-bold text-lg">{{ $server->ip_address }}:{{ $server->port }}</p>
                    <p class="text-sm opacity-90">📍 {{ $server->location }}</p>
                </div>
            </div>
            
            <!-- Информация о сервере -->
            <div class="space-y-3 mb-6">
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">⏳ Осталось дней:</span>
                    <span class="font-bold text-lg {{ $server->days_left < 7 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $server->days_left }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">📦 Тариф:</span>
                    <span class="font-bold">{{ $server->plan->name }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">💾 Память:</span>
                    <span class="font-bold">{{ $server->memory }} MB</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-600">👥 Игроков:</span>
                    <span class="font-bold">{{ $server->plan->player_slots }}</span>
                </div>
            </div>
            
            <!-- Кнопки управления -->
            <div class="flex space-x-3">
                <a href="{{ route('servers.show', $server) }}" 
                   class="flex-1 btn-primary text-center">
                    <i class="fas fa-cog mr-2"></i> Управление
                </a>
                <button class="w-12 flex items-center justify-center border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white rounded-lg transition">
                    <i class="fas fa-redo"></i>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Кнопка добавления -->
<div class="mt-8 text-center">
    <a href="{{ route('dashboard.create') }}" class="btn-primary inline-flex">
        <i class="fas fa-plus mr-2"></i> Добавить сервер
    </a>
</div>

<!-- Статистика -->
<div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
                <i class="fas fa-server text-blue-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Всего серверов</p>
                <p class="text-2xl font-bold">{{ $servers->count() }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
                <i class="fas fa-play text-green-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Активных</p>
                <p class="text-2xl font-bold">{{ $servers->where('status', 'running')->count() }}</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-lg p-6">
        <div class="flex items-center">
            <div class="p-3 bg-yellow-100 rounded-lg">
                <i class="fas fa-clock text-yellow-600 text-xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm text-gray-600">Скоро истекает</p>
                <p class="text-2xl font-bold">{{ $servers->where('days_left', '<', 7)->where('days_left', '>', 0)->count() }}</p>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
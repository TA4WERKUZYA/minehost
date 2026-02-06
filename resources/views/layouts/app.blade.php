<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AllyHost')</title>
    
    <!-- Tailwind CSS через CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Базовые стили -->
    <style>
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }
        
        .nav-glass {
            background: rgba(37, 99, 235, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        }
        
        .server-card {
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .server-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            min-width: 200px;
            z-index: 50;
        }
        
        .dropdown-menu.show {
            display: block;
        }
    </style>
    
    @yield('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Навбар -->
    <nav class="nav-glass text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <!-- Лого и навигация -->
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2 text-xl font-bold">
                        <i class="fas fa-server text-blue-300"></i>
                        <span>AllyHost</span>
                    </a>
                    
                    @auth
                    <div class="hidden md:flex ml-8 space-x-2">
                        <a href="{{ route('dashboard') }}" 
                           class="px-4 py-2 rounded-lg hover:bg-blue-700/50 transition {{ request()->is('dashboard') && !request()->is('dashboard/*') ? 'bg-blue-700/50' : '' }}">
                            <i class="fas fa-home mr-2"></i>Главная
                        </a>
                        <a href="{{ route('dashboard.create') }}" 
                           class="px-4 py-2 rounded-lg hover:bg-blue-700/50 transition {{ request()->is('dashboard/create') ? 'bg-blue-700/50' : '' }}">
                            <i class="fas fa-plus mr-2"></i>Заказать сервер
                        </a>
                    </div>
                    @endauth
                </div>
                
                <!-- Правая часть -->
                @auth
                <div class="flex items-center space-x-4">
                    <!-- Баланс -->
                    <div class="hidden md:flex items-center bg-blue-700/50 px-4 py-2 rounded-lg">
                        <i class="fas fa-coins text-yellow-300 mr-2"></i>
                        <span class="font-semibold">{{ Auth::user()->balance }} ₽</span>
                    </div>
                    
                    <!-- Профиль -->
                    <div class="relative">
                        <button id="userMenuButton" class="flex items-center space-x-3 p-2 rounded-lg hover:bg-blue-700/50 transition">
                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="hidden md:block text-left">
                                <div class="font-semibold">{{ Auth::user()->name }}</div>
                                <div class="text-sm text-blue-200">{{ Auth::user()->email }}</div>
                            </div>
                            <i class="fas fa-chevron-down text-sm"></i>
                        </button>
                        
                        <!-- Выпадающее меню -->
                        <div id="userMenu" class="dropdown-menu">
                            <div class="p-4 border-b">
                                <div class="font-semibold text-gray-800">{{ Auth::user()->name }}</div>
                                <div class="text-sm text-gray-600">{{ Auth::user()->email }}</div>
                            </div>
                            <div class="p-2">
                                <a href="{{ route('profile') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                    <i class="fas fa-user-circle mr-3 text-gray-500"></i>
                                    <span>Профиль</span>
                                </a>
                                <a href="{{ route('balance.index') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition">
    <i class="fas fa-wallet mr-3 text-gray-500"></i>
    <span>Пополнить баланс</span>
</a>
                                @if(Auth::user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                    <i class="fas fa-crown mr-3 text-yellow-500"></i>
                                    <span>Админ панель</span>
                                </a>
                                @endif
                            </div>
                            <div class="border-t p-2">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center w-full px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition">
                                        <i class="fas fa-sign-out-alt mr-3"></i>
                                        <span>Выйти</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </nav>
    
    <!-- Основное содержимое -->
    <main class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </main>
    
    <!-- JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Выпадающее меню профиля
        const userMenuButton = document.getElementById('userMenuButton');
        const userMenu = document.getElementById('userMenu');
        
        if (userMenuButton && userMenu) {
            userMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                userMenu.classList.toggle('show');
            });
            
            // Закрытие при клике вне меню
            document.addEventListener('click', function(e) {
                if (!userMenu.contains(e.target) && !userMenuButton.contains(e.target)) {
                    userMenu.classList.remove('show');
                }
            });
        }
        
        // Закрытие меню при нажатии ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && userMenu) {
                userMenu.classList.remove('show');
            }
        });
    });
    </script>
    
    @yield('scripts')
</body>
</html>
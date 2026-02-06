<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Minecraft Hosting</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .shake {
            animation: shake 0.5s ease-in-out;
        }
    </style>
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white/10 backdrop-blur-lg rounded-2xl p-8 shadow-2xl border border-white/20 max-w-md w-full">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-2xl mb-4">
                <i class="fas fa-server text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">Minecraft Hosting</h1>
            <p class="text-white/80 mt-2">Панель управления серверами</p>
        </div>
        
        <h2 class="text-2xl font-bold text-white mb-6 text-center">Вход в систему</h2>
        
        <!-- Сообщение об ошибке блокировки (специальный блок) -->
        @if ($errors->has('email') && str_contains($errors->first('email'), 'заблокирован'))
            <div id="ban-alert" class="mb-6 p-4 bg-red-500/30 border-2 border-red-500 rounded-lg shake">
                <div class="flex items-start">
                    <div class="mr-3">
                        <i class="fas fa-ban text-red-300 text-xl mt-1"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-red-300 mb-1">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Аккаунт заблокирован
                        </h3>
                        <p class="text-red-200 text-sm leading-relaxed">
                            {{ $errors->first('email') }}
                        </p>
                        
                        <!-- Детали блокировки -->
                        @php
                            $message = $errors->first('email');
                            $ban_info = [];
                            
                            if (str_contains($message, 'Причина:')) {
                                $parts = explode('Причина:', $message);
                                if (count($parts) > 1) {
                                    $reason_part = $parts[1];
                                    if (str_contains($reason_part, 'Блокировка до:')) {
                                        $reason_parts = explode('Блокировка до:', $reason_part);
                                        $ban_info['reason'] = trim($reason_parts[0]);
                                        if (count($reason_parts) > 1) {
                                            $ban_info['until'] = trim($reason_parts[1]);
                                        }
                                    } else {
                                        $ban_info['reason'] = trim($reason_part);
                                    }
                                }
                            }
                        @endphp
                        
                        @if (!empty($ban_info))
                        <div class="mt-3 p-3 bg-black/20 rounded-lg">
                            <div class="text-red-200 text-xs">
                                @if (!empty($ban_info['reason']))
                                    <div class="mb-2">
                                        <i class="fas fa-comment-alt mr-2"></i>
                                        <span class="font-medium">Причина:</span> {{ $ban_info['reason'] }}
                                    </div>
                                @endif
                                
                                @if (!empty($ban_info['until']))
                                    <div class="mb-2">
                                        <i class="fas fa-clock mr-2"></i>
                                        <span class="font-medium">Срок блокировки:</span> {{ $ban_info['until'] }}
                                    </div>
                                @endif
                                
                                <div class="pt-2 border-t border-red-500/30">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <span class="font-medium">Что делать?</span>
                                    <ul class="mt-1 ml-4 list-disc list-inside">
                                        <li>Дождитесь окончания срока блокировки</li>
                                        <li>Обратитесь к администратору для разблокировки</li>
                                        <li>Проверьте email на уведомления от администрации</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="mt-4 flex items-center justify-between">
                            <span class="text-red-200 text-xs">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                ID обращения: #BAN-{{ time() }}
                            </span>
                            <button onclick="document.getElementById('ban-alert').remove()" 
                                    class="text-red-300 hover:text-white text-sm">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        
        <!-- Обычные ошибки валидации -->
        @if ($errors->any() && !str_contains($errors->first('email'), 'заблокирован'))
            <div class="mb-6 p-4 bg-red-500/20 border border-red-500 rounded-lg">
                <div class="flex items-center mb-2">
                    <i class="fas fa-exclamation-circle text-red-300 mr-2"></i>
                    <span class="text-red-300 font-medium">Ошибка авторизации</span>
                </div>
                <ul class="text-red-200 text-sm">
                    @foreach ($errors->all() as $error)
                        @if (!str_contains($error, 'заблокирован'))
                            <li class="mb-1"><i class="fas fa-times mr-2"></i>{{ $error }}</li>
                        @endif
                    @endforeach
                </ul>
            </div>
        @endif
        
        <!-- Сообщение об успешном выходе -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-green-500/20 border border-green-500 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-300 mr-2"></i>
                    <span class="text-green-300">{{ session('success') }}</span>
                </div>
            </div>
        @endif
        
        <!-- Сообщение о разлогине из-за блокировки -->
        @if (session('error') && str_contains(session('error'), 'заблокирован'))
            <div class="mb-6 p-4 bg-red-500/30 border-2 border-red-500 rounded-lg">
                <div class="flex items-start">
                    <div class="mr-3">
                        <i class="fas fa-user-slash text-red-300 text-xl mt-1"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-red-300 mb-1">Сессия завершена</h3>
                        <p class="text-red-200 text-sm">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            
            <div class="mb-4">
                <label class="block text-white/80 mb-2">
                    <i class="fas fa-envelope mr-2"></i>Email
                </label>
                <input type="email" name="email" required 
                       class="w-full p-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 transition duration-300"
                       placeholder="Ваш email"
                       value="{{ old('email') }}"
                       id="emailInput">
                <div class="text-white/60 text-xs mt-1">
                    Введите email, указанный при регистрации
                </div>
            </div>
            
            <div class="mb-6">
                <label class="block text-white/80 mb-2">
                    <i class="fas fa-lock mr-2"></i>Пароль
                </label>
                <div class="relative">
                    <input type="password" name="password" required 
                           class="w-full p-3 rounded-lg bg-white/20 border border-white/30 text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-white/50 transition duration-300"
                           placeholder="Ваш пароль"
                           id="passwordInput">
                    <button type="button" onclick="togglePassword()" 
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-white/70 hover:text-white">
                        <i class="fas fa-eye" id="passwordToggle"></i>
                    </button>
                </div>
                <div class="text-white/60 text-xs mt-1">
                    Минимум 8 символов
                </div>
            </div>
            
            <div class="mb-6 flex items-center justify-between">
                <label class="flex items-center text-white/80 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-white/20 border-white/30">
                    <span class="ml-2 text-sm">Запомнить меня</span>
                </label>
                <a href="#" class="text-white/70 hover:text-white text-sm transition duration-300">
                    <i class="fas fa-key mr-1"></i>Забыли пароль?
                </a>
            </div>
            
            <button type="submit" 
                    class="w-full bg-white text-blue-600 font-bold py-3 rounded-lg hover:bg-gray-100 transition duration-300 transform hover:-translate-y-1 shadow-lg hover:shadow-xl active:scale-95"
                    id="submitBtn">
                <i class="fas fa-sign-in-alt mr-2"></i>Войти в систему
            </button>
            
            <!-- Счетчик попыток (если реализовано) -->
            @if(session('login_attempts'))
                <div class="mt-4 p-3 bg-yellow-500/20 border border-yellow-500 rounded-lg">
                    <div class="flex items-center text-yellow-300 text-sm">
                        <i class="fas fa-shield-alt mr-2"></i>
                        <span>Осталось попыток: {{ 5 - session('login_attempts') }} из 5</span>
                    </div>
                </div>
            @endif
        </form>
        
        <div class="mt-6 text-center border-t border-white/10 pt-6">
            <p class="text-white/70 text-sm mb-4">
                Нет аккаунта? Создайте его и начните использовать хостинг!
            </p>
            <a href="{{ route('register') }}" 
               class="inline-block w-full py-3 bg-white/20 text-white rounded-lg hover:bg-white/30 transition duration-300 border border-white/30">
                <i class="fas fa-user-plus mr-2"></i>Зарегистрироваться
            </a>
            
            <div class="mt-4 text-white/50 text-xs">
                <i class="fas fa-shield-alt mr-1"></i>
                Ваши данные защищены с помощью шифрования
            </div>
        </div>
    </div>
    
    <script>
        // Показать/скрыть пароль
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const toggleIcon = document.getElementById('passwordToggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Анимация при ошибке входа
        document.getElementById('loginForm')?.addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Проверка...';
            submitBtn.disabled = true;
        });
        
        
        // Показать/скрыть сообщение об ошибке блокировки
        function toggleBanAlert() {
            const alert = document.getElementById('ban-alert');
            if (alert) {
                alert.style.display = alert.style.display === 'none' ? 'block' : 'none';
            }
        }
        
        // Очистка формы при фокусе
        document.getElementById('emailInput')?.addEventListener('focus', function() {
            if (this.value === 'admin@example.com' || this.value === 'user@example.com') {
                this.value = '';
            }
        });
        
        document.getElementById('passwordInput')?.addEventListener('focus', function() {
            if (this.value === 'admin123' || this.value === 'user123') {
                this.value = '';
            }
        });
    </script>
</body>
</html>
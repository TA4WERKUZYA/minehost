<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Админ панель') - Minecraft Hosting</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #4e73df;
            --primary-dark: #2e59d9;
            --secondary-color: #6f42c1;
            --success-color: #1cc88a;
            --danger-color: #e74a3b;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
            --dark-color: #5a5c69;
            --light-color: #f8f9fc;
            --sidebar-width: 250px;
            --topbar-height: 70px;
            --shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            --transition: all 0.3s ease;
        }
        
        body {
            background: linear-gradient(135deg, #f8f9fc 0%, #e9ecef 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        /* Навбар */
        .navbar-top {
            height: var(--topbar-height);
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
            box-shadow: var(--shadow);
            border-bottom: 1px solid #e3e6f0;
            padding: 0 20px;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 1030;
            transition: var(--transition);
        }
        
        .navbar-brand {
            font-weight: 800;
            color: var(--primary-color);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .navbar-brand i {
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .navbar-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }
        
        .navbar-brand-text .subtitle {
            font-size: 0.75rem;
            font-weight: 400;
            color: var(--dark-color);
            margin-top: 2px;
        }
        
        /* User Dropdown */
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 16px;
            border-radius: 10px;
            background: rgba(78, 115, 223, 0.05);
            border: 1px solid rgba(78, 115, 223, 0.1);
            transition: var(--transition);
            cursor: pointer;
        }
        
        .user-dropdown:hover {
            background: rgba(78, 115, 223, 0.1);
            border-color: rgba(78, 115, 223, 0.2);
            transform: translateY(-1px);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2rem;
            border: 3px solid white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
        }
        
        .user-name {
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.95rem;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: var(--primary-color);
            background: rgba(78, 115, 223, 0.1);
            padding: 2px 8px;
            border-radius: 12px;
            display: inline-block;
            font-weight: 600;
            margin-top: 2px;
        }
        
        .dropdown-toggle::after {
            display: none;
        }
        
        .dropdown-menu {
            border: none;
            box-shadow: var(--shadow);
            border-radius: 10px;
            padding: 8px;
            margin-top: 10px;
            animation: dropdownFade 0.2s ease;
        }
        
        @keyframes dropdownFade {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .dropdown-item {
            padding: 10px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            color: var(--dark-color);
            font-weight: 500;
        }
        
        .dropdown-item i {
            width: 20px;
            color: var(--primary-color);
        }
        
        .dropdown-item:hover {
            background: linear-gradient(135deg, rgba(78, 115, 223, 0.1), rgba(111, 66, 193, 0.1));
            color: var(--primary-dark);
            transform: translateX(5px);
        }
        
        .dropdown-divider {
            margin: 8px 0;
            opacity: 0.2;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: var(--sidebar-width);
            z-index: 1040;
            padding: calc(var(--topbar-height) + 20px) 0 20px;
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
            transition: var(--transition);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-brand-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar-brand-icon i {
            font-size: 2rem;
            color: white;
        }
        
        .sidebar-brand-text {
            text-align: center;
        }
        
        .sidebar-brand-text h4 {
            color: white;
            font-weight: 800;
            margin: 0;
            font-size: 1.5rem;
        }
        
        .sidebar-brand-text small {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 12px 20px;
            border-radius: 0;
            border-left: 4px solid transparent;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: var(--transition);
            font-weight: 500;
        }
        
        .nav-link i {
            width: 20px;
            font-size: 1.1rem;
            text-align: center;
        }
        
        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left-color: rgba(255, 255, 255, 0.5);
            padding-left: 25px;
        }
        
        .nav-link.active {
            color: white;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0.2), transparent);
            border-left-color: white;
            font-weight: 600;
        }
        
        /* Контент */
        .content-wrapper {
            margin-left: var(--sidebar-width);
            margin-top: var(--topbar-height);
            padding: 30px;
            min-height: calc(100vh - var(--topbar-height));
            transition: var(--transition);
        }
        
        /* Алерты */
        .alert {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 15px 20px;
            margin-bottom: 25px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert-success {
            background: linear-gradient(135deg, rgba(28, 200, 138, 0.15), rgba(28, 200, 138, 0.05));
            border-left: 4px solid var(--success-color);
            color: #155724;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, rgba(231, 74, 59, 0.15), rgba(231, 74, 59, 0.05));
            border-left: 4px solid var(--danger-color);
            color: #721c24;
        }
        
        .alert i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .btn-close {
            opacity: 0.7;
            transition: var(--transition);
        }
        
        .btn-close:hover {
            opacity: 1;
            transform: scale(1.1);
        }
        
        /* Карточки */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: var(--transition);
            margin-bottom: 25px;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.35rem 2rem 0 rgba(58, 59, 69, 0.2);
        }
        
        .card-header {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fc 100%);
            border-bottom: 1px solid #e3e6f0;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
            font-weight: 700;
            color: var(--dark-color);
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Кнопки */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            border: none;
            border-radius: 10px;
            padding: 10px 25px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(78, 115, 223, 0.3);
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(78, 115, 223, 0.4);
        }
        
        /* Мобильная адаптивность */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .navbar-top {
                left: 0;
            }
            
            .content-wrapper {
                margin-left: 0;
            }
            
            .mobile-menu-btn {
                display: block !important;
            }
        }
        
        @media (min-width: 993px) {
            .mobile-menu-btn {
                display: none !important;
            }
        }
        
        /* Кнопка мобильного меню */
        .mobile-menu-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 1.5rem;
            padding: 5px;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .mobile-menu-btn:hover {
            background: rgba(78, 115, 223, 0.1);
            transform: rotate(90deg);
        }
        
        /* Фикс для контента */
        html {
            scroll-behavior: smooth;
        }
        
        /* Кастомный скроллбар */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, var(--primary-dark), var(--primary-color));
        }
        
        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Уведомления */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, var(--danger-color), #c0392b);
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            border: 2px solid white;
        }
        
        /* Time display */
        .time-display {
            background: rgba(78, 115, 223, 0.05);
            padding: 6px 15px;
            border-radius: 8px;
            border: 1px solid rgba(78, 115, 223, 0.1);
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.9rem;
        }
        
        .time-display i {
            color: var(--primary-color);
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <!-- Навбар -->
    <nav class="navbar-top">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <!-- Кнопка мобильного меню -->
                <button class="mobile-menu-btn me-3" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Бренд -->
                <a class="navbar-brand" href="/admin">
                    <div class="navbar-brand-text">
                        <span><i class="fas fa-cogs"></i> AllyHost Admin</span>
                        <span class="subtitle">Панель управления</span>
                    </div>
                </a>
                
                <!-- Время и дата -->
                <div class="time-display ms-4 d-none d-md-flex">
                    <i class="fas fa-clock"></i>
                    <span id="current-time">Загрузка...</span>
                </div>
            </div>
            
            <!-- Правая часть -->
            <div class="d-flex align-items-center gap-3">
                <!-- Уведомления -->
                <div class="position-relative">
                    <button class="btn btn-outline-primary btn-sm rounded-circle p-2 position-relative" 
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" style="min-width: 300px;">
                        <h6 class="dropdown-header">Уведомления</h6>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-server text-success"></i>
                            <div>
                                <small class="text-muted">10 мин назад</small>
                                <p class="mb-0">Новый сервер создан</p>
                            </div>
                        </a>
                        <a class="dropdown-item" href="#">
                            <i class="fas fa-exclamation-triangle text-warning"></i>
                            <div>
                                <small class="text-muted">2 часа назад</small>
                                <p class="mb-0">Оплата не прошла</p>
                            </div>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-center" href="#">
                            <i class="fas fa-eye"></i> Показать все
                        </a>
                    </div>
                </div>
                
                <!-- Пользователь -->
                <div class="dropdown">
                    <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ auth()->user()->name }}</span>
                            <span class="user-role">
                                @if(auth()->user()->role == 'admin')
                                    Администратор
                                @elseif(auth()->user()->role == 'moderator')
                                    Модератор
                                @else
                                    Пользователь
                                @endif
                            </span>
                        </div>
                        <i class="fas fa-chevron-down text-muted"></i>
                    </div>
                    
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="/dashboard">
                            <i class="fas fa-tachometer-alt"></i>
                            Панель пользователя
                        </a>
                        <a class="dropdown-item" href="/profile">
                            <i class="fas fa-user-cog"></i>
                            Настройки профиля
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="/logout" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt text-danger"></i>
                            <span class="text-danger">Выйти</span>
                        </a>
                        <form id="logout-form" action="/logout" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-server"></i>
                </div>
                <div class="sidebar-brand-text">
                    <h4>AllyHost</h4>
                    <small>Администрирование</small>
                </div>
            </div>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin') ? 'active' : '' }}" 
                   href="/admin">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Дашборд</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" 
                   href="/admin/users">
                    <i class="fas fa-users"></i>
                    <span>Пользователи</span>
                    <span class="badge bg-danger ms-auto" id="usersBadge">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/servers*') ? 'active' : '' }}" 
                   href="/admin/servers">
                    <i class="fas fa-server"></i>
                    <span>Серверы</span>
                    <span class="badge bg-info ms-auto" id="serversBadge">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/nodes*') ? 'active' : '' }}" 
                   href="/admin/nodes">
                    <i class="fas fa-network-wired"></i>
                    <span>Ноды</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/plans*') ? 'active' : '' }}" 
                   href="/admin/plans">
                    <i class="fas fa-tags"></i>
                    <span>Тарифы</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}" 
                   href="/admin/orders">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Заказы</span>
                    <span class="badge bg-warning ms-auto" id="ordersBadge">0</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/backups*') ? 'active' : '' }}" 
                   href="/admin/backups">
                    <i class="fas fa-hdd"></i>
                    <span>Бэкапы</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" 
                   href="/admin/settings">
                    <i class="fas fa-cog"></i>
                    <span>Настройки</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/cores*') ? 'active' : '' }}" 
                   href="/admin/cores">
                    <i class="fas fa-microchip"></i>
                    <span>Ядра</span>
                </a>
            </li>
            
            <li class="nav-item mt-4">
                <div class="px-3 py-2">
                    <small class="text-white-50">СИСТЕМА</small>
                </div>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="/admin/tasks">
                    <i class="fas fa-tasks"></i>
                    <span>Задачи</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="/admin/logs">
                    <i class="fas fa-file-alt"></i>
                    <span>Логи</span>
                </a>
            </li>
        </ul>
        
        <div class="px-3 py-4 mt-auto">
            <div class="text-center text-white-50">
                <small>© 2024 AllyHost</small><br>
                <small class="text-white-30">v1.0.0</small>
            </div>
        </div>
    </div>
    
    <!-- Контент -->
    <div class="content-wrapper">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                {{ session('warning') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <i class="fas fa-info-circle mr-2"></i>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        
        @yield('content')
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Функция обновления времени
        function updateTime() {
            const now = new Date();
            const timeElement = document.getElementById('current-time');
            
            const options = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            };
            
            timeElement.textContent = now.toLocaleString('ru-RU', options);
        }
        
        // Переключение сайдбара на мобильных устройствах
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('active');
        }
        
        // Закрытие сайдбара при клике вне его на мобильных
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const mobileBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 992 && 
                !sidebar.contains(event.target) && 
                !mobileBtn.contains(event.target) && 
                sidebar.classList.contains('active')) {
                sidebar.classList.remove('active');
            }
        });
        
        // Автоматически скрываем алерты через 5 секунд
        setTimeout(function() {
            document.querySelectorAll('.alert').forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Загрузка при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            // Инициализация времени
            updateTime();
            setInterval(updateTime, 1000);
            
            // Анимация активного пункта меню
            const activeLinks = document.querySelectorAll('.nav-link.active');
            activeLinks.forEach(link => {
                link.style.transform = 'translateX(5px)';
            });
            
            // Добавление анимации для карточек
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                });
            });
        });
        
        // Уведомление о новых событиях
        function showNotification(title, message, type = 'info') {
            const icon = type === 'success' ? 'fa-check-circle' : 
                         type === 'error' ? 'fa-exclamation-circle' : 
                         type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';
            
            const color = type === 'success' ? 'success' : 
                         type === 'error' ? 'danger' : 
                         type === 'warning' ? 'warning' : 'info';
            
            console.log(`Notification: ${title} - ${message}`);
        }
    </script>
    
    @yield('scripts')
</body>
</html>
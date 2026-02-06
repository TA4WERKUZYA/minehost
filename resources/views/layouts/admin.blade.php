<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Админ панель') - AllyHost</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fc;
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding: 48px 0 0;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, .8);
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, .1);
        }
        
        .sidebar .nav-link.active {
            color: #fff;
            background-color: rgba(255, 255, 255, .2);
        }
        
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .navbar {
            background-color: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15);
        }
        
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .content-wrapper {
                margin-left: 0;
            }
        }
        
        .card {
            border: 0;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, .15);
        }
        
        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            font-weight: 700;
        }
        
        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
        }
        
        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2653d4;
        }
        
        .table th {
            border-top: 0;
            font-weight: 700;
            color: #5a5c69;
        }
        
        .alert {
            border-radius: 0.35rem;
            border: 0;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Навбар -->
    <nav class="navbar navbar-expand navbar-light topbar static-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-cogs"></i> Админ панель
            </a>
            
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle fa-fw"></i>
                        <span>{{ auth()->user()->name }}</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt fa-fw"></i> Панель пользователя
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="{{ route('logout') }}" 
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="fas fa-sign-out-alt fa-fw"></i> Выйти
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand d-flex align-items-center justify-content-center py-4">
            <div class="sidebar-brand-text mx-3">
                <h4 class="text-white mb-0"><i class="fas fa-server"></i> Minecraft</h4>
                <small class="text-white-50">Администрирование</small>
            </div>
        </div>
        
        <hr class="sidebar-divider my-0">
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin') ? 'active' : '' }}" 
                   href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Дашборд</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/users*') ? 'active' : '' }}" 
                   href="{{ route('admin.users') }}">
                    <i class="fas fa-users"></i>
                    <span>Пользователи</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/servers*') ? 'active' : '' }}" 
                   href="{{ route('admin.servers') }}">
                    <i class="fas fa-server"></i>
                    <span>Серверы</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/plans*') ? 'active' : '' }}" 
                   href="{{ route('admin.plans') }}">
                    <i class="fas fa-tags"></i>
                    <span>Тарифы</span>
                </a>
            </li>
            
            <!-- НОВЫЙ ПУНКТ: ЯДРА -->
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/cores*') ? 'active' : '' }}" 
                   href="{{ route('admin.cores.index') }}">
                    <i class="fas fa-cube"></i>
                    <span>Ядра</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/nodes*') ? 'active' : '' }}" 
                   href="{{ route('admin.nodes') }}">
                    <i class="fas fa-network-wired"></i>
                    <span>Ноды</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/orders*') ? 'active' : '' }}" 
                   href="{{ route('admin.orders') }}">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Заказы</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/backups*') ? 'active' : '' }}" 
                   href="{{ route('admin.backups') }}">
                    <i class="fas fa-hdd"></i>
                    <span>Бэкапы</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" 
                   href="{{ route('admin.settings') }}">
                    <i class="fas fa-cog"></i>
                    <span>Настройки</span>
                </a>
            </li>
        </ul>
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
        
        @yield('content')
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Автоматически скрываем алерты через 5 секунд
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
        
        // Подсветка активного пункта меню
        document.addEventListener('DOMContentLoaded', function() {
            var currentPath = window.location.pathname;
            var navLinks = document.querySelectorAll('.sidebar .nav-link');
            
            navLinks.forEach(function(link) {
                var href = link.getAttribute('href');
                if (href && currentPath.startsWith(href) && href !== '/admin') {
                    link.classList.add('active');
                }
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>
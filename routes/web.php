<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\CoreController;
use App\Http\Controllers\Admin\CoreAdminController as AdminCoreController;

// Главная страница
Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    return redirect()->route('login');
});

// Аутентификация
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ВЕБХУК для ЮKassa - должен быть ДО middleware и без CSRF!
Route::post('/api/payment/webhook', [BalanceController::class, 'webhook'])
    ->withoutMiddleware(['web', 'csrf'])
    ->name('balance.webhook');
Route::post('/servers/{server}/check-status', [ServerController::class, 'checkBackgroundStatus'])
    ->name('servers.check-status')
    ->middleware(['auth', 'check.banned']);
    
// Защищенные маршруты
Route::middleware(['auth', 'check.banned'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/create', [DashboardController::class, 'create'])->name('dashboard.create');
    Route::post('/dashboard/create', [ServerController::class, 'store'])->name('dashboard.store');
    
    // Профиль
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::post('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    
    // ==================== БАЛАНС И ЮKASSA ====================
    Route::prefix('balance')->name('balance.')->group(function () {
        Route::get('/', [BalanceController::class, 'index'])->name('index');
        Route::post('/create', [BalanceController::class, 'create'])->name('create');
        Route::get('/success', [BalanceController::class, 'success'])->name('success');
        Route::get('/cancel', [BalanceController::class, 'cancel'])->name('cancel');
        Route::get('/history', [BalanceController::class, 'history'])->name('history');
        Route::get('/check/{payment}', [BalanceController::class, 'checkStatus'])->name('check');
    });
    
    // ==================== УПРАВЛЕНИЕ СЕРВЕРАМИ ====================
    Route::prefix('dashboard/servers')->name('servers.')->group(function () {
        // Основные маршруты
        Route::get('/{server}', [ServerController::class, 'show'])->name('show');
        Route::post('/{server}/start', [ServerController::class, 'start'])->name('start');
        Route::post('/{server}/stop', [ServerController::class, 'stop'])->name('stop');
        Route::post('/{server}/restart', [ServerController::class, 'restart'])->name('restart');
        Route::post('/{server}/status', [ServerController::class, 'checkStatus'])->name('check-status');
        Route::post('/{server}/command', [ServerController::class, 'sendCommand'])->name('command');
        Route::post('/servers/{server}/kill', [ServerController::class, 'kill'])->name('servers.kill');
        Route::post('/{server}/kill', [ServerController::class, 'kill'])->name('kill'); // Добавьте эту строку
        
    // API для AJAX запросов
        Route::get('/{server}/api/status', [ServerController::class, 'apiCheckStatus'])
            ->name('api.status');

        // Управление файлами
        Route::get('/{server}/files', [ServerController::class, 'files'])->name('files');
        
        // Консоль сервера
        Route::get('/{server}/console', [ServerController::class, 'console'])->name('console');
        
        // Настройки сервера
        Route::get('/{server}/settings', [ServerController::class, 'settings'])->name('settings');
        Route::post('/{server}/settings', [ServerController::class, 'updateSettings'])->name('update-settings');
        
        // Бэкапы
        Route::post('/{server}/backup', [ServerController::class, 'backup'])->name('backup');
        Route::get('/{server}/backups', [ServerController::class, 'backups'])->name('backups');
        
        // Управление ядром
        Route::get('/{server}/core', [CoreController::class, 'select'])->name('core.select');
        Route::post('/{server}/core/install', [CoreController::class, 'install'])->name('core.install');
        Route::get('/{server}/core/status', [CoreController::class, 'checkInstallationStatus'])->name('core.status');
        Route::get('/{server}/core/updates', [CoreController::class, 'checkUpdates'])->name('core.updates');
        Route::post('/{server}/core/update', [CoreController::class, 'updateCore'])->name('core.update');
        
        // API для AJAX запросов
        Route::prefix('{server}/api')->name('api.')->group(function () {
            Route::get('/status', [ServerController::class, 'apiCheckStatus'])->name('status');
            Route::get('/stats', [ServerController::class, 'apiGetStats'])->name('stats');
            Route::get('/players', [ServerController::class, 'apiGetPlayers'])->name('players');
            Route::get('/logs', [ServerController::class, 'apiGetLogs'])->name('logs');
        });
    });
});

// Админские маршруты
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', 'check.banned'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Управление пользователями
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminController::class, 'users'])->name('users');
        Route::post('/', [AdminController::class, 'storeUser'])->name('users.store');
        Route::get('/export', [AdminController::class, 'exportUsers'])->name('users.export');
        Route::get('/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
        Route::put('/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
        Route::post('/{user}/ban', [AdminController::class, 'banUser'])->name('users.ban');
        Route::post('/{user}/unban', [AdminController::class, 'unbanUser'])->name('users.unban');
        Route::post('/{user}/withdraw', [AdminController::class, 'withdrawBalance'])->name('users.withdraw');
        Route::post('/{user}/add-balance', [AdminController::class, 'addBalance'])->name('users.add-balance');
        Route::post('/{id}/restore', [AdminController::class, 'restoreUser'])->name('users.restore');
        Route::delete('/{id}/force-delete', [AdminController::class, 'forceDeleteUser'])->name('users.force-delete');
        Route::post('/{user}/login-as', [AdminController::class, 'loginAsUser'])->name('users.login-as');
        Route::post('/bulk-actions', [AdminController::class, 'bulkActions'])->name('users.bulk-actions');
    });
    
    // Управление серверами
    Route::prefix('servers')->group(function () {
        Route::get('/', [AdminController::class, 'servers'])->name('servers');
        Route::post('/', [AdminController::class, 'storeServer'])->name('servers.store');
        Route::get('/export', [AdminController::class, 'exportServers'])->name('servers.export');
        Route::get('/{server}', [AdminController::class, 'serverShow'])->name('servers.show');
        Route::post('/{server}/manage', [AdminController::class, 'serverManage'])->name('servers.manage');
        Route::delete('/{server}', [AdminController::class, 'destroyServer'])->name('servers.delete');
        Route::post('/bulk-actions', [AdminController::class, 'bulkServerActions'])->name('servers.bulk-actions');
    });
    
    // Ноды
    Route::prefix('nodes')->group(function () {
        Route::get('/', [AdminController::class, 'nodes'])->name('nodes');
        Route::get('/create', [AdminController::class, 'createNode'])->name('nodes.create');
        Route::post('/', [AdminController::class, 'storeNode'])->name('nodes.store');
        Route::get('/{node}/edit', [AdminController::class, 'editNode'])->name('nodes.edit');
        Route::put('/{node}', [AdminController::class, 'updateNode'])->name('nodes.update');
        Route::delete('/{node}', [AdminController::class, 'deleteNode'])->name('nodes.delete');
    });
    
    // Тарифы
    Route::prefix('plans')->name('plans.')->group(function () {
        Route::get('/', [AdminController::class, 'plans'])->name('index');
        Route::get('/create', [AdminController::class, 'createPlan'])->name('create');
        Route::post('/', [AdminController::class, 'storePlan'])->name('store');
        Route::get('/{plan}/edit', [AdminController::class, 'editPlan'])->name('edit');
        Route::put('/{plan}', [AdminController::class, 'updatePlan'])->name('update');
        Route::delete('/{plan}', [AdminController::class, 'deletePlan'])->name('delete');
        Route::post('/{plan}/toggle', [AdminController::class, 'togglePlanStatus'])->name('toggle');
    });
    
    // Ядра
    Route::prefix('cores')->name('cores.')->group(function () {
        Route::get('/', [AdminCoreController::class, 'index'])->name('index');
        Route::get('/create', [AdminCoreController::class, 'create'])->name('create');
        Route::post('/', [AdminCoreController::class, 'store'])->name('store');
        Route::get('/{core}/edit', [AdminCoreController::class, 'edit'])->name('edit');
        Route::put('/{core}', [AdminCoreController::class, 'update'])->name('update');
        Route::delete('/{core}', [AdminCoreController::class, 'destroy'])->name('destroy');
        Route::post('/{core}/make-default', [AdminCoreController::class, 'makeDefault'])->name('make-default');
        Route::get('/stats', [AdminCoreController::class, 'stats'])->name('stats');
    });
});

// API маршруты
Route::prefix('api')->name('api.')->group(function () {
    // Публичные API (без аутентификации)
    Route::prefix('v1')->name('v1.')->group(function () {
        // Для демона (с токеном)
        Route::middleware(['api.token'])->group(function () {
            Route::get('/servers', [ServerController::class, 'apiIndex'])->name('servers.index');
            Route::get('/servers/{server}', [ServerController::class, 'apiShow'])->name('servers.show');
            Route::post('/servers/{server}/command', [ServerController::class, 'apiCommand'])->name('servers.command');
            Route::post('/servers/{server}/status/update', [ServerController::class, 'apiUpdateStatus'])->name('servers.status.update');
        });
        
        Route::get('/cores', [CoreController::class, 'apiIndex'])->name('cores.index');
        Route::get('/cores/{core}/download', [CoreController::class, 'apiDownload'])->name('cores.download');
        Route::get('/servers/{server}/core/status', [CoreController::class, 'checkInstallationStatus'])->name('servers.core.status');
        Route::get('/servers/{server}/core/updates', [CoreController::class, 'checkUpdates'])->name('servers.core.updates');
    });
    
    // Защищенные API (с аутентификацией)
    Route::prefix('v1')->name('v1.')->middleware(['auth:sanctum', 'check.banned'])->group(function () {
        // Управление серверами пользователя
        Route::get('/my/servers', [ServerController::class, 'apiMyServers'])->name('my.servers');
        Route::get('/my/servers/{server}/status', [ServerController::class, 'apiCheckStatus'])->name('my.servers.status');
        Route::post('/my/servers/{server}/start', [ServerController::class, 'apiStart'])->name('my.servers.start');
        Route::post('/my/servers/{server}/stop', [ServerController::class, 'apiStop'])->name('my.servers.stop');
        Route::post('/my/servers/{server}/restart', [ServerController::class, 'apiRestart'])->name('my.servers.restart');
        
        // Статистика и мониторинг
        Route::get('/my/servers/{server}/stats', [ServerController::class, 'apiGetServerStats'])->name('my.servers.stats');
        Route::get('/my/servers/{server}/console', [ServerController::class, 'apiGetConsole'])->name('my.servers.console');
        Route::post('/my/servers/{server}/console', [ServerController::class, 'apiSendConsoleCommand'])->name('my.servers.console.send');
    });
});

// Fallback route для всех несуществующих маршрутов (ДОБАВЬТЕ ЭТО!)
Route::fallback(function () {
    return response()->view('errors.404', [], 404);
});
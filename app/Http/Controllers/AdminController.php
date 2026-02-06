<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Server;
use App\Models\Node;
use App\Models\Plan;
use App\Models\Order;
use App\Models\Backup;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    // Dashboard
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'total_servers' => Server::count(),
            'total_nodes' => Node::count(),
            'active_servers' => Server::where('status', 'active')->count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'monthly_revenue' => Order::where('status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->sum('amount'),
            'total_balance' => User::sum('balance'),
        ];
        
        $recent_orders = Order::with('user', 'plan')
            ->latest()
            ->limit(10)
            ->get();
            
        $recent_users = User::latest()
            ->limit(10)
            ->get();
            
        return view('admin.dashboard', compact('stats', 'recent_orders', 'recent_users'));
    }
    
    // Users Management
    public function users(Request $request)
    {
        try {
            // Проверяем существование отношений
            $query = User::query();
            
            // Добавляем счетчики если существуют соответствующие методы
            if (method_exists(User::class, 'servers')) {
                $query = $query->withCount(['servers']);
            }
            
            if (method_exists(User::class, 'orders')) {
                $query = $query->withCount(['orders'])
                              ->withSum('orders as total_spent', 'amount');
            }
            
            $query = $query->withTrashed();

            // Поиск
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('id', $search);
                });
            }

            // Фильтр по статусу
            if ($request->has('status') && $request->status) {
                switch ($request->status) {
                    case 'active':
                        $query->where('is_banned', false)->whereNull('deleted_at');
                        break;
                    case 'banned':
                        $query->where('is_banned', true);
                        break;
                    case 'unverified':
                        $query->whereNull('email_verified_at');
                        break;
                }
            }

            // Фильтр по роли
            if ($request->has('role') && $request->role) {
                $query->where('role', $request->role);
            }

            // Показ удаленных
            if (!$request->has('show_deleted') || !$request->show_deleted) {
                $query->whereNull('deleted_at');
            }

            // Сортировка
            switch ($request->get('sort', 'newest')) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'balance_desc':
                    $query->orderBy('balance', 'desc');
                    break;
                case 'balance_asc':
                    $query->orderBy('balance', 'asc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }

            $users = $query->paginate(25);

            // Статистика
            $stats = [
                'totalUsers' => User::count(),
                'activeUsers' => User::where('is_banned', false)->whereNull('deleted_at')->count(),
                'bannedUsers' => User::where('is_banned', true)->count(),
                'totalServers' => Server::count(),
                'totalBalance' => User::sum('balance'),
            ];

            return view('admin.users.index', array_merge([
                'users' => $users,
                'title' => 'Управление пользователями'
            ], $stats));
            
        } catch (\Exception $e) {
            // Если возникает ошибка, выводим упрощенную версию
            \Log::error('Error in users method: ' . $e->getMessage());
            
            $users = User::latest()->paginate(25);
            
            $stats = [
                'totalUsers' => User::count(),
                'activeUsers' => User::count(),
                'bannedUsers' => 0,
                'totalServers' => 0,
                'totalBalance' => 0,
            ];

            return view('admin.users.index', array_merge([
                'users' => $users,
                'title' => 'Управление пользователями'
            ], $stats));
        }
    }
    
    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }
    
    
    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($user->id),
            ],
            'role' => 'required|in:user,moderator,admin',
            'balance' => 'numeric|min:0',
            'is_banned' => 'boolean',
            'ban_reason' => 'nullable|string|max:500',
            'discord_id' => 'nullable|string|max:100',
            'telegram_id' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'timezone' => 'nullable|string|max:50',
        ]);

        // Если меняется email, сбрасываем подтверждение
        if ($user->email !== $validated['email']) {
            $validated['email_verified_at'] = null;
        }

        // Если пользователь был забанен/разбанен
        if ($request->has('is_banned')) {
            $validated['banned_at'] = $validated['is_banned'] ? now() : null;
            $validated['banned_until'] = $validated['is_banned'] ? now()->addDays(30) : null;
            
            if ($validated['is_banned'] && $request->has('stop_servers')) {
                if (method_exists($user, 'servers')) {
                    $user->servers()->update(['status' => 'stopped']);
                }
            }
        }

        $user->update($validated);

        // Обновление пароля если указан
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            
            $user->update([
                'password' => Hash::make($request->password)
            ]);
        }
        
        return redirect()->route('admin.users')
            ->with('success', 'Пользователь обновлен');
    }
    
    public function deleteUser(User $user)
    {
        // Проверяем существование серверов
        if (method_exists($user, 'servers') && $user->servers()->count() > 0) {
            return redirect()->route('admin.users')
                ->with('error', 'Нельзя удалить пользователя с активными серверами!');
        }

        $user->delete();
        
        return redirect()->route('admin.users')
            ->with('success', 'Пользователь удален');
    }

    public function restoreUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        $user->restore();
        
        return redirect()->route('admin.users')
            ->with('success', 'Пользователь восстановлен');
    }

    public function forceDeleteUser($id)
    {
        $user = User::withTrashed()->findOrFail($id);
        
        // Удаляем все связанные данные если существуют
        if (method_exists($user, 'servers')) {
            $user->servers()->delete();
        }
        
        if (method_exists($user, 'orders')) {
            $user->orders()->delete();
        }
        
        if (method_exists($user, 'transactions')) {
            $user->transactions()->delete();
        }
        
        $user->forceDelete();
        
        return redirect()->route('admin.users')
            ->with('success', 'Пользователь полностью удален');
    }

    public function banUser(Request $request, User $user)
{
    \Log::info('Ban user request data:', $request->all());
    
    $request->validate([
        'ban_reason' => 'required|string|max:500',
        'ban_duration' => 'required|in:0,1,3,7,30,custom',
        'ban_until' => 'nullable|date|after:now',
        'delete_servers' => 'nullable|boolean',
        'send_notification' => 'nullable|boolean',
    ]);

    DB::beginTransaction();
    try {
        // Устанавливаем данные блокировки
        $user->is_banned = true;
        $user->ban_reason = $request->ban_reason;
        $user->banned_at = now();
        $user->banned_by = auth()->id();
        
        // Обрабатываем срок блокировки
        if ($request->ban_duration === 'custom' && $request->ban_until) {
            $user->banned_until = $request->ban_until;
        } elseif ($request->ban_duration && $request->ban_duration !== '0' && $request->ban_duration !== 'custom') {
            // ПРЕОБРАЗУЕМ В ЧИСЛО!
            $days = (int) $request->ban_duration;
            $user->banned_until = now()->addDays($days);
        } else {
            $user->banned_until = null; // Навсегда
        }
        
        // Останавливаем серверы если нужно
        if ($request->has('delete_servers') && $request->delete_servers == '1') {
            if (method_exists($user, 'servers')) {
                $user->servers()->update(['status' => 'stopped']);
            }
        }
        
        $user->save();
        
        // Отправляем уведомление если нужно
        if ($request->has('send_notification') && $request->send_notification == '1') {
            \Log::info("User {$user->id} notified about ban. Reason: {$request->ban_reason}");
        }
        
        DB::commit();
        
        \Log::info("User {$user->id} banned by admin " . auth()->id() . 
                  ". Reason: {$request->ban_reason}, Duration: {$request->ban_duration}");
        
        return redirect()->route('admin.users')
            ->with('success', "Пользователь {$user->name} заблокирован");
            
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error("Error banning user {$user->id}: " . $e->getMessage());
        \Log::error("Error trace: " . $e->getTraceAsString());
        return redirect()->back()->with('error', 'Ошибка блокировки пользователя: ' . $e->getMessage());
    }
}

    public function unbanUser(Request $request, User $user)
{
    try {
        $user->update([
            'is_banned' => false,
            'ban_reason' => null,
            'banned_at' => null,
            'banned_until' => null,
            'banned_by' => null,
        ]);
        
        \Log::info("User {$user->id} unbanned by admin " . auth()->id());
        
        return redirect()->route('admin.users')
            ->with('success', "Пользователь {$user->name} разблокирован");
            
    } catch (\Exception $e) {
        \Log::error("Error unbanning user {$user->id}: " . $e->getMessage());
        return redirect()->back()->with('error', 'Ошибка разблокировки пользователя');
    }
}

    public function withdrawBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $user->balance,
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($user, $request) {
            $oldBalance = $user->balance;
            $user->decrement('balance', $request->amount);

            // Проверяем существование модели Transaction
            if (class_exists(Transaction::class)) {
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdrawal',
                    'amount' => -$request->amount,
                    'description' => 'Списание администратором: ' . $request->reason,
                    'old_balance' => $oldBalance,
                    'new_balance' => $user->balance,
                    'admin_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                ]);
            }
        });

        return redirect()->route('admin.users')
            ->with('success', 'Баланс успешно списан!');
    }

    public function addBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        DB::transaction(function () use ($user, $request) {
            $oldBalance = $user->balance;
            $user->increment('balance', $request->amount);

            // Проверяем существование модели Transaction
            if (class_exists(Transaction::class)) {
                Transaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $request->amount,
                    'description' => 'Начисление администратором: ' . $request->reason,
                    'old_balance' => $oldBalance,
                    'new_balance' => $user->balance,
                    'admin_id' => auth()->id(),
                    'ip_address' => $request->ip(),
                ]);
            }
        });

        return redirect()->route('admin.users')
            ->with('success', 'Баланс успешно пополнен!');
    }

    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:user,moderator,admin',
            'balance' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'balance' => $request->balance ?? 0,
            'email_verified_at' => $request->has('email_verified') ? now() : null,
        ]);

        return redirect()->route('admin.users')
            ->with('success', 'Пользователь успешно создан!');
    }

    public function loginAsUser(User $user)
    {
        auth()->login($user, true);
        
        return redirect()->route('dashboard')
            ->with('success', 'Вы вошли как пользователь ' . $user->name);
    }

    public function exportUsers(Request $request)
    {
        try {
            $query = User::query();
            
            // Добавляем счетчики если существуют
            if (method_exists(User::class, 'servers')) {
                $query = $query->withCount(['servers']);
            }
            
            if (method_exists(User::class, 'orders')) {
                $query = $query->withCount(['orders'])
                              ->withSum('orders as total_spent', 'amount');
            }
            
            $users = $query->get();

            $csv = fopen('php://temp', 'w');
            
            // Заголовки
            fputcsv($csv, [
                'ID',
                'Имя',
                'Email',
                'Роль',
                'Баланс',
                'Всего серверов',
                'Всего заказов',
                'Всего потрачено',
                'Статус',
                'Дата регистрации',
                'Последний вход',
            ]);

            // Данные
            foreach ($users as $user) {
                fputcsv($csv, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->role ?? 'user',
                    $user->balance ?? 0,
                    $user->servers_count ?? 0,
                    $user->orders_count ?? 0,
                    $user->total_spent ?? 0,
                    $user->is_banned ? 'Заблокирован' : ($user->email_verified_at ? 'Активен' : 'Не подтвержден'),
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Никогда',
                ]);
            }

            rewind($csv);
            $csvData = stream_get_contents($csv);
            fclose($csv);

            return response($csvData, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="users_' . date('Y-m-d_H-i') . '.csv"',
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());
            
            return redirect()->route('admin.users')
                ->with('error', 'Ошибка при экспорте данных');
        }
    }

    public function userTransactions(User $user)
    {
        if (!class_exists(Transaction::class) || !method_exists($user, 'transactions')) {
            return redirect()->route('admin.users')
                ->with('error', 'Транзакции не доступны');
        }
        
        $transactions = $user->transactions()
            ->latest()
            ->paginate(20);
            
        return view('admin.users.transactions', compact('user', 'transactions'));
    }

    public function userServers(User $user)
    {
        if (!method_exists($user, 'servers')) {
            return redirect()->route('admin.users')
                ->with('error', 'Серверы не доступны');
        }
        
        $servers = $user->servers()
            ->with('node', 'plan')
            ->latest()
            ->paginate(20);
            
        return view('admin.users.servers', compact('user', 'servers'));
    }

    public function userOrders(User $user)
    {
        if (!method_exists($user, 'orders')) {
            return redirect()->route('admin.users')
                ->with('error', 'Заказы не доступны');
        }
        
        $orders = $user->orders()
            ->with('plan')
            ->latest()
            ->paginate(20);
            
        return view('admin.users.orders', compact('user', 'orders'));
    }

    public function bulkActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:ban,unban,delete,restore,add_balance,withdraw_balance',
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
        ]);

        $count = 0;

        switch ($request->action) {
            case 'ban':
                User::whereIn('id', $request->users)
                    ->update([
                        'is_banned' => true,
                        'banned_at' => now(),
                    ]);
                $count = count($request->users);
                break;

            case 'unban':
                User::whereIn('id', $request->users)
                    ->update([
                        'is_banned' => false,
                        'ban_reason' => null,
                        'banned_at' => null,
                        'banned_until' => null,
                    ]);
                $count = count($request->users);
                break;

            case 'delete':
                User::whereIn('id', $request->users)->delete();
                $count = count($request->users);
                break;

            case 'restore':
                User::withTrashed()->whereIn('id', $request->users)->restore();
                $count = count($request->users);
                break;

            case 'add_balance':
                $request->validate([
                    'amount' => 'required|numeric|min:0.01',
                    'reason' => 'required|string|max:255',
                ]);

                DB::transaction(function () use ($request) {
                    foreach ($request->users as $userId) {
                        $user = User::find($userId);
                        $oldBalance = $user->balance;
                        $user->increment('balance', $request->amount);

                        // Проверяем существование модели Transaction
                        if (class_exists(Transaction::class)) {
                            Transaction::create([
                                'user_id' => $user->id,
                                'type' => 'deposit',
                                'amount' => $request->amount,
                                'description' => 'Массовое начисление: ' . $request->reason,
                                'old_balance' => $oldBalance,
                                'new_balance' => $user->balance,
                                'admin_id' => auth()->id(),
                            ]);
                        }
                    }
                });
                $count = count($request->users);
                break;

            case 'withdraw_balance':
                $request->validate([
                    'amount' => 'required|numeric|min:0.01',
                    'reason' => 'required|string|max:255',
                ]);

                DB::transaction(function () use ($request) {
                    foreach ($request->users as $userId) {
                        $user = User::find($userId);
                        if ($user->balance >= $request->amount) {
                            $oldBalance = $user->balance;
                            $user->decrement('balance', $request->amount);

                            // Проверяем существование модели Transaction
                            if (class_exists(Transaction::class)) {
                                Transaction::create([
                                    'user_id' => $user->id,
                                    'type' => 'withdrawal',
                                    'amount' => -$request->amount,
                                    'description' => 'Массовое списание: ' . $request->reason,
                                    'old_balance' => $oldBalance,
                                    'new_balance' => $user->balance,
                                    'admin_id' => auth()->id(),
                                ]);
                            }
                        }
                    }
                });
                $count = count($request->users);
                break;
        }

        return redirect()->route('admin.users')
            ->with('success', "Действие выполнено для {$count} пользователей");
    }
    
    // Servers Management
public function servers(Request $request)
{
    try {
        $query = Server::with(['user', 'node', 'plan'])
            ->latest();

        // Поиск
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('user', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('node', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Фильтр по статусу
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Фильтр по ноде
        if ($request->has('node_id') && $request->node_id) {
            $query->where('node_id', $request->node_id);
        }

        // Фильтр по пользователю
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Фильтр по типу игры
        if ($request->has('game_type') && $request->game_type) {
            $query->where('game_type', $request->game_type);
        }

        // Показ истекших
        if (!$request->has('show_expired') || !$request->show_expired) {
            $query->where(function($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
        }

        // Сортировка
        switch ($request->get('sort', 'newest')) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'memory_desc':
                $query->orderBy('memory', 'desc');
                break;
            case 'memory_asc':
                $query->orderBy('memory', 'asc');
                break;
            case 'disk_desc':
                $query->orderBy('disk', 'desc');
                break;
            case 'disk_asc':
                $query->orderBy('disk', 'asc');
                break;
            case 'cpu_desc':
                $query->orderBy('cpu', 'desc');
                break;
            case 'cpu_asc':
                $query->orderBy('cpu', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $servers = $query->paginate(25);

        // Статистика
        $stats = [
            'totalServers' => Server::count(),
            'activeServers' => Server::where('status', 'active')->count(),
            'stoppedServers' => Server::where('status', 'stopped')->count(),
            'suspendedServers' => Server::where('status', 'suspended')->count(),
            'creatingServers' => Server::where('status', 'creating')->count(),
            'totalMemory' => Server::sum('memory'),
            'totalDisk' => Server::sum('disk'),
        ];

        // Данные для фильтров
        $nodes = Node::where('is_active', true)->orderBy('name')->get();
        $users = User::orderBy('name')->get();
        $plans = Plan::where('is_active', true)->orderBy('price')->get();

        return view('admin.servers.index', array_merge([
            'servers' => $servers,
            'nodes' => $nodes,
            'users' => $users,
            'plans' => $plans,
            'title' => 'Управление серверами'
        ], $stats));
        
    } catch (\Exception $e) {
        \Log::error('Error in servers method: ' . $e->getMessage());
        
        $servers = Server::with(['user', 'node', 'plan'])->latest()->paginate(25);
        
        $stats = [
            'totalServers' => Server::count(),
            'activeServers' => 0,
            'stoppedServers' => 0,
            'suspendedServers' => 0,
            'creatingServers' => 0,
            'totalMemory' => 0,
            'totalDisk' => 0,
        ];

        return view('admin.servers.index', array_merge([
            'servers' => $servers,
            'nodes' => collect([]),
            'users' => collect([]),
            'plans' => collect([]),
            'title' => 'Управление серверами'
        ], $stats));
    }
}   public function serverShow(Server $server)
{
    try {
        // Загружаем все связанные данные
        $server->load(['user', 'node', 'plan', 'backups']);
        
        // Получаем список активных нод для модального окна переноса
        $nodes = Node::where('is_active', true)
                    ->where('id', '!=', $server->node_id)
                    ->orderBy('name')
                    ->get();
        
        // Статистика по серверу
        $stats = [
            'totalBackups' => $server->backups()->count(),
            'lastBackup' => $server->backups()->latest()->first(),
            'lastActivity' => $server->updated_at->diffForHumans(),
        ];
        
        // Получаем дополнительные данные для вкладок
        $core = null;
        if (class_exists('App\Models\Core') && $server->core_id) {
            $core = \App\Models\Core::find($server->core_id);
        }
        
        // Получаем информацию от демона (если есть)
        $daemonInfo = null;
        if ($server->settings && isset($server->settings['daemon_response'])) {
            $daemonInfo = $server->settings['daemon_response'];
        }
        
        return view('admin.servers.show', compact('server', 'stats', 'core', 'daemonInfo', 'nodes'));
        
    } catch (\Exception $e) {
        \Log::error('Error in serverShow method: ' . $e->getMessage());
        
        return redirect()->route('admin.servers')
            ->with('error', 'Ошибка при загрузке информации о сервере: ' . $e->getMessage());
    }
}

public function serverManage(Request $request, Server $server)
{
    $request->validate([
        'action' => 'required|in:start,stop,restart,suspend,unsuspend,reinstall,migrate,force_delete'
    ]);
    
    $action = $request->input('action');
    
    try {
        switch ($action) {
            case 'start':
                // Логика запуска сервера через демон
                // TODO: Интеграция с демоном
                $server->status = 'active';
                $server->started_at = now();
                $server->stopped_at = null;
                $server->save();
                
                $message = 'Сервер запущен';
                break;
                
            case 'stop':
                // Логика остановки сервера через демон
                $server->status = 'stopped';
                $server->stopped_at = now();
                $server->save();
                
                $message = 'Сервер остановлен';
                break;
                
            case 'restart':
                // Логика перезагрузки сервера
                // Сначала останавливаем
                $server->status = 'restarting';
                $server->save();
                
                // TODO: Интеграция с демоном
                // Затем запускаем
                sleep(3); // Имитация перезагрузки
                
                $server->status = 'active';
                $server->started_at = now();
                $server->save();
                
                $message = 'Сервер перезагружен';
                break;
                
            case 'suspend':
                $server->status = 'suspended';
                $server->suspended_at = now();
                $server->save();
                
                $message = 'Сервер заблокирован';
                break;
                
            case 'unsuspend':
                $server->status = 'active';
                $server->suspended_at = null;
                $server->save();
                
                $message = 'Сервер разблокирован';
                break;
                
            case 'reinstall':
                // Логика переустановки сервера
                $server->status = 'reinstalling';
                $server->save();
                
                // TODO: Логика переустановки через демон
                
                $message = 'Сервер поставлен в очередь на переустановку';
                break;
                
            case 'migrate':
                // Логика миграции на другую ноду
                $server->status = 'migrating';
                $server->save();
                
                $message = 'Сервер поставлен в очередь на миграцию';
                break;
                
            case 'force_delete':
                // Логика полного удаления
                $reason = $request->input('reason', 'Причина не указана');
                $deleteBackups = $request->has('delete_backups');
                $notifyUser = $request->has('notify_user');
                
                // Удаляем бэкапы если нужно
                if ($deleteBackups) {
                    $server->backups()->delete();
                }
                
                // Логируем удаление
                \Log::warning("Server {$server->id} force deleted by admin {$request->user()->id}. Reason: {$reason}");
                
                // Удаляем сервер
                $server->delete();
                
                // TODO: Отправить уведомление пользователю если нужно
                
                return redirect()->route('admin.servers')
                    ->with('success', "Сервер {$server->name} удален безвозвратно");
                
            default:
                return redirect()->back()->with('error', 'Неизвестное действие');
        }
        
        // Запись в логи
        \Log::info("Admin {$request->user()->name} выполнил действие {$action} на сервере {$server->id}");
        
        return redirect()->back()->with('success', $message);
        
    } catch (\Exception $e) {
        \Log::error("Error managing server {$server->id}: " . $e->getMessage());
        return redirect()->back()->with('error', 'Ошибка выполнения действия: ' . $e->getMessage());
    }
}

public function storeServer(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:100',
        'user_id' => 'required|exists:users,id',
        'plan_id' => 'required|exists:plans,id',
        'node_id' => 'required|exists:nodes,id',
        'game_type' => 'required|in:java,bedrock',
        'version' => 'nullable|string|max:50',
        'description' => 'nullable|string|max:500',
        'memory' => 'nullable|integer|min:512',
        'disk' => 'nullable|integer|min:1024',
        'cpu' => 'nullable|integer|min:10|max:100',
        'port' => 'nullable|integer|between:1024,65535',
    ]);
    
    // Получаем выбранный тариф
    $plan = Plan::find($validated['plan_id']);
    
    // Создаем сервер
    $server = Server::create([
        'name' => $validated['name'],
        'user_id' => $validated['user_id'],
        'plan_id' => $validated['plan_id'],
        'node_id' => $validated['node_id'],
        'game_type' => $validated['game_type'],
        'version' => $validated['version'] ?? '1.20.4',
        'description' => $validated['description'],
        'memory' => $validated['memory'] ?? $plan->memory,
        'disk' => $validated['disk'] ?? $plan->disk,
        'cpu' => $validated['cpu'] ?? 100,
        'port' => $validated['port'] ?? 25565,
        'status' => 'creating',
        'uuid' => \Str::uuid(),
        'ip_address' => Node::find($validated['node_id'])->ip_address,
        'expires_at' => now()->addDays($plan->period_days ?? 30),
    ]);
    
    // Здесь должна быть логика создания сервера через демон
    
    \Log::info("Администратор {$request->user()->name} создал сервер #{$server->id} для пользователя {$server->user->name}");
    
    return redirect()->route('admin.servers')
        ->with('success', "Сервер #{$server->id} создается");
}

// Управление платежами
public function payments(Request $request)
{
    $query = Payment::query()->with(['user'])->latest();

    // Фильтрация
    if ($request->has('status') && $request->status) {
        $query->where('status', $request->status);
    }

    if ($request->has('search') && $request->search) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('yookassa_id', 'like', "%{$search}%")
              ->orWhereHas('user', function($q) use ($search) {
                  $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }

    $payments = $query->paginate(25);

    $stats = [
        'totalPayments' => Payment::count(),
        'totalAmount' => Payment::where('status', 'succeeded')->sum('amount'),
        'pendingPayments' => Payment::where('status', 'pending')->count(),
        'succeededPayments' => Payment::where('status', 'succeeded')->count(),
    ];

    return view('admin.payments.index', compact('payments', 'stats'));
}

public function exportServers(Request $request)
{
    try {
        $servers = Server::with(['user', 'node', 'plan'])->get();

        $csv = fopen('php://temp', 'w');
        
        // Заголовки
        fputcsv($csv, [
            'ID',
            'Название',
            'Владелец',
            'Email владельца',
            'Нода',
            'IP:Порт',
            'Память (MB)',
            'Диск (MB)',
            'CPU (%)',
            'Статус',
            'Тип игры',
            'Версия',
            'Тариф',
            'Дата создания',
            'Дата истечения',
            'Описание',
        ]);

        // Данные
        foreach ($servers as $server) {
            fputcsv($csv, [
                $server->id,
                $server->name,
                $server->user->name,
                $server->user->email,
                $server->node->name ?? 'Не назначена',
                $server->ip_address && $server->port ? "{$server->ip_address}:{$server->port}" : 'Не назначен',
                $server->memory,
                $server->disk,
                $server->cpu,
                $server->status,
                $server->game_type,
                $server->version,
                $server->plan->name ?? 'Не указан',
                $server->created_at->format('Y-m-d H:i:s'),
                $server->expires_at ? $server->expires_at->format('Y-m-d H:i:s') : 'Бессрочно',
                $server->description,
            ]);
        }

        rewind($csv);
        $csvData = stream_get_contents($csv);
        fclose($csv);

        return response($csvData, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="servers_' . date('Y-m-d_H-i') . '.csv"',
        ]);
        
    } catch (\Exception $e) {
        \Log::error('Servers export error: ' . $e->getMessage());
        
        return redirect()->route('admin.servers')
            ->with('error', 'Ошибка при экспорте данных');
    }
}

public function destroyServer(Request $request, $id)
{
    try {
        $server = Server::findOrFail($id);
        $reason = $request->input('reason', 'Причина не указана');
        
        \Log::warning("Server {$server->id} deleted by admin {$request->user()->id}. Reason: {$reason}");
        
        $server->delete();
        
        return redirect()->route('admin.servers')
            ->with('success', "Сервер {$server->name} удален");
            
    } catch (\Exception $e) {
        \Log::error("Error deleting server {$id}: " . $e->getMessage());
        return redirect()->back()->with('error', 'Ошибка удаления сервера: ' . $e->getMessage());
    }
}
public function bulkServerActions(Request $request)
{
    $request->validate([
        'action' => 'required|in:start,stop,restart,suspend,unsuspend,delete,force_delete,migrate',
        'servers' => 'required|array',
        'servers.*' => 'exists:servers,id',
        'reason' => 'nullable|string|max:500',
        'node_id' => 'nullable|exists:nodes,id',
    ]);

    $count = 0;
    $failed = 0;
    $errors = [];

    foreach ($request->servers as $serverId) {
        try {
            $server = Server::find($serverId);
            
            switch ($request->action) {
                case 'start':
                    $server->update(['status' => 'active']);
                    $count++;
                    break;
                    
                case 'stop':
                    $server->update(['status' => 'stopped']);
                    $count++;
                    break;
                    
                case 'restart':
                    $server->update(['status' => 'restarting']);
                    $count++;
                    break;
                    
                case 'suspend':
                    $server->update([
                        'status' => 'suspended',
                        'suspended_at' => now(),
                        'suspension_reason' => $request->reason,
                    ]);
                    $count++;
                    break;
                    
                case 'unsuspend':
                    $server->update([
                        'status' => 'stopped',
                        'suspended_at' => null,
                        'suspension_reason' => null,
                    ]);
                    $count++;
                    break;
                    
                case 'delete':
                    if ($server->status !== 'active') {
                        $server->delete();
                        $count++;
                    } else {
                        $failed++;
                        $errors[] = "Сервер #{$serverId} активен и не может быть удален";
                    }
                    break;
                    
                case 'force_delete':
                    $server->forceDelete();
                    $count++;
                    break;
                    
                case 'migrate':
                    if ($request->node_id) {
                        $server->update(['node_id' => $request->node_id, 'status' => 'migrating']);
                        $count++;
                    }
                    break;
            }
            
        } catch (\Exception $e) {
            $failed++;
            $errors[] = "Ошибка с сервером #{$serverId}: " . $e->getMessage();
        }
    }
    
    \Log::info("Администратор {$request->user()->name} выполнил массовое действие: {$request->action} на {$count} серверах");
    
    $message = "Действие выполнено для {$count} серверов";
    if ($failed > 0) {
        $message .= ". Ошибок: {$failed}";
        session()->flash('warning', implode('<br>', $errors));
    }
    
    return redirect()->route('admin.servers')
        ->with('success', $message);
}
    
    // Nodes Management
    public function nodes()
    {
        $nodes = Node::withCount('servers')
            ->latest()
            ->paginate(20);
            
        return view('admin.nodes.index', compact('nodes'));
    }
    
    public function createNode()
    {
        return view('admin.nodes.create');
    }
    
    public function storeNode(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'hostname' => 'required|string',
            'location' => 'required|string',
            'total_memory' => 'required|integer|min:1024',
            'total_disk' => 'required|integer|min:10240',
            'total_cpu' => 'required|integer|min:1',
            'daemon_port' => 'required|integer|between:1024,65535',
            'daemon_token' => 'required|string',
        ]);
        
        Node::create($validated);
        
        return redirect()->route('admin.nodes')
            ->with('success', 'Нода добавлена');
    }
    
    public function editNode(Node $node)
    {
        return view('admin.nodes.edit', compact('node'));
    }
    
    public function updateNode(Request $request, Node $node)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|ip',
            'hostname' => 'required|string',
            'location' => 'required|string',
            'total_memory' => 'required|integer|min:1024',
            'total_disk' => 'required|integer|min:10240',
            'total_cpu' => 'required|integer|min:1',
            'daemon_port' => 'required|integer|between:1024,65535',
            'daemon_token' => 'required|string',
            'is_active' => 'boolean',
            'accept_new_servers' => 'boolean',
        ]);
        
        $node->update($validated);
        
        return redirect()->route('admin.nodes')
            ->with('success', 'Нода обновлена');
    }
    
    public function deleteNode(Node $node)
    {
        if ($node->servers()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Нельзя удалить ноду с серверами');
        }
        
        $node->delete();
        
        return redirect()->route('admin.nodes')
            ->with('success', 'Нода удалена');
    }
    
    // Plans Management
    public function plans()
{
    try {
        $plans = Plan::orderBy('price_monthly')->paginate(20);
        return view('admin.plans.index', compact('plans'));
    } catch (\Exception $e) {
        \Log::error('Error loading plans: ' . $e->getMessage());
        return redirect()->route('admin.dashboard')
            ->with('error', 'Ошибка загрузки тарифов');
    }
}

public function createPlan()
{
    return view('admin.plans.create');
}

public function storePlan(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'game_type' => 'required|in:java,bedrock',
        'memory' => 'required|integer|min:512|max:16384',
        'disk_space' => 'required|integer|min:1024|max:102400',
        'player_slots' => 'required|integer|min:1|max:100',
        'price_monthly' => 'required|numeric|min:1|max:100',
        'price_quarterly' => 'nullable|numeric|min:1|max:300',
        'price_half_year' => 'nullable|numeric|min:1|max:600',
        'price_yearly' => 'nullable|numeric|min:1|max:1200',
        'is_active' => 'boolean',
    ]);

    // Исправляем обработку чекбокса
    $validated['is_active'] = $request->has('is_active');

    Plan::create($validated);

    return redirect()->route('admin.plans.index')
        ->with('success', 'Тариф успешно создан');
}

public function editPlan(Plan $plan)
{
    return view('admin.plans.edit', compact('plan'));
}

public function updatePlan(Request $request, Plan $plan)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:500',
        'game_type' => 'required|in:java,bedrock',
        'memory' => 'required|integer|min:512|max:16384',
        'disk_space' => 'required|integer|min:1024|max:102400',
        'player_slots' => 'required|integer|min:1|max:100',
        'price_monthly' => 'required|numeric|min:1|max:100',
        'price_quarterly' => 'nullable|numeric|min:1|max:300',
        'price_half_year' => 'nullable|numeric|min:1|max:600',
        'price_yearly' => 'nullable|numeric|min:1|max:1200',
        'is_active' => 'boolean',
    ]);

    // Исправляем обработку чекбокса
    $validated['is_active'] = $request->has('is_active');

    $plan->update($validated);

    return redirect()->route('admin.plans.index')
        ->with('success', 'Тариф успешно обновлен');
}

public function deletePlan(Plan $plan)
{
    try {
        // Проверяем, используется ли тариф в активных серверах
        if ($plan->servers()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Нельзя удалить тариф, используемый в активных серверах');
        }

        $plan->delete();

        return redirect()->route('admin.plans')
            ->with('success', 'Тариф удален');
    } catch (\Exception $e) {
        \Log::error('Error deleting plan: ' . $e->getMessage());
        return redirect()->back()
            ->with('error', 'Ошибка удаления тарифа');
    }
}

public function togglePlanStatus($id)
{
    try {
        $plan = Plan::findOrFail($id);
        $plan->is_active = !$plan->is_active;
        $plan->save();

        return response()->json([
            'success' => true,
            'message' => 'Статус тарифа изменен',
            'is_active' => $plan->is_active
        ]);
    } catch (\Exception $e) {
        \Log::error('Error toggling plan status: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Ошибка изменения статуса'
        ], 500);
    }
}
    
    // Orders Management
    public function orders()
    {
        $orders = Order::with('user', 'plan')
            ->latest()
            ->paginate(20);
            
        return view('admin.orders.index', compact('orders'));
    }
    
    // Backups Management
    public function backups()
    {
        $backups = Backup::with('server')
            ->latest()
            ->paginate(20);
            
        return view('admin.backups.index', compact('backups'));
    }
    
    // Settings
    public function settings()
    {
        return view('admin.settings');
    }
    
    // Update Settings
    public function updateSettings(Request $request)
    {
        // Здесь можно добавить сохранение настроек
        return redirect()->route('admin.settings')
            ->with('success', 'Настройки обновлены');
    }
}
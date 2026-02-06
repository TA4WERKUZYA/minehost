<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\Plan;
use App\Models\Node;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Безопасное получение пользователя
            $user = auth()->user();
            
            if (!$user) {
                \Log::warning('Dashboard: User not authenticated, redirecting to login');
                return redirect()->route('login')->with('error', 'Требуется авторизация');
            }
            
            \Log::info('Dashboard access', ['user_id' => $user->id]);
            
            // Получаем серверы пользователя
            $servers = Server::with(['plan', 'node'])
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();
            
            \Log::info('User servers count:', ['count' => $servers->count()]);
            
            // Получаем активные тарифы
            $plans = Plan::where('is_active', true)->get();
            
            // Получаем доступные ноды
            $nodes = Node::where('is_active', true)
                        ->where('accept_new_servers', true)
                        ->get();
            
            // Получаем последние заказы
            $orders = Order::where('user_id', $user->id)
                          ->orderBy('created_at', 'desc')
                          ->limit(5)
                          ->get();
            
            return view('dashboard.index', [
                'servers' => $servers,
                'plans' => $plans,
                'nodes' => $nodes,
                'orders' => $orders,
                'user' => $user,
                'title' => 'Панель управления'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Dashboard error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Возвращаем пустую дашборд с ошибкой
            return view('dashboard.index', [
                'servers' => collect(),
                'plans' => collect(),
                'nodes' => collect(),
                'orders' => collect(),
                'user' => null,
                'error' => 'Ошибка загрузки данных',
                'title' => 'Панель управления'
            ]);
        }
    }
    
    public function create()
    {
        try {
            // Безопасное получение пользователя
            $user = auth()->user();
            
            if (!$user) {
                \Log::warning('Create form: User not authenticated');
                return redirect()->route('login')->with('error', 'Требуется авторизация');
            }
            
            \Log::info('Create form accessed', ['user_id' => $user->id]);
            
            $plans = Plan::where('is_active', true)->get();
            $nodes = Node::where('is_active', true)
                        ->where('accept_new_servers', true)
                        ->get();
            
            if ($plans->isEmpty()) {
                \Log::warning('No active plans available');
                return redirect()->route('dashboard')
                    ->with('error', 'Нет доступных тарифов для создания сервера');
            }
            
            if ($nodes->isEmpty()) {
                \Log::warning('No available nodes', ['user_id' => $user->id]);
                return redirect()->route('dashboard')
                    ->with('error', 'Нет доступных нод для создания сервера');
            }
            
            return view('dashboard.create', [
                'plans' => $plans,
                'nodes' => $nodes,
                'user' => $user,
                'title' => 'Создание сервера'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Create form error:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->route('dashboard')
                ->with('error', 'Ошибка загрузки формы: ' . $e->getMessage());
        }
    }
    
    public function confirm()
    {
        $user = auth()->user();
        
        if (!$user) {
            return redirect()->route('login')->with('error', 'Требуется авторизация');
        }
        
        return view('dashboard.confirm', [
            'user' => $user,
            'title' => 'Подтверждение заказа'
        ]);
    }
}
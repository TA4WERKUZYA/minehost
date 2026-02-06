<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Показать форму входа
     */
    public function showLoginForm()
    {
        // Если пользователь уже авторизован, перенаправить на dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

    /**
     * Обработать попытку входа
     */
    public function login(Request $request)
    {
        // Валидация
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Сначала находим пользователя по email для проверки статуса
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'Неверный email или пароль.',
            ])->onlyInput('email');
        }

        // Проверяем не удален ли пользователь
        if (!is_null($user->deleted_at)) {
            Log::warning('Login attempt for deleted user', [
                'user_id' => $user->id,
                'email' => $credentials['email']
            ]);
            
            return back()->withErrors([
                'email' => 'Аккаунт был удален.',
            ])->onlyInput('email');
        }

        // Проверяем не заблокирован ли пользователь
        if ($user->is_banned) {
            // Проверяем истекла ли временная блокировка
            if ($user->banned_until && now()->gt($user->banned_until)) {
                // Срок блокировки истек, автоматически разблокируем
                $user->update([
                    'is_banned' => false,
                    'ban_reason' => null,
                    'banned_at' => null,
                    'banned_until' => null,
                    'banned_by' => null,
                ]);
                
                Log::info('User automatically unbanned after ban expiration', [
                    'user_id' => $user->id,
                    'email' => $credentials['email']
                ]);
            } else {
                // Пользователь заблокирован
                $message = 'Ваш аккаунт заблокирован.';
                
                if ($user->ban_reason) {
                    $message .= ' Причина: ' . $user->ban_reason;
                }
                
                if ($user->banned_until) {
                    $message .= ' Блокировка до: ' . $user->banned_until->format('d.m.Y H:i:s');
                    $message .= ' (осталось: ' . $user->banned_until->diffForHumans() . ')';
                } else {
                    $message .= ' Блокировка постоянная.';
                }
                
                $message .= ' Для разблокировки обратитесь к администратору.';
                
                Log::warning('Login attempt for banned user', [
                    'user_id' => $user->id,
                    'email' => $credentials['email'],
                    'ban_reason' => $user->ban_reason,
                    'banned_until' => $user->banned_until
                ]);
                
                return back()->withErrors([
                    'email' => $message,
                ])->onlyInput('email');
            }
        }

        // Проверяем не подтвержден ли email (опционально)
        if (!$user->email_verified_at) {
            // Можно добавить здесь редирект на страницу подтверждения email
            // или разрешить вход без подтверждения - зависит от требований
        }

        // Попытка аутентификации
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            
            // Обновляем время последнего входа
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);
            
            Log::info('User logged in successfully', [
                'user_id' => Auth::id(),
                'email' => $credentials['email'],
                'ip' => $request->ip()
            ]);
            
            return redirect()->intended(route('dashboard'));
        }

        // Если аутентификация не удалась
        Log::warning('Failed login attempt', [
            'email' => $credentials['email'],
            'ip' => $request->ip()
        ]);
        
        return back()->withErrors([
            'email' => 'Неверный email или пароль.',
        ])->onlyInput('email');
    }

    /**
     * Показать форму регистрации
     */
    public function showRegistrationForm()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.register');
    }

    /**
     * Обработать регистрацию
     */
    public function register(Request $request)
    {
        // Валидация
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Создание пользователя
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'balance' => 0,
            'role' => 'user', // Добавляем роль по умолчанию
            'is_banned' => false,
        ]);

        // Автоматический вход после регистрации
        Auth::login($user);

        Log::info('New user registered', [
            'user_id' => $user->id,
            'email' => $validated['email'],
            'ip' => $request->ip()
        ]);

        return redirect()->route('dashboard')->with('success', 'Регистрация успешна!');
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        $userId = Auth::id();
        $userEmail = Auth::user()->email ?? null;
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        Log::info('User logged out', [
            'user_id' => $userId,
            'email' => $userEmail
        ]);
        
        return redirect('/')->with('success', 'Вы успешно вышли из системы.');
    }

    /**
     * Показать профиль пользователя
     */
    public function profile()
    {
        $user = Auth::user();
        
        // Проверяем не заблокирован ли пользователь при просмотре профиля
        if ($user->is_banned) {
            return redirect()->route('login')->withErrors([
                'email' => 'Ваш аккаунт заблокирован. Обратитесь к администратору.'
            ]);
        }
        
        return view('auth.profile', [
            'user' => $user
        ]);
    }

    /**
     * Обновить профиль пользователя
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        // Проверяем не заблокирован ли пользователь
        if ($user->is_banned) {
            return redirect()->route('login')->withErrors([
                'email' => 'Ваш аккаунт заблокирован. Обратитесь к администратору.'
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|min:8|confirmed',
        ]);

        // Обновление данных
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // Обновление пароля если указан новый
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Текущий пароль неверен.'
                ]);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        Log::info('User profile updated', [
            'user_id' => $user->id,
            'email' => $user->email
        ]);

        return back()->with('success', 'Профиль успешно обновлен.');
    }

    /**
     * Дополнительный middleware для проверки блокировки при каждом запросе
     * Можно добавить в конструктор или как отдельный middleware
     */
    public function __construct()
    {
        // Можно добавить middleware для проверки блокировки
        // Но лучше создать отдельный middleware для этого
    }
}
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Логирование для отладки
        \Log::info('AdminMiddleware: проверка доступа', [
            'user_id' => Auth::id(),
            'path' => $request->path()
        ]);

        if (!Auth::check()) {
            \Log::warning('AdminMiddleware: пользователь не авторизован');
            return redirect()->route('login')->with('error', 'Требуется авторизация');
        }

        $user = Auth::user();
        
        // Проверяем поле is_admin
        if (!$user->is_admin) {
            \Log::warning('AdminMiddleware: доступ запрещен', [
                'user_id' => $user->id,
                'is_admin' => $user->is_admin
            ]);
            return redirect()->route('dashboard')->with('error', 'Доступ запрещен. Требуются права администратора.');
        }

        \Log::info('AdminMiddleware: доступ разрешен', ['user_id' => $user->id]);
        return $next($request);
    }
}
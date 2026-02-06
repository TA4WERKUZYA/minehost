<?php

use Illuminate\Support\Facades\Route;

Route::get('/test-admin', function () {
    // Проверка существования класса
    $classExists = class_exists(\App\Http\Middleware\AdminMiddleware::class);
    
    // Попробуем создать экземпляр
    try {
        $middleware = new \App\Http\Middleware\AdminMiddleware();
        $instance = '✅ Создан';
    } catch (\Exception $e) {
        $instance = '❌ Ошибка: ' . $e->getMessage();
    }
    
    // Проверка Kernel
    $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
    $aliases = [];
    if (method_exists($kernel, 'getMiddlewareAliases')) {
        $aliases = $kernel->getMiddlewareAliases();
    }
    
    return response()->json([
        'admin_middleware_class_exists' => $classExists,
        'admin_middleware_instance' => $instance,
        'middleware_aliases' => $aliases,
        'user' => auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'is_admin' => auth()->user()->is_admin
        ] : null
    ]);
});

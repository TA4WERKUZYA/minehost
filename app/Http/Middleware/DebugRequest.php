<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        // Логируем только POST запросы к dashboard/create
        if ($request->isMethod('POST') && $request->is('dashboard/create')) {
            \Log::info('=== DEBUG REQUEST ===');
            \Log::info('Path: ' . $request->path());
            \Log::info('Full URL: ' . $request->fullUrl());
            \Log::info('Method: ' . $request->method());
            \Log::info('Data: ', $request->all());
            \Log::info('Route: ' . ($request->route() ? $request->route()->getName() : 'null'));
            \Log::info('Controller: ' . ($request->route() ? print_r($request->route()->getAction(), true) : 'null'));
            \Log::info('=== END DEBUG ===');
        }
        
        return $next($request);
    }
}

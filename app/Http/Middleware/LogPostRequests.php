<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPostRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('post') && $request->path() === 'dashboard/create') {
            \Log::info('=== POST TO DASHBOARD/CREATE ===');
            \Log::info('URL: ' . $request->fullUrl());
            \Log::info('User ID: ' . (auth()->check() ? auth()->id() : 'guest'));
            \Log::info('Input data:', $request->all());
            \Log::info('Headers:', $request->headers->all());
        }
        
        return $next($request);
    }
}

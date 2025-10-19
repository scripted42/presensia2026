<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'school.isolation' => \App\Http\Middleware\SchoolIsolationMiddleware::class,
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'security.monitor' => \App\Http\Middleware\SecurityMonitoringMiddleware::class,
            'cors' => \App\Http\Middleware\Cors::class,
        ]);

        // Add CORS middleware for ngrok support
        $middleware->append(\App\Http\Middleware\Cors::class);
        
        // Jalankan monitoring keamanan + audit trail untuk semua request web
        // Catatan: jika ingin membatasi, gunakan group 'web' saja
        $middleware->append(\App\Http\Middleware\SecurityMonitoringMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

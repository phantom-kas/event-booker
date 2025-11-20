<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
         apiPrefix: 'api',
         api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
         $middleware->append(\App\Http\Middleware\ForceJson::class);

        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'role'       => \App\Http\Middleware\RoleMiddleware::class,
            'verified'   => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            
            
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

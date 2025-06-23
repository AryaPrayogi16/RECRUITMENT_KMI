<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',  // Comment ini jika tidak pakai API
        commands: __DIR__.'/../routes/console.php',  // Comment ini jika tidak pakai custom commands
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // PASTIKAN MIDDLEWARE INI TERDAFTAR!
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        
        // Optional: Tambahkan middleware global jika diperlukan
        // $middleware->append(\App\Http\Middleware\SomeGlobalMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })

    ->create();
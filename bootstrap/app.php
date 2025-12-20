<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureTwoFactorAuthenticated;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\VpsAccessMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Add SetLocale to web middleware group
        $middleware->web(append: [
            SetLocale::class,
        ]);
        
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'vps.access' => VpsAccessMiddleware::class,
            'two-factor' => EnsureTwoFactorAuthenticated::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

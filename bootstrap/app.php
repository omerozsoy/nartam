<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'yonetici' => \App\Http\Middleware\YoneticiOl::class,
        ]);
        // Giriş yapmamış kullanıcıları kendi giriş sayfamıza yönlendir ('login' değil).
        $middleware->redirectGuestsTo(fn () => route('giris'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IdentifyRestaurant;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
  ->withMiddleware(function (Middleware $middleware) {

    // تسجيل الـ Aliases هنا بشكل صحيح
    $middleware->alias([
        'restaurant.role' => \App\Http\Middleware\CheckRestaurantRole::class,
        'restaurant.api' => \App\Http\Middleware\VerifyRestaurantApiToken::class,
        'subscribed' => \App\Http\Middleware\CheckSubscription::class,

    ]);

    // استثناءات الـ CSRF تبقى وحدها هنا
    $middleware->validateCsrfTokens(except: [
        'orders',
        'orders/*',
    ]);

})
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

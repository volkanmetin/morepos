<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //$middleware->append();

        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ]);

        $middleware
            ->group('tenant', [
                \Spatie\Multitenancy\Http\Middleware\NeedsTenant::class,
                \Spatie\Multitenancy\Http\Middleware\EnsureValidTenantSession::class,
            ])
            ->web(append: [
                \App\Http\Middleware\SetLocale::class,
                \App\Http\Middleware\CheckAuthRoutes::class,
                \App\Http\Middleware\HandleInertiaRequests::class,
            ]);
    })
    ->withEvents(discover: [
        //__DIR__.'/../app/Listeners',
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

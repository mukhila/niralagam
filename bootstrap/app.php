<?php

use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\SuperAdminMiddleware;
use App\Http\Middleware\TenantRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        // Before SubstituteBindings, so route-bound models are tenant-scoped.
        $middleware->api(prepend: [ResolveTenant::class]);
        $middleware->alias([
            'super_admin' => SuperAdminMiddleware::class,
            'tenant.role' => TenantRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

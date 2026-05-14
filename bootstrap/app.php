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
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\EnsureUserIsActive::class,
        ]);

        $middleware->alias([
            'check2fa'              => \App\Http\Middleware\Check2FA::class,
            'ensure_profile'        => \App\Http\Middleware\EnsureActiveProfile::class,
            'impersonation.timeout' => \App\Http\Middleware\CheckImpersonationTimeout::class,
            'impersonation.block'   => \App\Http\Middleware\BlockDestructiveActionsOnImpersonation::class,
            'role'                  => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission'            => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission'    => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Aplica middlewares de impersonação em toda a stack web
        $middleware->web(append: [
            \App\Http\Middleware\CheckImpersonationTimeout::class,
            \App\Http\Middleware\BlockDestructiveActionsOnImpersonation::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

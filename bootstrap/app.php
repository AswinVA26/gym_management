<?php

use App\Http\Middleware\Ability;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\RequireTenant;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TenantSetup;
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
            'tenant' => TenantSetup::class,
            'role' => EnsureRole::class,
            'ability' => Ability::class,
            'require.tenant' => RequireTenant::class,
        ]);

        $middleware->append(SecurityHeaders::class);

        $middleware->trustProxies(at: '*');

        $middleware->trustHosts(at: function () {
            $configured = parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost';

            return array_values(array_unique([
                'localhost',
                '127.0.0.1',
                $configured,
                request()->getHost(),
            ]));
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

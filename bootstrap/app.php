<?php

declare(strict_types=1);

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\PreventReplay;
use App\Support\Api\ApiExceptionRenderer;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            // Public landing page at '/'. Defined HERE (not in web.php) so it
            // survives a Breeze reinstall that overwrites routes/web.php.
            // Content is fully editable from Admin → Site Settings.
            Route::middleware('web')
                ->get('/', [\App\Http\Controllers\HomeController::class, 'index'])
                ->name('home');

            // Admin panel (Phase 3–6) under /admin with 'admin.' name prefix.
            Route::middleware('web')
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Named middleware aliases used by the license API (Phase 4–5).
        $middleware->alias([
            'api.json'   => ForceJsonResponse::class,
            'api.replay' => PreventReplay::class,
        ]);

        // Where to send guests hitting an 'auth' route, and where to send
        // already-authenticated users hitting a 'guest' route.
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Render domain/API exceptions as the canonical JSON envelope.
        $exceptions->render(function (\Throwable $e, Request $request) {
            return ApiExceptionRenderer::render($e, $request);
        });
    })
    ->create();

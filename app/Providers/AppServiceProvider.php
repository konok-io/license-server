<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Per-IP throttle for the client-facing license API (Phase 4–5).
        RateLimiter::for('license-api', function (Request $request): Limit {
            return Limit::perMinute(
                (int) config('license.rate_limit.per_minute', 60)
            )->by($request->ip());
        });
    }
}

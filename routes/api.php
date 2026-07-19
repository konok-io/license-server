<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\ActivationController;
use App\Http\Controllers\Api\V1\DomainCheckController;
use App\Http\Controllers\Api\V1\InstallationCheckController;
use App\Http\Controllers\Api\V1\ResetController;
use App\Http\Controllers\Api\V1\VerificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Client-facing License API (v1)
|--------------------------------------------------------------------------
| Consumed by ERP installations. Stateless, key-authenticated, JSON only.
|
| Middleware applied (see bootstrap/app.php withRouting/withMiddleware):
|   - 'api.json'      → App\Http\Middleware\ForceJsonResponse
|   - 'api.replay'    → App\Http\Middleware\PreventReplay
|   - throttle        → per-IP rate limiting
*/

Route::prefix('v1')
    ->middleware(['api.json', 'api.replay', 'throttle:license-api'])
    ->group(function (): void {
        Route::post('activate', ActivationController::class)->name('api.activate');
        Route::post('verify', VerificationController::class)->name('api.verify');
        Route::post('check-domain', DomainCheckController::class)->name('api.check-domain');
        Route::post('check-installation', InstallationCheckController::class)->name('api.check-installation');
        Route::post('reset', ResetController::class)->name('api.reset');
    });

// Convenience aliases without the /v1 prefix (as specified in the brief).
Route::middleware(['api.json', 'api.replay', 'throttle:license-api'])
    ->group(function (): void {
        Route::post('activate', ActivationController::class);
        Route::post('verify', VerificationController::class);
        Route::post('check-domain', DomainCheckController::class);
        Route::post('check-installation', InstallationCheckController::class);
        Route::post('reset', ResetController::class);
    });

<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ActivationLogController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\BlacklistController;
use App\Http\Controllers\Admin\ControlController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\LicenseResetController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\VerificationLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Panel Routes (Phases 3–6)
|--------------------------------------------------------------------------
| Loaded from bootstrap/app.php under the 'admin' prefix + 'admin.' name,
| wrapped in the 'web' middleware group. All routes require authentication.
*/

Route::middleware(['auth'])->group(function (): void {

    // Landing → analytics dashboard.
    Route::get('/', fn () => redirect()->route('admin.analytics.index'))->name('dashboard');

    /* ---------- Site Settings (public homepage content) ---------- */
    Route::get('settings', [SiteSettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SiteSettingController::class, 'update'])->name('settings.update');

    /* ---------- Customers ---------- */
    Route::get('customers/data', [CustomerController::class, 'data'])->name('customers.data');
    Route::resource('customers', CustomerController::class)->except(['create', 'edit']);

    /* ---------- Licenses ---------- */
    Route::get('licenses/data', [LicenseController::class, 'data'])->name('licenses.data');
    Route::post('licenses/{license}/kill', [LicenseController::class, 'kill'])->name('licenses.kill');
    Route::post('licenses/{license}/reactivate', [LicenseController::class, 'reactivate'])->name('licenses.reactivate');
    Route::post('licenses/{license}/suspend', [LicenseController::class, 'suspend'])->name('licenses.suspend');
    Route::resource('licenses', LicenseController::class)->except(['create', 'edit']);

    /* ---------- License Resets ---------- */
    Route::get('resets/data', [LicenseResetController::class, 'data'])->name('resets.data');
    Route::get('resets', [LicenseResetController::class, 'index'])->name('resets.index');
    Route::post('licenses/{license}/reset', [LicenseResetController::class, 'store'])->name('licenses.reset');

    /* ---------- Blacklist ---------- */
    Route::get('blacklists/data', [BlacklistController::class, 'data'])->name('blacklists.data');
    Route::post('blacklists/{blacklist}/lift', [BlacklistController::class, 'lift'])->name('blacklists.lift');
    Route::resource('blacklists', BlacklistController::class)->only(['index', 'store', 'destroy']);

    /* ---------- Activation Logs ---------- */
    Route::get('activation-logs/data', [ActivationLogController::class, 'data'])->name('activation-logs.data');
    Route::get('activation-logs', [ActivationLogController::class, 'index'])->name('activation-logs.index');

    /* ---------- Verification Logs ---------- */
    Route::get('verification-logs/data', [VerificationLogController::class, 'data'])->name('verification-logs.data');
    Route::get('verification-logs', [VerificationLogController::class, 'index'])->name('verification-logs.index');

    /* ---------- Audit Logs ---------- */
    Route::get('audit-logs/data', [AuditLogController::class, 'data'])->name('audit-logs.data');
    Route::get('audit-logs/verify-chain', [AuditLogController::class, 'verifyChain'])->name('audit-logs.verify-chain');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

    /* ---------- Control / Enforcement (Phase 6) ---------- */
    Route::prefix('control')->name('control.')->group(function (): void {
        Route::post('licenses/{license}/kill', [ControlController::class, 'killLicense'])->name('licenses.kill');
        Route::post('licenses/{license}/revive', [ControlController::class, 'reviveLicense'])->name('licenses.revive');
        Route::post('licenses/{license}/suspend', [ControlController::class, 'suspendLicense'])->name('licenses.suspend');
        Route::post('licenses/{license}/resume', [ControlController::class, 'resumeLicense'])->name('licenses.resume');
        Route::post('licenses/{license}/disable-domain', [ControlController::class, 'disableDomain'])->name('licenses.disable-domain');
        Route::post('licenses/{license}/disable-installation', [ControlController::class, 'disableInstallation'])->name('licenses.disable-installation');
        Route::post('customers/{customer}/disable', [ControlController::class, 'disableCustomer'])->name('customers.disable');
        Route::post('customers/{customer}/enable', [ControlController::class, 'enableCustomer'])->name('customers.enable');
    });

    /* ---------- Analytics (Phase 6) ---------- */
    Route::prefix('analytics')->name('analytics.')->group(function (): void {
        Route::get('/', [AnalyticsController::class, 'index'])->name('index');
        Route::get('stats', [AnalyticsController::class, 'stats'])->name('stats');
        Route::get('charts', [AnalyticsController::class, 'charts'])->name('charts');
        Route::get('widgets', [AnalyticsController::class, 'widgets'])->name('widgets');
        Route::get('reports/activations', [AnalyticsController::class, 'activationReport'])->name('reports.activations');
        Route::get('reports/verifications', [AnalyticsController::class, 'verificationReport'])->name('reports.verifications');
    });
});

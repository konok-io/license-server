<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckInstallationRequest;
use App\Services\Api\LicenseValidationService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/v1/check-installation
 * Confirms an installation is still bound and valid (daily verification-lite).
 */
class InstallationCheckController extends Controller
{
    public function __construct(
        private readonly LicenseValidationService $validator,
    ) {
    }

    public function __invoke(CheckInstallationRequest $request): JsonResponse
    {
        $installationId = $request->string('installation_id')->toString();

        $license = $this->validator->resolveByKey($request->string('license_key')->toString());

        $this->validator->assertUsable($license);
        $this->validator->assertNotBlacklisted($license, $installationId, null, $request->ip());
        $activation = $this->validator->assertInstallationBound($license, $installationId);

        // Touch the heartbeat timestamp.
        $activation->forceFill(['last_verified_at' => now()])->save();
        $license->forceFill(['last_verified_at' => now()])->save();

        return ApiResponse::success([
            'installation_valid' => true,
            'installation_id'    => $installationId,
            'status'             => $activation->status->value,
            'expires_at'         => $license->expires_at?->toIso8601String(),
            'last_verified_at'   => $activation->last_verified_at?->toIso8601String(),
        ]);
    }
}

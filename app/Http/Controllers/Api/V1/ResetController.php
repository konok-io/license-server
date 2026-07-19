<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ResetRequest;
use App\Services\Api\ApiResetService;
use App\Services\Api\LicenseValidationService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/v1/reset
 * Releases one (or all) installation binding(s) so the license can move hosts.
 */
class ResetController extends Controller
{
    public function __construct(
        private readonly LicenseValidationService $validator,
        private readonly ApiResetService $reset,
    ) {
    }

    public function __invoke(ResetRequest $request): JsonResponse
    {
        $license = $this->validator->resolveByKey($request->string('license_key')->toString());

        // A killed license cannot be self-reset by the client.
        $this->validator->assertUsable($license);

        $result = $this->reset->reset($license, [
            'installation_id' => $request->input('installation_id'),
            'reason'          => $request->input('reason'),
            'ip'              => $request->ip(),
        ]);

        return ApiResponse::success([
            'reset'                => true,
            'activations_cleared'  => $result['cleared'],
            'remaining_activations'=> $license->fresh()->activation_count,
        ]);
    }
}

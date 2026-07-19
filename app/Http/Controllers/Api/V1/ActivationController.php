<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ActivateRequest;
use App\Services\Api\ActivationService;
use App\Services\Api\LicenseValidationService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/v1/activate
 * Binds an installation to a license and returns a signed activation grant.
 */
class ActivationController extends Controller
{
    public function __construct(
        private readonly LicenseValidationService $validator,
        private readonly ActivationService $activation,
    ) {
    }

    public function __invoke(ActivateRequest $request): JsonResponse
    {
        $license = $this->validator->resolveByKey($request->string('license_key')->toString());

        $result = $this->activation->activate($license, [
            'installation_id' => $request->string('installation_id')->toString(),
            'domain'          => $request->input('domain'),
            'server_type'     => $request->input('server_type'),
            'fingerprint'     => $request->input('fingerprint'),
            'os_info'         => $request->input('os_info'),
            'ip'              => $request->ip(),
            'user_agent'      => $request->userAgent(),
        ]);

        return ApiResponse::success(
            data: [
                'activated'       => true,
                'license_uuid'    => $result['license']->uuid,
                'installation_id' => $result['activation']->installation_id,
                'grant'           => $result['payload'],
            ],
            signature: $result['signature'],
            status: 201,
        );
    }
}

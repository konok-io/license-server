<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckDomainRequest;
use App\Services\Api\LicenseValidationService;
use App\Support\Api\ApiResponse;
use App\Support\DomainNormalizer;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/v1/check-domain
 * Confirms a domain is permitted for the license without consuming a slot.
 */
class DomainCheckController extends Controller
{
    public function __construct(
        private readonly LicenseValidationService $validator,
    ) {
    }

    public function __invoke(CheckDomainRequest $request): JsonResponse
    {
        $license = $this->validator->resolveByKey($request->string('license_key')->toString());
        $normalized = DomainNormalizer::normalize($request->string('domain')->toString());

        $this->validator->assertUsable($license);
        $this->validator->assertNotBlacklisted($license, null, $normalized, $request->ip());
        $this->validator->assertServerTypeAllowed($license, $request->input('server_type'));
        $this->validator->assertDomainAllowed($license, $request->string('domain')->toString());

        return ApiResponse::success([
            'domain_allowed' => true,
            'domain'         => $normalized,
            'license_type'   => $license->type->value,
        ]);
    }
}

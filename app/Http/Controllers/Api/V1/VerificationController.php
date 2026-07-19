<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\VerifyRequest;
use App\Services\Api\LicenseValidationService;
use App\Services\Api\VerificationService;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * POST /api/v1/verify
 *
 * Daily verification heartbeat. Returns an RSA-4096 SIGNED verdict the client
 * can trust offline. The verdict's `action` tells the ERP whether to continue,
 * enter grace, expire, re-activate, or shut down (remote kill).
 *
 * Even "negative" verdicts (kill / expire / deny) return a signed 200-style
 * envelope body with the appropriate HTTP status, so the client can verify the
 * signature before acting — an unsigned 403 could be a spoof and must be
 * treated as "keep last known good state until next successful verify".
 */
class VerificationController extends Controller
{
    public function __construct(
        private readonly LicenseValidationService $validator,
        private readonly VerificationService $verification,
    ) {
    }

    public function __invoke(VerifyRequest $request): JsonResponse
    {
        // resolveByKey throws LICENSE_NOT_FOUND (rendered by the global handler)
        // when the key is unknown — that response is intentionally unsigned.
        $license = $this->validator->resolveByKey($request->string('license_key')->toString());

        $result = $this->verification->verify($license, [
            'installation_id' => $request->string('installation_id')->toString(),
            'domain'          => $request->input('domain'),
            'nonce'           => $request->input('nonce'),
            'ip'              => $request->ip(),
            'user_agent'      => $request->userAgent(),
            'request'         => $request->only(['installation_id', 'domain', 'nonce']),
        ]);

        return ApiResponse::success(
            data: [
                'verified' => true,
                'verdict'  => $result['payload'],
            ],
            signature: $result['signature'],
            status: $result['http_status'],
        );
    }
}

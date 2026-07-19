<?php

declare(strict_types=1);

namespace App\Support\Api;

use App\Enums\ApiErrorCode;
use Illuminate\Http\JsonResponse;

/**
 * Canonical API envelope for every client-facing response:
 * {
 *   "success": bool,
 *   "data": {...}|null,
 *   "error": { "code": "...", "message": "...", "details": {...} }|null,
 *   "signature": "base64"|null,
 *   "server_time": "ISO-8601"
 * }
 */
class ApiResponse
{
    public static function success(array $data = [], ?string $signature = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success'     => true,
            'data'        => $data,
            'error'       => null,
            'signature'   => $signature,
            'server_time' => now()->toIso8601String(),
        ], $status);
    }

    public static function error(
        ApiErrorCode $code,
        string $message,
        array $details = [],
        ?int $status = null,
    ): JsonResponse {
        return response()->json([
            'success'     => false,
            'data'        => null,
            'error'       => [
                'code'    => $code->value,
                'message' => $message,
                'details' => $details === [] ? null : $details,
            ],
            'signature'   => null,
            'server_time' => now()->toIso8601String(),
        ], $status ?? $code->httpStatus());
    }
}

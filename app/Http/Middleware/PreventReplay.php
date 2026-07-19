<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\ApiErrorCode;
use App\Support\Api\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Replay protection. Clients send an X-Nonce header (and optionally X-Timestamp).
 * A nonce may be used once within the TTL window; reuse is rejected.
 * Requests without a nonce pass through (nonce is opt-in per client capability).
 */
class PreventReplay
{
    private const TTL_SECONDS = 300; // 5-minute window

    public function handle(Request $request, \Closure $next): Response
    {
        $nonce = $request->header('X-Nonce');

        if ($nonce === null || $nonce === '') {
            return $next($request);
        }

        // Optional timestamp skew check.
        $timestamp = $request->header('X-Timestamp');
        if ($timestamp !== null && abs(time() - (int) $timestamp) > self::TTL_SECONDS) {
            return ApiResponse::error(
                ApiErrorCode::ReplayDetected,
                'Request timestamp is outside the allowed window.',
            );
        }

        $cacheKey = 'api:nonce:' . hash('sha256', $nonce);

        if (Cache::has($cacheKey)) {
            return ApiResponse::error(
                ApiErrorCode::ReplayDetected,
                'This request has already been processed.',
            );
        }

        Cache::put($cacheKey, true, self::TTL_SECONDS);

        return $next($request);
    }
}

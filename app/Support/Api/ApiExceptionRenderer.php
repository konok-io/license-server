<?php

declare(strict_types=1);

namespace App\Support\Api;

use App\Enums\ApiErrorCode;
use App\Exceptions\Api\LicenseApiException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Central mapping of exceptions → canonical API envelopes.
 * Registered in bootstrap/app.php (see PHASE4_INTEGRATION.md).
 */
class ApiExceptionRenderer
{
    public static function render(\Throwable $e, Request $request): ?JsonResponse
    {
        // Only intervene for API traffic.
        if (! $request->is('api/*')) {
            return null;
        }

        if ($e instanceof LicenseApiException) {
            return $e->render();
        }

        if ($e instanceof ValidationException) {
            return ApiResponse::error(
                ApiErrorCode::ValidationFailed,
                'The request payload is invalid.',
                $e->errors(),
            );
        }

        if ($e instanceof AuthenticationException) {
            return ApiResponse::error(
                ApiErrorCode::Unauthorized,
                'Authentication is required.',
            );
        }

        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();

            $code = match ($status) {
                404     => ApiErrorCode::LicenseNotFound,
                429     => ApiErrorCode::RateLimited,
                401     => ApiErrorCode::Unauthorized,
                default => ApiErrorCode::ServerError,
            };

            return ApiResponse::error(
                $code,
                $status === 404 ? 'The requested resource was not found.'
                    : ($status === 429 ? 'Too many requests. Please slow down.'
                    : 'The request could not be processed.'),
                status: $status,
            );
        }

        // Unhandled — return a generic 500 without leaking internals.
        return ApiResponse::error(
            ApiErrorCode::ServerError,
            'An unexpected error occurred. Please try again later.',
            details: config('app.debug') ? ['exception' => $e->getMessage()] : [],
        );
    }
}

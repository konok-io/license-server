<?php

declare(strict_types=1);

namespace App\Exceptions\Api;

use App\Enums\ApiErrorCode;
use App\Support\Api\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * Domain-level API failure. Services throw this; it renders itself into the
 * canonical error envelope so controllers stay thin.
 */
class LicenseApiException extends \RuntimeException
{
    /** @param array<string, mixed> $details */
    public function __construct(
        public readonly ApiErrorCode $errorCode,
        string $message,
        public readonly array $details = [],
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, $errorCode->httpStatus(), $previous);
    }

    /** @param array<string, mixed> $details */
    public static function make(ApiErrorCode $code, string $message, array $details = []): self
    {
        return new self($code, $message, $details);
    }

    public function render(): JsonResponse
    {
        return ApiResponse::error($this->errorCode, $this->getMessage(), $this->details);
    }
}

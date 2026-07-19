<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ApiErrorCode;
use App\Support\Api\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Key-authenticated via body + API middleware.
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'license_key'     => ['required', 'string', 'max:255'],
            'installation_id' => ['required', 'string', 'max:191'],
            'domain'          => ['nullable', 'string', 'max:255'],
            'nonce'           => ['nullable', 'string', 'max:64'],
            'signature'       => ['nullable', 'string'], // optional client-signed request
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                ApiErrorCode::ValidationFailed,
                'The request payload is invalid.',
                $validator->errors()->toArray(),
            )
        );
    }
}

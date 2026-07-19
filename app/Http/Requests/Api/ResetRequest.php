<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ApiErrorCode;
use App\Support\Api\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'license_key'     => ['required', 'string', 'max:255'],
            // Omit installation_id to reset ALL installations for the license.
            'installation_id' => ['nullable', 'string', 'max:191'],
            'reason'          => ['nullable', 'string', 'max:500'],
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

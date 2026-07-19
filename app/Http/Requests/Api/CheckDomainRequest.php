<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use App\Enums\ApiErrorCode;
use App\Support\Api\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'license_key' => ['required', 'string', 'max:255'],
            'domain'      => ['required', 'string', 'max:255'],
            'server_type' => ['nullable', 'string', 'in:localhost,domain,vps'],
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

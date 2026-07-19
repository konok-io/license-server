<?php

declare(strict_types=1);

namespace App\Http\Requests\License;

use App\Enums\LicenseStatus;
use App\Enums\LicenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('license')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type'                        => ['required', new Enum(LicenseType::class)],
            'status'                      => ['required', new Enum(LicenseStatus::class)],
            'max_activations'             => ['required', 'integer', 'min:1', 'max:1000'],
            'grace_days'                  => ['required', 'integer', 'min:0', 'max:90'],
            'verification_interval_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'version'                     => ['nullable', 'string', 'max:20'],
            'starts_at'                   => ['nullable', 'date'],
            'expires_at'                  => ['nullable', 'date'],
            'features'                    => ['nullable', 'array'],
        ];
    }
}

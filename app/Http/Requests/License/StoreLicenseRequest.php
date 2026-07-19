<?php

declare(strict_types=1);

namespace App\Http\Requests\License;

use App\Enums\LicenseType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\License::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'customer_id'                 => ['required', 'integer', Rule::exists('customers', 'id')],
            'product'                     => ['required', 'string', 'max:100'],
            'version'                     => ['nullable', 'string', 'max:20'],
            'type'                        => ['required', new Enum(LicenseType::class)],
            'max_activations'             => ['required', 'integer', 'min:1', 'max:1000'],
            'grace_days'                  => ['required', 'integer', 'min:0', 'max:90'],
            'verification_interval_hours' => ['required', 'integer', 'min:1', 'max:720'],
            'starts_at'                   => ['nullable', 'date'],
            'expires_at'                  => ['nullable', 'date', 'after:starts_at'],
            'features'                    => ['nullable', 'array'],
        ];
    }
}

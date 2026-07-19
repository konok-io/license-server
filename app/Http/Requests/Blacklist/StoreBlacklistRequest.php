<?php

declare(strict_types=1);

namespace App\Http\Requests\Blacklist;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlacklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\LicenseBlacklist::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'license_id'      => ['nullable', 'integer', Rule::exists('licenses', 'id')],
            'installation_id' => ['nullable', 'string', 'max:191'],
            'domain'          => ['nullable', 'string', 'max:255'],
            'ip_address'      => ['nullable', 'ip'],
            'reason'          => ['required', 'string', 'max:500'],
            'kill_license'    => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['kill_license' => $this->boolean('kill_license')]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            // At least one targeting field must be present.
            $hasTarget = collect(['license_id', 'installation_id', 'domain', 'ip_address'])
                ->contains(fn (string $field): bool => $this->filled($field));

            if (! $hasTarget) {
                $validator->errors()->add(
                    'reason',
                    'At least one target (license, installation, domain, or IP) is required.'
                );
            }
        });
    }
}

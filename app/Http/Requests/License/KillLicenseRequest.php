<?php

declare(strict_types=1);

namespace App\Http\Requests\License;

use Illuminate\Foundation\Http\FormRequest;

class KillLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kill', $this->route('license')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}

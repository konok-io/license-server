<?php

declare(strict_types=1);

namespace App\Http\Requests\Control;

use Illuminate\Foundation\Http\FormRequest;

class DisableDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('kill', $this->route('license')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'domain' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Requests\Reset;

use Illuminate\Foundation\Http\FormRequest;

class StoreResetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reset', $this->route('license')) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}

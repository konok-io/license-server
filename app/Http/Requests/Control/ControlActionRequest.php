<?php

declare(strict_types=1);

namespace App\Http\Requests\Control;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Shared request for simple control actions (kill / suspend / disable customer)
 * that only need an optional reason. Authorization is checked per-route in the
 * controller via the appropriate policy ability.
 */
class ControlActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'reason'      => ['nullable', 'string', 'max:500'],
            'blacklist'   => ['sometimes', 'boolean'],
        ];
    }
}

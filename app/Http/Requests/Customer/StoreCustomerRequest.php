<?php

declare(strict_types=1);

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\Customer::class) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'company'   => ['nullable', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('customers', 'email')],
            'phone'     => ['nullable', 'string', 'max:30'],
            'country'   => ['required', 'string', 'size:2'],
            'is_active' => ['boolean'],
            'notes'     => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'country'   => strtoupper((string) $this->input('country', 'SA')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}

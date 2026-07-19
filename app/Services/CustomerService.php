<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function __construct(
        private readonly CustomerRepositoryInterface $customers,
    ) {
    }

    public function create(array $data): Customer
    {
        return DB::transaction(fn (): Customer => $this->customers->create($data));
    }

    public function update(Customer $customer, array $data): Customer
    {
        return DB::transaction(fn (): Customer => $this->customers->update($customer, $data));
    }

    public function delete(Customer $customer): bool
    {
        return DB::transaction(fn (): bool => $this->customers->delete($customer));
    }

    public function toggleActive(Customer $customer): Customer
    {
        return $this->customers->update($customer, ['is_active' => ! $customer->is_active]);
    }
}

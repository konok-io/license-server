<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;

/**
 * @extends BaseRepository<Customer>
 */
class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    public function __construct(Customer $model)
    {
        parent::__construct($model);
    }

    public function activeCount(): int
    {
        return $this->model->newQuery()->where('is_active', true)->count();
    }
}

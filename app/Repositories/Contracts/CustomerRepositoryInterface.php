<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

/**
 * @extends RepositoryInterface<\App\Models\Customer>
 */
interface CustomerRepositoryInterface extends RepositoryInterface
{
    public function activeCount(): int;
}

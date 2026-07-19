<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\License;

/**
 * @extends RepositoryInterface<\App\Models\License>
 */
interface LicenseRepositoryInterface extends RepositoryInterface
{
    public function findByKeyHash(string $hash): ?License;

    public function findByUuid(string $uuid): ?License;

    /** @return array<string, int> */
    public function statusCounts(): array;
}

<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\License;
use App\Repositories\Contracts\LicenseRepositoryInterface;

/**
 * @extends BaseRepository<License>
 */
class LicenseRepository extends BaseRepository implements LicenseRepositoryInterface
{
    public function __construct(License $model)
    {
        parent::__construct($model);
    }

    public function findByKeyHash(string $hash): ?License
    {
        return $this->model->newQuery()->where('license_key_hash', $hash)->first();
    }

    public function findByUuid(string $uuid): ?License
    {
        return $this->model->newQuery()->where('uuid', $uuid)->first();
    }

    /** @return array<string, int> */
    public function statusCounts(): array
    {
        return $this->model->newQuery()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->toArray();
    }
}

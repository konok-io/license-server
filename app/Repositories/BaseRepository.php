<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @implements RepositoryInterface<TModel>
 */
abstract class BaseRepository implements RepositoryInterface
{
    /** @param TModel $model */
    public function __construct(protected Model $model)
    {
    }

    /** @return \Illuminate\Database\Eloquent\Builder<TModel> */
    public function query()
    {
        return $this->model->newQuery();
    }

    /** @return Collection<int, TModel> */
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->newQuery()->get($columns);
    }

    /** @return LengthAwarePaginator<TModel> */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->newQuery()->latest()->paginate($perPage, $columns);
    }

    /** @return TModel|null */
    public function find(int|string $id): ?Model
    {
        return $this->model->newQuery()->find($id);
    }

    /** @return TModel */
    public function findOrFail(int|string $id): Model
    {
        return $this->model->newQuery()->findOrFail($id);
    }

    /** @return TModel */
    public function create(array $attributes): Model
    {
        return $this->model->newQuery()->create($attributes);
    }

    /**
     * @param TModel $model
     * @return TModel
     */
    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes)->save();

        return $model->refresh();
    }

    /** @param TModel $model */
    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }
}

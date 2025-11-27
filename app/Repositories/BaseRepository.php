<?php

namespace App\Repositories;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        $query = $this->model->select($columns);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    public function find(string|int $id, array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->model->select($columns);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->find($id);
    }

    public function findOrFail(string|int $id, array $columns = ['*'], array $relations = []): Model
    {
        $query = $this->model->select($columns);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(string|int $id, array $data): bool
    {
        $record = $this->model->findOrFail($id);

        return $record->update($data);
    }

    public function delete(string|int $id): bool
    {
        $record = $this->model->findOrFail($id);

        return $record->delete();
    }

    public function findBy(array $criteria, array $columns = ['*'], array $relations = []): Collection
    {
        $query = $this->model->select($columns);

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    public function findOneBy(array $criteria, array $columns = ['*'], array $relations = []): ?Model
    {
        $query = $this->model->select($columns);

        foreach ($criteria as $key => $value) {
            $query->where($key, $value);
        }

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = [])
    {
        $query = $this->model->select($columns);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->paginate($perPage);
    }

    public function count(): int
    {
        return $this->model->count();
    }
}

<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get all records
     *
     * @param  array  $columns  Columns to select (default: ['*'])
     * @param  array  $relations  Relationships to eager load
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Find a record by ID
     *
     * @param  array  $columns  Columns to select (default: ['*'])
     * @param  array  $relations  Relationships to eager load
     */
    public function find(int $id, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Find a record by ID or fail
     *
     * @param  array  $columns  Columns to select (default: ['*'])
     * @param  array  $relations  Relationships to eager load
     */
    public function findOrFail(int $id, array $columns = ['*'], array $relations = []): Model;

    /**
     * Create a new record
     */
    public function create(array $data): Model;

    /**
     * Update a record
     */
    public function update(int $id, array $data): bool;

    /**
     * Delete a record
     */
    public function delete(int $id): bool;

    /**
     * Find records by multiple criteria
     *
     * @param  array  $columns  Columns to select (default: ['*'])
     * @param  array  $relations  Relationships to eager load
     */
    public function findBy(array $criteria, array $columns = ['*'], array $relations = []): Collection;

    /**
     * Find one record by criteria
     *
     * @param  array  $columns  Columns to select (default: ['*'])
     * @param  array  $relations  Relationships to eager load
     */
    public function findOneBy(array $criteria, array $columns = ['*'], array $relations = []): ?Model;

    /**
     * Get paginated records
     *
     * @param  array  $columns  Columns to select (default: ['*'])
     * @param  array  $relations  Relationships to eager load
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []);

    /**
     * Count all records
     */
    public function count(): int;
}

<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user by email
     *
     * @param  array  $columns  Columns to select (default: ['*'])
     * @param  array  $relations  Relationships to eager load
     */
    public function findByEmail(string $email, array $columns = ['*'], array $relations = []): ?User;
}

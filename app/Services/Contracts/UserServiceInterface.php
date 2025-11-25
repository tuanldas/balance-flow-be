<?php

namespace App\Services\Contracts;

use App\Models\User;

interface UserServiceInterface extends BaseServiceInterface
{
    /**
     * Get user by email
     *
     * @param  array  $columns  Columns to select (default: ['*'])
     * @param  array  $relations  Relationships to eager load
     */
    public function getUserByEmail(string $email, array $columns = ['*'], array $relations = []): ?User;

    /**
     * Create a new user with hashed password
     */
    public function createUser(array $data): User;
}

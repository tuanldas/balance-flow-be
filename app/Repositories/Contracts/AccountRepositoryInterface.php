<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface AccountRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all accounts for a user
     */
    public function getAllForUser(
        string $userId,
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * Get only active accounts for a user
     */
    public function getActiveForUser(
        string $userId,
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * Get paginated accounts for a user
     */
    public function paginateForUser(
        string $userId,
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): mixed;

    /**
     * Find account for a specific user
     */
    public function findForUser(
        string $id,
        string $userId,
        array $columns = ['*'],
        array $relations = []
    ): ?Model;

    /**
     * Get accounts by account type for a user
     */
    public function getByAccountType(
        string $accountTypeId,
        string $userId,
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * Get total balance for a user
     */
    public function getTotalBalance(string $userId, ?string $currency = null): float;

    /**
     * Update account balance
     */
    public function updateBalance(string $id, float $amount, string $operation = 'set'): bool;
}

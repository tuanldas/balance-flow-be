<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface TransactionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get paginated transactions for a user
     */
    public function getPaginatedForUser(
        string $userId,
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = [],
        ?string $sortBy = 'transaction_date',
        string $sortDirection = 'desc',
        array $filters = []
    ): mixed;

    /**
     * Get transactions for a user by date range
     */
    public function getByDateRange(
        string $userId,
        string $startDate,
        string $endDate,
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * Get transactions for a user by category
     */
    public function getByCategory(
        string $userId,
        string $categoryId,
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): mixed;

    /**
     * Get total amount for user by type (income/expense)
     */
    public function getTotalByType(
        string $userId,
        string $type,
        ?string $startDate = null,
        ?string $endDate = null
    ): float;
}

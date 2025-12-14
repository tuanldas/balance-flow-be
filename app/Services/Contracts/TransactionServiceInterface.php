<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface TransactionServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated transactions for a user
     */
    public function getPaginatedForUser(
        string $userId,
        int $perPage = 15,
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
        string $endDate
    ): Collection;

    /**
     * Get transactions for a user by category
     */
    public function getByCategory(
        string $userId,
        string $categoryId,
        int $perPage = 15
    ): mixed;

    /**
     * Create a transaction for a user
     */
    public function createTransaction(string $userId, array $data): Model;

    /**
     * Update a user's transaction
     */
    public function updateTransaction(string $userId, string $id, array $data): bool;

    /**
     * Delete a user's transaction
     */
    public function deleteTransaction(string $userId, string $id): bool;

    /**
     * Get transaction summary for a user
     */
    public function getSummary(
        string $userId,
        ?string $startDate = null,
        ?string $endDate = null
    ): array;
}

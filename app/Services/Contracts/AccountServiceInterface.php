<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface AccountServiceInterface extends BaseServiceInterface
{
    /**
     * Get all accounts for a user
     */
    public function getAllForUser(string $userId): Collection;

    /**
     * Get only active accounts for a user
     */
    public function getActiveForUser(string $userId): Collection;

    /**
     * Get paginated accounts for a user
     */
    public function getPaginatedForUser(string $userId, int $perPage = 15): mixed;

    /**
     * Find account for a specific user
     */
    public function findForUser(string $id, string $userId): ?Model;

    /**
     * Create account for a user
     */
    public function createForUser(array $data, string $userId): Model;

    /**
     * Update account for a user
     */
    public function updateForUser(string $id, array $data, string $userId): bool;

    /**
     * Delete account for a user
     */
    public function deleteForUser(string $id, string $userId): bool;

    /**
     * Get accounts by account type for a user
     */
    public function getByAccountType(string $accountTypeId, string $userId): Collection;

    /**
     * Get total balance for a user
     */
    public function getTotalBalance(string $userId, ?string $currency = null): array;

    /**
     * Update account balance
     */
    public function updateBalance(string $id, float $amount, string $operation, ?string $userId = null): bool;

    /**
     * Toggle account active status
     */
    public function toggleActiveStatus(string $id, string $userId): bool;
}

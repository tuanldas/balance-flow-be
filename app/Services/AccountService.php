<?php

namespace App\Services;

use App\Repositories\Contracts\AccountRepositoryInterface;
use App\Services\Contracts\AccountServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AccountService extends BaseService implements AccountServiceInterface
{
    protected AccountRepositoryInterface $accountRepository;

    public function __construct(AccountRepositoryInterface $accountRepository)
    {
        $this->accountRepository = $accountRepository;
        parent::__construct($accountRepository);
    }

    /**
     * Get all accounts for a user
     */
    public function getAllForUser(string $userId): Collection
    {
        return $this->accountRepository->getAllForUser($userId, ['*'], ['accountType']);
    }

    /**
     * Get only active accounts for a user
     */
    public function getActiveForUser(string $userId): Collection
    {
        return $this->accountRepository->getActiveForUser($userId, ['*'], ['accountType']);
    }

    /**
     * Get paginated accounts for a user
     */
    public function getPaginatedForUser(string $userId, int $perPage = 15): mixed
    {
        return $this->accountRepository->paginateForUser($userId, $perPage, ['*'], ['accountType']);
    }

    /**
     * Find account for a specific user
     */
    public function findForUser(string $id, string $userId): ?Model
    {
        return $this->accountRepository->findForUser($id, $userId, ['*'], ['accountType']);
    }

    /**
     * Create account for a user
     */
    public function createForUser(array $data, string $userId): Model
    {
        $data['user_id'] = $userId;

        // Set default values
        if (! isset($data['balance'])) {
            $data['balance'] = 0;
        }

        if (! isset($data['currency'])) {
            $data['currency'] = 'VND';
        }

        if (! isset($data['is_active'])) {
            $data['is_active'] = true;
        }

        $account = $this->accountRepository->create($data);

        // Reload with accountType relationship
        return $this->findForUser($account->id, $userId);
    }

    /**
     * Update account for a user
     * Note: Balance cannot be updated directly via this method (security)
     */
    public function updateForUser(string $id, array $data, string $userId): bool
    {
        $account = $this->accountRepository->findForUser($id, $userId);

        if (! $account) {
            return false;
        }

        // Remove balance and user_id from update data (security)
        unset($data['balance'], $data['user_id']);

        return $this->accountRepository->update($id, $data);
    }

    /**
     * Delete account for a user
     */
    public function deleteForUser(string $id, string $userId): bool
    {
        $account = $this->accountRepository->findForUser($id, $userId);

        if (! $account) {
            return false;
        }

        // Check if account has transactions
        if ($account->transactions()->exists()) {
            return false;
        }

        return $this->accountRepository->delete($id);
    }

    /**
     * Get accounts by account type for a user
     */
    public function getByAccountType(string $accountTypeId, string $userId): Collection
    {
        return $this->accountRepository->getByAccountType($accountTypeId, $userId, ['*'], ['accountType']);
    }

    /**
     * Get total balance for a user
     */
    public function getTotalBalance(string $userId, ?string $currency = null): array
    {
        $currency = $currency ?? 'VND';

        return [
            'total_balance' => $this->accountRepository->getTotalBalance($userId, $currency),
            'currency' => $currency,
        ];
    }

    /**
     * Update account balance
     */
    public function updateBalance(string $id, float $amount, string $operation, ?string $userId = null): bool
    {
        // If userId provided, verify ownership
        if ($userId) {
            $account = $this->accountRepository->findForUser($id, $userId);

            if (! $account) {
                return false;
            }
        }

        return $this->accountRepository->updateBalance($id, $amount, $operation);
    }

    /**
     * Toggle account active status
     */
    public function toggleActiveStatus(string $id, string $userId): bool
    {
        $account = $this->accountRepository->findForUser($id, $userId);

        if (! $account) {
            return false;
        }

        return $this->accountRepository->update($id, [
            'is_active' => ! $account->is_active,
        ]);
    }
}

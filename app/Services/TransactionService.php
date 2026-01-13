<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Services\Contracts\TransactionServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class TransactionService extends BaseService implements TransactionServiceInterface
{
    protected TransactionRepositoryInterface $transactionRepository;

    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(
        TransactionRepositoryInterface $repository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        parent::__construct($repository);
        $this->transactionRepository = $repository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get paginated transactions for a user
     */
    public function getPaginatedForUser(
        string $userId,
        int $perPage = 15,
        ?string $sortBy = 'transaction_date',
        string $sortDirection = 'desc',
        array $filters = []
    ): mixed {
        // Only eager load account if Account model exists (after Accounts module is merged)
        $relations = ['category'];
        if (class_exists(\App\Models\Account::class)) {
            $relations[] = 'account';
        }

        return $this->transactionRepository->getPaginatedForUser(
            $userId,
            $perPage,
            ['*'],
            $relations,
            $sortBy,
            $sortDirection,
            $filters
        );
    }

    /**
     * Get transactions for a user by date range
     */
    public function getByDateRange(
        string $userId,
        string $startDate,
        string $endDate
    ): Collection {
        return $this->transactionRepository->getByDateRange(
            $userId,
            $startDate,
            $endDate,
            ['*'],
            ['category']
        );
    }

    /**
     * Get transactions for a user by category
     */
    public function getByCategory(
        string $userId,
        string $categoryId,
        int $perPage = 15
    ): mixed {
        return $this->transactionRepository->getByCategory(
            $userId,
            $categoryId,
            $perPage,
            ['*'],
            ['category']
        );
    }

    /**
     * Create a transaction for a user
     */
    public function createTransaction(string $userId, array $data): Model
    {
        // Validate category belongs to user or is a system category
        $category = $this->categoryRepository->find($data['category_id']);

        if (! $category) {
            throw new \Exception(__('transactions.category_not_found'));
        }

        // Check if category is accessible (system or user's own)
        if (! $category->is_system && $category->user_id !== $userId) {
            throw new \Exception(__('transactions.category_not_accessible'));
        }

        $data['user_id'] = $userId;

        // Ensure amount is positive (sign will be determined by category type)
        $data['amount'] = abs((float) $data['amount']);

        return $this->transactionRepository->create($data);
    }

    /**
     * Update a user's transaction
     */
    public function updateTransaction(string $userId, string $id, array $data): bool
    {
        $transaction = $this->transactionRepository->find($id);

        if (! $transaction) {
            throw new \Exception(__('transactions.not_found'));
        }

        // Can only update own transactions
        if ($transaction->user_id !== $userId) {
            throw new \Exception(__('transactions.unauthorized'));
        }

        // If changing category, validate it
        if (isset($data['category_id']) && $data['category_id'] !== $transaction->category_id) {
            $category = $this->categoryRepository->find($data['category_id']);

            if (! $category) {
                throw new \Exception(__('transactions.category_not_found'));
            }

            // Check if category is accessible
            if (! $category->is_system && $category->user_id !== $userId) {
                throw new \Exception(__('transactions.category_not_accessible'));
            }
        }

        // Don't allow changing user_id
        unset($data['user_id']);

        // Ensure amount is positive if provided
        if (isset($data['amount'])) {
            $data['amount'] = abs((float) $data['amount']);
        }

        return $this->transactionRepository->update($id, $data);
    }

    /**
     * Delete a user's transaction
     */
    public function deleteTransaction(string $userId, string $id): bool
    {
        $transaction = $this->transactionRepository->find($id);

        if (! $transaction) {
            throw new \Exception(__('transactions.not_found'));
        }

        // Can only delete own transactions
        if ($transaction->user_id !== $userId) {
            throw new \Exception(__('transactions.unauthorized'));
        }

        return $this->transactionRepository->delete($id);
    }

    /**
     * Get transaction summary for a user
     */
    public function getSummary(
        string $userId,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $totalIncome = $this->transactionRepository->getTotalByType(
            $userId,
            'income',
            $startDate,
            $endDate
        );

        $totalExpense = $this->transactionRepository->getTotalByType(
            $userId,
            'expense',
            $startDate,
            $endDate
        );

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $totalIncome - $totalExpense,
        ];
    }
}

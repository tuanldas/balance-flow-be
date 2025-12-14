<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class TransactionRepository extends BaseRepository implements TransactionRepositoryInterface
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
    }

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
    ): mixed {
        $query = $this->model->select($columns)
            ->where('user_id', $userId);

        if (! empty($filters['category_ids'])) {
            $query->whereIn('category_id', $filters['category_ids']);
        }

        if (! empty($filters['search'])) {
            $query->where('merchant_name', 'ilike', '%'.$filters['search'].'%');
        }

        $query->orderBy($sortBy ?? 'transaction_date', $sortDirection);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get transactions for a user by date range
     */
    public function getByDateRange(
        string $userId,
        string $startDate,
        string $endDate,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $start = \Carbon\Carbon::parse($startDate)->startOfDay();
        $end = \Carbon\Carbon::parse($endDate)->endOfDay();

        $query = $this->model->select($columns)
            ->where('user_id', $userId)
            ->whereBetween('transaction_date', [$start, $end])
            ->orderBy('transaction_date', 'desc');

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get transactions for a user by category
     */
    public function getByCategory(
        string $userId,
        string $categoryId,
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): mixed {
        $query = $this->model->select($columns)
            ->where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->orderBy('transaction_date', 'desc');

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get total amount for user by type (income/expense)
     */
    public function getTotalByType(
        string $userId,
        string $type,
        ?string $startDate = null,
        ?string $endDate = null
    ): float {
        $query = $this->model
            ->where('user_id', $userId)
            ->whereHas('category', function ($q) use ($type) {
                $q->where('category_type', $type);
            });

        if ($startDate && $endDate) {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = \Carbon\Carbon::parse($endDate)->endOfDay();
            $query->whereBetween('transaction_date', [$start, $end]);
        }

        return (float) $query->sum('amount');
    }
}

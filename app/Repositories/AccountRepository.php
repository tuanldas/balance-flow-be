<?php

namespace App\Repositories;

use App\Models\Account;
use App\Repositories\Contracts\AccountRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AccountRepository extends BaseRepository implements AccountRepositoryInterface
{
    public function __construct(Account $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all accounts for a user
     */
    public function getAllForUser(
        string $userId,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->model->select($columns)
            ->where('user_id', $userId);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get paginated accounts for a user
     */
    public function paginateForUser(
        string $userId,
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): mixed {
        $query = $this->model->select($columns)
            ->where('user_id', $userId);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find account for a specific user
     */
    public function findForUser(
        string $id,
        string $userId,
        array $columns = ['*'],
        array $relations = []
    ): ?Model {
        $query = $this->model->select($columns)
            ->where('id', $id)
            ->where('user_id', $userId);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    /**
     * Get accounts by account type for a user
     */
    public function getByAccountType(
        string $accountTypeId,
        string $userId,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->model->select($columns)
            ->where('user_id', $userId)
            ->where('account_type_id', $accountTypeId);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get total balance for a user
     */
    public function getTotalBalance(string $userId, ?string $currency = null): float
    {
        $query = $this->model
            ->where('user_id', $userId);

        if ($currency) {
            $query->where('currency', $currency);
        }

        return (float) $query->sum('balance');
    }

    /**
     * Update account balance
     *
     * @param  string  $operation  'add', 'subtract', or 'set'
     */
    public function updateBalance(string $id, float $amount, string $operation = 'set'): bool
    {
        $account = $this->model->find($id);

        if (! $account) {
            return false;
        }

        switch ($operation) {
            case 'add':
                $account->balance = (float) $account->balance + $amount;
                break;
            case 'subtract':
                $account->balance = (float) $account->balance - $amount;
                break;
            case 'set':
            default:
                $account->balance = $amount;
                break;
        }

        return $account->save();
    }
}

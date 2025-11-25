<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function findByEmail(string $email, array $columns = ['*'], array $relations = []): ?User
    {
        $query = $this->model->select($columns)->where('email', $email);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }
}

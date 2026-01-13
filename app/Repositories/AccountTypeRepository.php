<?php

namespace App\Repositories;

use App\Models\AccountType;
use App\Repositories\Contracts\AccountTypeRepositoryInterface;

class AccountTypeRepository extends BaseRepository implements AccountTypeRepositoryInterface
{
    public function __construct(AccountType $model)
    {
        parent::__construct($model);
    }
}

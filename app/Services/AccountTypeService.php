<?php

namespace App\Services;

use App\Repositories\Contracts\AccountTypeRepositoryInterface;
use App\Services\Contracts\AccountTypeServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class AccountTypeService implements AccountTypeServiceInterface
{
    public function __construct(
        protected AccountTypeRepositoryInterface $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }
}

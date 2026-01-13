<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface AccountTypeServiceInterface
{
    /**
     * Get all account types
     */
    public function getAll(): Collection;
}

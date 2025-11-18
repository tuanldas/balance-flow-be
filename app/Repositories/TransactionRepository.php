<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Transaction;
use App\Repositories\Contracts\TransactionRepositoryInterface;

final readonly class TransactionRepository implements TransactionRepositoryInterface
{
    /**
     * Chuyển tất cả transactions từ category này sang category khác
     */
    public function transferToCategory(string $fromCategoryId, string $toCategoryId): int
    {
        return Transaction::where('category_id', $fromCategoryId)
            ->update(['category_id' => $toCategoryId]);
    }

    /**
     * Đếm số lượng transactions theo category
     */
    public function countByCategory(string $categoryId): int
    {
        return Transaction::where('category_id', $categoryId)->count();
    }
}

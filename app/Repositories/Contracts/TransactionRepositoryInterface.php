<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface TransactionRepositoryInterface
{
    /**
     * Chuyển tất cả transactions từ category này sang category khác
     */
    public function transferToCategory(string $fromCategoryId, string $toCategoryId): int;

    /**
     * Đếm số lượng transactions theo category
     */
    public function countByCategory(string $categoryId): int;
}

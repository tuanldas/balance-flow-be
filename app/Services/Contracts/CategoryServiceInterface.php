<?php

namespace App\Services\Contracts;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CategoryServiceInterface
{
    /**
     * Lấy tất cả categories có thể truy cập bởi user (system + user's own) với phân trang
     */
    public function getAllAccessibleCategories(string $userId, ?string $type = null, int $perPage = 15, string $sortBy = 'name', string $sortDirection = 'asc'): LengthAwarePaginator;

    /**
     * Lấy tất cả categories có thể truy cập bởi user (system + user's own) không phân trang
     *
     * @return Collection<int, Category>
     */
    public function getAllAccessibleCategoriesWithoutPagination(string $userId, ?string $type = null): Collection;

    /**
     * Tạo category mới cho user
     *
     * @param  array<string, mixed>  $data
     */
    public function createUserCategory(string $userId, array $data): Category;

    /**
     * Cập nhật category
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateCategory(string $categoryId, string $userId, array $data): Category;

    /**
     * Xóa category (có thể chuyển transactions sang category khác trước)
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \RuntimeException
     */
    public function deleteCategory(string $categoryId, string $userId, ?string $transferToCategoryId = null): bool;

    /**
     * Lấy thông tin category theo ID
     */
    public function getCategoryById(string $categoryId): ?Category;

    /**
     * Đếm số lượng transactions của category
     */
    public function getTransactionCount(string $categoryId): int;
}

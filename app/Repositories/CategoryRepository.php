<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

final readonly class CategoryRepository implements CategoryRepositoryInterface
{
    /**
     * Tạo category mới
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Tìm category theo ID
     */
    public function findById(string $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Lấy tất cả categories có thể truy cập bởi user (system + user's own) với phân trang
     */
    public function getAccessibleByUser(string $userId, ?string $type = null, int $perPage = 15, string $sortBy = 'name', string $sortDirection = 'asc'): LengthAwarePaginator
    {
        $query = Category::accessibleByUser($userId);

        if ($type !== null) {
            $query->ofType($type);
        }

        // Apply sorting based on parameters
        $query->orderBy($sortBy, $sortDirection);

        // Add secondary sorting by name if sorting by other fields
        if ($sortBy !== 'name') {
            $query->orderBy('name', 'asc');
        }

        return $query->paginate($perPage);
    }

    /**
     * Lấy tất cả categories có thể truy cập bởi user (system + user's own) không phân trang
     *
     * @return Collection<int, Category>
     */
    public function getAllAccessibleByUser(string $userId, ?string $type = null): Collection
    {
        $query = Category::accessibleByUser($userId);

        if ($type !== null) {
            $query->ofType($type);
        }

        return $query->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Lấy system categories
     *
     * @return Collection<int, Category>
     */
    public function getSystemCategories(?string $type = null): Collection
    {
        $query = Category::system();

        if ($type !== null) {
            $query->ofType($type);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Lấy user categories
     *
     * @return Collection<int, Category>
     */
    public function getUserCategories(string $userId, ?string $type = null): Collection
    {
        $query = Category::userCategories($userId);

        if ($type !== null) {
            $query->ofType($type);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Cập nhật category
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    /**
     * Xóa category
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }

    /**
     * Đếm số lượng transactions của category
     */
    public function countTransactions(string $categoryId): int
    {
        return Category::find($categoryId)?->transactions()->count() ?? 0;
    }

    /**
     * Kiểm tra xem user có quyền chỉnh sửa category hay không
     */
    public function canUserModify(Category $category, string $userId): bool
    {
        // System categories cannot be modified by regular users
        if ($category->is_system) {
            return false;
        }

        // User can only modify their own categories
        return $category->user_id === $userId;
    }
}

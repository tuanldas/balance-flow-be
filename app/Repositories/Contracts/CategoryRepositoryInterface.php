<?php



namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface
{
    /**
     * Tạo category mới
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category;

    /**
     * Tìm category theo ID
     */
    public function findById(string $id): ?Category;

    /**
     * Lấy tất cả categories có thể truy cập bởi user (system + user's own) với phân trang
     */
    public function getAccessibleByUser(string $userId, ?string $type = null, int $perPage = 15): LengthAwarePaginator;

    /**
     * Lấy tất cả categories có thể truy cập bởi user (system + user's own) không phân trang
     *
     * @return Collection<int, Category>
     */
    public function getAllAccessibleByUser(string $userId, ?string $type = null): Collection;

    /**
     * Lấy system categories
     *
     * @return Collection<int, Category>
     */
    public function getSystemCategories(?string $type = null): Collection;

    /**
     * Lấy user categories
     *
     * @return Collection<int, Category>
     */
    public function getUserCategories(string $userId, ?string $type = null): Collection;

    /**
     * Cập nhật category
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): bool;

    /**
     * Xóa category
     */
    public function delete(Category $category): bool;

    /**
     * Đếm số lượng transactions của category
     */
    public function countTransactions(string $categoryId): int;

    /**
     * Kiểm tra xem user có quyền chỉnh sửa category hay không
     */
    public function canUserModify(Category $category, string $userId): bool;
}

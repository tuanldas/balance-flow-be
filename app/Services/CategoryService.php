<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Services\Contracts\CategoryServiceInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final readonly class CategoryService implements CategoryServiceInterface
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private TransactionRepositoryInterface $transactionRepository,
    ) {}

    /**
     * Lấy tất cả categories có thể truy cập bởi user (system + user's own) với phân trang
     */
    public function getAllAccessibleCategories(string $userId, ?string $type = null, int $perPage = 15, string $sortBy = 'name', string $sortDirection = 'asc'): LengthAwarePaginator
    {
        return $this->categoryRepository->getAccessibleByUser($userId, $type, $perPage, $sortBy, $sortDirection);
    }

    /**
     * Lấy tất cả categories có thể truy cập bởi user (system + user's own) không phân trang
     *
     * @return Collection<int, Category>
     */
    public function getAllAccessibleCategoriesWithoutPagination(string $userId, ?string $type = null): Collection
    {
        return $this->categoryRepository->getAllAccessibleByUser($userId, $type);
    }

    /**
     * Tạo category mới cho user
     *
     * @param  array<string, mixed>  $data
     */
    public function createUserCategory(string $userId, array $data): Category
    {
        // Ensure the category is not marked as system
        $data['is_system'] = false;
        $data['user_id'] = $userId;

        // Handle file upload
        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $iconPath = $data['icon']->store('category-icons', 'public');
            $data['icon_path'] = $iconPath;
            unset($data['icon']);
        }

        return $this->categoryRepository->create($data);
    }

    /**
     * Cập nhật category
     *
     * @param  array<string, mixed>  $data
     *
     * @throws AuthorizationException
     */
    public function updateCategory(string $categoryId, string $userId, array $data): Category
    {
        $category = $this->categoryRepository->findById($categoryId);

        if ($category === null) {
            throw new RuntimeException(__('messages.category.not_found'));
        }

        if (! $this->categoryRepository->canUserModify($category, $userId)) {
            throw new AuthorizationException(__('messages.category.unauthorized'));
        }

        // Prevent changing is_system flag
        unset($data['is_system'], $data['user_id']);

        // Handle file upload
        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            // Delete old icon if exists
            if ($category->icon_path && Storage::disk('public')->exists($category->icon_path)) {
                Storage::disk('public')->delete($category->icon_path);
            }

            // Store new icon
            $iconPath = $data['icon']->store('category-icons', 'public');
            $data['icon_path'] = $iconPath;
            unset($data['icon']);
        }

        $this->categoryRepository->update($category, $data);

        return $category->fresh();
    }

    /**
     * Xóa category (có thể chuyển transactions sang category khác trước)
     *
     * @throws AuthorizationException
     * @throws RuntimeException
     */
    public function deleteCategory(string $categoryId, string $userId, ?string $transferToCategoryId = null): bool
    {
        $category = $this->categoryRepository->findById($categoryId);

        if ($category === null) {
            throw new RuntimeException(__('messages.category.not_found'));
        }

        if (! $this->categoryRepository->canUserModify($category, $userId)) {
            throw new AuthorizationException(__('messages.category.unauthorized'));
        }

        $transactionCount = $this->categoryRepository->countTransactions($categoryId);

        // If there are transactions, handle them
        if ($transactionCount > 0) {
            if ($transferToCategoryId === null) {
                throw new RuntimeException(__('messages.category.has_transactions', ['count' => $transactionCount]));
            }

            // Verify target category exists and user can access it
            $targetCategory = $this->categoryRepository->findById($transferToCategoryId);
            if ($targetCategory === null) {
                throw new RuntimeException(__('messages.category.transfer_target_not_found'));
            }

            // Verify target category is accessible by user (either system or user's own)
            if (! $targetCategory->is_system && $targetCategory->user_id !== $userId) {
                throw new AuthorizationException(__('messages.category.transfer_target_unauthorized'));
            }

            // Verify both categories have the same type
            if ($targetCategory->type !== $category->type) {
                throw new RuntimeException(__('messages.category.transfer_type_mismatch'));
            }

            // Transfer transactions to the new category
            $this->transactionRepository->transferToCategory($categoryId, $transferToCategoryId);
        }

        // Delete icon file if exists
        if ($category->icon_path && Storage::disk('public')->exists($category->icon_path)) {
            Storage::disk('public')->delete($category->icon_path);
        }

        return $this->categoryRepository->delete($category);
    }

    /**
     * Lấy thông tin category theo ID
     */
    public function getCategoryById(string $categoryId): ?Category
    {
        return $this->categoryRepository->findById($categoryId);
    }

    /**
     * Đếm số lượng transactions của category
     */
    public function getTransactionCount(string $categoryId): int
    {
        return $this->categoryRepository->countTransactions($categoryId);
    }
}

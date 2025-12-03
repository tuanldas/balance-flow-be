<?php

namespace App\Services;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Services\Contracts\CategoryServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryService extends BaseService implements CategoryServiceInterface
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->categoryRepository = $repository;
    }

    /**
     * Get all categories for a user (system + user's custom categories)
     */
    public function getAllForUser(string $userId): Collection
    {
        $systemCategories = $this->categoryRepository->getSystemCategories(
            ['*'],
            ['subcategories']
        );

        $userCategories = $this->categoryRepository->getUserCategories(
            $userId,
            ['*'],
            ['subcategories']
        );

        return $systemCategories->concat($userCategories);
    }

    /**
     * Get paginated categories for a user
     */
    public function getPaginatedForUser(string $userId, int $perPage = 15): mixed
    {
        return $this->categoryRepository->getPaginatedForUser(
            $userId,
            $perPage,
            ['*'],
            ['subcategories']
        );
    }

    /**
     * Get categories by type for a user
     */
    public function getCategoriesByType(string $userId, string $type): Collection
    {
        return $this->categoryRepository->getCategoriesByType(
            $type,
            $userId,
            ['*'],
            ['subcategories']
        );
    }

    /**
     * Get paginated categories by type for a user
     */
    public function getPaginatedByType(string $userId, string $type, int $perPage = 15): mixed
    {
        return $this->categoryRepository->getPaginatedByType(
            $type,
            $userId,
            $perPage,
            ['*'],
            ['subcategories']
        );
    }

    /**
     * Get parent categories only
     */
    public function getParentCategories(string $userId): Collection
    {
        return $this->categoryRepository->getParentCategories(
            $userId,
            ['*'],
            ['subcategories']
        );
    }

    /**
     * Get subcategories of a parent
     */
    public function getSubcategories(string $parentId): Collection
    {
        return $this->categoryRepository->getSubcategories($parentId);
    }

    /**
     * Create a new user category
     */
    public function createUserCategory(string $userId, array $data): mixed
    {
        // Validate parent_id if provided
        if (isset($data['parent_id'])) {
            $parent = $this->categoryRepository->find($data['parent_id']);

            if (! $parent) {
                throw new \Exception(__('categories.parent_not_found'));
            }

            // Ensure parent has same category_type
            if ($parent->category_type !== $data['category_type']) {
                throw new \Exception(__('categories.parent_type_mismatch'));
            }

            // Ensure parent is accessible by user (system or user's own)
            if (! $parent->is_system && $parent->user_id !== $userId) {
                throw new \Exception(__('categories.parent_not_accessible'));
            }
        }

        $data['user_id'] = $userId;
        $data['is_system'] = false;

        return $this->categoryRepository->create($data);
    }

    /**
     * Update a user category
     */
    public function updateUserCategory(string $userId, string $id, array $data): bool
    {
        $category = $this->categoryRepository->find($id);

        if (! $category) {
            throw new \Exception(__('categories.not_found'));
        }

        // Can't update system categories
        if ($category->is_system) {
            throw new \Exception(__('categories.cannot_update_system'));
        }

        // Can only update own categories
        if ($category->user_id !== $userId) {
            throw new \Exception(__('categories.unauthorized'));
        }

        // If changing parent_id, validate it
        if (isset($data['parent_id']) && $data['parent_id'] !== $category->parent_id) {
            $parent = $this->categoryRepository->find($data['parent_id']);

            if (! $parent) {
                throw new \Exception(__('categories.parent_not_found'));
            }

            // Can't set self as parent
            if ($parent->id === $category->id) {
                throw new \Exception(__('categories.cannot_set_self_as_parent'));
            }

            // Ensure parent has same category_type
            if ($parent->category_type !== $category->category_type) {
                throw new \Exception(__('categories.parent_type_mismatch'));
            }
        }

        // Don't allow changing user_id or is_system
        unset($data['user_id'], $data['is_system']);

        return $this->categoryRepository->update($id, $data);
    }

    /**
     * Delete a user category
     */
    public function deleteUserCategory(string $userId, string $id): bool
    {
        $category = $this->categoryRepository->find($id);

        if (! $category) {
            throw new \Exception(__('categories.not_found'));
        }

        // Can't delete system categories
        if ($category->is_system) {
            throw new \Exception(__('categories.cannot_delete_system'));
        }

        // Can only delete own categories
        if ($category->user_id !== $userId) {
            throw new \Exception(__('categories.unauthorized'));
        }

        // Check if can delete
        if (! $this->categoryRepository->canDelete($id)) {
            throw new \Exception(__('categories.cannot_delete_has_transactions'));
        }

        return $this->categoryRepository->delete($id);
    }
}

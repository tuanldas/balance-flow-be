<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all system categories
     */
    public function getSystemCategories(
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->model->select($columns)
            ->where('is_system', true)
            ->whereNull('parent_id') // Only get parent categories
            ->withCount('subcategories');

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get all user categories
     */
    public function getUserCategories(
        string $userId,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->model->select($columns)
            ->where('user_id', $userId)
            ->where('is_system', false)
            ->whereNull('parent_id') // Only get parent categories
            ->withCount('subcategories');

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get categories by type
     */
    public function getCategoriesByType(
        string $type,
        ?string $userId = null,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->model->select($columns)
            ->where('category_type', $type)
            ->whereNull('parent_id') // Only get parent categories
            ->withCount('subcategories');

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('is_system', true)
                    ->orWhere('user_id', $userId);
            });
        } else {
            $query->where('is_system', true);
        }

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get paginated categories for a user (system + user's)
     */
    public function getPaginatedForUser(
        string $userId,
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): mixed {
        $query = $this->model->select($columns)
            ->whereNull('parent_id') // Only get parent categories
            ->withCount('subcategories')
            ->where(function ($q) use ($userId) {
                $q->where('is_system', true)
                    ->orWhere('user_id', $userId);
            });

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get paginated categories by type
     */
    public function getPaginatedByType(
        string $type,
        string $userId,
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): mixed {
        $query = $this->model->select($columns)
            ->where('category_type', $type)
            ->whereNull('parent_id') // Only get parent categories
            ->withCount('subcategories')
            ->where(function ($q) use ($userId) {
                $q->where('is_system', true)
                    ->orWhere('user_id', $userId);
            });

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get all parent categories (no subcategories)
     */
    public function getParentCategories(
        ?string $userId = null,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->model->select($columns)
            ->whereNull('parent_id');

        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('is_system', true)
                    ->orWhere('user_id', $userId);
            });
        } else {
            $query->where('is_system', true);
        }

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Get all subcategories of a parent category
     */
    public function getSubcategories(
        string $parentId,
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->model->select($columns)
            ->where('parent_id', $parentId);

        if (! empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }

    /**
     * Check if category can be deleted
     * Can't delete if:
     * - It's a system category
     * - It has transactions
     * - It has subcategories
     */
    public function canDelete(string $id): bool
    {
        $category = $this->find($id);

        if (! $category) {
            return false;
        }

        // Can't delete system categories
        if ($category->is_system) {
            return false;
        }

        // Can't delete if has subcategories
        if ($category->subcategories()->exists()) {
            return false;
        }

        // Can't delete if has transactions (check when transactions table exists)
        // if ($category->transactions()->exists()) {
        //     return false;
        // }

        return true;
    }
}

<?php

namespace App\Repositories\Contracts;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

interface CategoryRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get all system categories
     */
    public function getSystemCategories(
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * Get all user categories
     */
    public function getUserCategories(
        string $userId,
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * Get categories by type
     */
    public function getCategoriesByType(
        string $type,
        ?string $userId = null,
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * Get paginated categories for a user (system + user's)
     */
    public function getPaginatedForUser(
        string $userId,
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): mixed;

    /**
     * Get paginated categories by type
     */
    public function getPaginatedByType(
        string $type,
        string $userId,
        int $perPage = 15,
        array $columns = ['*'],
        array $relations = []
    ): mixed;

    /**
     * Get all parent categories (no subcategories)
     */
    public function getParentCategories(
        ?string $userId = null,
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * Get all subcategories of a parent category
     */
    public function getSubcategories(
        string $parentId,
        array $columns = ['*'],
        array $relations = []
    ): Collection;

    /**
     * Check if category can be deleted
     */
    public function canDelete(string $id): bool;
}

<?php

namespace App\Services\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface CategoryServiceInterface extends BaseServiceInterface
{
    /**
     * Get all categories for a user (system + user's custom categories)
     */
    public function getAllForUser(string $userId): Collection;

    /**
     * Get paginated categories for a user
     */
    public function getPaginatedForUser(string $userId, int $perPage = 15): mixed;

    /**
     * Get categories by type for a user
     */
    public function getCategoriesByType(string $userId, string $type): Collection;

    /**
     * Get paginated categories by type for a user
     */
    public function getPaginatedByType(string $userId, string $type, int $perPage = 15): mixed;

    /**
     * Get parent categories only
     */
    public function getParentCategories(string $userId): Collection;

    /**
     * Get subcategories of a parent
     */
    public function getSubcategories(string $parentId): Collection;

    /**
     * Create a new user category
     */
    public function createUserCategory(string $userId, array $data): mixed;

    /**
     * Update a user category
     */
    public function updateUserCategory(string $userId, string $id, array $data): bool;

    /**
     * Delete a user category
     */
    public function deleteUserCategory(string $userId, string $id): bool;
}

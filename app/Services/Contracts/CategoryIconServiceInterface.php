<?php

namespace App\Services\Contracts;

use Illuminate\Http\UploadedFile;

interface CategoryIconServiceInterface
{
    /**
     * Get list of default icons
     */
    public function getDefaultIcons(): array;

    /**
     * Copy a default icon to user's storage
     */
    public function copyDefaultIconToUser(string $userId, string $iconName): string;

    /**
     * Upload a custom icon for user
     */
    public function uploadIcon(string $userId, UploadedFile $file): string;

    /**
     * Delete user's icon file
     */
    public function deleteIcon(string $iconPath): bool;

    /**
     * Check if icon is a default icon
     */
    public function isDefaultIcon(string $iconName): bool;

    /**
     * Get user's icon storage path
     */
    public function getUserIconPath(string $userId): string;
}

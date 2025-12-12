<?php

namespace App\Services;

use App\Services\Contracts\CategoryIconServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CategoryIconService implements CategoryIconServiceInterface
{
    protected string $defaultIconsPath;

    protected string $publicDisk = 'public';

    public function __construct()
    {
        $this->defaultIconsPath = database_path('seeders/category-icons');
    }

    /**
     * Get list of default icons
     */
    public function getDefaultIcons(): array
    {
        if (! File::isDirectory($this->defaultIconsPath)) {
            return [];
        }

        $files = File::files($this->defaultIconsPath);
        $icons = [];

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $name = pathinfo($filename, PATHINFO_FILENAME);

            $icons[] = [
                'name' => $name,
                'filename' => $filename,
                'url' => url('storage/category-icons/'.$filename),
            ];
        }

        return $icons;
    }

    /**
     * Copy a default icon to user's storage
     */
    public function copyDefaultIconToUser(string $userId, string $iconName): string
    {
        $sourcePath = $this->defaultIconsPath.'/'.$iconName;

        if (! File::exists($sourcePath)) {
            // Try adding .svg extension if not provided
            if (! str_contains($iconName, '.')) {
                $sourcePath = $this->defaultIconsPath.'/'.$iconName.'.svg';
                $iconName = $iconName.'.svg';
            }

            if (! File::exists($sourcePath)) {
                throw new \Exception(__('categories.icon_not_found'));
            }
        }

        $userIconPath = $this->getUserIconPath($userId);
        $destinationPath = $userIconPath.'/'.$iconName;

        // Ensure directory exists
        Storage::disk($this->publicDisk)->makeDirectory($userIconPath);

        // Copy file
        $content = File::get($sourcePath);
        Storage::disk($this->publicDisk)->put($destinationPath, $content);

        return 'storage/'.$destinationPath;
    }

    /**
     * Upload a custom icon for user
     */
    public function uploadIcon(string $userId, UploadedFile $file): string
    {
        $userIconPath = $this->getUserIconPath($userId);

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid().'.'.$extension;

        // Store file
        $path = $file->storeAs($userIconPath, $filename, $this->publicDisk);

        return 'storage/'.$path;
    }

    /**
     * Delete user's icon file
     */
    public function deleteIcon(string $iconPath): bool
    {
        // Don't delete default icons
        if (str_contains($iconPath, 'storage/category-icons/')) {
            return true;
        }

        // Remove 'storage/' prefix if present
        $relativePath = str_replace('storage/', '', $iconPath);

        if (Storage::disk($this->publicDisk)->exists($relativePath)) {
            return Storage::disk($this->publicDisk)->delete($relativePath);
        }

        return true;
    }

    /**
     * Check if icon is a default icon
     */
    public function isDefaultIcon(string $iconName): bool
    {
        // Remove path prefixes
        $iconName = basename($iconName);

        // Remove extension if needed for comparison
        $nameWithoutExt = pathinfo($iconName, PATHINFO_FILENAME);

        $sourcePath = $this->defaultIconsPath.'/'.$iconName;
        $sourcePathWithSvg = $this->defaultIconsPath.'/'.$nameWithoutExt.'.svg';

        return File::exists($sourcePath) || File::exists($sourcePathWithSvg);
    }

    /**
     * Get user's icon storage path (relative to public disk)
     */
    public function getUserIconPath(string $userId): string
    {
        return 'users/'.$userId.'/category-icons';
    }
}

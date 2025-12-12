<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Skip seeding if system categories already exist
        if (Category::where('is_system', true)->exists()) {
            $this->command->info('System categories already exist. Skipping seeder...');

            return;
        }

        // Ensure category-icons directory exists in storage
        Storage::disk('public')->makeDirectory('category-icons');

        // Copy icons from seeders to storage
        $this->copyIconsToStorage();
        // Income categories
        $incomeCategories = [
            ['name' => 'Lương', 'icon' => 'salary.svg', 'color' => '#4CAF50'],
            ['name' => 'Thưởng', 'icon' => 'bonus.svg', 'color' => '#8BC34A'],
            ['name' => 'Đầu tư', 'icon' => 'investment.svg', 'color' => '#009688'],
            ['name' => 'Kinh doanh', 'icon' => 'freelance.svg', 'color' => '#00BCD4'],
            ['name' => 'Tiết kiệm', 'icon' => 'savings.svg', 'color' => '#2E7D32'],
            ['name' => 'Thu nhập khác', 'icon' => 'other-income.svg', 'color' => '#03A9F4'],
        ];

        foreach ($incomeCategories as $category) {
            Category::create([
                'user_id' => null,
                'name' => $category['name'],
                'category_type' => 'income',
                'parent_id' => null,
                'icon' => 'storage/category-icons/'.$category['icon'],
                'color' => $category['color'],
                'is_system' => true,
            ]);
        }

        // Expense categories
        $expenseCategories = [
            [
                'name' => 'Ăn uống',
                'icon' => 'food.svg',
                'color' => '#FF5722',
                'subcategories' => [
                    ['name' => 'Ăn sáng', 'icon' => 'food.svg'],
                    ['name' => 'Ăn trưa', 'icon' => 'food.svg'],
                    ['name' => 'Ăn tối', 'icon' => 'food.svg'],
                    ['name' => 'Cà phê', 'icon' => 'food.svg'],
                ],
            ],
            ['name' => 'Mua sắm', 'icon' => 'shopping.svg', 'color' => '#E91E63'],
            ['name' => 'Đi lại', 'icon' => 'transportation.svg', 'color' => '#9C27B0'],
            ['name' => 'Nhà cửa', 'icon' => 'housing.svg', 'color' => '#673AB7'],
            ['name' => 'Y tế', 'icon' => 'healthcare.svg', 'color' => '#3F51B5'],
            ['name' => 'Giáo dục', 'icon' => 'education.svg', 'color' => '#2196F3'],
            ['name' => 'Giải trí', 'icon' => 'entertainment.svg', 'color' => '#00BCD4'],
            ['name' => 'Hóa đơn', 'icon' => 'utilities.svg', 'color' => '#009688'],
            ['name' => 'Bảo hiểm', 'icon' => 'insurance.svg', 'color' => '#4CAF50'],
            ['name' => 'Quà tặng', 'icon' => 'gift.svg', 'color' => '#E040FB'],
            ['name' => 'Chi phí khác', 'icon' => 'other-expense.svg', 'color' => '#FFC107'],
        ];

        foreach ($expenseCategories as $category) {
            $parent = Category::create([
                'user_id' => null,
                'name' => $category['name'],
                'category_type' => 'expense',
                'parent_id' => null,
                'icon' => 'storage/category-icons/'.$category['icon'],
                'color' => $category['color'],
                'is_system' => true,
            ]);

            // Create subcategories if exists
            if (isset($category['subcategories'])) {
                foreach ($category['subcategories'] as $subcategory) {
                    Category::create([
                        'user_id' => null,
                        'name' => $subcategory['name'],
                        'category_type' => 'expense',
                        'parent_id' => $parent->id,
                        'icon' => 'storage/category-icons/'.$subcategory['icon'],
                        'color' => $category['color'],
                        'is_system' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Copy icon files from database/seeders/category-icons to storage/app/public/category-icons
     */
    private function copyIconsToStorage(): void
    {
        $sourceDir = database_path('seeders/category-icons');
        $icons = File::files($sourceDir);

        foreach ($icons as $icon) {
            $filename = $icon->getFilename();
            $content = File::get($icon->getPathname());
            Storage::disk('public')->put('category-icons/'.$filename, $content);
        }
    }
}

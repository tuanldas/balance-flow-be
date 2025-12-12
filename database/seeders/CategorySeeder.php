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
            ['name' => 'Lương', 'icon' => 'wallet.png'],
            ['name' => 'Thưởng', 'icon' => 'interest.png'],
            ['name' => 'Đầu tư', 'icon' => 'finance.png'],
            ['name' => 'Kinh doanh', 'icon' => 'work.png'],
            ['name' => 'Tiết kiệm', 'icon' => 'wallet.png'],
            ['name' => 'Thu nhập khác', 'icon' => 'other.png'],
        ];

        foreach ($incomeCategories as $category) {
            Category::create([
                'user_id' => null,
                'name' => $category['name'],
                'category_type' => 'income',
                'parent_id' => null,
                'icon' => 'storage/category-icons/'.$category['icon'],
                'is_system' => true,
            ]);
        }

        // Expense categories
        $expenseCategories = [
            [
                'name' => 'Ăn uống',
                'icon' => 'food.svg',
                'subcategories' => [
                    ['name' => 'Ăn sáng', 'icon' => 'food.svg'],
                    ['name' => 'Ăn trưa', 'icon' => 'food.svg'],
                    ['name' => 'Ăn tối', 'icon' => 'food.svg'],
                    ['name' => 'Cà phê', 'icon' => 'food.svg'],
                ],
            ],
            ['name' => 'Mua sắm', 'icon' => 'shopping.png'],
            ['name' => 'Đi lại', 'icon' => 'transport.png'],
            ['name' => 'Nhà cửa', 'icon' => 'home.png'],
            ['name' => 'Y tế', 'icon' => 'heal.png'],
            ['name' => 'Giáo dục', 'icon' => 'education.png'],
            ['name' => 'Giải trí', 'icon' => 'movie.png'],
            ['name' => 'Hóa đơn', 'icon' => 'bill.png'],
            ['name' => 'Bảo hiểm', 'icon' => 'insurance.png'],
            ['name' => 'Quà tặng', 'icon' => 'gift.png'],
            ['name' => 'Chi phí khác', 'icon' => 'other.png'],
        ];

        foreach ($expenseCategories as $category) {
            $parent = Category::create([
                'user_id' => null,
                'name' => $category['name'],
                'category_type' => 'expense',
                'parent_id' => null,
                'icon' => 'storage/category-icons/'.$category['icon'],
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

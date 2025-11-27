<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Income categories
        $incomeCategories = [
            ['name' => 'Lương', 'icon' => 'work', 'color' => '#4CAF50'],
            ['name' => 'Thưởng', 'icon' => 'card_giftcard', 'color' => '#8BC34A'],
            ['name' => 'Đầu tư', 'icon' => 'trending_up', 'color' => '#009688'],
            ['name' => 'Kinh doanh', 'icon' => 'business', 'color' => '#00BCD4'],
            ['name' => 'Chuyển khoản', 'icon' => 'sync_alt', 'color' => '#9E9E9E'],
            ['name' => 'Thu nhập khác', 'icon' => 'attach_money', 'color' => '#03A9F4'],
        ];

        foreach ($incomeCategories as $category) {
            Category::create([
                'user_id' => null,
                'name' => $category['name'],
                'category_type' => 'income',
                'parent_id' => null,
                'icon' => $category['icon'],
                'color' => $category['color'],
                'is_system' => true,
            ]);
        }

        // Expense categories
        $expenseCategories = [
            [
                'name' => 'Ăn uống',
                'icon' => 'restaurant',
                'color' => '#FF5722',
                'subcategories' => [
                    ['name' => 'Ăn sáng', 'icon' => 'breakfast_dining'],
                    ['name' => 'Ăn trưa', 'icon' => 'lunch_dining'],
                    ['name' => 'Ăn tối', 'icon' => 'dinner_dining'],
                    ['name' => 'Cà phê', 'icon' => 'local_cafe'],
                ],
            ],
            ['name' => 'Mua sắm', 'icon' => 'shopping_cart', 'color' => '#E91E63'],
            ['name' => 'Đi lại', 'icon' => 'directions_car', 'color' => '#9C27B0'],
            ['name' => 'Nhà cửa', 'icon' => 'home', 'color' => '#673AB7'],
            ['name' => 'Y tế', 'icon' => 'local_hospital', 'color' => '#3F51B5'],
            ['name' => 'Giáo dục', 'icon' => 'school', 'color' => '#2196F3'],
            ['name' => 'Giải trí', 'icon' => 'movie', 'color' => '#00BCD4'],
            ['name' => 'Hóa đơn', 'icon' => 'receipt', 'color' => '#009688'],
            ['name' => 'Bảo hiểm', 'icon' => 'security', 'color' => '#4CAF50'],
            ['name' => 'Chuyển khoản', 'icon' => 'sync_alt', 'color' => '#9E9E9E'],
            ['name' => 'Chi phí khác', 'icon' => 'more_horiz', 'color' => '#FFC107'],
        ];

        foreach ($expenseCategories as $category) {
            $parent = Category::create([
                'user_id' => null,
                'name' => $category['name'],
                'category_type' => 'expense',
                'parent_id' => null,
                'icon' => $category['icon'],
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
                        'icon' => $subcategory['icon'],
                        'color' => $category['color'],
                        'is_system' => true,
                    ]);
                }
            }
        }
    }
}

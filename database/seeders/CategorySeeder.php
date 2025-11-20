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
        $categories = [
            // Income Categories
            ['name' => 'messages.categories.income.salary', 'type' => 'income', 'icon_file' => 'salary.svg', 'is_system' => true],
            ['name' => 'messages.categories.income.bonus', 'type' => 'income', 'icon_file' => 'bonus.svg', 'is_system' => true],
            ['name' => 'messages.categories.income.investment', 'type' => 'income', 'icon_file' => 'investment.svg', 'is_system' => true],
            ['name' => 'messages.categories.income.freelance', 'type' => 'income', 'icon_file' => 'freelance.svg', 'is_system' => true],
            ['name' => 'messages.categories.income.gift', 'type' => 'income', 'icon_file' => 'gift.svg', 'is_system' => true],
            ['name' => 'messages.categories.income.other', 'type' => 'income', 'icon_file' => 'other-income.svg', 'is_system' => true],

            // Expense Categories
            ['name' => 'messages.categories.expense.food', 'type' => 'expense', 'icon_file' => 'food.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.transportation', 'type' => 'expense', 'icon_file' => 'transportation.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.housing', 'type' => 'expense', 'icon_file' => 'housing.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.utilities', 'type' => 'expense', 'icon_file' => 'utilities.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.healthcare', 'type' => 'expense', 'icon_file' => 'healthcare.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.entertainment', 'type' => 'expense', 'icon_file' => 'entertainment.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.shopping', 'type' => 'expense', 'icon_file' => 'shopping.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.education', 'type' => 'expense', 'icon_file' => 'education.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.insurance', 'type' => 'expense', 'icon_file' => 'insurance.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.savings', 'type' => 'expense', 'icon_file' => 'savings.svg', 'is_system' => true],
            ['name' => 'messages.categories.expense.other', 'type' => 'expense', 'icon_file' => 'other-expense.svg', 'is_system' => true],
        ];

        $sourceIconsPath = database_path('seeders/category-icons');

        foreach ($categories as $categoryData) {
            $iconFile = $categoryData['icon_file'];
            $sourceFilePath = "{$sourceIconsPath}/{$iconFile}";

            // Copy icon file to storage
            if (File::exists($sourceFilePath)) {
                $iconContent = File::get($sourceFilePath);
                $storagePath = "category-icons/{$iconFile}";
                Storage::disk('public')->put($storagePath, $iconContent);

                unset($categoryData['icon_file']);
                $categoryData['icon_path'] = $storagePath;
            } else {
                unset($categoryData['icon_file']);
                $categoryData['icon_path'] = null;
            }

            Category::create($categoryData);
        }
    }
}

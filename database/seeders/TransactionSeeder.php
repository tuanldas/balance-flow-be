<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create one
        $user = User::first();

        if (! $user) {
            $this->command->warn('No user found. Please create a user first or run UserSeeder.');

            return;
        }

        // Get categories for this user (system + user's own)
        $incomeCategories = Category::where(function ($query) use ($user) {
            $query->where('is_system', true)
                ->orWhere('user_id', $user->id);
        })
            ->where('category_type', 'income')
            ->pluck('id')
            ->toArray();

        $expenseCategories = Category::where(function ($query) use ($user) {
            $query->where('is_system', true)
                ->orWhere('user_id', $user->id);
        })
            ->where('category_type', 'expense')
            ->pluck('id')
            ->toArray();

        if (empty($incomeCategories) || empty($expenseCategories)) {
            $this->command->warn('No categories found. Please run CategorySeeder first.');

            return;
        }

        $this->command->info('Creating 30 transactions for user: '.$user->email);

        // Today transactions (10)
        $this->createTransactions($user->id, $incomeCategories, $expenseCategories, 10, 'today');

        // Yesterday transactions (10)
        $this->createTransactions($user->id, $incomeCategories, $expenseCategories, 10, 'yesterday');

        // Last week transactions (10)
        $this->createTransactions($user->id, $incomeCategories, $expenseCategories, 10, 'lastWeek');

        $this->command->info('Created 30 transactions successfully.');
    }

    /**
     * Create transactions for a specific time period.
     */
    private function createTransactions(
        string $userId,
        array $incomeCategories,
        array $expenseCategories,
        int $count,
        string $period
    ): void {
        // Create mix of income and expense (30% income, 70% expense)
        $incomeCount = (int) ceil($count * 0.3);
        $expenseCount = $count - $incomeCount;

        // Create income transactions
        for ($i = 0; $i < $incomeCount; $i++) {
            $factory = Transaction::factory()
                ->forUser($userId)
                ->forCategory(fake()->randomElement($incomeCategories))
                ->completed();

            // Apply time period
            $factory = match ($period) {
                'today' => $factory->today(),
                'yesterday' => $factory->yesterday(),
                'lastWeek' => $factory->lastWeek(),
                default => $factory,
            };

            // Income amounts typically larger
            $factory->create([
                'amount' => fake()->randomFloat(2, 1000000, 50000000),
                'merchant_name' => fake()->randomElement([
                    'Salary',
                    'Freelance Project',
                    'Investment Return',
                    'Bonus',
                    'Gift',
                    'Refund',
                    'Side Job',
                ]),
            ]);
        }

        // Create expense transactions
        for ($i = 0; $i < $expenseCount; $i++) {
            $factory = Transaction::factory()
                ->forUser($userId)
                ->forCategory(fake()->randomElement($expenseCategories))
                ->completed();

            // Apply time period
            $factory = match ($period) {
                'today' => $factory->today(),
                'yesterday' => $factory->yesterday(),
                'lastWeek' => $factory->lastWeek(),
                default => $factory,
            };

            // Expense amounts typically smaller
            $factory->create([
                'amount' => fake()->randomFloat(2, 20000, 2000000),
            ]);
        }
    }
}

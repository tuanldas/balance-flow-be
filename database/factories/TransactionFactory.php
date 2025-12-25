<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $merchants = [
            'Grab Food',
            'Shopee',
            'Lazada',
            'Circle K',
            'Family Mart',
            'Big C',
            'Lotte Mart',
            'VinMart',
            'Starbucks',
            'Highland Coffee',
            'The Coffee House',
            'KFC',
            'McDonald\'s',
            'Pizza Hut',
            'Jollibee',
            'Spotify',
            'Netflix',
            'Amazon Prime',
            'Gym',
            'Petrol Station',
            'Electric Bill',
            'Water Bill',
            'Internet Bill',
            'Phone Bill',
            'Rent',
            'Salary',
            'Freelance',
            'Investment Return',
            'Gift',
            'Refund',
        ];

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'amount' => fake()->randomFloat(2, 10000, 10000000),
            'name' => fake()->randomElement($merchants),
            'transaction_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the transaction is for a specific user.
     */
    public function forUser(string $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    /**
     * Indicate that the transaction is for a specific category.
     */
    public function forCategory(string $categoryId): static
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $categoryId,
        ]);
    }

    /**
     * Indicate that the transaction happened today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => now()->setTime(
                fake()->numberBetween(0, 23),
                fake()->numberBetween(0, 59),
                fake()->numberBetween(0, 59)
            ),
        ]);
    }

    /**
     * Indicate that the transaction happened yesterday.
     */
    public function yesterday(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => now()->subDay()->setTime(
                fake()->numberBetween(0, 23),
                fake()->numberBetween(0, 59),
                fake()->numberBetween(0, 59)
            ),
        ]);
    }

    /**
     * Indicate that the transaction happened last week.
     */
    public function lastWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'transaction_date' => fake()->dateTimeBetween('-7 days', '-2 days'),
        ]);
    }

    /**
     * Set a specific amount for the transaction.
     */
    public function amount(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'amount' => $amount,
        ]);
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['income', 'expense']);

        return [
            'user_id' => null,
            'name' => fake()->words(2, true),
            'category_type' => $type,
            'parent_id' => null,
            'icon' => fake()->randomElement(['restaurant', 'shopping_cart', 'home', 'work', 'trending_up']),
            'color' => fake()->hexColor(),
            'is_system' => false,
        ];
    }

    /**
     * Indicate that the category is a system category.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'is_system' => true,
        ]);
    }

    /**
     * Indicate that the category is for a specific user.
     */
    public function forUser(string $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
            'is_system' => false,
        ]);
    }

    /**
     * Indicate that the category is of type income.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_type' => 'income',
        ]);
    }

    /**
     * Indicate that the category is of type expense.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'category_type' => 'expense',
        ]);
    }

    /**
     * Indicate that the category is a subcategory.
     */
    public function subcategory(string $parentId): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }
}

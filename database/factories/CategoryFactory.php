<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
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
        return [
            'name' => fake()->words(2, true),
            'type' => fake()->randomElement(['income', 'expense']),
            'icon_path' => null, // Icon files should be uploaded in tests using UploadedFile::fake()
            'is_system' => false,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the category is a system category.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'categories.'.fake()->randomElement(['income', 'expense']).'.'.fake()->word(),
            'is_system' => true,
            'user_id' => null,
        ]);
    }

    /**
     * Indicate that the category is an income category.
     */
    public function income(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'income',
        ]);
    }

    /**
     * Indicate that the category is an expense category.
     */
    public function expense(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'expense',
        ]);
    }
}

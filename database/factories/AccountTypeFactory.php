<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountType>
 */
class AccountTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            ['name' => 'Tiền mặt', 'icon' => 'payments', 'color' => '#4CAF50'],
            ['name' => 'Ngân hàng', 'icon' => 'account_balance', 'color' => '#2196F3'],
            ['name' => 'Ví điện tử', 'icon' => 'account_balance_wallet', 'color' => '#9C27B0'],
            ['name' => 'Thẻ tín dụng', 'icon' => 'credit_card', 'color' => '#F44336'],
            ['name' => 'Tiết kiệm', 'icon' => 'savings', 'color' => '#FF9800'],
            ['name' => 'Đầu tư', 'icon' => 'trending_up', 'color' => '#00BCD4'],
        ];

        $type = fake()->randomElement($types);

        return [
            'name' => $type['name'],
            'icon' => $type['icon'],
            'color' => $type['color'],
        ];
    }
}

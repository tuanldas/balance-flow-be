<?php

namespace Database\Factories;

use App\Models\AccountType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $names = [
            'Ví cá nhân',
            'Tiền mặt',
            'VCB Checking',
            'Vietcombank',
            'Techcombank',
            'MB Bank',
            'Ví Momo',
            'ZaloPay',
            'VNPay',
            'Tiết kiệm 6 tháng',
            'Tiết kiệm 12 tháng',
            'Thẻ Visa Debit',
            'Thẻ Mastercard',
            'Đầu tư chứng khoán',
        ];

        $icons = [
            'payments',
            'account_balance',
            'account_balance_wallet',
            'credit_card',
            'savings',
            'trending_up',
        ];

        return [
            'user_id' => User::factory(),
            'account_type_id' => AccountType::factory(),
            'name' => fake()->randomElement($names),
            'balance' => fake()->randomFloat(2, 0, 100000000),
            'currency' => fake()->randomElement(['VND', 'USD', 'EUR']),
            'icon' => fake()->randomElement($icons),
            'color' => fake()->hexColor(),
            'description' => fake()->optional(0.3)->sentence(),
        ];
    }

    /**
     * Indicate that the account belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the account belongs to a specific account type.
     */
    public function forAccountType(AccountType $accountType): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type_id' => $accountType->id,
        ]);
    }

    /**
     * Indicate that the account has zero balance.
     */
    public function empty(): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => 0,
        ]);
    }

    /**
     * Set a specific balance for the account.
     */
    public function withBalance(float $balance): static
    {
        return $this->state(fn (array $attributes) => [
            'balance' => $balance,
        ]);
    }
}

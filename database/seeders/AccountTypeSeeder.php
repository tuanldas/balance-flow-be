<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accountTypes = [
            [
                'name' => 'Tiền mặt',
                'icon' => 'payments',
                'color' => '#4CAF50',
            ],
            [
                'name' => 'Ngân hàng',
                'icon' => 'account_balance',
                'color' => '#2196F3',
            ],
            [
                'name' => 'Ví điện tử',
                'icon' => 'account_balance_wallet',
                'color' => '#9C27B0',
            ],
            [
                'name' => 'Thẻ tín dụng',
                'icon' => 'credit_card',
                'color' => '#F44336',
            ],
            [
                'name' => 'Tiết kiệm',
                'icon' => 'savings',
                'color' => '#FF9800',
            ],
            [
                'name' => 'Đầu tư',
                'icon' => 'trending_up',
                'color' => '#00BCD4',
            ],
        ];

        foreach ($accountTypes as $type) {
            AccountType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}

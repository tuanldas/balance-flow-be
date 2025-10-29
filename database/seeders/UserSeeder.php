<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo hoặc cập nhật người dùng với email cố định
        $user = User::query()->updateOrCreate(
            ['email' => 'tuanldas@gmail.com'],
            [
                'name' => 'Tuan LDAS',
                'password' => Hash::make('123123'),
                'email_verified_at' => now(),
            ]
        );
    }
}

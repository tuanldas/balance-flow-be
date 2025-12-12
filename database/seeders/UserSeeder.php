<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if user already exists
        if (User::where('email', 'tuanldas@gmail.com')->exists()) {
            $this->command->info('User tuanldas@gmail.com already exists. Skipping...');

            return;
        }

        User::create([
            'name' => 'Tuan Le',
            'email' => 'tuanldas@gmail.com',
            'password' => 'Anhtuan@123',
            'email_verified_at' => now(),
        ]);

        $this->command->info('User tuanldas@gmail.com created successfully.');
    }
}

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
        $admin = User::updateOrCreate(
            ['email' => 'admin@mangoon.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('changeme,1'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role using the helper method in User model
        $admin->assignRole('admin');

        $this->command->info('Admin user seeded successfully.');
        $this->command->info('Email: admin@mangoon.test');
        $this->command->info('Password: changeme,1');
    }
}

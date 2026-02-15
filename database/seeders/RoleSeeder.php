<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrator with full system access and permissions',
                'guard_name' => 'web',
                'level' => 100,
                'is_active' => true,
                'is_system' => true,
                'metadata' => [
                    'color' => '#dc2626',
                    'icon' => 'heroicon-o-shield-check',
                    'badge_style' => 'danger',
                ],
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Editor with content management access',
                'guard_name' => 'web',
                'level' => 50,
                'is_active' => true,
                'is_system' => true,
                'metadata' => [
                    'color' => '#0891b2',
                    'icon' => 'heroicon-o-pencil-square',
                    'badge_style' => 'info',
                ],
            ],
            [
                'name' => 'Reader',
                'slug' => 'reader',
                'description' => 'Reader with basic viewing access',
                'guard_name' => 'web',
                'level' => 10,
                'is_active' => true,
                'is_system' => true,
                'metadata' => [
                    'color' => '#16a34a',
                    'icon' => 'heroicon-o-book-open',
                    'badge_style' => 'success',
                ],
            ],
            [
                'name' => 'Moderator',
                'slug' => 'moderator',
                'description' => 'Moderator with content moderation capabilities',
                'guard_name' => 'web',
                'level' => 40,
                'is_active' => true,
                'is_system' => false,
                'metadata' => [
                    'color' => '#7c3aed',
                    'icon' => 'heroicon-o-flag',
                    'badge_style' => 'warning',
                ],
            ],
            [
                'name' => 'Contributor',
                'slug' => 'contributor',
                'description' => 'Contributor with limited content creation access',
                'guard_name' => 'web',
                'level' => 30,
                'is_active' => true,
                'is_system' => false,
                'metadata' => [
                    'color' => '#ea580c',
                    'icon' => 'heroicon-o-user-plus',
                    'badge_style' => 'primary',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                $roleData
            );
        }

        $this->command->info('Roles seeded successfully!');
    }
}

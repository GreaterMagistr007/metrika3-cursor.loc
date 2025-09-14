<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;

final class AdminUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin
        AdminUser::firstOrCreate(
            ['phone' => '+79999999999'],
            [
                'name' => 'Super Admin',
                'role' => 'super_admin',
                'phone_verified_at' => now(),
            ]
        );

        // Create regular admin
        AdminUser::firstOrCreate(
            ['phone' => '+79999999998'],
            [
                'name' => 'Admin User',
                'role' => 'admin',
                'phone_verified_at' => now(),
            ]
        );

        $this->command->info('Admin users seeded successfully!');
        $this->command->info('Super Admin: +79999999999');
        $this->command->info('Admin: +79999999998');
    }
}
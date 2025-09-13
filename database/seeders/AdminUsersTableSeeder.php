<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminUsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем номер телефона супер-администратора из .env
        $superAdminPhone = env('SUPER_ADMIN_PHONE', '+7 (999) 000-00-00');
        
        $adminUser = [
            'phone' => $superAdminPhone,
            'telegram_id' => null,
            'telegram_data' => null,
            'name' => 'Супер-администратор',
            'role' => 'super_admin',
            'phone_verified_at' => now(),
            'last_login_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('admin_users')->insert($adminUser);
    }
}

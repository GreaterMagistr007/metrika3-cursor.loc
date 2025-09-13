<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'user.invite',
                'description' => 'Приглашение пользователей',
                'category' => 'user',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'user.remove',
                'description' => 'Удаление пользователей',
                'category' => 'user',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'settings.view',
                'description' => 'Просмотр настроек',
                'category' => 'settings',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'settings.edit',
                'description' => 'Редактирование настроек',
                'category' => 'settings',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'message.read',
                'description' => 'Просмотр сообщений',
                'category' => 'message',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'message.manage',
                'description' => 'Управление сообщениями',
                'category' => 'message',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('permissions')->insert($permissions);
    }
}

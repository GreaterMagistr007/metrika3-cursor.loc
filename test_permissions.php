<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ТЕСТИРОВАНИЕ СИСТЕМЫ ПРАВ ===\n\n";

// Проверяем, есть ли права в базе данных
echo "1. Проверка прав в базе данных...\n";
$permissions = \App\Models\Permission::all();
echo "   Найдено прав: " . $permissions->count() . "\n";

if ($permissions->count() === 0) {
    echo "   ❌ ПРАВА НЕ НАЙДЕНЫ! Запустите сидер прав.\n";
    echo "   Выполните: php artisan db:seed --class=PermissionsTableSeeder\n\n";
} else {
    echo "   ✅ Права найдены:\n";
    foreach ($permissions as $permission) {
        echo "   - {$permission->name} ({$permission->category}): {$permission->description}\n";
    }
    echo "\n";
}

// Создаем тестового пользователя и кабинет
echo "2. Создание тестового пользователя и кабинета...\n";
$user = \App\Models\User::firstOrCreate(
    ['phone' => '+1234567890'],
    ['name' => 'Тестовый Пользователь', 'phone_verified_at' => now()]
);

$cabinet = \App\Models\Cabinet::create([
    'name' => 'Тестовый Кабинет для Прав',
    'description' => 'Кабинет для тестирования системы прав',
    'owner_id' => $user->id,
]);

echo "   Пользователь: ID {$user->id}\n";
echo "   Кабинет: ID {$cabinet->id}\n\n";

// Проверяем автоматическое назначение прав владельцу
echo "3. Проверка автоматического назначения прав владельцу...\n";
$cabinetUser = \App\Models\CabinetUser::where('cabinet_id', $cabinet->id)
    ->where('user_id', $user->id)
    ->first();

if ($cabinetUser) {
    echo "   ✅ CabinetUser создан\n";
    echo "   Роль: {$cabinetUser->role}\n";
    echo "   Владелец: " . ($cabinetUser->is_owner ? 'Да' : 'Нет') . "\n";
    
    $ownerPermissions = $cabinetUser->permissions;
    echo "   Назначено прав: " . $ownerPermissions->count() . "\n";
    
    if ($ownerPermissions->count() > 0) {
        echo "   ✅ Права назначены автоматически:\n";
        foreach ($ownerPermissions as $permission) {
            echo "   - {$permission->name} ({$permission->category})\n";
        }
    } else {
        echo "   ❌ Права не назначены!\n";
    }
} else {
    echo "   ❌ CabinetUser не создан!\n";
}

echo "\n";

// Тестируем назначение прав по ролям
echo "4. Тестирование назначения прав по ролям...\n";

// Создаем пользователей с разными ролями
$manager = \App\Models\User::firstOrCreate(
    ['phone' => '+1234567891'],
    ['name' => 'Менеджер', 'phone_verified_at' => now()]
);

$operator = \App\Models\User::firstOrCreate(
    ['phone' => '+1234567892'],
    ['name' => 'Оператор', 'phone_verified_at' => now()]
);

// Добавляем их в кабинет с разными ролями
$managerCabinetUser = \App\Models\CabinetUser::create([
    'cabinet_id' => $cabinet->id,
    'user_id' => $manager->id,
    'role' => 'manager',
    'is_owner' => false,
]);

$operatorCabinetUser = \App\Models\CabinetUser::create([
    'cabinet_id' => $cabinet->id,
    'user_id' => $operator->id,
    'role' => 'operator',
    'is_owner' => false,
]);

// Назначаем права по ролям
$cabinetService = app(\App\Services\CabinetService::class);

// Используем рефлексию для доступа к приватному методу
$reflection = new ReflectionClass($cabinetService);
$assignPermissionsMethod = $reflection->getMethod('assignDefaultPermissions');
$assignPermissionsMethod->setAccessible(true);

$assignPermissionsMethod->invoke($cabinetService, $managerCabinetUser, 'manager');
$assignPermissionsMethod->invoke($cabinetService, $operatorCabinetUser, 'operator');

echo "   Менеджер - назначено прав: " . $managerCabinetUser->permissions->count() . "\n";
echo "   Оператор - назначено прав: " . $operatorCabinetUser->permissions->count() . "\n\n";

// Проверяем права по категориям
echo "5. Проверка прав по категориям...\n";

$categories = $permissions->groupBy('category');
foreach ($categories as $category => $categoryPermissions) {
    echo "   Категория '$category':\n";
    
    $managerHasCategory = $managerCabinetUser->permissions->where('category', $category)->count() > 0;
    $operatorHasCategory = $operatorCabinetUser->permissions->where('category', $category)->count() > 0;
    
    echo "   - Менеджер: " . ($managerHasCategory ? '✅' : '❌') . "\n";
    echo "   - Оператор: " . ($operatorHasCategory ? '✅' : '❌') . "\n";
}

echo "\n";

// Тестируем проверку прав
echo "6. Тестирование проверки прав...\n";

// Создаем тестовое право
$testPermission = \App\Models\Permission::first();
if ($testPermission) {
    $userHasPermission = $cabinetUser->hasPermission($testPermission->name);
    $managerHasPermission = $managerCabinetUser->hasPermission($testPermission->name);
    $operatorHasPermission = $operatorCabinetUser->hasPermission($testPermission->name);
    
    echo "   Тестовое право: {$testPermission->name}\n";
    echo "   Владелец: " . ($userHasPermission ? '✅' : '❌') . "\n";
    echo "   Менеджер: " . ($managerHasPermission ? '✅' : '❌') . "\n";
    echo "   Оператор: " . ($operatorHasPermission ? '✅' : '❌') . "\n";
} else {
    echo "   ❌ Нет прав для тестирования\n";
}

echo "\n";

// Очистка тестовых данных
echo "7. Очистка тестовых данных...\n";
$cabinet->delete(); // Каскадное удаление удалит связанные записи
echo "   ✅ Тестовые данные удалены\n\n";

echo "=== ТЕСТИРОВАНИЕ СИСТЕМЫ ПРАВ ЗАВЕРШЕНО ===\n";
echo "Проверьте результаты выше. Система прав должна:\n";
echo "- Автоматически назначать все права владельцу\n";
echo "- Назначать права по ролям (admin, manager, operator)\n";
echo "- Корректно проверять наличие прав у пользователей\n";

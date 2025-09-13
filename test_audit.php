<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ТЕСТИРОВАНИЕ СИСТЕМЫ АУДИТА ===\n\n";

// Создаем тестового пользователя
echo "1. Создание тестового пользователя...\n";
$user = \App\Models\User::firstOrCreate(
    ['phone' => '+1234567890'],
    ['name' => 'Тестовый Пользователь', 'phone_verified_at' => now()]
);

echo "   Пользователь: ID {$user->id}, Имя: {$user->name}\n\n";

// Тестируем логирование событий
echo "2. Тестирование логирования событий...\n";

// Создаем кабинет (должно автоматически залогироваться)
echo "   Создание кабинета...\n";
$cabinet = \App\Models\Cabinet::create([
    'name' => 'Тестовый Кабинет для Аудита',
    'description' => 'Кабинет для тестирования системы аудита',
    'owner_id' => $user->id,
]);

echo "   ✅ Кабинет создан с ID: {$cabinet->id}\n";

// Проверяем логи аудита
$auditLogs = \App\Models\AuditLog::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->get();

echo "   Найдено записей аудита: " . $auditLogs->count() . "\n";

if ($auditLogs->count() > 0) {
    echo "   ✅ Записи аудита найдены:\n";
    foreach ($auditLogs as $log) {
        echo "   - {$log->event}: {$log->description} (время: {$log->created_at})\n";
        if ($log->metadata) {
            echo "     Метаданные: " . json_encode($log->metadata) . "\n";
        }
    }
} else {
    echo "   ❌ Записи аудита не найдены!\n";
}

echo "\n";

// Тестируем ручное логирование
echo "3. Тестирование ручного логирования...\n";

$user->logAuditEvent('test_event', 'Тестовое событие', [
    'test_data' => 'test_value',
    'cabinet_id' => $cabinet->id
]);

echo "   ✅ Ручное событие залогировано\n";

// Проверяем новую запись
$latestLog = \App\Models\AuditLog::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->first();

if ($latestLog && $latestLog->event === 'test_event') {
    echo "   ✅ Ручное событие найдено в логах\n";
    echo "   Описание: {$latestLog->description}\n";
    echo "   Метаданные: " . json_encode($latestLog->metadata) . "\n";
} else {
    echo "   ❌ Ручное событие не найдено в логах!\n";
}

echo "\n";

// Тестируем логирование через CabinetService
echo "4. Тестирование логирования через CabinetService...\n";

$cabinetService = app(\App\Services\CabinetService::class);

// Создаем второго пользователя для приглашения
$user2 = \App\Models\User::firstOrCreate(
    ['phone' => '+1234567891'],
    ['name' => 'Второй Пользователь', 'phone_verified_at' => now()]
);

// Приглашаем пользователя (должно залогироваться)
$inviteResult = $cabinetService->inviteUserToCabinet($cabinet, '+1234567891', 'operator');

if ($inviteResult['success']) {
    echo "   ✅ Пользователь приглашен\n";
    
    // Проверяем логи приглашения
    $inviteLogs = \App\Models\AuditLog::where('user_id', $user->id)
        ->where('event', 'user_invited')
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "   Найдено записей о приглашениях: " . $inviteLogs->count() . "\n";
    
    if ($inviteLogs->count() > 0) {
        $latestInviteLog = $inviteLogs->first();
        echo "   ✅ Приглашение залогировано:\n";
        echo "   Событие: {$latestInviteLog->event}\n";
        echo "   Описание: {$latestInviteLog->description}\n";
        echo "   Метаданные: " . json_encode($latestInviteLog->metadata) . "\n";
    } else {
        echo "   ❌ Приглашение не залогировано!\n";
    }
} else {
    echo "   ❌ Ошибка приглашения: {$inviteResult['message']}\n";
}

echo "\n";

// Тестируем логирование удаления
echo "5. Тестирование логирования удаления...\n";

$cabinetService->removeUserFromCabinet($cabinet, $user2);

echo "   ✅ Пользователь удален из кабинета\n";

// Проверяем логи удаления
$removeLogs = \App\Models\AuditLog::where('user_id', $user->id)
    ->where('event', 'user_removed')
    ->orderBy('created_at', 'desc')
    ->get();

echo "   Найдено записей об удалениях: " . $removeLogs->count() . "\n";

if ($removeLogs->count() > 0) {
    $latestRemoveLog = $removeLogs->first();
    echo "   ✅ Удаление залогировано:\n";
    echo "   Событие: {$latestRemoveLog->event}\n";
    echo "   Описание: {$latestRemoveLog->description}\n";
    echo "   Метаданные: " . json_encode($latestRemoveLog->metadata) . "\n";
} else {
    echo "   ❌ Удаление не залогировано!\n";
}

echo "\n";

// Показываем все логи пользователя
echo "6. Все записи аудита пользователя:\n";
$allLogs = \App\Models\AuditLog::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->get();

echo "   Всего записей: " . $allLogs->count() . "\n";
foreach ($allLogs as $log) {
    echo "   - {$log->created_at->format('Y-m-d H:i:s')}: {$log->event} - {$log->description}\n";
}

echo "\n";

// Очистка тестовых данных
echo "7. Очистка тестовых данных...\n";
$cabinet->delete(); // Каскадное удаление удалит связанные записи
echo "   ✅ Тестовые данные удалены\n\n";

echo "=== ТЕСТИРОВАНИЕ СИСТЕМЫ АУДИТА ЗАВЕРШЕНО ===\n";
echo "Проверьте результаты выше. Система аудита должна:\n";
echo "- Автоматически логировать создание кабинетов\n";
echo "- Логировать приглашения и удаления пользователей\n";
echo "- Сохранять метаданные событий\n";
echo "- Записывать IP адрес и User Agent\n";

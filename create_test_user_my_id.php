<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "👤 Создание тестового пользователя с вашим Telegram ID...\n";

$telegramId = 713661544;
$phone = '+79998887744';

// Проверяем, существует ли уже пользователь
$existingUser = User::where('telegram_id', $telegramId)->first();
if ($existingUser) {
    echo "⚠️ Пользователь с Telegram ID {$telegramId} уже существует:\n";
    echo "   🆔 ID: {$existingUser->id}\n";
    echo "   👤 Name: {$existingUser->name}\n";
    echo "   📱 Phone: {$existingUser->phone}\n";
    echo "   🤖 Telegram ID: {$existingUser->telegram_id}\n\n";
} else {
    // Создаем нового пользователя
    $user = User::create([
        'name' => 'Konstantin A (Test)',
        'phone' => $phone,
        'telegram_id' => $telegramId,
        'phone_verified_at' => now(),
        'last_login_at' => now()
    ]);

    echo "✅ Пользователь создан:\n";
    echo "   🆔 ID: {$user->id}\n";
    echo "   👤 Name: {$user->name}\n";
    echo "   📱 Phone: {$user->phone}\n";
    echo "   🤖 Telegram ID: {$user->telegram_id}\n\n";
}

echo "🧪 Тестирование:\n";
echo "1. Откройте: http://localhost:8000/telegram-register?telegram_id={$telegramId}\n";
echo "2. Должна произойти автоматическая авторизация\n";
echo "3. Вы должны попасть на главную страницу приложения\n\n";

echo "🎉 Готово к тестированию!\n";

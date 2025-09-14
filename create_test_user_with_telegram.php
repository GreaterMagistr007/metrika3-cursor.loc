<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\AuthService;

echo "👤 Создание тестового пользователя с Telegram ID...\n";

// Создаем тестового пользователя
$user = User::create([
    'name' => 'Test User',
    'phone' => '+79998887766',
    'telegram_id' => 123456789, // Тестовый ID
    'phone_verified_at' => now(),
    'last_login_at' => now()
]);

echo "✅ Пользователь создан:\n";
echo "🆔 ID: " . $user->id . "\n";
echo "📱 Phone: " . $user->phone . "\n";
echo "👤 Name: " . $user->name . "\n";
echo "🤖 Telegram ID: " . $user->telegram_id . "\n\n";

// Тестируем отправку OTP
echo "📤 Тестирование отправки OTP...\n";

$authService = app(AuthService::class);
$otp = $authService->generateAndSendOtp($user->phone, $user->telegram_id);

echo "🔐 Сгенерированный OTP: " . $otp . "\n";
echo "✅ OTP отправлен через AuthService\n\n";

echo "🧪 Теперь можете протестировать регистрацию с номером +79998887766\n";
echo "📱 Введите код: " . $otp . "\n";

echo "\n";

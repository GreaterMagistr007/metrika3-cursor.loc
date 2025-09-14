<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Services\AuthService;

echo "🤖 Тестирование реальной отправки OTP через Telegram...\n";

// Получаем токен бота
$botToken = env('TELEGRAM_BOT_TOKEN');
echo "📱 Bot Token: " . substr($botToken, 0, 10) . "...\n";

// Получаем обновления для поиска реального Telegram ID
echo "📡 Поиск реального Telegram ID...\n";
$url = "https://api.telegram.org/bot{$botToken}/getUpdates";
$response = file_get_contents($url);
$data = json_decode($response, true);

$realTelegramId = null;
if ($data && $data['ok'] && !empty($data['result'])) {
    $latestUpdate = end($data['result']);
    if (isset($latestUpdate['message'])) {
        $realTelegramId = $latestUpdate['message']['from']['id'];
        $userName = $latestUpdate['message']['from']['first_name'];
        echo "✅ Найден реальный пользователь: {$userName} (ID: {$realTelegramId})\n";
    }
}

if (!$realTelegramId) {
    echo "❌ Реальный Telegram ID не найден. Сначала отправьте сообщение боту @M_150_site_bot\n";
    exit(1);
}

// Тестируем отправку OTP
echo "\n📤 Отправка тестового OTP...\n";

$authService = app(AuthService::class);
$testPhone = '+79998887777';
$otp = $authService->generateAndSendOtp($testPhone, $realTelegramId);

echo "🔐 Сгенерированный OTP: {$otp}\n";
echo "📱 Отправлено на номер: {$testPhone}\n";
echo "👤 Отправлено пользователю с ID: {$realTelegramId}\n";

// Проверяем, что OTP сохранился в кеше
$cachedOtp = \Illuminate\Support\Facades\Cache::get("auth_otp:{$testPhone}");
if ($cachedOtp === $otp) {
    echo "✅ OTP корректно сохранен в кеше\n";
} else {
    echo "❌ Ошибка сохранения OTP в кеше\n";
}

echo "\n🎉 Тест завершен! Проверьте Telegram для получения сообщения.\n";

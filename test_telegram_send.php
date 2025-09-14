<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Http;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get bot token from environment
$botToken = env('TELEGRAM_BOT_TOKEN');
$testTelegramId = 123456789; // Замените на реальный Telegram ID для тестирования

if (empty($botToken)) {
    echo "❌ TELEGRAM_BOT_TOKEN не настроен в .env файле\n";
    exit(1);
}

echo "🤖 Тестирование отправки сообщения через Telegram Bot...\n";
echo "📱 Bot Token: " . substr($botToken, 0, 10) . "...\n";
echo "👤 Test Telegram ID: {$testTelegramId}\n\n";

// Test message
$message = "🔐 <b>Тестовое сообщение</b>\n\n";
$message .= "Ваш код: <code>123456</code>\n\n";
$message .= "⏰ Код действителен 5 минут.\n";
$message .= "🔒 Не передавайте код третьим лицам.";

try {
    $response = Http::timeout(10)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
        'chat_id' => $testTelegramId,
        'text' => $message,
        'parse_mode' => 'HTML'
    ]);

    if ($response->successful()) {
        $data = $response->json();
        echo "✅ Сообщение отправлено успешно!\n";
        echo "📨 Message ID: " . $data['result']['message_id'] . "\n";
        echo "👤 Chat ID: " . $data['result']['chat']['id'] . "\n";
    } else {
        echo "❌ Ошибка отправки сообщения:\n";
        echo "Status: " . $response->status() . "\n";
        echo "Response: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Исключение при отправке: " . $e->getMessage() . "\n";
}

echo "\n";

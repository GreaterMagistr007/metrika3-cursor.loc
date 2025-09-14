<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get bot token from environment
$botToken = env('TELEGRAM_BOT_TOKEN');

if (empty($botToken)) {
    echo "❌ TELEGRAM_BOT_TOKEN не настроен в .env файле\n";
    exit(1);
}

echo "🤖 Получение информации о Telegram Bot...\n";
echo "📱 Bot Token: " . substr($botToken, 0, 10) . "...\n\n";

try {
    $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getMe");

    if ($response->successful()) {
        $data = $response->json();
        if ($data['ok']) {
            $bot = $data['result'];
            echo "✅ Информация о боте получена:\n";
            echo "🆔 ID: " . $bot['id'] . "\n";
            echo "👤 Username: @" . $bot['username'] . "\n";
            echo "📛 First Name: " . $bot['first_name'] . "\n";
            echo "🔗 Ссылка: https://t.me/" . $bot['username'] . "\n";
            echo "\n💡 Для тестирования отправьте боту команду /start\n";
        } else {
            echo "❌ Ошибка в ответе API: " . json_encode($data) . "\n";
        }
    } else {
        echo "❌ Ошибка запроса:\n";
        echo "Status: " . $response->status() . "\n";
        echo "Response: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Исключение: " . $e->getMessage() . "\n";
}

echo "\n";

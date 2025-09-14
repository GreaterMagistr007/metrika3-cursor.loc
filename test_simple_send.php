<?php

// Простой тест отправки через curl
$botToken = '7396908423:AAFk3WRot3sy_fpUnii1Up0-M5e8Z7PlbkA';
$testTelegramId = '123456789'; // Замените на ваш реальный Telegram ID

echo "🤖 Тестирование отправки сообщения...\n";

$message = urlencode("🔐 Тестовое сообщение\n\nВаш код: 123456\n\n⏰ Код действителен 5 минут.");

$url = "https://api.telegram.org/bot{$botToken}/sendMessage?chat_id={$testTelegramId}&text={$message}";

echo "📤 Отправка запроса...\n";
echo "🔗 URL: " . substr($url, 0, 100) . "...\n\n";

$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data && $data['ok']) {
    echo "✅ Сообщение отправлено успешно!\n";
    echo "📨 Message ID: " . $data['result']['message_id'] . "\n";
} else {
    echo "❌ Ошибка отправки:\n";
    echo $response . "\n";
}

echo "\n";

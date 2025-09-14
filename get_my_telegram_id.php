<?php

// Скрипт для получения вашего Telegram ID
$botToken = '7396908423:AAFk3WRot3sy_fpUnii1Up0-M5e8Z7PlbkA';

echo "🤖 Для получения вашего Telegram ID:\n";
echo "1. Найдите бота @M_150_site_bot в Telegram\n";
echo "2. Нажмите /start\n";
echo "3. Отправьте любое сообщение боту\n";
echo "4. Запустите этот скрипт снова\n\n";

echo "📡 Получение обновлений от бота...\n";

$url = "https://api.telegram.org/bot{$botToken}/getUpdates";
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data && $data['ok'] && !empty($data['result'])) {
    echo "📨 Найдено сообщений: " . count($data['result']) . "\n\n";
    
    foreach ($data['result'] as $update) {
        if (isset($update['message'])) {
            $message = $update['message'];
            $chat = $message['chat'];
            $user = $message['from'];
            
            echo "👤 Пользователь: " . $user['first_name'] . " " . ($user['last_name'] ?? '') . "\n";
            echo "🆔 Telegram ID: " . $user['id'] . "\n";
            echo "💬 Сообщение: " . $message['text'] . "\n";
            echo "📅 Дата: " . date('Y-m-d H:i:s', $message['date']) . "\n";
            echo "---\n";
        }
    }
} else {
    echo "❌ Не удалось получить обновления или они пустые\n";
    echo "Response: " . $response . "\n";
}

echo "\n";

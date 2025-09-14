<?php

$botToken = '7396908423:AAFk3WRot3sy_fpUnii1Up0-M5e8Z7PlbkA';

echo "🤖 Получение Telegram ID через простой запрос...\n\n";

echo "📋 Инструкция:\n";
echo "1. Найдите бота @M_150_site_bot в Telegram\n";
echo "2. Нажмите /start\n";
echo "3. Отправьте любое сообщение боту (например, 'Привет')\n";
echo "4. Нажмите Enter для проверки...\n\n";

// Ждем нажатия Enter
readline();

echo "🔍 Проверяем обновления...\n";

$url = "https://api.telegram.org/bot{$botToken}/getUpdates";
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data && $data['ok'] && !empty($data['result'])) {
    echo "📨 Найдено сообщений: " . count($data['result']) . "\n\n";
    
    $found = false;
    foreach ($data['result'] as $update) {
        if (isset($update['message'])) {
            $message = $update['message'];
            $user = $message['from'];
            
            echo "👤 Пользователь: " . $user['first_name'] . " " . ($user['last_name'] ?? '') . "\n";
            echo "🆔 Telegram ID: " . $user['id'] . "\n";
            echo "💬 Сообщение: " . $message['text'] . "\n";
            echo "📅 Дата: " . date('Y-m-d H:i:s', $message['date']) . "\n";
            echo "---\n";
            
            $found = true;
        }
    }
    
    if ($found) {
        echo "\n✅ Ваш Telegram ID найден!\n";
        echo "🌐 Тестовая ссылка: http://localhost:8000/telegram-register?telegram_id=" . $user['id'] . "\n";
    } else {
        echo "❌ Сообщения не найдены. Убедитесь, что вы отправили сообщение боту.\n";
    }
} else {
    echo "❌ Не удалось получить обновления или они пустые\n";
    echo "Response: " . $response . "\n";
}

echo "\n";

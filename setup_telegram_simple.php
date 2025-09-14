<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$botToken = env('TELEGRAM_BOT_TOKEN');
$webAppUrl = 'http://localhost:8000/telegram-register';

if (empty($botToken)) {
    echo "❌ TELEGRAM_BOT_TOKEN не настроен в .env файле\n";
    exit(1);
}

echo "🤖 Настройка Telegram Bot для Mini App...\n";
echo "📱 Bot Token: " . substr($botToken, 0, 10) . "...\n";
echo "🌐 Web App URL: {$webAppUrl}\n\n";

// 1. Устанавливаем команды бота
echo "1️⃣ Установка команд бота...\n";
$commands = [
    [
        'command' => 'start',
        'description' => 'Начать работу с ботом и открыть приложение'
    ]
];

$commandsJson = json_encode(['commands' => $commands]);
$commandsUrl = "https://api.telegram.org/bot{$botToken}/setMyCommands";

$commandsCmd = "curl -s -X POST \"{$commandsUrl}\" -H \"Content-Type: application/json\" -d '{$commandsJson}'";
$commandsResult = shell_exec($commandsCmd);
$commandsData = json_decode($commandsResult, true);

if ($commandsData && $commandsData['ok']) {
    echo "✅ Команды установлены успешно\n";
} else {
    echo "❌ Ошибка установки команд: " . $commandsResult . "\n";
}

// 2. Отправляем приветственное сообщение с кнопкой
echo "\n2️⃣ Отправка приветственного сообщения...\n";
$message = "👋 Добро пожаловать в Metrika3 Cabinet!\n\n";
$message .= "Нажмите кнопку ниже, чтобы открыть приложение:";

$inlineKeyboard = [
    'inline_keyboard' => [
        [
            [
                'text' => '🚀 Открыть приложение',
                'web_app' => [
                    'url' => $webAppUrl
                ]
            ]
        ]
    ]
];

// Получаем обновления для поиска последнего чата
$updatesCmd = "curl -s \"https://api.telegram.org/bot{$botToken}/getUpdates\"";
$updatesResult = shell_exec($updatesCmd);
$updates = json_decode($updatesResult, true);

if ($updates && $updates['ok'] && !empty($updates['result'])) {
    $latestUpdate = end($updates['result']);
    if (isset($latestUpdate['message'])) {
        $chatId = $latestUpdate['message']['chat']['id'];
        
        $messageData = [
            'chat_id' => $chatId,
            'text' => $message,
            'reply_markup' => $inlineKeyboard
        ];
        
        $messageJson = json_encode($messageData);
        $messageUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        $messageCmd = "curl -s -X POST \"{$messageUrl}\" -H \"Content-Type: application/json\" -d '{$messageJson}'";
        $messageResult = shell_exec($messageCmd);
        $messageData = json_decode($messageResult, true);
        
        if ($messageData && $messageData['ok']) {
            echo "✅ Приветственное сообщение отправлено\n";
        } else {
            echo "❌ Ошибка отправки сообщения: " . $messageResult . "\n";
        }
    } else {
        echo "⚠️ Не найдено сообщений для отправки приветствия\n";
    }
} else {
    echo "⚠️ Не удалось получить обновления бота\n";
}

echo "\n3️⃣ Инструкции по тестированию:\n";
echo "   1. Найдите бота @M_150_site_bot в Telegram\n";
echo "   2. Нажмите /start или кнопку 'Открыть приложение'\n";
echo "   3. Mini App должен открыться автоматически\n";
echo "   4. Если не открывается, проверьте URL: {$webAppUrl}\n\n";

echo "4️⃣ Альтернативный способ тестирования:\n";
echo "   Откройте в браузере: {$webAppUrl}?telegram_id=123456789\n\n";

echo "🎉 Настройка завершена!\n";

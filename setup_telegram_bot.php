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

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode(['commands' => $commands])
    ]
]);

$response = file_get_contents("https://api.telegram.org/bot{$botToken}/setMyCommands", false, $context);

$result = json_decode($response, true);
if ($result && $result['ok']) {
    echo "✅ Команды установлены успешно\n";
} else {
    echo "❌ Ошибка установки команд: " . $response . "\n";
}

// 2. Устанавливаем меню бота
echo "\n2️⃣ Установка меню бота...\n";
$menuButton = [
    'type' => 'web_app',
    'text' => 'Открыть приложение',
    'web_app' => [
        'url' => $webAppUrl
    ]
];

$context2 = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => json_encode(['menu_button' => $menuButton])
    ]
]);

$response = file_get_contents("https://api.telegram.org/bot{$botToken}/setChatMenuButton", false, $context2);

$result = json_decode($response, true);
if ($result && $result['ok']) {
    echo "✅ Меню бота установлено успешно\n";
} else {
    echo "❌ Ошибка установки меню: " . $response . "\n";
}

// 3. Отправляем приветственное сообщение с кнопкой
echo "\n3️⃣ Отправка приветственного сообщения...\n";
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
$updatesResponse = file_get_contents("https://api.telegram.org/bot{$botToken}/getUpdates");
$updates = json_decode($updatesResponse, true);

if ($updates && $updates['ok'] && !empty($updates['result'])) {
    $latestUpdate = end($updates['result']);
    if (isset($latestUpdate['message'])) {
        $chatId = $latestUpdate['message']['chat']['id'];
        
        $context3 = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode([
                    'chat_id' => $chatId,
                    'text' => $message,
                    'reply_markup' => $inlineKeyboard
                ])
            ]
        ]);
        
        $response = file_get_contents("https://api.telegram.org/bot{$botToken}/sendMessage", false, $context3);
        
        $result = json_decode($response, true);
        if ($result && $result['ok']) {
            echo "✅ Приветственное сообщение отправлено\n";
        } else {
            echo "❌ Ошибка отправки сообщения: " . $response . "\n";
        }
    } else {
        echo "⚠️ Не найдено сообщений для отправки приветствия\n";
    }
} else {
    echo "⚠️ Не удалось получить обновления бота\n";
}

echo "\n4️⃣ Инструкции по тестированию:\n";
echo "   1. Найдите бота @M_150_site_bot в Telegram\n";
echo "   2. Нажмите /start или кнопку 'Открыть приложение'\n";
echo "   3. Mini App должен открыться автоматически\n";
echo "   4. Если не открывается, проверьте URL: {$webAppUrl}\n\n";

echo "🎉 Настройка завершена!\n";

<?php

echo "🧪 Тестирование Telegram Mini App...\n\n";

echo "1️⃣ Проверка доступности страницы регистрации...\n";
$url = 'http://localhost:8000/telegram-register?telegram_id=123456789';
$response = @file_get_contents($url);

if ($response !== false) {
    echo "✅ Страница доступна: {$url}\n";
} else {
    echo "❌ Страница недоступна: {$url}\n";
    echo "   Убедитесь, что сервер запущен: php artisan serve\n";
}

echo "\n2️⃣ Проверка тестовой страницы Mini App...\n";
$testUrl = 'http://localhost:8000/test_mini_app.html';
$testResponse = @file_get_contents($testUrl);

if ($testResponse !== false) {
    echo "✅ Тестовая страница доступна: {$testUrl}\n";
} else {
    echo "❌ Тестовая страница недоступна: {$testUrl}\n";
}

echo "\n3️⃣ Инструкции по настройке Mini App:\n";
echo "   1. Откройте @BotFather в Telegram\n";
echo "   2. Выберите /mybots → M_150_site_bot\n";
echo "   3. Нажмите 'Bot Settings' → 'Menu Button'\n";
echo "   4. Выберите 'Configure menu button'\n";
echo "   5. Введите текст: 'Открыть приложение'\n";
echo "   6. Выберите 'Web App'\n";
echo "   7. Введите URL: http://localhost:8000/telegram-register\n";
echo "   8. Сохраните настройки\n\n";

echo "4️⃣ Альтернативный способ - через команду /newapp:\n";
echo "   1. Откройте @BotFather\n";
echo "   2. Нажмите /newapp\n";
echo "   3. Выберите бота M_150_site_bot\n";
echo "   4. Введите название: Metrika3 Cabinet\n";
echo "   5. Введите описание: Система управления кабинетами\n";
echo "   6. Введите URL: http://localhost:8000/telegram-register\n";
echo "   7. Введите короткое имя: metrika3-cabinet\n\n";

echo "5️⃣ Тестирование после настройки:\n";
echo "   1. Найдите бота @M_150_site_bot в Telegram\n";
echo "   2. Нажмите /start\n";
echo "   3. Должна появиться кнопка 'Открыть приложение'\n";
echo "   4. Нажмите кнопку - Mini App должен открыться\n";
echo "   5. Если не открывается, проверьте URL в настройках бота\n\n";

echo "6️⃣ Отладка:\n";
echo "   - Проверьте, что сервер запущен: php artisan serve\n";
echo "   - Проверьте URL: http://localhost:8000/telegram-register\n";
echo "   - Проверьте настройки бота в @BotFather\n";
echo "   - Убедитесь, что URL доступен извне (для продакшена)\n\n";

echo "🎉 Инструкции готовы!\n";

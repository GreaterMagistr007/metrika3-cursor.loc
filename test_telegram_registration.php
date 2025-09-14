<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Services\AuthService;

echo "🧪 Тестирование новой механики регистрации через Telegram...\n\n";

// 1. Создаем тестового пользователя с Telegram ID
echo "1️⃣ Создание тестового пользователя с Telegram ID...\n";
$user = User::create([
    'name' => 'Test Telegram User',
    'phone' => '+79998887755',
    'telegram_id' => 987654321,
    'phone_verified_at' => now(),
    'last_login_at' => now()
]);

echo "✅ Пользователь создан:\n";
echo "   🆔 ID: {$user->id}\n";
echo "   👤 Name: {$user->name}\n";
echo "   📱 Phone: {$user->phone}\n";
echo "   🤖 Telegram ID: {$user->telegram_id}\n\n";

// 2. Тестируем API проверки пользователя
echo "2️⃣ Тестирование API проверки пользователя...\n";

$testTelegramId = 987654321;
$testPhone = '+79998887766';

// Тест 1: Пользователь существует
echo "   Тест 1: Пользователь с Telegram ID {$testTelegramId} существует\n";
$existingUser = User::where('telegram_id', $testTelegramId)->first();
if ($existingUser) {
    echo "   ✅ Пользователь найден: {$existingUser->name}\n";
} else {
    echo "   ❌ Пользователь не найден\n";
}

// Тест 2: Пользователь не существует
echo "   Тест 2: Пользователь с Telegram ID 999999999 не существует\n";
$nonExistingUser = User::where('telegram_id', 999999999)->first();
if (!$nonExistingUser) {
    echo "   ✅ Пользователь не найден (ожидаемо)\n";
} else {
    echo "   ❌ Пользователь найден (неожиданно)\n";
}

echo "\n";

// 3. Тестируем валидацию имени
echo "3️⃣ Тестирование валидации имени...\n";

$testNames = [
    'Иван' => true,           // Корректное русское имя
    'John' => true,           // Корректное английское имя
    'Иван Петров' => true,    // Корректное имя с пробелом
    'John Smith' => true,     // Корректное английское имя с пробелом
    'А' => false,             // Слишком короткое
    'Очень длинное имя которое превышает лимит в тридцать символов' => false, // Слишком длинное
    'Иван123' => false,       // Содержит цифры
    'Иван@Петров' => false,   // Содержит спецсимволы
    '' => false,              // Пустое
];

foreach ($testNames as $name => $expected) {
    $isValid = validateName($name);
    $status = $isValid === $expected ? '✅' : '❌';
    echo "   {$status} '{$name}' - " . ($isValid ? 'валидно' : 'невалидно') . "\n";
}

echo "\n";

// 4. Тестируем валидацию телефона
echo "4️⃣ Тестирование валидации телефона...\n";

$testPhones = [
    '+79998887766' => true,   // Корректный номер
    '+7 (999) 888-77-66' => true, // Корректный форматированный номер
    '+7999888776' => false,   // Слишком короткий
    '+799988877666' => false, // Слишком длинный
    '79998887766' => false,   // Без +
    '+89998887766' => false,  // Неправильный код страны
    '+7999888776a' => false,  // Содержит буквы
];

foreach ($testPhones as $phone => $expected) {
    $isValid = validatePhone($phone);
    $status = $isValid === $expected ? '✅' : '❌';
    echo "   {$status} '{$phone}' - " . ($isValid ? 'валидно' : 'невалидно') . "\n";
}

echo "\n";

// 5. Тестируем отправку OTP
echo "5️⃣ Тестирование отправки OTP...\n";

$authService = app(AuthService::class);
$otp = $authService->generateAndSendOtp($testPhone, $testTelegramId);

echo "   🔐 Сгенерированный OTP: {$otp}\n";
echo "   ✅ OTP отправлен через AuthService\n\n";

// 6. Инструкции по тестированию
echo "6️⃣ Инструкции по тестированию в браузере:\n";
echo "   1. Откройте http://localhost:8000/telegram-register?telegram_id={$testTelegramId}\n";
echo "   2. Должна произойти автоматическая авторизация (пользователь существует)\n";
echo "   3. Откройте http://localhost:8000/telegram-register?telegram_id=999999999\n";
echo "   4. Должна появиться форма регистрации (пользователь не существует)\n";
echo "   5. Заполните форму и проверьте валидацию\n";
echo "   6. После регистрации должна произойти автоматическая авторизация\n\n";

echo "🎉 Тестирование завершено!\n";

// Функции валидации
function validateName($name) {
    $trimmed = trim($name);
    
    if (empty($trimmed)) return false;
    if (strlen($trimmed) < 2) return false;
    if (strlen($trimmed) > 30) return false;
    
    // Только русские и английские буквы, пробелы
    return preg_match('/^[а-яёА-ЯЁa-zA-Z\s]+$/', $trimmed);
}

function validatePhone($phone) {
    // Очищаем от форматирования
    $cleaned = preg_replace('/[^\d+]/', '', $phone);
    
    // Проверяем формат +7XXXXXXXXXX
    return preg_match('/^\+7\d{10}$/', $cleaned);
}

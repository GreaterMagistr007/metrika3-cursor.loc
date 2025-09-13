<?php

declare(strict_types=1);

/**
 * Скрипт для запуска автотестов бэкенда
 */

echo "🧪 ЗАПУСК АВТОТЕСТОВ БЭКЕНДА\n";
echo "============================\n\n";

// Проверяем, что мы в корневой директории проекта
if (!file_exists('artisan')) {
    echo "❌ Ошибка: Запустите скрипт из корневой директории проекта\n";
    exit(1);
}

// Проверяем, что PHPUnit установлен
$phpunitPath = 'vendor/bin/phpunit';
if (PHP_OS_FAMILY === 'Windows') {
    $phpunitPath = 'vendor\\bin\\phpunit.bat';
}

if (!file_exists($phpunitPath)) {
    echo "❌ Ошибка: PHPUnit не установлен. Запустите: composer install\n";
    exit(1);
}

// Функция для выполнения команд
function runCommand(string $command): array
{
    echo "Выполняется: $command\n";
    $output = [];
    $returnCode = 0;
    exec($command . ' 2>&1', $output, $returnCode);
    return [$output, $returnCode];
}

// Функция для вывода результатов
function printResults(array $output, int $returnCode, string $testName): bool
{
    echo "\n--- Результаты $testName ---\n";
    foreach ($output as $line) {
        echo $line . "\n";
    }
    
    if ($returnCode === 0) {
        echo "✅ $testName: ПРОЙДЕН\n";
        return true;
    } else {
        echo "❌ $testName: ПРОВАЛЕН\n";
        return false;
    }
    echo "\n";
}

$allPassed = true;

// 1. Тесты аутентификации
echo "1. Тестирование аутентификации...\n";
[$output, $returnCode] = runCommand("$phpunitPath tests/Feature/AuthTest.php");
$allPassed &= printResults($output, $returnCode, 'Аутентификация');

// 2. Тесты кабинетов
echo "2. Тестирование кабинетов...\n";
[$output, $returnCode] = runCommand("$phpunitPath tests/Feature/CabinetTest.php");
$allPassed &= printResults($output, $returnCode, 'Кабинеты');

// 3. Тесты управления пользователями в кабинетах
echo "3. Тестирование управления пользователями...\n";
[$output, $returnCode] = runCommand("$phpunitPath tests/Feature/CabinetUserTest.php");
$allPassed &= printResults($output, $returnCode, 'Управление пользователями');

// 4. Тесты системы прав
echo "4. Тестирование системы прав...\n";
[$output, $returnCode] = runCommand("$phpunitPath tests/Feature/PermissionTest.php");
$allPassed &= printResults($output, $returnCode, 'Система прав');

// 5. Тесты аудита
echo "5. Тестирование аудита...\n";
[$output, $returnCode] = runCommand("$phpunitPath tests/Feature/AuditTest.php");
$allPassed &= printResults($output, $returnCode, 'Аудит');

// 6. Все тесты вместе
echo "6. Запуск всех тестов...\n";
[$output, $returnCode] = runCommand("$phpunitPath tests/Feature/");
$allPassed &= printResults($output, $returnCode, 'Все тесты');

// Итоговый результат
echo "\n" . str_repeat("=", 50) . "\n";
if ($allPassed) {
    echo "🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО!\n";
    echo "✅ Бэкенд готов к использованию\n";
    exit(0);
} else {
    echo "❌ НЕКОТОРЫЕ ТЕСТЫ ПРОВАЛЕНЫ!\n";
    echo "🔧 Проверьте ошибки выше и исправьте их\n";
    exit(1);
}

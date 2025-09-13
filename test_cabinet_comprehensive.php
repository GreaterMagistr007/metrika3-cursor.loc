<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== КОМПЛЕКСНОЕ ТЕСТИРОВАНИЕ API КАБИНЕТОВ ===\n\n";

// Создаем тестовых пользователей
echo "1. Создание тестовых пользователей...\n";
$user1 = \App\Models\User::firstOrCreate(
    ['phone' => '+1234567890'],
    ['name' => 'Владелец Кабинета', 'phone_verified_at' => now()]
);
$user2 = \App\Models\User::firstOrCreate(
    ['phone' => '+1234567891'],
    ['name' => 'Менеджер', 'phone_verified_at' => now()]
);
$user3 = \App\Models\User::firstOrCreate(
    ['phone' => '+1234567892'],
    ['name' => 'Оператор', 'phone_verified_at' => now()]
);

echo "   Пользователь 1 (Владелец): ID {$user1->id}, Телефон {$user1->phone}\n";
echo "   Пользователь 2 (Менеджер): ID {$user2->id}, Телефон {$user2->phone}\n";
echo "   Пользователь 3 (Оператор): ID {$user3->id}, Телефон {$user3->phone}\n\n";

// Создаем токен для владельца
$token = $user1->createToken('test-token')->plainTextToken;
echo "2. Создан токен для владельца: " . substr($token, 0, 20) . "...\n\n";

// Функция для выполнения HTTP запросов
function makeRequest($method, $url, $data = null, $token = null) {
    $headers = ['Content-Type' => 'application/json'];
    if ($token) {
        $headers['Authorization'] = 'Bearer ' . $token;
    }
    
    $response = \Illuminate\Support\Facades\Http::withHeaders($headers);
    
    switch (strtoupper($method)) {
        case 'GET':
            return $response->get($url);
        case 'POST':
            return $response->post($url, $data);
        case 'PUT':
            return $response->put($url, $data);
        case 'DELETE':
            return $response->delete($url);
        case 'PATCH':
            return $response->patch($url, $data);
        default:
            throw new Exception("Unsupported method: $method");
    }
}

// Функция для вывода результата
function printResult($testName, $response) {
    echo "   $testName:\n";
    echo "   Статус: " . $response->status() . "\n";
    echo "   Ответ: " . $response->body() . "\n\n";
}

// Тест 1: Создание кабинета
echo "3. ТЕСТ: Создание кабинета\n";
$createResponse = makeRequest('POST', 'http://localhost:8000/api/cabinets', [
    'name' => 'Тестовый Кабинет',
    'description' => 'Описание тестового кабинета'
], $token);
printResult('Создание кабинета', $createResponse);

if ($createResponse->status() !== 201) {
    echo "❌ ОШИБКА: Не удалось создать кабинет. Прерываем тестирование.\n";
    exit(1);
}

$cabinetData = $createResponse->json();
$cabinetId = $cabinetData['cabinet']['id'];
echo "✅ Кабинет создан с ID: $cabinetId\n\n";

// Тест 2: Получение списка кабинетов
echo "4. ТЕСТ: Получение списка кабинетов\n";
$listResponse = makeRequest('GET', 'http://localhost:8000/api/cabinets', null, $token);
printResult('Список кабинетов', $listResponse);

// Тест 3: Получение деталей кабинета
echo "5. ТЕСТ: Получение деталей кабинета\n";
$detailsResponse = makeRequest('GET', "http://localhost:8000/api/cabinets/$cabinetId", null, $token);
printResult('Детали кабинета', $detailsResponse);

// Тест 4: Приглашение пользователя (менеджер)
echo "6. ТЕСТ: Приглашение менеджера в кабинет\n";
$inviteManagerResponse = makeRequest('POST', "http://localhost:8000/api/cabinets/$cabinetId/invite", [
    'phone' => '+1234567891',
    'role' => 'manager'
], $token);
printResult('Приглашение менеджера', $inviteManagerResponse);

// Тест 5: Приглашение пользователя (оператор)
echo "7. ТЕСТ: Приглашение оператора в кабинет\n";
$inviteOperatorResponse = makeRequest('POST', "http://localhost:8000/api/cabinets/$cabinetId/invite", [
    'phone' => '+1234567892',
    'role' => 'operator'
], $token);
printResult('Приглашение оператора', $inviteOperatorResponse);

// Тест 6: Попытка пригласить несуществующего пользователя
echo "8. ТЕСТ: Попытка пригласить несуществующего пользователя\n";
$inviteNonExistentResponse = makeRequest('POST', "http://localhost:8000/api/cabinets/$cabinetId/invite", [
    'phone' => '+9999999999',
    'role' => 'operator'
], $token);
printResult('Приглашение несуществующего пользователя', $inviteNonExistentResponse);

// Тест 7: Попытка пригласить уже существующего пользователя
echo "9. ТЕСТ: Попытка пригласить уже существующего пользователя\n";
$inviteExistingResponse = makeRequest('POST', "http://localhost:8000/api/cabinets/$cabinetId/invite", [
    'phone' => '+1234567891',
    'role' => 'operator'
], $token);
printResult('Приглашение существующего пользователя', $inviteExistingResponse);

// Тест 8: Обновление кабинета
echo "10. ТЕСТ: Обновление кабинета\n";
$updateResponse = makeRequest('PUT', "http://localhost:8000/api/cabinets/$cabinetId", [
    'name' => 'Обновленный Тестовый Кабинет',
    'description' => 'Обновленное описание кабинета'
], $token);
printResult('Обновление кабинета', $updateResponse);

// Тест 9: Получение обновленных деталей кабинета
echo "11. ТЕСТ: Получение обновленных деталей кабинета\n";
$updatedDetailsResponse = makeRequest('GET', "http://localhost:8000/api/cabinets/$cabinetId", null, $token);
printResult('Обновленные детали кабинета', $updatedDetailsResponse);

// Тест 10: Создание токена для менеджера и попытка доступа
echo "12. ТЕСТ: Доступ менеджера к кабинету\n";
$managerToken = $user2->createToken('manager-token')->plainTextToken;
$managerAccessResponse = makeRequest('GET', "http://localhost:8000/api/cabinets/$cabinetId", null, $managerToken);
printResult('Доступ менеджера к кабинету', $managerAccessResponse);

// Тест 11: Попытка менеджера удалить пользователя (должна быть ошибка)
echo "13. ТЕСТ: Попытка менеджера удалить пользователя (должна быть ошибка)\n";
$managerRemoveResponse = makeRequest('DELETE', "http://localhost:8000/api/cabinets/$cabinetId/users/$user3->id", null, $managerToken);
printResult('Попытка менеджера удалить пользователя', $managerRemoveResponse);

// Тест 12: Удаление пользователя владельцем
echo "14. ТЕСТ: Удаление пользователя владельцем\n";
$removeUserResponse = makeRequest('DELETE', "http://localhost:8000/api/cabinets/$cabinetId/users/$user3->id", null, $token);
printResult('Удаление пользователя владельцем', $removeUserResponse);

// Тест 13: Передача прав владения
echo "15. ТЕСТ: Передача прав владения\n";
$transferResponse = makeRequest('PATCH', "http://localhost:8000/api/cabinets/$cabinetId/transfer-ownership", [
    'new_owner_phone' => '+1234567891'
], $token);
printResult('Передача прав владения', $transferResponse);

// Тест 14: Проверка доступа нового владельца
echo "16. ТЕСТ: Проверка доступа нового владельца\n";
$newOwnerAccessResponse = makeRequest('GET', "http://localhost:8000/api/cabinets/$cabinetId", null, $managerToken);
printResult('Доступ нового владельца', $newOwnerAccessResponse);

// Тест 15: Попытка старого владельца удалить кабинет (должна быть ошибка)
echo "17. ТЕСТ: Попытка старого владельца удалить кабинет (должна быть ошибка)\n";
$oldOwnerDeleteResponse = makeRequest('DELETE', "http://localhost:8000/api/cabinets/$cabinetId", null, $token);
printResult('Попытка старого владельца удалить кабинет', $oldOwnerDeleteResponse);

// Тест 16: Удаление кабинета новым владельцем
echo "18. ТЕСТ: Удаление кабинета новым владельцем\n";
$deleteResponse = makeRequest('DELETE', "http://localhost:8000/api/cabinets/$cabinetId", null, $managerToken);
printResult('Удаление кабинета новым владельцем', $deleteResponse);

echo "=== ТЕСТИРОВАНИЕ ЗАВЕРШЕНО ===\n";
echo "Проверьте результаты выше. Все тесты должны показать ожидаемое поведение:\n";
echo "- Создание, обновление, удаление кабинетов\n";
echo "- Приглашение и удаление пользователей\n";
echo "- Передача прав владения\n";
echo "- Проверка прав доступа\n";
echo "- Обработка ошибок\n";

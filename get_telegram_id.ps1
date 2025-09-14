Write-Host "🤖 Получение Telegram ID через PowerShell..." -ForegroundColor Green
Write-Host ""
Write-Host "📋 Инструкция:" -ForegroundColor Yellow
Write-Host "1. Найдите бота @M_150_site_bot в Telegram" -ForegroundColor White
Write-Host "2. Нажмите /start" -ForegroundColor White
Write-Host "3. Отправьте любое сообщение боту (например, 'Привет')" -ForegroundColor White
Write-Host "4. Нажмите Enter для проверки..." -ForegroundColor White
Write-Host ""
Read-Host "Нажмите Enter для продолжения"

Write-Host "🔍 Проверяем обновления..." -ForegroundColor Cyan

try {
    $response = Invoke-RestMethod -Uri "https://api.telegram.org/bot7396908423:AAFk3WRot3sy_fpUnii1Up0-M5e8Z7PlbkA/getUpdates"
    
    if ($response.ok -and $response.result.Count -gt 0) {
        Write-Host "📨 Найдено сообщений: $($response.result.Count)" -ForegroundColor Green
        Write-Host ""
        
        $found = $false
        foreach ($update in $response.result) {
            if ($update.message) {
                $message = $update.message
                $user = $message.from
                
                Write-Host "👤 Пользователь: $($user.first_name) $($user.last_name)" -ForegroundColor White
                Write-Host "🆔 Telegram ID: $($user.id)" -ForegroundColor Yellow
                Write-Host "💬 Сообщение: $($message.text)" -ForegroundColor White
                Write-Host "📅 Дата: $(Get-Date -UnixTimeSeconds $message.date -Format 'yyyy-MM-dd HH:mm:ss')" -ForegroundColor Gray
                Write-Host "---" -ForegroundColor Gray
                
                $found = $true
            }
        }
        
        if ($found) {
            Write-Host ""
            Write-Host "✅ Ваш Telegram ID найден!" -ForegroundColor Green
            Write-Host "🌐 Тестовая ссылка: http://localhost:8000/telegram-register?telegram_id=$($user.id)" -ForegroundColor Cyan
        } else {
            Write-Host "❌ Сообщения не найдены. Убедитесь, что вы отправили сообщение боту." -ForegroundColor Red
        }
    } else {
        Write-Host "❌ Не удалось получить обновления или они пустые" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Ошибка при получении данных: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Read-Host "Нажмите Enter для выхода"

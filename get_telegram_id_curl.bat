@echo off
echo 🤖 Получение Telegram ID через curl...
echo.
echo 📋 Инструкция:
echo 1. Найдите бота @M_150_site_bot в Telegram
echo 2. Нажмите /start
echo 3. Отправьте любое сообщение боту (например, 'Привет')
echo 4. Нажмите Enter для проверки...
echo.
pause

echo 🔍 Проверяем обновления...
curl -s "https://api.telegram.org/bot7396908423:AAFk3WRot3sy_fpUnii1Up0-M5e8Z7PlbkA/getUpdates" | jq -r '.result[] | select(.message) | "👤 Пользователь: " + .message.from.first_name + " " + (.message.from.last_name // "") + "\n🆔 Telegram ID: " + (.message.from.id | tostring) + "\n💬 Сообщение: " + .message.text + "\n---"'

echo.
echo ✅ Если вы видите ваш Telegram ID выше, используйте его для тестирования!
echo 🌐 Тестовая ссылка: http://localhost:8000/telegram-register?telegram_id=ВАШ_ID
echo.
pause

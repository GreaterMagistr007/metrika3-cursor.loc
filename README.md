# 🏢 Metrika3 - Система управления кабинетами

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-red.svg" alt="Laravel Version">
  <img src="https://img.shields.io/badge/Vue.js-3.x-green.svg" alt="Vue.js Version">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP Version">
  <img src="https://img.shields.io/badge/Tests-Passing-brightgreen.svg" alt="Tests Status">
</p>

## 📋 Описание проекта

Metrika3 - это современная система управления кабинетами с поддержкой Telegram Mini App, построенная на Laravel 12 и Vue.js 3. Система предоставляет полный функционал для управления пользователями, кабинетами, правами доступа и аудитом действий.

## ✨ Основные возможности

- 🔐 **Аутентификация через Telegram** - Вход в систему через Telegram Mini App
- 👥 **Управление пользователями** - Регистрация, приглашение, управление ролями
- 🏢 **Управление кабинетами** - Создание, настройка, передача прав владения
- 🔑 **Система прав и ролей** - Гибкая система разрешений (admin, manager, operator)
- 📊 **Аудит и логирование** - Полное отслеживание действий пользователей
- 🧪 **Автотестирование** - Полное покрытие тестами всех функций бэкенда

## 🚀 Быстрый старт

### Установка

1. **Клонирование репозитория**
   ```bash
   git clone <repository-url>
   cd metrika3-cursor.loc
   ```

2. **Установка зависимостей**
   ```bash
   composer install
   npm install
   ```

3. **Настройка окружения**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Настройка базы данных**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Запуск сервера**
   ```bash
   php artisan serve
   npm run dev
   ```

## 🧪 Тестирование

### Запуск автотестов

```bash
# Запуск всех тестов
php run_tests.php

# Или через PHPUnit
vendor/bin/phpunit tests/Feature/

# Запуск конкретных тестов
vendor/bin/phpunit tests/Feature/AuthTest.php
vendor/bin/phpunit tests/Feature/CabinetTest.php
vendor/bin/phpunit tests/Feature/CabinetUserTest.php
vendor/bin/phpunit tests/Feature/PermissionTest.php
vendor/bin/phpunit tests/Feature/AuditTest.php
```

## 🤖 Telegram Bot команды

### Проверка сообщений бота

```bash
# Проверить новые сообщения в боте
php artisan telegram:check

# Проверить с определенного offset
php artisan telegram:check --offset=12345

# Добавить в cron для автоматической проверки каждые 5 минут
*/5 * * * * cd /path/to/project && php artisan telegram:check >> /dev/null 2>&1
```

### Настройка Telegram Bot

1. **Создание бота через @BotFather**
   - Отправьте команду `/newbot`
   - Укажите имя и username бота
   - Получите токен бота

2. **Настройка переменных окружения**
   ```env
   TELEGRAM_BOT_TOKEN=your_bot_token_here
   TELEGRAM_BOT_SECRET=your_bot_secret_here
   ```

3. **Настройка команды /start**
   - Бот автоматически обрабатывает команду `/start`
   - Создает пользователя в базе данных
   - Отправляет ссылку для входа в приложение

4. **Mini App настройка (для продакшна)**
   - Используйте команду `/newapp` в @BotFather
   - Укажите URL: `https://yourdomain.com/telegram-register`
   - Настройте кнопку "Открыть приложение"

### Покрытие тестами

- ✅ **Аутентификация** - Регистрация, вход, верификация OTP
- ✅ **Кабинеты** - CRUD операции, права доступа
- ✅ **Управление пользователями** - Приглашение, удаление, передача прав
- ✅ **Система прав** - Роли, разрешения, проверка доступа
- ✅ **Аудит** - Логирование действий, отслеживание изменений

## 📁 Структура проекта

```
app/
├── Http/
│   ├── Controllers/Api/     # API контроллеры
│   ├── Requests/Api/        # Валидация запросов
│   └── Resources/           # API ресурсы
├── Models/                  # Eloquent модели
├── Services/                # Бизнес-логика
└── Traits/                  # Переиспользуемые трейты

tests/
├── Feature/                 # Функциональные тесты
└── Unit/                    # Модульные тесты

database/
├── factories/               # Фабрики для тестов
├── migrations/              # Миграции БД
└── seeders/                 # Сидеры БД
```

## 🔧 API Endpoints

### Аутентификация
- `POST /api/auth/register` - Регистрация пользователя
- `POST /api/auth/login` - Вход в систему
- `POST /api/auth/verify-otp` - Верификация OTP
- `GET /api/auth/me` - Получение профиля
- `POST /api/auth/logout` - Выход из системы

### Кабинеты
- `GET /api/cabinets` - Список кабинетов
- `POST /api/cabinets` - Создание кабинета
- `GET /api/cabinets/{id}` - Детали кабинета
- `PUT /api/cabinets/{id}` - Обновление кабинета
- `DELETE /api/cabinets/{id}` - Удаление кабинета

### Управление пользователями
- `POST /api/cabinets/{id}/invite` - Приглашение пользователя
- `DELETE /api/cabinets/{id}/users/{user}` - Удаление пользователя
- `PATCH /api/cabinets/{id}/transfer-ownership` - Передача прав владения

## 🛠️ Технологии

- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Vue.js 3, Vite, TailwindCSS
- **База данных**: SQLite (разработка), MySQL/PostgreSQL (продакшн)
- **Тестирование**: PHPUnit
- **Аутентификация**: Laravel Sanctum
- **Интеграция**: Telegram Bot API

## 📝 Требования к разработке

- **ВСЕ** функции бэкенда должны быть покрыты тестами
- После каждого шага разработки запускать полный набор тестов
- Все тесты должны проходить успешно перед переходом к следующему шагу
- При добавлении новой функциональности создавать соответствующие тесты

## 📄 Лицензия

Проект разрабатывается в рамках внутреннего использования.

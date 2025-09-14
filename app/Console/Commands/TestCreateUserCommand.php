<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\AuthService;

class TestCreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-user {--phone=+79998887766} {--name=Test User} {--telegram-id=123456789}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создать тестового пользователя с Telegram ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $phone = $this->option('phone');
        $name = $this->option('name');
        $telegramId = $this->option('telegram-id');

        $this->info('👤 Создание тестового пользователя с Telegram ID...');

        // Проверяем, не существует ли уже пользователь с таким телефоном
        $existingUser = User::where('phone', $phone)->first();
        if ($existingUser) {
            $this->warn("⚠️  Пользователь с телефоном {$phone} уже существует:");
            $this->line("🆔 ID: {$existingUser->id}");
            $this->line("👤 Name: {$existingUser->name}");
            $this->line("📱 Phone: {$existingUser->phone}");
            $this->line("🤖 Telegram ID: {$existingUser->telegram_id}");
            
            if ($this->confirm('Обновить существующего пользователя?')) {
                $existingUser->update([
                    'name' => $name,
                    'telegram_id' => $telegramId,
                    'phone_verified_at' => now(),
                    'last_login_at' => now()
                ]);
                $this->info('✅ Пользователь обновлен');
            } else {
                $this->info('❌ Операция отменена');
                return 0;
            }
        } else {
            // Создаем нового пользователя
            $user = User::create([
                'name' => $name,
                'phone' => $phone,
                'telegram_id' => $telegramId,
                'phone_verified_at' => now(),
                'last_login_at' => now()
            ]);

            $this->info('✅ Пользователь создан:');
            $this->line("🆔 ID: {$user->id}");
            $this->line("👤 Name: {$user->name}");
            $this->line("📱 Phone: {$user->phone}");
            $this->line("🤖 Telegram ID: {$user->telegram_id}");
        }

        $this->newLine();

        // Тестируем отправку OTP
        $this->info('📤 Тестирование отправки OTP...');
        
        try {
            $authService = app(AuthService::class);
            $otp = $authService->generateAndSendOtp($phone, $telegramId);
            
            $this->info("🔐 Сгенерированный OTP: {$otp}");
            $this->info('✅ OTP отправлен через AuthService');
            $this->newLine();
            $this->info("🧪 Теперь можете протестировать регистрацию с номером {$phone}");
            $this->info("📱 Введите код: {$otp}");
        } catch (\Exception $e) {
            $this->error("❌ Ошибка отправки OTP: " . $e->getMessage());
        }

        return 0;
    }
}

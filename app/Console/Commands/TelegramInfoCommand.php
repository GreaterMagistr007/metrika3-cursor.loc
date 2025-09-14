<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TelegramInfoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:info';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Получить информацию о Telegram боте';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');

        if (empty($botToken)) {
            $this->error('❌ TELEGRAM_BOT_TOKEN не настроен в .env файле');
            return 1;
        }

        $this->info('🤖 Получение информации о Telegram Bot...');
        $this->line('📱 Bot Token: ' . substr($botToken, 0, 10) . '...');
        $this->newLine();

        try {
            $response = Http::timeout(10)->get("https://api.telegram.org/bot{$botToken}/getMe");

            if ($response->successful()) {
                $data = $response->json();
                if ($data['ok']) {
                    $bot = $data['result'];
                    $this->info('✅ Информация о боте получена:');
                    $this->line('🆔 ID: ' . $bot['id']);
                    $this->line('👤 Username: @' . $bot['username']);
                    $this->line('📛 First Name: ' . $bot['first_name']);
                    $this->line('🔗 Ссылка: https://t.me/' . $bot['username']);
                    $this->newLine();
                    $this->info('💡 Для тестирования отправьте боту команду /start');
                } else {
                    $this->error('❌ Ошибка в ответе API: ' . json_encode($data));
                    return 1;
                }
            } else {
                $this->error('❌ Ошибка запроса:');
                $this->line('Status: ' . $response->status());
                $this->line('Response: ' . $response->body());
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Исключение: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}

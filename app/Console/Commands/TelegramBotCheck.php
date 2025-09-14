<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\TelegramMessage;

class TelegramBotCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:check {--offset=0 : Offset for getting updates}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for new messages in Telegram bot and process /start commands';

    /**
     * Telegram Bot Token
     */
    private $botToken;

    /**
     * Application URL for registration links
     */
    private $appUrl;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->appUrl = config('app.url');

        if (!$this->botToken) {
            $this->error('Telegram bot token not configured. Please set TELEGRAM_BOT_TOKEN in .env');
            return 1;
        }

        $offset = $this->option('offset');
        $this->info("Checking Telegram bot for new messages (offset: {$offset})...");

        try {
            $updates = $this->getUpdates($offset);

            if (empty($updates)) {
                $this->info('No new messages found.');
                return 0;
            }

            $this->info('Found ' . count($updates) . ' new message(s).');

            $lastUpdateId = $offset;
            $processedCount = 0;

            foreach ($updates as $update) {
                $lastUpdateId = $update['update_id'];

                if (isset($update['message']) && isset($update['message']['text'])) {
                    $message = $update['message'];
                    $messageId = $message['message_id'];
                    $text = $message['text'];
                    $chatId = $message['chat']['id'];
                    $userId = $message['from']['id'];
                    $firstName = $message['from']['first_name'] ?? '';
                    $lastName = $message['from']['last_name'] ?? '';
                    $username = $message['from']['username'] ?? '';
                    $messageDate = date('Y-m-d H:i:s', $message['date']);

                    // Check if message already processed
                    $existingMessage = TelegramMessage::where('message_id', $messageId)->first();
                    if ($existingMessage) {
                        $this->info("Message {$messageId} already processed, skipping...");
                        continue;
                    }

                    // Save message to database
                    TelegramMessage::create([
                        'message_id' => $messageId,
                        'chat_id' => $chatId,
                        'user_id' => $userId,
                        'text' => $text,
                        'message_date' => $messageDate,
                        'processed' => false
                    ]);

                    $this->info("Saved message from user {$userId} ({$firstName} {$lastName}): {$text}");

                    if ($text) {
                        $this->processStartCommand($chatId, $userId, $firstName, $lastName, $username);
                        $processedCount++;

                        // Mark message as processed
                        TelegramMessage::where('message_id', $messageId)->update(['processed' => true]);
                    }
                }
            }

            $this->info("Processed {$processedCount} /start command(s).");
            $this->info("Next offset: " . ($lastUpdateId + 1));

            // Save the last processed update ID
            $this->saveLastUpdateId($lastUpdateId + 1);

        } catch (\Exception $e) {
            $this->error('Error processing Telegram updates: ' . $e->getMessage());
            Log::error('Telegram bot check failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }

    /**
     * Get updates from Telegram Bot API
     */
    private function getUpdates($offset = 0)
    {
        $response = Http::timeout(30)->get("https://api.telegram.org/bot{$this->botToken}/getUpdates", [
            'offset' => $offset,
            'timeout' => 10
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to get updates from Telegram API: ' . $response->body());
        }

        $data = $response->json();

        if (!$data['ok']) {
            throw new \Exception('Telegram API error: ' . ($data['description'] ?? 'Unknown error'));
        }

        return $data['result'] ?? [];
    }

    /**
     * Process /start command
     */
    private function processStartCommand($chatId, $userId, $firstName, $lastName, $username)
    {
        try {
            // Check if user already exists
            $user = User::where('telegram_id', $userId)->first();

            if ($user) {
                // User exists - send welcome back message
                $url = "{$this->appUrl}/telegram-register?telegram_id={$userId}";
                $message = "👋 Добро пожаловать обратно, {$firstName}!\n\n" .
                    "Вы уже зарегистрированы в системе.\n" .
                    "Для входа в приложение нажмите на ссылку ниже:\n\n" .
                    "🔗 <a href=\"{$url}\">Войти в приложение</a>";

                $message = "👋 Добро пожаловать обратно, {$firstName}!<br><br>" .
                    "Вы уже зарегистрированы в системе.<br>" .
                    "Для входа в приложение нажмите на ссылку ниже:<br><br>" .
                    "🔗 <a href='{$url}'>Войти в приложение</a>";

                $this->sendMessage($chatId, $message);

                $this->info("User {$userId} already exists - sent welcome back message");
            } else {
                // Create new user
                $user = User::create([
                    'name' => trim($firstName . ' ' . $lastName),
                    'phone' => '',
                    'telegram_id' => $userId,
                    'phone_verified_at' => null,
                    'last_login_at' => now()
                ]);

                $this->info("Created new user with ID: {$user->id}, Telegram ID: {$userId}");

                $url = "{$this->appUrl}/telegram-register?telegram_id={$userId}";

                // Send registration message
                $message = "🎉 Добро пожаловать в Metrika3 Cabinet, {$firstName}!\n\n" .
                    "Вы успешно зарегистрированы в системе.\n" .
                    "Для входа в приложение нажмите на ссылку ниже:\n\n" .
                    "🔗 <a href=\"{$url}\">Войти в приложение</a>\n\n" .
                    "После входа вам будет предложено заполнить профиль.";

                $this->sendMessage($chatId, $message);

                $this->info("Sent registration message to user {$userId}");
            }

        } catch (\Exception $e) {
            $this->error("Failed to process /start command for user {$userId}: " . $e->getMessage());
            Log::error('Failed to process /start command', [
                'user_id' => $userId,
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
        }
    }


    /**
     * Send message to Telegram chat
     */
    private function sendMessage($chatId, $text)
    {
        $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to send message: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Save last processed update ID
     */
    private function saveLastUpdateId($updateId)
    {
        // Save to cache or database for persistence
        cache()->put('telegram_last_update_id', $updateId, now()->addDays(30));
    }

    /**
     * Get last processed update ID
     */
    private function getLastUpdateId()
    {
        return cache()->get('telegram_last_update_id', 0);
    }
}

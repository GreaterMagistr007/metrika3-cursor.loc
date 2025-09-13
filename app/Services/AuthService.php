<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class AuthService
{
    private const OTP_CACHE_PREFIX = 'auth_otp:';
    private const OTP_CACHE_TTL = 300; // 5 minutes
    private const OTP_LENGTH = 6;

    public function __construct(
        private readonly string $telegramBotToken,
        private readonly string $telegramBotSecret
    ) {}

    /**
     * Generate and send OTP via Telegram.
     */
    public function generateAndSendOtp(string $phone): string
    {
        // Generate OTP
        $otp = $this->generateOtp();
        
        // Store in cache
        Cache::put(
            self::OTP_CACHE_PREFIX . $phone,
            $otp,
            self::OTP_CACHE_TTL
        );

        // Send via Telegram
        $this->sendOtpViaTelegram($phone, $otp);

        return $otp;
    }

    /**
     * Verify OTP for given phone.
     */
    public function verifyOtp(string $phone, string $otp): bool
    {
        $cachedOtp = Cache::get(self::OTP_CACHE_PREFIX . $phone);
        
        return $cachedOtp && hash_equals($cachedOtp, $otp);
    }

    /**
     * Parse and validate Telegram init data.
     */
    public function parseTelegramData(string $initData): ?array
    {
        try {
            // Parse query string
            parse_str($initData, $data);
            
            if (!isset($data['user']) || !isset($data['hash'])) {
                return null;
            }

            // Validate hash
            if (!$this->validateTelegramHash($data)) {
                return null;
            }

            // Parse user data
            $userData = json_decode($data['user'], true);
            
            if (!$userData) {
                return null;
            }

            return [
                'user' => $userData,
                'auth_date' => $data['auth_date'] ?? null,
                'hash' => $data['hash']
            ];

        } catch (\Exception $e) {
            Log::error('Failed to parse Telegram data', [
                'error' => $e->getMessage(),
                'init_data' => $initData
            ]);
            
            return null;
        }
    }

    /**
     * Generate random OTP.
     */
    private function generateOtp(): string
    {
        return str_pad(
            (string) random_int(0, 999999),
            self::OTP_LENGTH,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Send OTP via Telegram Bot API.
     */
    private function sendOtpViaTelegram(string $phone, string $otp): void
    {
        try {
            // Find user by phone to get telegram_id
            $user = \App\Models\User::where('phone', $phone)->first();
            
            if (!$user || !$user->telegram_id) {
                Log::warning('Cannot send OTP: user not found or no telegram_id', [
                    'phone' => $phone
                ]);
                return;
            }

            $message = "Ваш код подтверждения: {$otp}\n\nКод действителен 5 минут.";

            $response = Http::post("https://api.telegram.org/bot{$this->telegramBotToken}/sendMessage", [
                'chat_id' => $user->telegram_id,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            if (!$response->successful()) {
                Log::error('Failed to send OTP via Telegram', [
                    'phone' => $phone,
                    'telegram_id' => $user->telegram_id,
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Exception while sending OTP via Telegram', [
                'phone' => $phone,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate Telegram hash.
     */
    private function validateTelegramHash(array $data): bool
    {
        if (!isset($data['hash'])) {
            return false;
        }

        $hash = $data['hash'];
        unset($data['hash']);

        // Sort data by key
        ksort($data);

        // Create data check string
        $dataCheckString = '';
        foreach ($data as $key => $value) {
            $dataCheckString .= "{$key}={$value}\n";
        }
        $dataCheckString = rtrim($dataCheckString, "\n");

        // Create secret key
        $secretKey = hash('sha256', $this->telegramBotSecret, true);

        // Calculate hash
        $calculatedHash = hash_hmac('sha256', $dataCheckString, $secretKey);

        return hash_equals($hash, $calculatedHash);
    }
}

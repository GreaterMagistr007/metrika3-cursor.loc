<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\TelegramAuthRequest;
use App\Http\Requests\Api\Auth\VerifyOtpRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

final class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Request OTP for phone authentication.
     */
    public function requestOtp(LoginRequest $request): JsonResponse
    {
        try {
            $phone = $request->validated()['phone'];
            
            // Check if user exists
            $user = User::where('phone', $phone)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Пользователь не найден. Обратитесь к администратору для регистрации.',
                    'error_code' => 'USER_NOT_FOUND'
                ], 404);
            }

            // Generate and send OTP
            $otp = $this->authService->generateAndSendOtp($phone);
            
            return response()->json([
                'message' => 'Код подтверждения отправлен в Telegram',
                'expires_in' => 300 // 5 minutes
            ]);

        } catch (\Exception $e) {
            Log::error('OTP request failed', [
                'phone' => $request->input('phone'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка отправки кода. Попробуйте позже.',
                'error_code' => 'OTP_SEND_FAILED'
            ], 500);
        }
    }

    /**
     * Verify OTP and authenticate user.
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $phone = $data['phone'];
            $otp = $data['otp'];

            // Verify OTP
            if (!$this->authService->verifyOtp($phone, $otp)) {
                return response()->json([
                    'message' => 'Неверный код подтверждения',
                    'error_code' => 'INVALID_OTP'
                ], 401);
            }

            // Find or create user
            $user = User::where('phone', $phone)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'Пользователь не найден',
                    'error_code' => 'USER_NOT_FOUND'
                ], 404);
            }

            // Update last login
            $user->update(['last_login_at' => now()]);

            // Create token
            $token = $user->createToken('auth-token')->plainTextToken;

            // Clear OTP from cache
            Cache::forget("auth_otp:{$phone}");

            return response()->json([
                'message' => 'Успешная авторизация',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'telegram_id' => $user->telegram_id,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]);

        } catch (\Exception $e) {
            Log::error('OTP verification failed', [
                'phone' => $request->input('phone'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка верификации. Попробуйте позже.',
                'error_code' => 'VERIFICATION_FAILED'
            ], 500);
        }
    }

    /**
     * Authenticate user via Telegram Mini App.
     */
    public function telegram(TelegramAuthRequest $request): JsonResponse
    {
        try {
            $initData = $request->validated()['init_data'];
            
            // Parse and validate Telegram data
            $telegramData = $this->authService->parseTelegramData($initData);
            
            if (!$telegramData) {
                return response()->json([
                    'message' => 'Неверные данные Telegram',
                    'error_code' => 'INVALID_TELEGRAM_DATA'
                ], 401);
            }

            $telegramId = $telegramData['user']['id'];
            $telegramUser = $telegramData['user'];

            // Find user by telegram_id
            $user = User::where('telegram_id', $telegramId)->first();

            // If user not found, check if they need to provide phone
            if (!$user) {
                return response()->json([
                    'message' => 'Пользователь не найден. Необходимо указать номер телефона.',
                    'error_code' => 'PHONE_REQUIRED',
                    'telegram_data' => $telegramUser
                ], 404);
            }

            // Update telegram data and last login
            $user->update([
                'telegram_data' => $telegramUser,
                'last_login_at' => now()
            ]);

            // Create token
            $token = $user->createToken('telegram-auth-token')->plainTextToken;

            return response()->json([
                'message' => 'Успешная авторизация через Telegram',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'telegram_id' => $user->telegram_id,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]);

        } catch (\Exception $e) {
            Log::error('Telegram auth failed', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка авторизации через Telegram',
                'error_code' => 'TELEGRAM_AUTH_FAILED'
            ], 500);
        }
    }

    /**
     * Logout user and revoke token.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($user) {
                // Revoke current token
                $user->currentAccessToken()->delete();
            }

            return response()->json([
                'message' => 'Успешный выход из системы'
            ]);

        } catch (\Exception $e) {
            Log::error('Logout failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'message' => 'Ошибка выхода из системы'
            ], 500);
        }
    }

    /**
     * Get current authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'telegram_id' => $user->telegram_id,
                'phone_verified_at' => $user->phone_verified_at,
                'last_login_at' => $user->last_login_at,
            ]
        ]);
    }
}

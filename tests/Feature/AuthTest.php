<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class AuthTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = app(AuthService::class);
    }

    /** @test */
    public function user_can_register_with_valid_data(): void
    {
        $userData = [
            'name' => 'Test User',
            'phone' => '+71234567890',
            'telegram_id' => 123456789,
            'telegram_data' => ['id' => 123456789, 'first_name' => 'Test']
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'expires_in'
            ]);

        // User is not created immediately - only after OTP verification
        $this->assertDatabaseMissing('users', [
            'phone' => '+71234567890',
            'name' => 'Test User',
            'telegram_id' => 123456789
        ]);
    }

    /** @test */
    public function registration_fails_with_invalid_phone(): void
    {
        $userData = [
            'name' => 'Test User',
            'phone' => 'invalid-phone',
            'telegram_id' => 123456789
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /** @test */
    public function registration_fails_with_duplicate_phone(): void
    {
        User::factory()->create(['phone' => '+71234567890']);

        $userData = [
            'name' => 'Test User',
            'phone' => '+71234567890',
            'telegram_id' => 123456789
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone']);
    }

    /** @test */
    public function user_can_request_otp_with_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '+71234567890',
            'phone_verified_at' => now()
        ]);

        $response = $this->postJson('/api/auth/request-otp', [
            'phone' => '+71234567890'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'expires_in'
            ]);
    }

    /** @test */
    public function request_otp_fails_with_invalid_phone(): void
    {
        $response = $this->postJson('/api/auth/request-otp', [
            'phone' => '+79999999999'
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Пользователь не найден. Обратитесь к администратору для регистрации.',
                'error_code' => 'USER_NOT_FOUND'
            ]);
    }

    /** @test */
    public function user_can_verify_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '+71234567890',
            'phone_verified_at' => null
        ]);

        // Simulate OTP in cache
        $otp = '123456';
        Cache::put('auth_otp:+71234567890', $otp, 300);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phone' => '+71234567890',
            'otp' => $otp
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'phone'],
                'token'
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone_verified_at' => now()->toDateTimeString()
        ]);
    }

    /** @test */
    public function otp_verification_fails_with_invalid_otp(): void
    {
        $user = User::factory()->create([
            'phone' => '+71234567890',
            'phone_verified_at' => null
        ]);

        $response = $this->postJson('/api/auth/verify-otp', [
            'phone' => '+71234567890',
            'otp' => '000000'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Неверный код подтверждения',
                'error_code' => 'INVALID_OTP'
            ]);
    }

    /** @test */
    public function authenticated_user_can_get_profile(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'phone' => $user->phone
                ]
            ]);
    }

    /** @test */
    public function user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Успешный выход из системы']);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'test'
        ]);
    }
}

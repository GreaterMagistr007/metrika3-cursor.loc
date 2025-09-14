<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\AuditLog;
use App\Models\Cabinet;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminTest extends TestCase
{
    use RefreshDatabase;

    private AdminUser $admin;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test admin
        $this->admin = AdminUser::factory()->create([
            'phone' => '+79999999999',
            'name' => 'Test Admin',
            'role' => 'super_admin',
        ]);
        
        $this->token = $this->admin->createToken('test')->plainTextToken;
    }

    /** @test */
    public function it_can_register_admin(): void
    {
        $response = $this->postJson('/api/admin/auth/register', [
            'phone' => '+79999999997',
            'name' => 'New Admin',
            'role' => 'admin',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'admin' => [
                    'id',
                    'phone',
                    'name',
                    'role',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('admin_users', [
            'phone' => '+79999999997',
            'name' => 'New Admin',
            'role' => 'admin',
        ]);
    }

    /** @test */
    public function it_can_login_admin(): void
    {
        $response = $this->postJson('/api/admin/auth/login', [
            'phone' => $this->admin->phone,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'admin' => [
                    'id',
                    'phone',
                    'name',
                    'role',
                ],
                'token',
            ]);
    }

    /** @test */
    public function it_can_get_admin_profile(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/admin/auth/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'admin' => [
                    'id',
                    'phone',
                    'name',
                    'role',
                    'is_super_admin',
                    'is_admin',
                ],
            ]);
    }

    /** @test */
    public function it_can_update_admin_profile(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson('/api/admin/auth/profile', [
            'name' => 'Updated Admin Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Профиль успешно обновлен',
                'admin' => [
                    'name' => 'Updated Admin Name',
                ],
            ]);
    }

    /** @test */
    public function it_can_logout_admin(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/admin/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Успешный выход из системы',
            ]);
    }

    /** @test */
    public function it_can_get_users_list(): void
    {
        // Create test users
        User::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'users' => [
                    '*' => [
                        'id',
                        'phone',
                        'name',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function it_can_get_user_details(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/admin/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'phone',
                    'name',
                    'created_at',
                ],
            ]);
    }

    /** @test */
    public function it_can_update_user(): void
    {
        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/admin/users/{$user->id}", [
            'name' => 'Updated User Name',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Пользователь успешно обновлен',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated User Name',
        ]);
    }

    /** @test */
    public function it_can_get_cabinets_list(): void
    {
        // Create test cabinets
        Cabinet::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/admin/cabinets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cabinets' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function it_can_get_cabinet_details(): void
    {
        $cabinet = Cabinet::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/admin/cabinets/{$cabinet->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cabinet' => [
                    'id',
                    'name',
                    'description',
                    'created_at',
                ],
            ]);
    }

    /** @test */
    public function it_can_get_audit_logs(): void
    {
        // Create test audit logs
        AuditLog::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/admin/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'logs' => [
                    '*' => [
                        'id',
                        'user_id',
                        'event',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function it_can_get_audit_log_statistics(): void
    {
        // Create test audit logs
        AuditLog::factory()->count(5)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/admin/audit-logs/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'statistics' => [
                    'total_logs',
                    'unique_users',
                    'unique_cabinets',
                    'event_counts',
                ],
            ]);
    }

    /** @test */
    public function it_can_get_messages_list(): void
    {
        // Create test messages
        Message::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/admin/messages');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'messages' => [
                    '*' => [
                        'id',
                        'type',
                        'title',
                        'text',
                        'is_active',
                        'created_at',
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function it_can_get_system_statistics(): void
    {
        // Create test data
        User::factory()->count(2)->create();
        Cabinet::factory()->count(1)->create();
        Message::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/admin/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'users_count',
                'cabinets_count',
                'admin_users_count',
                'audit_logs_count',
                'messages_count',
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_admin_routes(): void
    {
        $response = $this->getJson('/api/admin/users');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /** @test */
    public function it_requires_admin_role_for_admin_routes(): void
    {
        // Create regular user
        $user = User::factory()->create();
        $userToken = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $userToken,
        ])->getJson('/api/admin/users');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Доступ запрещен. Требуется аутентификация администратора.',
                'error_code' => 'ADMIN_AUTH_REQUIRED',
            ]);
    }

    /** @test */
    public function it_can_manage_admin_users(): void
    {
        // Test create admin user
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/admin/admin-users', [
            'phone' => '+79999999996',
            'name' => 'New Admin',
            'role' => 'admin',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Администратор успешно создан',
            ]);

        // Test get admin users list
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/admin/admin-users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'admin_users' => [
                    '*' => [
                        'id',
                        'phone',
                        'name',
                        'role',
                    ],
                ],
            ]);
    }
}

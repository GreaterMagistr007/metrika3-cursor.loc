<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\CabinetUser;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CabinetTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'phone' => '+1234567890',
            'phone_verified_at' => now()
        ]);
        
        $this->token = $this->user->createToken('test')->plainTextToken;
        
        // Create permissions
        Permission::factory()->create(['name' => 'user.invite', 'category' => 'user']);
        Permission::factory()->create(['name' => 'user.remove', 'category' => 'user']);
        Permission::factory()->create(['name' => 'settings.view', 'category' => 'settings']);
        Permission::factory()->create(['name' => 'cabinet.manage', 'category' => 'cabinet']);
        Permission::factory()->create(['name' => 'cabinet.view', 'category' => 'cabinet']);
        
        // Create cabinet using service to ensure proper setup with permissions
        $this->cabinet = app(\App\Services\CabinetService::class)->createCabinet(
            $this->user, 
            'Test Cabinet', 
            'Test Description'
        );
    }

    /** @test */
    public function user_can_create_cabinet(): void
    {
        $cabinetData = [
            'name' => 'Test Cabinet',
            'description' => 'Test Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/cabinets', $cabinetData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'cabinet' => ['id', 'name', 'description', 'owner', 'users_count']
            ]);

        $this->assertDatabaseHas('cabinets', [
            'name' => 'Test Cabinet',
            'description' => 'Test Description',
            'owner_id' => $this->user->id
        ]);

        // Check that owner is added to cabinet
        $this->assertDatabaseHas('cabinet_user', [
            'cabinet_id' => $response->json('cabinet.id'),
            'user_id' => $this->user->id,
            'role' => 'admin',
            'is_owner' => true
        ]);
    }

    /** @test */
    public function user_can_get_cabinets_list(): void
    {
        // Create cabinets using the service to ensure proper setup
        $cabinet1 = app(\App\Services\CabinetService::class)->createCabinet(
            $this->user, 
            'Test Cabinet 1', 
            'Test Description 1'
        );
        $cabinet2 = app(\App\Services\CabinetService::class)->createCabinet(
            $this->user, 
            'Test Cabinet 2', 
            'Test Description 2'
        );

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson('/api/cabinets');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cabinets' => [
                    '*' => ['id', 'name', 'description', 'owner', 'users_count']
                ]
            ]);

        $this->assertCount(2, $response->json('cabinets'));
    }

    /** @test */
    public function user_can_get_cabinet_details(): void
    {
        // Create cabinet using the service to ensure proper setup
        $cabinet = app(\App\Services\CabinetService::class)->createCabinet(
            $this->user, 
            'Test Cabinet', 
            'Test Description'
        );
        
        // Add some users to cabinet
        $user2 = User::factory()->create();
        CabinetUser::create([
            'cabinet_id' => $cabinet->id,
            'user_id' => $user2->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/cabinets/{$cabinet->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'cabinet' => ['id', 'name', 'description', 'owner'],
                'users' => [
                    '*' => ['id', 'cabinet_id', 'user_id', 'role', 'is_owner', 'user', 'permissions']
                ]
            ]);
    }

    /** @test */
    public function user_cannot_access_other_users_cabinet(): void
    {
        $otherUser = User::factory()->create();
        $cabinet = Cabinet::factory()->create(['owner_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/cabinets/{$cabinet->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Доступ к кабинету запрещен',
                'error_code' => 'CABINET_ACCESS_DENIED'
            ]);
    }

    /** @test */
    public function owner_can_update_cabinet(): void
    {
        $cabinet = Cabinet::factory()->create(['owner_id' => $this->user->id]);

        $updateData = [
            'name' => 'Updated Cabinet',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/cabinets/{$cabinet->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Кабинет успешно обновлен',
                'cabinet' => [
                    'name' => 'Updated Cabinet',
                    'description' => 'Updated Description'
                ]
            ]);

        $this->assertDatabaseHas('cabinets', [
            'id' => $cabinet->id,
            'name' => 'Updated Cabinet',
            'description' => 'Updated Description'
        ]);
    }

    /** @test */
    public function non_owner_cannot_update_cabinet(): void
    {
        $otherUser = User::factory()->create();
        $cabinet = Cabinet::factory()->create(['owner_id' => $otherUser->id]);

        $updateData = [
            'name' => 'Updated Cabinet',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->putJson("/api/cabinets/{$cabinet->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Только владелец может изменять кабинет',
                'error_code' => 'CABINET_OWNER_REQUIRED'
            ]);
    }

    /** @test */
    public function owner_can_delete_cabinet(): void
    {
        $cabinet = Cabinet::factory()->create(['owner_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/cabinets/{$cabinet->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Кабинет успешно удален']);

        $this->assertDatabaseMissing('cabinets', ['id' => $cabinet->id]);
    }

    /** @test */
    public function non_owner_cannot_delete_cabinet(): void
    {
        $otherUser = User::factory()->create();
        $cabinet = Cabinet::factory()->create(['owner_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->deleteJson("/api/cabinets/{$cabinet->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Только владелец может удалить кабинет',
                'error_code' => 'CABINET_OWNER_REQUIRED'
            ]);
    }

    /** @test */
    public function cabinet_creation_requires_authentication(): void
    {
        $cabinetData = [
            'name' => 'Test Cabinet',
            'description' => 'Test Description'
        ];

        $response = $this->postJson('/api/cabinets', $cabinetData);

        $response->assertStatus(401);
    }

    /** @test */
    public function cabinet_creation_validates_required_fields(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/cabinets', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}

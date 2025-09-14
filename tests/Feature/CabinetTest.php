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
    private Cabinet $cabinet;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'phone' => '+71234567890',
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
            'name' => 'New Test Cabinet',
            'description' => 'New Test Description'
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
            'name' => 'New Test Cabinet',
            'description' => 'New Test Description',
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
        // Create another cabinet using the service
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
                'data' => [
                    '*' => ['id', 'name', 'description', 'owner', 'users_count']
                ]
            ]);

        $this->assertCount(2, $response->json('data'));
    }

    /** @test */
    public function user_can_get_cabinet_details(): void
    {
        // Add some users to cabinet
        $user2 = User::factory()->create();
        CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $user2->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/cabinets/{$this->cabinet->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'description', 'owner'],
                'users' => [
                    '*' => ['id', 'cabinet_id', 'user_id', 'role', 'is_owner', 'user', 'permissions']
                ]
            ]);
    }

    /** @test */
    public function user_cannot_access_other_users_cabinet(): void
    {
        $otherUser = User::factory()->create();
        $otherCabinet = Cabinet::factory()->create(['owner_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->getJson("/api/cabinets/{$otherCabinet->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function owner_can_update_cabinet(): void
    {
        $updateData = [
            'name' => 'Updated Cabinet',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->putJson("/api/cabinets/{$this->cabinet->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Кабинет успешно обновлен'
            ]);

        $this->assertDatabaseHas('cabinets', [
            'id' => $this->cabinet->id,
            'name' => 'Updated Cabinet',
            'description' => 'Updated Description'
        ]);
    }

    /** @test */
    public function non_owner_cannot_update_cabinet(): void
    {
        $otherUser = User::factory()->create();
        $otherCabinet = Cabinet::factory()->create(['owner_id' => $otherUser->id]);
        
        $otherToken = $otherUser->createToken('test')->plainTextToken;

        $updateData = [
            'name' => 'Updated Cabinet',
            'description' => 'Updated Description'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'X-Cabinet-Id' => $otherCabinet->id
        ])->putJson("/api/cabinets/{$otherCabinet->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Недостаточно прав для выполнения действия',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'required_permission' => 'cabinet.manage'
            ]);
    }

    /** @test */
    public function owner_can_delete_cabinet(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->deleteJson("/api/cabinets/{$this->cabinet->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Кабинет успешно удален'
            ]);

        $this->assertDatabaseMissing('cabinets', [
            'id' => $this->cabinet->id
        ]);
    }

    /** @test */
    public function non_owner_cannot_delete_cabinet(): void
    {
        $otherUser = User::factory()->create();
        $otherCabinet = Cabinet::factory()->create(['owner_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'X-Cabinet-Id' => $otherCabinet->id
        ])->deleteJson("/api/cabinets/{$otherCabinet->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Недостаточно прав для выполнения действия',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'required_permission' => 'cabinet.manage'
            ]);
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

    /** @test */
    public function cabinet_creation_validates_name_length(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token
        ])->postJson('/api/cabinets', [
            'name' => 'A', // Too short
            'description' => 'Test Description'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}

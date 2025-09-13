<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\CabinetUser;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CabinetUserTestFixed extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $manager;
    private User $operator;
    private Cabinet $cabinet;
    private string $ownerToken;
    private string $managerToken;
    private string $operatorToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->owner = User::factory()->create([
            'phone' => '+71234567890',
            'phone_verified_at' => now()
        ]);
        
        $this->manager = User::factory()->create([
            'phone' => '+71234567891',
            'phone_verified_at' => now()
        ]);
        
        $this->operator = User::factory()->create([
            'phone' => '+71234567892',
            'phone_verified_at' => now()
        ]);
        
        // Create permissions
        Permission::factory()->create(['name' => 'user.invite', 'category' => 'user']);
        Permission::factory()->create(['name' => 'user.remove', 'category' => 'user']);
        Permission::factory()->create(['name' => 'user.view', 'category' => 'user']);
        Permission::factory()->create(['name' => 'user.manage', 'category' => 'user']);
        Permission::factory()->create(['name' => 'cabinet.manage', 'category' => 'cabinet']);
        Permission::factory()->create(['name' => 'settings.view', 'category' => 'settings']);
        
        // Create cabinet using service to ensure proper setup with permissions
        $this->cabinet = app(\App\Services\CabinetService::class)->createCabinet(
            $this->owner, 
            'Test Cabinet', 
            'Test Description'
        );
        
        $this->ownerToken = $this->owner->createToken('test')->plainTextToken;
        $this->managerToken = $this->manager->createToken('test')->plainTextToken;
        $this->operatorToken = $this->operator->createToken('test')->plainTextToken;
    }

    /** @test */
    public function owner_can_invite_user_to_cabinet(): void
    {
        $inviteData = [
            'phone' => $this->manager->phone,
            'role' => 'manager'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->postJson("/api/cabinets/{$this->cabinet->id}/invite", $inviteData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'cabinet_user' => ['id', 'cabinet_id', 'user_id', 'role', 'is_owner', 'user', 'permissions']
            ]);

        $this->assertDatabaseHas('cabinet_user', [
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false
        ]);
    }

    /** @test */
    public function invite_fails_with_nonexistent_user(): void
    {
        $inviteData = [
            'phone' => '+79999999999',
            'role' => 'manager'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->postJson("/api/cabinets/{$this->cabinet->id}/invite", $inviteData);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Пользователь не найден',
                'error_code' => 'USER_NOT_FOUND'
            ]);
    }

    /** @test */
    public function invite_fails_when_user_already_in_cabinet(): void
    {
        // First add user to cabinet
        CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $inviteData = [
            'phone' => $this->manager->phone,
            'role' => 'manager'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->postJson("/api/cabinets/{$this->cabinet->id}/invite", $inviteData);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Пользователь уже в кабинете',
                'error_code' => 'USER_ALREADY_IN_CABINET'
            ]);
    }

    /** @test */
    public function owner_can_remove_user_from_cabinet(): void
    {
        // First add user to cabinet
        CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->deleteJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Пользователь успешно удален из кабинета'
            ]);

        $this->assertDatabaseMissing('cabinet_user', [
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id
        ]);
    }

    /** @test */
    public function manager_cannot_remove_user_from_cabinet(): void
    {
        // First add manager to cabinet
        CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->deleteJson("/api/cabinets/{$this->cabinet->id}/users/{$this->operator->id}");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Недостаточно прав для выполнения действия',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'required_permission' => 'user.remove'
            ]);
    }

    /** @test */
    public function owner_can_transfer_ownership(): void
    {
        // First add manager to cabinet
        CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $transferData = [
            'new_owner_phone' => $this->manager->phone
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->patchJson("/api/cabinets/{$this->cabinet->id}/transfer-ownership", $transferData);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Права владения успешно переданы']);

        // Check that ownership was transferred
        $this->assertDatabaseHas('cabinets', [
            'id' => $this->cabinet->id,
            'owner_id' => $this->manager->id
        ]);

        // Check that new owner has is_owner = true
        $this->assertDatabaseHas('cabinet_user', [
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'is_owner' => true,
            'role' => 'admin'
        ]);

        // Check that old owner is no longer owner in cabinet table
        $this->assertDatabaseMissing('cabinets', [
            'id' => $this->cabinet->id,
            'owner_id' => $this->owner->id
        ]);
    }

    /** @test */
    public function transfer_ownership_fails_with_nonexistent_user(): void
    {
        $transferData = [
            'new_owner_phone' => '+79999999999'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->patchJson("/api/cabinets/{$this->cabinet->id}/transfer-ownership", $transferData);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Пользователь не найден',
                'error_code' => 'USER_NOT_FOUND'
            ]);
    }

    /** @test */
    public function transfer_ownership_fails_when_user_not_in_cabinet(): void
    {
        $transferData = [
            'new_owner_phone' => $this->manager->phone
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->patchJson("/api/cabinets/{$this->cabinet->id}/transfer-ownership", $transferData);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Пользователь не найден в кабинете',
                'error_code' => 'USER_NOT_IN_CABINET'
            ]);
    }

    /** @test */
    public function non_owner_cannot_transfer_ownership(): void
    {
        // First add manager to cabinet
        CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $transferData = [
            'new_owner_phone' => $this->operator->phone
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->patchJson("/api/cabinets/{$this->cabinet->id}/transfer-ownership", $transferData);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Недостаточно прав для выполнения действия',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'required_permission' => 'cabinet.manage'
            ]);
    }

    /** @test */
    public function invite_requires_authentication(): void
    {
        $inviteData = [
            'phone' => $this->manager->phone,
            'role' => 'manager'
        ];

        $response = $this->postJson("/api/cabinets/{$this->cabinet->id}/invite", $inviteData);

        $response->assertStatus(401);
    }

    /** @test */
    public function invite_validates_required_fields(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->postJson("/api/cabinets/{$this->cabinet->id}/invite", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone', 'role']);
    }
}

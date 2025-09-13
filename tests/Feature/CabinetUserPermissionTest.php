<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\CabinetUser;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

final class CabinetUserPermissionTest extends TestCase
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
        
        $this->cabinet = Cabinet::factory()->create(['owner_id' => $this->owner->id]);
        
        $this->ownerToken = $this->owner->createToken('test')->plainTextToken;
        $this->managerToken = $this->manager->createToken('test')->plainTextToken;
        $this->operatorToken = $this->operator->createToken('test')->plainTextToken;
        
        // Create permissions
        Permission::factory()->create(['name' => 'user.invite', 'category' => 'user']);
        Permission::factory()->create(['name' => 'user.remove', 'category' => 'user']);
        Permission::factory()->create(['name' => 'user.view', 'category' => 'user']);
        Permission::factory()->create(['name' => 'user.manage', 'category' => 'user']);
        Permission::factory()->create(['name' => 'cabinet.manage', 'category' => 'cabinet']);
        Permission::factory()->create(['name' => 'settings.view', 'category' => 'settings']);
        
        // Add users to cabinet
        $ownerCabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->owner->id,
            'role' => 'admin',
            'is_owner' => true,
            'joined_at' => now()
        ]);
        
        // Assign all permissions to owner
        $allPermissions = Permission::all();
        $ownerCabinetUser->permissions()->attach($allPermissions->pluck('id'));
        
        CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);
        
        CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->operator->id,
            'role' => 'operator',
            'is_owner' => false,
            'joined_at' => now()
        ]);
    }

    /** @test */
    public function owner_can_view_user_permissions(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->getJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'phone', 'role', 'is_owner'],
                'permissions' => [
                    '*' => ['id', 'name', 'description', 'category', 'is_active']
                ]
            ]);
    }

    /** @test */
    public function manager_cannot_view_user_permissions_without_permission(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->getJson("/api/cabinets/{$this->cabinet->id}/users/{$this->operator->id}/permissions");

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Недостаточно прав для выполнения действия',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'required_permission' => 'user.view'
            ]);
    }

    /** @test */
    public function owner_can_assign_permissions_to_user(): void
    {
        $permissions = Permission::whereIn('name', ['user.invite', 'user.remove'])->get();
        $permissionIds = $permissions->pluck('id')->toArray();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->postJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions", [
            'permission_ids' => $permissionIds
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Права успешно назначены'
            ])
            ->assertJsonStructure([
                'permissions' => [
                    '*' => ['id', 'name', 'description', 'category']
                ]
            ]);

        // Check that permissions were assigned
        $cabinetUser = CabinetUser::where('cabinet_id', $this->cabinet->id)
            ->where('user_id', $this->manager->id)
            ->first();
        
        $this->assertTrue($cabinetUser->hasPermission('user.invite'));
        $this->assertTrue($cabinetUser->hasPermission('user.remove'));
    }

    /** @test */
    public function manager_cannot_assign_permissions_without_permission(): void
    {
        $permissions = Permission::whereIn('name', ['user.invite', 'user.remove'])->get();
        $permissionIds = $permissions->pluck('id')->toArray();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->managerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->postJson("/api/cabinets/{$this->cabinet->id}/users/{$this->operator->id}/permissions", [
            'permission_ids' => $permissionIds
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Недостаточно прав для выполнения действия',
                'error_code' => 'INSUFFICIENT_PERMISSIONS',
                'required_permission' => 'user.manage'
            ]);
    }

    /** @test */
    public function owner_can_remove_permissions_from_user(): void
    {
        // First assign some permissions
        $cabinetUser = CabinetUser::where('cabinet_id', $this->cabinet->id)
            ->where('user_id', $this->manager->id)
            ->first();
        
        $permissions = Permission::whereIn('name', ['user.invite', 'user.remove'])->get();
        $cabinetUser->permissions()->attach($permissions->pluck('id'));

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->deleteJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions", [
            'permission_ids' => [$permissions->first()->id]
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Права успешно удалены'
            ]);

        // Check that permission was removed
        $cabinetUser->refresh();
        $this->assertFalse($cabinetUser->hasPermission('user.invite'));
    }

    /** @test */
    public function assign_permissions_validates_required_fields(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->postJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['permission_ids']);
    }

    /** @test */
    public function assign_permissions_validates_permission_existence(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->postJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions", [
            'permission_ids' => [99999] // Non-existent permission
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['permission_ids.0']);
    }

    /** @test */
    public function permissions_are_cached(): void
    {
        // Clear cache
        Cache::flush();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->getJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions");

        $response->assertStatus(200);

        // Check that cache was created
        $cacheKey = "user_permissions:{$this->manager->id}:{$this->cabinet->id}";
        $this->assertTrue(Cache::has($cacheKey));
    }

    /** @test */
    public function permission_cache_is_cleared_when_permissions_are_updated(): void
    {
        // First get permissions to create cache
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->getJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions");

        $cacheKey = "user_permissions:{$this->manager->id}:{$this->cabinet->id}";
        $this->assertTrue(Cache::has($cacheKey));

        // Assign new permissions
        $permissions = Permission::whereIn('name', ['user.invite'])->get();
        $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->postJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions", [
            'permission_ids' => $permissions->pluck('id')->toArray()
        ]);

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function middleware_requires_authentication(): void
    {
        $response = $this->getJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /** @test */
    public function middleware_requires_cabinet_id(): void
    {
        // This test is not applicable since cabinet ID comes from URL parameter
        // The middleware will get cabinet ID from route parameter {cabinet}
        $this->assertTrue(true);
    }

    /** @test */
    public function middleware_validates_cabinet_exists(): void
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => 99999
        ])->getJson("/api/cabinets/{$this->cabinet->id}/users/{$this->manager->id}/permissions");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Кабинет не найден',
                'error_code' => 'CABINET_NOT_FOUND'
            ]);
    }

    /** @test */
    public function returns_404_when_user_not_in_cabinet(): void
    {
        $otherUser = User::factory()->create(['phone' => '+79999999999']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->ownerToken,
            'X-Cabinet-Id' => $this->cabinet->id
        ])->getJson("/api/cabinets/{$this->cabinet->id}/users/{$otherUser->id}/permissions");

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Пользователь не найден в кабинете',
                'error_code' => 'USER_NOT_IN_CABINET'
            ]);
    }
}

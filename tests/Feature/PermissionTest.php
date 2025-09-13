<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Cabinet;
use App\Models\CabinetUser;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PermissionTest extends TestCase
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
            'phone' => '+1234567890',
            'phone_verified_at' => now()
        ]);
        
        $this->manager = User::factory()->create([
            'phone' => '+1234567891',
            'phone_verified_at' => now()
        ]);
        
        $this->operator = User::factory()->create([
            'phone' => '+1234567892',
            'phone_verified_at' => now()
        ]);
        
        $this->cabinet = Cabinet::factory()->create(['owner_id' => $this->owner->id]);
        
        $this->ownerToken = $this->owner->createToken('test')->plainTextToken;
        $this->managerToken = $this->manager->createToken('test')->plainTextToken;
        $this->operatorToken = $this->operator->createToken('test')->plainTextToken;
        
        // Create permissions
        Permission::factory()->create(['name' => 'user.invite', 'category' => 'user', 'is_active' => true]);
        Permission::factory()->create(['name' => 'user.remove', 'category' => 'user', 'is_active' => true]);
        Permission::factory()->create(['name' => 'settings.view', 'category' => 'settings', 'is_active' => true]);
        Permission::factory()->create(['name' => 'settings.edit', 'category' => 'settings', 'is_active' => true]);
        Permission::factory()->create(['name' => 'message.read', 'category' => 'message', 'is_active' => true]);
        Permission::factory()->create(['name' => 'message.manage', 'category' => 'message', 'is_active' => true]);
    }

    /** @test */
    public function owner_gets_all_permissions_when_created(): void
    {
        $cabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->owner->id,
            'role' => 'admin',
            'is_owner' => true,
            'joined_at' => now()
        ]);

        // Simulate permission assignment (this would be done by CabinetService)
        $allPermissions = Permission::all();
        $cabinetUser->permissions()->attach($allPermissions->pluck('id'));

        $this->assertTrue($cabinetUser->hasPermission('user.invite'));
        $this->assertTrue($cabinetUser->hasPermission('user.remove'));
        $this->assertTrue($cabinetUser->hasPermission('settings.view'));
        $this->assertTrue($cabinetUser->hasPermission('settings.edit'));
        $this->assertTrue($cabinetUser->hasPermission('message.read'));
        $this->assertTrue($cabinetUser->hasPermission('message.manage'));
    }

    /** @test */
    public function manager_gets_limited_permissions(): void
    {
        $cabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        // Simulate permission assignment for manager
        $managerPermissions = Permission::whereIn('category', ['user'])->get();
        $cabinetUser->permissions()->attach($managerPermissions->pluck('id'));

        // Manager should have user permissions
        $this->assertTrue($cabinetUser->hasPermission('user.invite'));
        $this->assertTrue($cabinetUser->hasPermission('user.remove'));
        
        // Manager should not have settings permissions (if not in their category)
        $this->assertFalse($cabinetUser->hasPermission('settings.view'));
        $this->assertFalse($cabinetUser->hasPermission('settings.edit'));
    }

    /** @test */
    public function operator_gets_basic_permissions(): void
    {
        $cabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->operator->id,
            'role' => 'operator',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        // Simulate permission assignment for operator
        $operatorPermissions = Permission::whereIn('category', ['settings'])->get();
        $cabinetUser->permissions()->attach($operatorPermissions->pluck('id'));

        // Operator should not have user management permissions
        $this->assertFalse($cabinetUser->hasPermission('user.invite'));
        $this->assertFalse($cabinetUser->hasPermission('user.remove'));
        
        // Operator should have settings permissions (we assigned them)
        $this->assertTrue($cabinetUser->hasPermission('settings.view'));
    }

    /** @test */
    public function user_can_check_permission_in_cabinet(): void
    {
        $cabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $cabinetUser->permissions()->attach(Permission::where('name', 'user.invite')->first()->id);

        $this->assertTrue($this->manager->hasPermissionInCabinet('user.invite', $this->cabinet->id));
        $this->assertFalse($this->manager->hasPermissionInCabinet('user.remove', $this->cabinet->id));
    }

    /** @test */
    public function user_without_cabinet_access_has_no_permissions(): void
    {
        $this->assertFalse($this->manager->hasPermissionInCabinet('user.invite', $this->cabinet->id));
    }

    /** @test */
    public function owner_can_check_ownership(): void
    {
        $this->assertTrue($this->owner->isOwnerOf($this->cabinet->id));
        $this->assertFalse($this->manager->isOwnerOf($this->cabinet->id));
    }

    /** @test */
    public function permission_can_be_assigned_to_cabinet_user(): void
    {
        $cabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $permission = Permission::where('name', 'user.invite')->first();
        $cabinetUser->assignPermission($permission);

        $this->assertTrue($cabinetUser->hasPermission('user.invite'));
    }

    /** @test */
    public function permission_can_be_removed_from_cabinet_user(): void
    {
        $cabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $permission = Permission::where('name', 'user.invite')->first();
        $cabinetUser->permissions()->attach($permission->id);
        
        $this->assertTrue($cabinetUser->hasPermission('user.invite'));

        $cabinetUser->removePermission($permission);
        
        $this->assertFalse($cabinetUser->hasPermission('user.invite'));
    }

    /** @test */
    public function inactive_permissions_are_not_granted(): void
    {
        $permission = Permission::factory()->create([
            'name' => 'test.permission',
            'category' => 'test',
            'is_active' => false
        ]);

        $cabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $cabinetUser->permissions()->attach($permission->id);

        $this->assertFalse($cabinetUser->hasPermission('test.permission'));
    }

    /** @test */
    public function cabinet_user_can_check_if_owner(): void
    {
        $ownerCabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->owner->id,
            'role' => 'admin',
            'is_owner' => true,
            'joined_at' => now()
        ]);

        $managerCabinetUser = CabinetUser::create([
            'cabinet_id' => $this->cabinet->id,
            'user_id' => $this->manager->id,
            'role' => 'manager',
            'is_owner' => false,
            'joined_at' => now()
        ]);

        $this->assertTrue($ownerCabinetUser->isOwner());
        $this->assertFalse($managerCabinetUser->isOwner());
    }
}

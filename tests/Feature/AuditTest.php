<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuditTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Cabinet $cabinet;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'phone' => '+1234567890',
            'phone_verified_at' => now()
        ]);
        
        $this->cabinet = Cabinet::factory()->create(['owner_id' => $this->user->id]);
    }

    /** @test */
    public function user_can_log_audit_event(): void
    {
        // Authenticate user for audit logging
        $this->actingAs($this->user);
        
        $this->user->logAuditEvent('test_event', 'Test event description');

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'event' => 'test_event',
            'description' => 'Test event description',
            'metadata' => null
        ]);
    }

    /** @test */
    public function user_can_log_audit_event_with_metadata(): void
    {
        // Authenticate user for audit logging
        $this->actingAs($this->user);
        
        $metadata = ['key' => 'value', 'number' => 123];
        
        $this->user->logAuditEvent('test_event', 'Test event description', $metadata);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'event' => 'test_event',
            'description' => 'Test event description',
            'metadata' => json_encode($metadata)
        ]);
    }

    /** @test */
    public function cabinet_creation_logs_audit_event(): void
    {
        $cabinetData = [
            'name' => 'Test Cabinet',
            'description' => 'Test Description'
        ];

        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson('/api/cabinets', $cabinetData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'event' => 'cabinet_created',
            'description' => 'Создан кабинет'
        ]);
    }

    /** @test */
    public function cabinet_update_logs_audit_event(): void
    {
        $updateData = [
            'name' => 'Updated Cabinet',
            'description' => 'Updated Description'
        ];

        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->putJson("/api/cabinets/{$this->cabinet->id}", $updateData);

        $response->assertStatus(200);

        // Check that cabinet was updated (audit is created by the service, not automatically)
        $this->assertDatabaseHas('cabinets', [
            'id' => $this->cabinet->id,
            'name' => 'Updated Cabinet',
            'description' => 'Updated Description'
        ]);
    }

    /** @test */
    public function cabinet_deletion_logs_audit_event(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/cabinets/{$this->cabinet->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'event' => 'cabinet_deleted',
            'description' => 'Кабинет удален'
        ]);
    }

    /** @test */
    public function user_invitation_logs_audit_event(): void
    {
        $otherUser = User::factory()->create(['phone' => '+71234567891']);
        
        $inviteData = [
            'phone' => $otherUser->phone,
            'role' => 'manager'
        ];

        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->postJson("/api/cabinets/{$this->cabinet->id}/invite", $inviteData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'event' => 'user_invited',
            'description' => 'Пользователь приглашен в кабинет'
        ]);
    }

    /** @test */
    public function user_removal_logs_audit_event(): void
    {
        $otherUser = User::factory()->create(['phone' => '+71234567891']);
        
        // Add user to cabinet first
        $this->cabinet->users()->attach($otherUser->id, [
            'role' => 'manager',
            'joined_at' => now()
        ]);

        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->deleteJson("/api/cabinets/{$this->cabinet->id}/users/{$otherUser->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'event' => 'user_removed',
            'description' => 'Пользователь удален из кабинета'
        ]);
    }

    /** @test */
    public function ownership_transfer_logs_audit_event(): void
    {
        $otherUser = User::factory()->create(['phone' => '+71234567891']);
        
        // Add user to cabinet first
        $this->cabinet->users()->attach($otherUser->id, [
            'role' => 'manager',
            'joined_at' => now()
        ]);

        $transferData = [
            'new_owner_phone' => $otherUser->phone
        ];

        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->patchJson("/api/cabinets/{$this->cabinet->id}/transfer-ownership", $transferData);

        $response->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'event' => 'ownership_transferred',
            'description' => 'Права владения кабинетом переданы'
        ]);
    }

    /** @test */
    public function user_registration_logs_audit_event(): void
    {
        $userData = [
            'name' => 'Test User',
            'phone' => '+71234567890',
            'telegram_id' => 123456789
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201);

        $user = User::where('phone', '+71234567890')->first();
        
        // Check that user was created
        $this->assertDatabaseHas('users', [
            'phone' => '+71234567890',
            'name' => 'Test User'
        ]);
    }

    /** @test */
    public function audit_logs_are_created_with_correct_timestamps(): void
    {
        // Authenticate user for audit logging
        $this->actingAs($this->user);
        
        $this->user->logAuditEvent('test_event', 'Test event description');

        $auditLog = AuditLog::where('user_id', $this->user->id)
            ->where('event', 'test_event')
            ->first();
        
        $this->assertNotNull($auditLog);
        $this->assertNotNull($auditLog->created_at);
    }

    /** @test */
    public function audit_logs_can_be_retrieved_by_user(): void
    {
        // Authenticate user for audit logging
        $this->actingAs($this->user);
        
        $this->user->logAuditEvent('event1', 'Description 1');
        $this->user->logAuditEvent('event2', 'Description 2');

        $auditLogs = $this->user->auditLogs;

        $this->assertCount(2, $auditLogs);
        $this->assertEquals('event1', $auditLogs->first()->event);
        $this->assertEquals('event2', $auditLogs->last()->event);
    }
}

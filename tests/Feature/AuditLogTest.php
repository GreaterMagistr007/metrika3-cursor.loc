<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\CabinetCreated;
use App\Events\CabinetDeleted;
use App\Events\CabinetUpdated;
use App\Events\OwnershipTransferred;
use App\Events\PermissionAssigned;
use App\Events\PermissionRevoked;
use App\Events\UserInvited;
use App\Events\UserRemoved;
use App\Jobs\LogAuditEvent;
use App\Models\AuditLog;
use App\Models\Cabinet;
use App\Models\Permission;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Cabinet $cabinet;
    private Permission $permission;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->cabinet = Cabinet::factory()->create(['owner_id' => $this->user->id]);
        $this->permission = Permission::factory()->create();
    }

    /** @test */
    public function it_can_log_audit_event(): void
    {
        $repository = new AuditLogRepository();
        
        $auditData = [
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event',
            'description' => 'Test audit event',
            'metadata' => ['test' => 'data'],
        ];

        $auditLog = $repository->log($auditData);

        $this->assertInstanceOf(AuditLog::class, $auditLog);
        $this->assertEquals($this->user->id, $auditLog->user_id);
        $this->assertEquals($this->cabinet->id, $auditLog->cabinet_id);
        $this->assertEquals('test.event', $auditLog->event);
        $this->assertEquals('Test audit event', $auditLog->description);
        $this->assertEquals(['test' => 'data'], $auditLog->metadata);
    }

    /** @test */
    public function it_can_get_audit_logs_with_filtering(): void
    {
        $repository = new AuditLogRepository();
        
        // Create test audit logs
        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event1',
            'description' => 'Test event 1',
        ]);

        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event2',
            'description' => 'Test event 2',
        ]);

        // Test filtering by event
        $logs = $repository->getLogs(['event' => 'test.event1'], 10);
        $this->assertCount(1, $logs->items());
        $this->assertEquals('test.event1', $logs->items()[0]->event);

        // Test filtering by user
        $logs = $repository->getLogs(['user_id' => $this->user->id], 10);
        $this->assertCount(2, $logs->items());
    }

    /** @test */
    public function it_can_get_audit_log_statistics(): void
    {
        $repository = new AuditLogRepository();
        
        // Clear any existing audit logs
        AuditLog::truncate();
        
        // Create test audit logs
        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event1',
            'description' => 'Test event 1',
        ]);

        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event2',
            'description' => 'Test event 2',
        ]);

        $statistics = $repository->getStatistics();
        
        $this->assertEquals(2, $statistics['total_logs']);
        $this->assertEquals(1, $statistics['unique_users']);
        $this->assertEquals(1, $statistics['unique_cabinets']);
        $this->assertArrayHasKey('event_counts', $statistics);
    }

    /** @test */
    public function it_can_get_recent_audit_logs(): void
    {
        $repository = new AuditLogRepository();
        
        // Create test audit logs
        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event1',
            'description' => 'Test event 1',
        ]);

        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event2',
            'description' => 'Test event 2',
        ]);

        $recentLogs = $repository->getRecentLogs(1);
        
        $this->assertCount(1, $recentLogs);
        $this->assertEquals('test.event2', $recentLogs->first()->event);
    }

    /** @test */
    public function it_dispatches_audit_events_when_cabinet_created(): void
    {
        Event::fake();
        
        CabinetCreated::dispatch(
            $this->user,
            $this->cabinet,
            '127.0.0.1',
            'Test User Agent'
        );

        Event::assertDispatched(CabinetCreated::class);
    }

    /** @test */
    public function it_dispatches_audit_events_when_cabinet_updated(): void
    {
        Event::fake();
        
        $changes = [
            'name' => ['old' => 'Old Name', 'new' => 'New Name']
        ];
        
        CabinetUpdated::dispatch(
            $this->user,
            $this->cabinet,
            $changes,
            '127.0.0.1',
            'Test User Agent'
        );

        Event::assertDispatched(CabinetUpdated::class);
    }

    /** @test */
    public function it_dispatches_audit_events_when_cabinet_deleted(): void
    {
        Event::fake();
        
        CabinetDeleted::dispatch(
            $this->user,
            $this->cabinet,
            '127.0.0.1',
            'Test User Agent'
        );

        Event::assertDispatched(CabinetDeleted::class);
    }

    /** @test */
    public function it_dispatches_audit_events_when_user_invited(): void
    {
        Event::fake();
        
        $invitedUser = User::factory()->create();
        
        UserInvited::dispatch(
            $this->user,
            $invitedUser,
            $this->cabinet,
            'manager',
            '127.0.0.1',
            'Test User Agent'
        );

        Event::assertDispatched(UserInvited::class);
    }

    /** @test */
    public function it_dispatches_audit_events_when_user_removed(): void
    {
        Event::fake();
        
        $removedUser = User::factory()->create();
        
        UserRemoved::dispatch(
            $this->user,
            $removedUser,
            $this->cabinet,
            '127.0.0.1',
            'Test User Agent'
        );

        Event::assertDispatched(UserRemoved::class);
    }

    /** @test */
    public function it_dispatches_audit_events_when_permission_assigned(): void
    {
        Event::fake();
        
        $targetUser = User::factory()->create();
        
        PermissionAssigned::dispatch(
            $this->user,
            $targetUser,
            $this->cabinet,
            $this->permission,
            '127.0.0.1',
            'Test User Agent'
        );

        Event::assertDispatched(PermissionAssigned::class);
    }

    /** @test */
    public function it_dispatches_audit_events_when_permission_revoked(): void
    {
        Event::fake();
        
        $targetUser = User::factory()->create();
        
        PermissionRevoked::dispatch(
            $this->user,
            $targetUser,
            $this->cabinet,
            $this->permission,
            '127.0.0.1',
            'Test User Agent'
        );

        Event::assertDispatched(PermissionRevoked::class);
    }

    /** @test */
    public function it_dispatches_audit_events_when_ownership_transferred(): void
    {
        Event::fake();
        
        $newOwner = User::factory()->create();
        
        OwnershipTransferred::dispatch(
            $this->user,
            $newOwner,
            $this->cabinet,
            '127.0.0.1',
            'Test User Agent'
        );

        Event::assertDispatched(OwnershipTransferred::class);
    }

    /** @test */
    public function it_queues_audit_log_job(): void
    {
        Queue::fake();
        
        $auditData = [
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event',
            'description' => 'Test audit event',
        ];

        LogAuditEvent::dispatch($auditData);

        Queue::assertPushed(LogAuditEvent::class);
    }

    /** @test */
    public function it_can_access_admin_audit_logs_api(): void
    {
        // Create test audit logs
        $repository = new AuditLogRepository();
        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event',
            'description' => 'Test audit event',
        ]);

        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/audit-logs');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'logs',
                'pagination' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'from',
                    'to',
                ],
                'filters',
            ]);
    }

    /** @test */
    public function it_can_filter_audit_logs_by_event(): void
    {
        // Create test audit logs
        $repository = new AuditLogRepository();
        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event1',
            'description' => 'Test event 1',
        ]);

        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event2',
            'description' => 'Test event 2',
        ]);

        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/audit-logs?event=test.event1');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('logs'));
        $this->assertEquals('test.event1', $response->json('logs.0.event'));
    }

    /** @test */
    public function it_can_get_audit_log_statistics_api(): void
    {
        // Create test audit logs
        $repository = new AuditLogRepository();
        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event',
            'description' => 'Test audit event',
        ]);

        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
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
    public function it_can_get_recent_audit_logs_api(): void
    {
        // Create test audit logs
        $repository = new AuditLogRepository();
        $repository->log([
            'user_id' => $this->user->id,
            'cabinet_id' => $this->cabinet->id,
            'event' => 'test.event',
            'description' => 'Test audit event',
        ]);

        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/admin/audit-logs/recent?limit=5');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'logs',
            ]);
    }
}

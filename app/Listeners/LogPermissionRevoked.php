<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PermissionRevoked;
use App\Jobs\LogAuditEvent;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogPermissionRevoked implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PermissionRevoked $event): void
    {
        LogAuditEvent::dispatch([
            'user_id' => $event->revoker->id,
            'cabinet_id' => $event->cabinet->id,
            'subject_type' => User::class,
            'subject_id' => $event->targetUser->id,
            'event' => 'permission.revoked',
            'description' => "Право '{$event->permission->name}' отозвано у пользователя {$event->targetUser->name} в кабинете '{$event->cabinet->name}'",
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'metadata' => [
                'target_user_id' => $event->targetUser->id,
                'target_user_name' => $event->targetUser->name,
                'target_user_phone' => $event->targetUser->phone,
                'permission_id' => $event->permission->id,
                'permission_name' => $event->permission->name,
                'permission_description' => $event->permission->description,
                'cabinet_id' => $event->cabinet->id,
                'cabinet_name' => $event->cabinet->name,
            ],
        ]);
    }
}

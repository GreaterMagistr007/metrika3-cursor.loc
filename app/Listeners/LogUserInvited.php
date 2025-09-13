<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserInvited;
use App\Jobs\LogAuditEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogUserInvited implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserInvited $event): void
    {
        LogAuditEvent::dispatch([
            'user_id' => $event->inviter->id,
            'cabinet_id' => $event->cabinet->id,
            'subject_type' => User::class,
            'subject_id' => $event->invitedUser->id,
            'event' => 'user.invited',
            'description' => "Пользователь {$event->invitedUser->name} ({$event->invitedUser->phone}) приглашен в кабинет '{$event->cabinet->name}' с ролью '{$event->role}'",
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'metadata' => [
                'invited_user_id' => $event->invitedUser->id,
                'invited_user_name' => $event->invitedUser->name,
                'invited_user_phone' => $event->invitedUser->phone,
                'cabinet_id' => $event->cabinet->id,
                'cabinet_name' => $event->cabinet->name,
                'role' => $event->role,
            ],
        ]);
    }
}

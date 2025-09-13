<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\UserRemoved;
use App\Jobs\LogAuditEvent;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogUserRemoved implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserRemoved $event): void
    {
        LogAuditEvent::dispatch([
            'user_id' => $event->remover->id,
            'cabinet_id' => $event->cabinet->id,
            'subject_type' => User::class,
            'subject_id' => $event->removedUser->id,
            'event' => 'user.removed',
            'description' => "Пользователь {$event->removedUser->name} ({$event->removedUser->phone}) удален из кабинета '{$event->cabinet->name}'",
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'metadata' => [
                'removed_user_id' => $event->removedUser->id,
                'removed_user_name' => $event->removedUser->name,
                'removed_user_phone' => $event->removedUser->phone,
                'cabinet_id' => $event->cabinet->id,
                'cabinet_name' => $event->cabinet->name,
            ],
        ]);
    }
}

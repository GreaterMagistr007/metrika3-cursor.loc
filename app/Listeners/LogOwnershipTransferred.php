<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\OwnershipTransferred;
use App\Jobs\LogAuditEvent;
use App\Models\Cabinet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogOwnershipTransferred implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OwnershipTransferred $event): void
    {
        LogAuditEvent::dispatch([
            'user_id' => $event->previousOwner->id,
            'cabinet_id' => $event->cabinet->id,
            'subject_type' => Cabinet::class,
            'subject_id' => $event->cabinet->id,
            'event' => 'ownership.transferred',
            'description' => "Права владения кабинетом '{$event->cabinet->name}' переданы от {$event->previousOwner->name} к {$event->newOwner->name}",
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'metadata' => [
                'cabinet_id' => $event->cabinet->id,
                'cabinet_name' => $event->cabinet->name,
                'previous_owner_id' => $event->previousOwner->id,
                'previous_owner_name' => $event->previousOwner->name,
                'previous_owner_phone' => $event->previousOwner->phone,
                'new_owner_id' => $event->newOwner->id,
                'new_owner_name' => $event->newOwner->name,
                'new_owner_phone' => $event->newOwner->phone,
            ],
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CabinetCreated;
use App\Jobs\LogAuditEvent;
use App\Models\Cabinet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogCabinetCreated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CabinetCreated $event): void
    {
        LogAuditEvent::dispatch([
            'user_id' => $event->owner->id,
            'cabinet_id' => $event->cabinet->id,
            'subject_type' => Cabinet::class,
            'subject_id' => $event->cabinet->id,
            'event' => 'cabinet.created',
            'description' => "Кабинет '{$event->cabinet->name}' создан пользователем {$event->owner->name}",
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'metadata' => [
                'cabinet_id' => $event->cabinet->id,
                'cabinet_name' => $event->cabinet->name,
                'cabinet_description' => $event->cabinet->description,
                'owner_id' => $event->owner->id,
                'owner_name' => $event->owner->name,
                'owner_phone' => $event->owner->phone,
            ],
        ]);
    }
}

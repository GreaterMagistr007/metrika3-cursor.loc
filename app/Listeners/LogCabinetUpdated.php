<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CabinetUpdated;
use App\Jobs\LogAuditEvent;
use App\Models\Cabinet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogCabinetUpdated implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CabinetUpdated $event): void
    {
        LogAuditEvent::dispatch([
            'user_id' => $event->updater->id,
            'cabinet_id' => $event->cabinet->id,
            'subject_type' => Cabinet::class,
            'subject_id' => $event->cabinet->id,
            'event' => 'cabinet.updated',
            'description' => "Кабинет '{$event->cabinet->name}' обновлен пользователем {$event->updater->name}",
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'metadata' => [
                'cabinet_id' => $event->cabinet->id,
                'cabinet_name' => $event->cabinet->name,
                'changes' => $event->changes,
                'updater_id' => $event->updater->id,
                'updater_name' => $event->updater->name,
                'updater_phone' => $event->updater->phone,
            ],
        ]);
    }
}

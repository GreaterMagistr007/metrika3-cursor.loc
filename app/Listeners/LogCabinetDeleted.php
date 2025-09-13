<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CabinetDeleted;
use App\Jobs\LogAuditEvent;
use App\Models\Cabinet;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LogCabinetDeleted implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CabinetDeleted $event): void
    {
        LogAuditEvent::dispatch([
            'user_id' => $event->deleter->id,
            'cabinet_id' => $event->cabinet->id,
            'subject_type' => Cabinet::class,
            'subject_id' => $event->cabinet->id,
            'event' => 'cabinet.deleted',
            'description' => "Кабинет '{$event->cabinet->name}' удален пользователем {$event->deleter->name}",
            'ip_address' => $event->ipAddress,
            'user_agent' => $event->userAgent,
            'metadata' => [
                'cabinet_id' => $event->cabinet->id,
                'cabinet_name' => $event->cabinet->name,
                'cabinet_description' => $event->cabinet->description,
                'deleter_id' => $event->deleter->id,
                'deleter_name' => $event->deleter->name,
                'deleter_phone' => $event->deleter->phone,
            ],
        ]);
    }
}

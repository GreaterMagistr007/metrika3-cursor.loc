<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Cabinet;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class PermissionAssigned
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly User $assigner,
        public readonly User $targetUser,
        public readonly Cabinet $cabinet,
        public readonly Permission $permission,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {}
}

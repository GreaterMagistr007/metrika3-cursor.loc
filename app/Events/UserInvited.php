<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class UserInvited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly User $inviter,
        public readonly User $invitedUser,
        public readonly Cabinet $cabinet,
        public readonly string $role,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {}
}

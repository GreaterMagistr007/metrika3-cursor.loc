<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class OwnershipTransferred
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly User $previousOwner,
        public readonly User $newOwner,
        public readonly Cabinet $cabinet,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null
    ) {}
}

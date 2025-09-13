<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\CabinetCreated;
use App\Events\CabinetDeleted;
use App\Events\CabinetUpdated;
use App\Events\OwnershipTransferred;
use App\Events\PermissionAssigned;
use App\Events\PermissionRevoked;
use App\Events\UserInvited;
use App\Events\UserRemoved;
use App\Listeners\LogCabinetCreated;
use App\Listeners\LogCabinetDeleted;
use App\Listeners\LogCabinetUpdated;
use App\Listeners\LogOwnershipTransferred;
use App\Listeners\LogPermissionAssigned;
use App\Listeners\LogPermissionRevoked;
use App\Listeners\LogUserInvited;
use App\Listeners\LogUserRemoved;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

final class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UserInvited::class => [
            LogUserInvited::class,
        ],
        UserRemoved::class => [
            LogUserRemoved::class,
        ],
        PermissionAssigned::class => [
            LogPermissionAssigned::class,
        ],
        PermissionRevoked::class => [
            LogPermissionRevoked::class,
        ],
        CabinetCreated::class => [
            LogCabinetCreated::class,
        ],
        CabinetUpdated::class => [
            LogCabinetUpdated::class,
        ],
        CabinetDeleted::class => [
            LogCabinetDeleted::class,
        ],
        OwnershipTransferred::class => [
            LogOwnershipTransferred::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

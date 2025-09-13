<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService(
                telegramBotToken: config('services.telegram.bot_token') ?? '',
                telegramBotSecret: config('services.telegram.bot_secret') ?? ''
            );
        });

        $this->app->singleton(\App\Services\CabinetService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

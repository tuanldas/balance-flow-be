<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ServiceLayerProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Contracts\UserServiceInterface::class,
            \App\Services\UserService::class
        );

        $this->app->bind(
            \App\Services\Contracts\CategoryServiceInterface::class,
            \App\Services\CategoryService::class
        );

        // Bind Auth Adapter (default: Sanctum)
        // To switch to different auth provider (e.g., JWT), change this binding
        $this->app->bind(
            \App\Adapters\Auth\Contracts\AuthAdapterInterface::class,
            \App\Adapters\Auth\SanctumAuthAdapter::class
        );

        $this->app->bind(
            \App\Services\Contracts\AuthServiceInterface::class,
            \App\Services\AuthService::class
        );

        $this->app->bind(
            \App\Services\Contracts\CategoryIconServiceInterface::class,
            \App\Services\CategoryIconService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

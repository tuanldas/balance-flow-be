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

        $this->app->bind(
            \App\Services\Contracts\AuthServiceInterface::class,
            \App\Services\AuthService::class
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

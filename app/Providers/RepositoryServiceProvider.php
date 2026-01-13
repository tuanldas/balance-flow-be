<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\Contracts\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\CategoryRepositoryInterface::class,
            \App\Repositories\CategoryRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\TransactionRepositoryInterface::class,
            \App\Repositories\TransactionRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AccountRepositoryInterface::class,
            \App\Repositories\AccountRepository::class
        );

        $this->app->bind(
            \App\Repositories\Contracts\AccountTypeRepositoryInterface::class,
            \App\Repositories\AccountTypeRepository::class
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

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Adapters\Contracts\TokenAdapterInterface;
use App\Adapters\PassportTokenAdapter;
use App\Services\Contracts\AuthServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Đăng ký repositories
        $this->app->singleton(UserRepositoryInterface::class, UserRepository::class);

        // Đăng ký adapters
        $this->app->singleton(TokenAdapterInterface::class, PassportTokenAdapter::class);

        // Đăng ký services
        $this->app->singleton(AuthServiceInterface::class, function ($app) {
            return new AuthService(
                $app->make(UserRepositoryInterface::class),
                $app->make(TokenAdapterInterface::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Cấu hình Passport
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));
    }
}

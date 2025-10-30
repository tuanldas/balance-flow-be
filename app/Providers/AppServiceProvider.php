<?php

declare(strict_types=1);

namespace App\Providers;

use App\Adapters\Contracts\TokenAdapterInterface;
use App\Adapters\PassportTokenAdapter;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\EmailVerificationServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\EmailVerificationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Str;
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

        $this->app->singleton(EmailVerificationServiceInterface::class, function ($app) {
            return new EmailVerificationService(
                $app->make(UserRepositoryInterface::class),
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

        // Tuỳ biến URL đặt lại mật khẩu cho môi trường API-first
        ResetPassword::createUrlUsing(function ($notifiable, string $token): string {
            $frontend = rtrim((string) config('app.frontend_url'), '/');
            $email = urlencode((string) ($notifiable->email ?? ''));

            return $frontend . '/reset-password?token=' . $token . '&email=' . $email;
        });
    }
}

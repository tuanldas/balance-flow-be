<?php



namespace App\Providers;

use App\Adapters\Contracts\TokenAdapterInterface;
use App\Adapters\PassportTokenAdapter;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\TransactionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\TransactionRepository;
use App\Repositories\UserRepository;
use App\Services\AuthService;
use App\Services\CategoryService;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\CategoryServiceInterface;
use App\Services\Contracts\EmailVerificationServiceInterface;
use App\Services\EmailVerificationService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
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
        $this->app->singleton(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->singleton(TransactionRepositoryInterface::class, TransactionRepository::class);

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

        $this->app->singleton(CategoryServiceInterface::class, function ($app) {
            return new CategoryService(
                $app->make(CategoryRepositoryInterface::class),
                $app->make(TransactionRepositoryInterface::class)
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

            return $frontend.'/reset-password?token='.$token.'&email='.$email;
        });

        // Tuỳ biến URL xác minh email: chuyển về FE với path /verify-email?id=<id>&hash=<hash>
        VerifyEmail::createUrlUsing(function ($notifiable): string {
            $frontend = rtrim((string) config('app.frontend_url'), '/');
            $id = (string) $notifiable->getKey();
            $hash = sha1($notifiable->getEmailForVerification());

            return $frontend.'/verify-email?id='.urlencode($id).'&hash='.$hash;
        });
    }
}

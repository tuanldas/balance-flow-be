<?php



namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\EmailVerificationServiceInterface;

final readonly class EmailVerificationService implements EmailVerificationServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function sendVerification(User $user): void
    {
        if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return;
        }

        if (method_exists($user, 'sendEmailVerificationNotification')) {
            $user->sendEmailVerificationNotification();
        }
    }

    public function verifyBySignedUrl(string $userId, string $hash): bool
    {
        $user = $this->userRepository->findById($userId);

        // User không tồn tại
        if (! $user instanceof User) {
            return false;
        }

        // Kiểm tra hash theo chuẩn Laravel: sha1(email-for-verification)
        $expected = sha1($user->getEmailForVerification());
        if (! hash_equals($expected, $hash)) {
            return false;
        }

        // Đã verify rồi thì trả về true
        if (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail()) {
            return true;
        }

        // Thực hiện verify
        if (method_exists($user, 'markEmailAsVerified')) {
            return (bool) $user->markEmailAsVerified();
        }

        return false;
    }
}

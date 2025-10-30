<?php

declare(strict_types=1);

namespace App\Services;

use App\Adapters\Contracts\TokenAdapterInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

final readonly class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenAdapterInterface $tokenAdapter,
    ) {
    }

    /**
     * Đăng ký user mới và tạo token
     *
     * @return array<string, mixed>
     */
    public function register(string $name, string $email, string $password): array
    {
        $user = $this->userRepository->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $tokenPayload = $this->tokenAdapter->getPasswordGrantToken($email, $password);

        return [
            'user' => $user,
            'access_token' => $tokenPayload['access_token'] ?? null,
            'refresh_token' => $tokenPayload['refresh_token'] ?? null,
            'token_type' => $tokenPayload['token_type'] ?? 'Bearer',
            'expires_in' => $tokenPayload['expires_in'] ?? null,
        ];
    }

    /**
     * Đăng nhập user và tạo token
     *
     * @return array<string, mixed>|null
     */
    public function login(string $email, string $password): ?array
    {
        $tokenPayload = $this->tokenAdapter->getPasswordGrantToken($email, $password);

        if (!isset($tokenPayload['access_token'])) {
            return null;
        }

        return [
            'access_token' => $tokenPayload['access_token'],
            'refresh_token' => $tokenPayload['refresh_token'] ?? null,
            'token_type' => $tokenPayload['token_type'] ?? 'Bearer',
            'expires_in' => $tokenPayload['expires_in'] ?? null,
        ];
    }

    /**
     * Refresh access token
     *
     * @return array<string, mixed>|null
     */
    public function refreshToken(string $refreshToken): ?array
    {
        $tokenPayload = $this->tokenAdapter->refreshGrantToken($refreshToken);

        if (!isset($tokenPayload['access_token'])) {
            return null;
        }

        return [
            'access_token' => $tokenPayload['access_token'],
            'refresh_token' => $tokenPayload['refresh_token'] ?? null,
            'token_type' => $tokenPayload['token_type'] ?? 'Bearer',
            'expires_in' => $tokenPayload['expires_in'] ?? null,
        ];
    }

    /**
     * Đăng xuất user
     */
    public function logout(User $user): void
    {
        $user->token()->revoke();
    }

    /**
     * Đổi mật khẩu người dùng
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, (string) $user->getAuthPassword())) {
            return false;
        }

        $this->userRepository->update($user, [
            'password' => Hash::make($newPassword),
        ]);

        // Revoke toàn bộ token hiện có để đảm bảo an toàn
        if (method_exists($user, 'tokens')) {
            $user->tokens()->update(['revoked' => true]);
        }

        return true;
    }

    /**
     * Gửi email chứa liên kết đặt lại mật khẩu
     */
    public function sendPasswordResetLink(string $email): bool
    {
        // Laravel sẽ xử lý tạo token và gửi email theo cấu hình mail
        $status = Password::sendResetLink([
            'email' => $email,
        ]);

        return $status === Password::RESET_LINK_SENT;
    }

    /**
     * Đặt lại mật khẩu bằng token hợp lệ
     */
    public function resetPassword(string $email, string $token, string $newPassword): bool
    {
        $status = Password::reset(
            [
                'email' => $email,
                'token' => $token,
                'password' => $newPassword,
                'password_confirmation' => $newPassword,
            ],
            function (User $user) use ($newPassword): void {
                $this->userRepository->update($user, [
                    'password' => Hash::make($newPassword),
                ]);

                // Revoke toàn bộ token sau khi reset để đảm bảo an toàn
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->update(['revoked' => true]);
                }
            }
        );

        return $status === Password::PASSWORD_RESET;
    }
}

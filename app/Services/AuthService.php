<?php

namespace App\Services;

use App\Adapters\Auth\Contracts\AuthAdapterInterface;
use App\Models\User;
use App\Services\Contracts\AuthServiceInterface;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    /**
     * Authentication adapter
     */
    protected AuthAdapterInterface $authAdapter;

    /**
     * Constructor
     */
    public function __construct(AuthAdapterInterface $authAdapter)
    {
        $this->authAdapter = $authAdapter;
    }

    /**
     * Generate token name with email and current datetime
     */
    protected function generateTokenName(string $email): string
    {
        $datetime = now()->format('d/m/Y');

        return "{$email}_{$datetime}";
    }

    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        // Create user with hashed password
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Send email verification notification
        $user->sendEmailVerificationNotification();

        // Do NOT generate token - user must verify email first
        return $user;
    }

    /**
     * Login user and generate token
     *
     * @return array ['user' => User, 'token' => string]
     *
     * @throws AuthenticationException
     */
    public function login(array $credentials): array
    {
        // Attempt to authenticate user using adapter
        if (! $this->authAdapter->verifyCredentials($credentials)) {
            throw new AuthenticationException('Email hoặc mật khẩu không chính xác.');
        }

        $user = $this->authAdapter->getCurrentUser();

        // Check if email is verified
        if (! $user->hasVerifiedEmail()) {
            throw new AuthenticationException('Vui lòng xác thực email trước khi đăng nhập. Kiểm tra hộp thư của bạn.');
        }

        // Generate token with dynamic name using adapter
        $tokenName = $this->generateTokenName($user->email);
        $token = $this->authAdapter->generateToken($user, $tokenName);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Logout user and revoke current token
     */
    public function logout(User $user): bool
    {
        // Revoke current access token using adapter
        return $this->authAdapter->revokeCurrentToken($user);
    }

    /**
     * Logout user from all devices
     */
    public function logoutAll(User $user): bool
    {
        // Revoke all tokens using adapter
        return $this->authAdapter->revokeAllTokens($user);
    }

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User
    {
        return $this->authAdapter->getCurrentUser();
    }

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User
    {
        // Only update allowed fields
        $allowedFields = ['name', 'email'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));

        $user->update($updateData);

        return $user->fresh();
    }

    /**
     * Change user password
     *
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Verify current password
        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Mật khẩu hiện tại không chính xác.'],
            ]);
        }

        // Update password
        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Revoke all tokens (force re-login) using adapter
        $this->authAdapter->revokeAllTokens($user);

        return true;
    }

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(string $email): bool
    {
        $status = Password::sendResetLink(['email' => $email]);

        return $status === Password::RESET_LINK_SENT;
    }

    /**
     * Reset password using token
     *
     * @throws ValidationException
     */
    public function resetPassword(array $data): bool
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password) {
                $user->update([
                    'password' => Hash::make($password),
                ]);

                // Revoke all tokens using adapter
                $this->authAdapter->revokeAllTokens($user);
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return true;
    }

    /**
     * Verify email address
     *
     * @throws ValidationException
     */
    public function verifyEmail(string $userId, string $hash): bool
    {
        $user = User::find($userId);

        if (! $user) {
            throw ValidationException::withMessages([
                'user' => ['Không tìm thấy người dùng.'],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email đã được xác thực trước đó.'],
            ]);
        }

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw ValidationException::withMessages([
                'hash' => ['Link xác thực không hợp lệ.'],
            ]);
        }

        $user->markEmailAsVerified();

        return true;
    }

    /**
     * Resend email verification notification
     *
     * @throws ValidationException
     */
    public function resendVerificationEmail(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'email' => ['Email đã được xác thực.'],
            ]);
        }

        $user->sendEmailVerificationNotification();

        return true;
    }
}

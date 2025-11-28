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
     *
     * @var AuthAdapterInterface
     */
    protected AuthAdapterInterface $authAdapter;

    /**
     * Constructor
     *
     * @param AuthAdapterInterface $authAdapter
     */
    public function __construct(AuthAdapterInterface $authAdapter)
    {
        $this->authAdapter = $authAdapter;
    }
    /**
     * Generate token name with email and current datetime
     *
     * @param string $email
     * @return string
     */
    protected function generateTokenName(string $email): string
    {
        $datetime = now()->format('d/m/Y');
        return "{$email}_{$datetime}";
    }

    /**
     * Register a new user
     *
     * @param array $data
     * @return array ['user' => User, 'token' => string]
     */
    public function register(array $data): array
    {
        // Create user with hashed password
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Generate token with dynamic name using adapter
        $tokenName = $this->generateTokenName($user->email);
        $token = $this->authAdapter->generateToken($user, $tokenName);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Login user and generate token
     *
     * @param array $credentials
     * @return array ['user' => User, 'token' => string]
     * @throws AuthenticationException
     */
    public function login(array $credentials): array
    {
        // Attempt to authenticate user using adapter
        if (!$this->authAdapter->verifyCredentials($credentials)) {
            throw new AuthenticationException('Email hoặc mật khẩu không chính xác.');
        }

        $user = $this->authAdapter->getCurrentUser();

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
     *
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        // Revoke current access token using adapter
        return $this->authAdapter->revokeCurrentToken($user);
    }

    /**
     * Logout user from all devices
     *
     * @param User $user
     * @return bool
     */
    public function logoutAll(User $user): bool
    {
        // Revoke all tokens using adapter
        return $this->authAdapter->revokeAllTokens($user);
    }

    /**
     * Get current authenticated user
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        return $this->authAdapter->getCurrentUser();
    }

    /**
     * Update user profile
     *
     * @param User $user
     * @param array $data
     * @return User
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
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     * @throws ValidationException
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool
    {
        // Verify current password
        if (!Hash::check($currentPassword, $user->password)) {
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
     *
     * @param string $email
     * @return bool
     */
    public function sendPasswordResetLink(string $email): bool
    {
        $status = Password::sendResetLink(['email' => $email]);

        return $status === Password::RESET_LINK_SENT;
    }

    /**
     * Reset password using token
     *
     * @param array $data
     * @return bool
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
}

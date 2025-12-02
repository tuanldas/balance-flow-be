<?php

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new user
     */
    public function register(array $data): User;

    /**
     * Login user and generate token
     *
     * @return array ['user' => User, 'token' => string]
     */
    public function login(array $credentials): array;

    /**
     * Logout user and revoke current token
     */
    public function logout(User $user): bool;

    /**
     * Logout user from all devices
     */
    public function logoutAll(User $user): bool;

    /**
     * Get current authenticated user
     */
    public function getCurrentUser(): ?User;

    /**
     * Update user profile
     */
    public function updateProfile(User $user, array $data): User;

    /**
     * Change user password
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool;

    /**
     * Send password reset link
     */
    public function sendPasswordResetLink(string $email): bool;

    /**
     * Reset password using token
     */
    public function resetPassword(array $data): bool;

    /**
     * Verify email address
     */
    public function verifyEmail(string $userId, string $hash): bool;

    /**
     * Resend email verification notification
     */
    public function resendVerificationEmail(User $user): bool;
}

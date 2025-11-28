<?php

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Register a new user
     *
     * @param array $data
     * @return array ['user' => User, 'token' => string]
     */
    public function register(array $data): array;

    /**
     * Login user and generate token
     *
     * @param array $credentials
     * @return array ['user' => User, 'token' => string]
     */
    public function login(array $credentials): array;

    /**
     * Logout user and revoke current token
     *
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool;

    /**
     * Logout user from all devices
     *
     * @param User $user
     * @return bool
     */
    public function logoutAll(User $user): bool;

    /**
     * Get current authenticated user
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User;

    /**
     * Update user profile
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User;

    /**
     * Change user password
     *
     * @param User $user
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool;

    /**
     * Send password reset link
     *
     * @param string $email
     * @return bool
     */
    public function sendPasswordResetLink(string $email): bool;

    /**
     * Reset password using token
     *
     * @param array $data
     * @return bool
     */
    public function resetPassword(array $data): bool;
}

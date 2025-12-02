<?php

namespace App\Adapters\Auth\Contracts;

use App\Models\User;

/**
 * Interface for authentication adapters
 *
 * This allows switching between different authentication providers
 * (Sanctum, JWT, Passport, etc.) without changing business logic
 */
interface AuthAdapterInterface
{
    /**
     * Generate authentication token for user
     *
     * @return string Plain text token
     */
    public function generateToken(User $user, string $tokenName): string;

    /**
     * Revoke current access token
     */
    public function revokeCurrentToken(User $user): bool;

    /**
     * Revoke all user tokens
     */
    public function revokeAllTokens(User $user): bool;

    /**
     * Verify user credentials
     *
     * @param  array  $credentials  ['email' => string, 'password' => string]
     */
    public function verifyCredentials(array $credentials): bool;

    /**
     * Get currently authenticated user
     */
    public function getCurrentUser(): ?User;
}

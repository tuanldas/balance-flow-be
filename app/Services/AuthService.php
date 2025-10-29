<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Contracts\AuthServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\TokenServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final readonly class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TokenServiceInterface $tokenService,
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

        $tokenPayload = $this->tokenService->getPasswordGrantToken($email, $password);

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
        $tokenPayload = $this->tokenService->getPasswordGrantToken($email, $password);

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
        $tokenPayload = $this->tokenService->refreshGrantToken($refreshToken);

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
}
<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;

interface AuthServiceInterface
{
    /**
     * Đăng ký user mới và tạo token
     *
     * @return array<string, mixed>
     */
    public function register(string $name, string $email, string $password): array;

    /**
     * Đăng nhập user và tạo token
     *
     * @return array<string, mixed>|null
     */
    public function login(string $email, string $password): ?array;

    /**
     * Refresh access token
     *
     * @return array<string, mixed>|null
     */
    public function refreshToken(string $refreshToken): ?array;

    /**
     * Đăng xuất user
     */
    public function logout(User $user): void;

    /**
     * Đổi mật khẩu người dùng
     */
    public function changePassword(User $user, string $currentPassword, string $newPassword): bool;
}
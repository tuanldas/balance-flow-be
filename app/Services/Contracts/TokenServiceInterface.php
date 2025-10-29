<?php

declare(strict_types=1);

namespace App\Services\Contracts;

interface TokenServiceInterface
{
    /**
     * Lấy token theo Password Grant
     *
     * @return array<string, mixed>
     */
    public function getPasswordGrantToken(string $username, string $password): array;

    /**
     * Refresh access token theo chuẩn Password Grant
     *
     * @return array<string, mixed>
     */
    public function refreshGrantToken(string $refreshToken): array;
}
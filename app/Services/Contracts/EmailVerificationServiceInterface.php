<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;

interface EmailVerificationServiceInterface
{
    /**
     * Gửi email xác minh cho user.
     */
    public function sendVerification(User $user): void;

    /**
     * Xác minh email bằng đường dẫn đã ký.
     */
    public function verifyBySignedUrl(string $userId, string $hash): bool;
}



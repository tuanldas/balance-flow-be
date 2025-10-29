<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface
{
    /**
     * Tạo user mới
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): User;

    /**
     * Tìm user theo email
     */
    public function findByEmail(string $email): ?User;

    /**
     * Tìm user theo ID
     */
    public function findById(string $id): ?User;

    /**
     * Cập nhật user
     *
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): bool;

    /**
     * Xóa user
     */
    public function delete(User $user): bool;
}
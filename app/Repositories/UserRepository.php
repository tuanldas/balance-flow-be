<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Models\User;

final readonly class UserRepository implements UserRepositoryInterface
{
    /**
     * Tạo user mới
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): User
    {
        return User::create($data);
    }

    /**
     * Tìm user theo email
     */
    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * Tìm user theo ID
     */
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    /**
     * Cập nhật user
     *
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    /**
     * Xóa user
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }
}
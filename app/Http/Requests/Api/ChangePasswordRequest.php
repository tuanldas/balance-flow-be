<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class ChangePasswordRequest extends FormRequest
{
    /**
     * Người dùng đã đăng nhập mới được đổi mật khẩu
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Quy tắc validate
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'min:6'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
    }
}



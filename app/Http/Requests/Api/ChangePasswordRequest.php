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
            'new_password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'current_password.required' => __('validation.required', ['attribute' => __('validation.attributes.current_password')]),
            'current_password.min' => __('validation.min.string', ['attribute' => __('validation.attributes.current_password'), 'min' => 6]),
            'new_password.required' => __('validation.required', ['attribute' => __('validation.attributes.new_password')]),
            'new_password.min' => __('validation.min.string', ['attribute' => __('validation.attributes.new_password'), 'min' => 6]),
            'new_password.confirmed' => __('validation.confirmed', ['attribute' => __('validation.attributes.new_password')]),
            'new_password.different' => __('validation.different', ['attribute' => __('validation.attributes.new_password'), 'other' => __('validation.attributes.current_password')]),
        ];
    }
}

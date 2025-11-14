<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class LoginRequest extends FormRequest
{
    /**
     * Xác định xem user có được phép thực hiện request này không
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Lấy các rules validation áp dụng cho request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }

    /**
     * Lấy các message lỗi tùy chỉnh cho validator
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => __('validation.required', ['attribute' => __('validation.attributes.email')]),
            'email.email' => __('validation.email', ['attribute' => __('validation.attributes.email')]),
            'email.max' => __('validation.max.string', ['attribute' => __('validation.attributes.email'), 'max' => 255]),
            'password.required' => __('validation.required', ['attribute' => __('validation.attributes.password')]),
            'password.min' => __('validation.min.string', ['attribute' => __('validation.attributes.password'), 'min' => 6]),
        ];
    }
}

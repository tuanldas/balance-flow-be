<?php



namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class RefreshTokenRequest extends FormRequest
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
            'refresh_token' => ['required', 'string', 'min:10'],
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
            'refresh_token.required' => __('validation.required', ['attribute' => __('validation.attributes.refresh_token')]),
            'refresh_token.string' => __('validation.string', ['attribute' => __('validation.attributes.refresh_token')]),
            'refresh_token.min' => __('validation.min.string', ['attribute' => __('validation.attributes.refresh_token'), 'min' => 10]),
        ];
    }
}

<?php



namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class ForgotPasswordRequest extends FormRequest
{
    /**
     * Xác định quyền thực thi request
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Luật validate input
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
        ];
    }

    /**
     * Thông điệp lỗi tuỳ chỉnh
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => __('validation.required', ['attribute' => 'email']),
            'email.email' => __('validation.email', ['attribute' => 'email']),
        ];
    }
}

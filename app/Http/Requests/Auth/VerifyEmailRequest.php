<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|string|exists:users,id',
            'hash' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id.required' => 'ID người dùng là bắt buộc.',
            'id.string' => 'ID người dùng phải là chuỗi ký tự.',
            'id.exists' => 'Người dùng không tồn tại.',
            'hash.required' => 'Mã xác thực là bắt buộc.',
            'hash.string' => 'Mã xác thực phải là chuỗi ký tự.',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'parent_id' => 'nullable|uuid|exists:categories,id',
            'icon' => 'nullable|string|max:50',
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
            'parent_id.uuid' => 'ID danh mục cha không hợp lệ',
            'parent_id.exists' => 'Danh mục cha không tồn tại',
            'color.regex' => 'Mã màu phải có định dạng #RRGGBB',
        ];
    }
}

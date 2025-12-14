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
            'icon' => 'nullable|string|max:255',
            'icon_file' => 'nullable|file|mimes:svg,png,jpg,jpeg|max:512',
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => __('categories.validation.name.string'),
            'name.max' => __('categories.validation.name.max'),
            'parent_id.uuid' => __('categories.validation.parent_id.uuid'),
            'parent_id.exists' => __('categories.validation.parent_id.exists'),
            'icon.string' => __('categories.validation.icon.string'),
            'icon.max' => __('categories.validation.icon.max'),
            'icon_file.file' => __('categories.validation.icon_file.file'),
            'icon_file.mimes' => __('categories.validation.icon_file.mimes'),
            'icon_file.max' => __('categories.validation.icon_file.max'),
        ];
    }
}

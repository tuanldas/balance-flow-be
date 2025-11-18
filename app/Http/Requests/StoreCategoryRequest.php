<?php



namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'icon' => ['required', 'file', 'image', 'mimes:png,jpg,jpeg,svg', 'max:1024'],
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
            'name.required' => __('messages.category.validation.name_required'),
            'name.string' => __('messages.category.validation.name_string'),
            'name.max' => __('messages.category.validation.name_max'),
            'type.required' => __('messages.category.validation.type_required'),
            'type.in' => __('messages.category.validation.type_invalid'),
            'icon.required' => __('messages.category.validation.icon_required'),
            'icon.file' => __('messages.category.validation.icon_file'),
            'icon.image' => __('messages.category.validation.icon_image'),
            'icon.mimes' => __('messages.category.validation.icon_mimes'),
            'icon.max' => __('messages.category.validation.icon_max'),
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountRequest extends FormRequest
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
            'account_type_id' => 'required|uuid|exists:account_types,id',
            'name' => 'required|string|max:255',
            'balance' => 'nullable|numeric|min:0|max:999999999999.99',
            'currency' => 'nullable|string|size:3|in:VND,USD,EUR,GBP,JPY',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'account_type_id.required' => __('accounts.validation.account_type_id.required'),
            'account_type_id.uuid' => __('accounts.validation.account_type_id.uuid'),
            'account_type_id.exists' => __('accounts.validation.account_type_id.exists'),
            'name.required' => __('accounts.validation.name.required'),
            'name.string' => __('accounts.validation.name.string'),
            'name.max' => __('accounts.validation.name.max'),
            'balance.numeric' => __('accounts.validation.balance.numeric'),
            'balance.min' => __('accounts.validation.balance.min'),
            'balance.max' => __('accounts.validation.balance.max'),
            'currency.string' => __('accounts.validation.currency.string'),
            'currency.size' => __('accounts.validation.currency.size'),
            'currency.in' => __('accounts.validation.currency.in'),
            'icon.string' => __('accounts.validation.icon.string'),
            'icon.max' => __('accounts.validation.icon.max'),
            'color.string' => __('accounts.validation.color.string'),
            'color.size' => __('accounts.validation.color.size'),
            'color.regex' => __('accounts.validation.color.regex'),
            'description.string' => __('accounts.validation.description.string'),
            'description.max' => __('accounts.validation.description.max'),
        ];
    }
}

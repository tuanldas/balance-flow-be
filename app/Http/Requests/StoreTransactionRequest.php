<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'category_id' => 'required|uuid|exists:categories,id',
            'account_id' => 'required|uuid|exists:accounts,id',
            'amount' => 'required|numeric|min:0.01|max:999999999999.99',
            'name' => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => __('transactions.validation.category_id.required'),
            'category_id.uuid' => __('transactions.validation.category_id.uuid'),
            'category_id.exists' => __('transactions.validation.category_id.exists'),
            'account_id.required' => __('transactions.validation.account_id.required'),
            'account_id.uuid' => __('transactions.validation.account_id.uuid'),
            'account_id.exists' => __('transactions.validation.account_id.exists'),
            'amount.required' => __('transactions.validation.amount.required'),
            'amount.numeric' => __('transactions.validation.amount.numeric'),
            'amount.min' => __('transactions.validation.amount.min'),
            'amount.max' => __('transactions.validation.amount.max'),
            'name.string' => __('transactions.validation.name.string'),
            'name.max' => __('transactions.validation.name.max'),
            'transaction_date.required' => __('transactions.validation.transaction_date.required'),
            'transaction_date.date' => __('transactions.validation.transaction_date.date'),
            'notes.string' => __('transactions.validation.notes.string'),
            'notes.max' => __('transactions.validation.notes.max'),
        ];
    }
}

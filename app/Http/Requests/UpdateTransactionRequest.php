<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransactionRequest extends FormRequest
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
            'category_id' => 'sometimes|uuid|exists:categories,id',
            'amount' => 'sometimes|numeric|min:0.01|max:999999999999.99',
            'merchant_name' => 'nullable|string|max:255',
            'transaction_date' => 'sometimes|date',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.uuid' => __('transactions.validation.category_id.uuid'),
            'category_id.exists' => __('transactions.validation.category_id.exists'),
            'amount.numeric' => __('transactions.validation.amount.numeric'),
            'amount.min' => __('transactions.validation.amount.min'),
            'amount.max' => __('transactions.validation.amount.max'),
            'merchant_name.string' => __('transactions.validation.merchant_name.string'),
            'merchant_name.max' => __('transactions.validation.merchant_name.max'),
            'transaction_date.date' => __('transactions.validation.transaction_date.date'),
            'notes.string' => __('transactions.validation.notes.string'),
            'notes.max' => __('transactions.validation.notes.max'),
        ];
    }
}

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Accounts Language Lines - English
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for account-related
    | messages that we need to display to the user.
    |
    */

    // List & Retrieve
    'list_success' => 'Accounts retrieved successfully.',
    'list_failed' => 'Failed to retrieve accounts.',
    'retrieved_success' => 'Account retrieved successfully.',
    'not_found' => 'Account not found.',

    // Create
    'created_success' => 'Account created successfully.',
    'create_failed' => 'Failed to create account.',

    // Update
    'updated_success' => 'Account updated successfully.',
    'update_failed' => 'Failed to update account.',

    // Delete
    'deleted_success' => 'Account deleted successfully.',
    'delete_failed' => 'Failed to delete account.',
    'has_transactions' => 'Cannot delete account with transactions.',

    // Toggle
    'toggle_success' => 'Account status toggled successfully.',

    // Balance
    'balance_updated' => 'Balance updated successfully.',
    'insufficient_balance' => 'Insufficient balance.',

    // Authorization
    'unauthorized' => 'You are not authorized to perform this action.',

    // Validation messages
    'validation' => [
        'account_type_id' => [
            'required' => 'Account type is required.',
            'uuid' => 'Account type ID is invalid.',
            'exists' => 'Account type does not exist.',
        ],
        'name' => [
            'required' => 'Account name is required.',
            'string' => 'Account name must be a string.',
            'max' => 'Account name must not exceed :max characters.',
        ],
        'balance' => [
            'numeric' => 'Balance must be a number.',
            'min' => 'Balance cannot be negative.',
            'max' => 'Balance is too large.',
        ],
        'currency' => [
            'string' => 'Currency code must be a string.',
            'size' => 'Currency code must be exactly 3 characters.',
            'in' => 'Currency code is invalid. Only accepts: VND, USD, EUR, GBP, JPY.',
        ],
        'icon' => [
            'string' => 'Icon must be a string.',
            'max' => 'Icon must not exceed :max characters.',
        ],
        'color' => [
            'string' => 'Color must be a string.',
            'size' => 'Color must be exactly 7 characters.',
            'regex' => 'Color must be in hex format (#RRGGBB).',
        ],
        'description' => [
            'string' => 'Description must be a string.',
            'max' => 'Description must not exceed :max characters.',
        ],
        'is_active' => [
            'boolean' => 'Active status must be true or false.',
        ],
    ],
];

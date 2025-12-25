<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Transactions Language Lines - English
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for transaction-related
    | messages that we need to display to the user.
    |
    */

    // List & Retrieve
    'list_success' => 'Transactions retrieved successfully.',
    'list_failed' => 'Failed to retrieve transactions.',
    'retrieved_success' => 'Transaction retrieved successfully.',
    'not_found' => 'Transaction not found.',

    // Create
    'created_success' => 'Transaction created successfully.',
    'create_failed' => 'Failed to create transaction.',

    // Update
    'updated_success' => 'Transaction updated successfully.',
    'update_failed' => 'Failed to update transaction.',

    // Delete
    'deleted_success' => 'Transaction deleted successfully.',
    'delete_failed' => 'Failed to delete transaction.',

    // Category
    'category_not_found' => 'Category not found.',
    'category_not_accessible' => 'You do not have access to this category.',

    // Authorization
    'unauthorized' => 'You are not authorized to perform this action.',

    // Summary
    'summary_success' => 'Transaction summary retrieved successfully.',

    // Validation messages
    'validation' => [
        'category_id' => [
            'required' => 'Category is required.',
            'uuid' => 'Category ID is invalid.',
            'exists' => 'Category does not exist.',
        ],
        'amount' => [
            'required' => 'Amount is required.',
            'numeric' => 'Amount must be a number.',
            'min' => 'Amount must be greater than 0.',
            'max' => 'Amount is too large.',
        ],
        'name' => [
            'string' => 'Transaction name must be a string.',
            'max' => 'Transaction name must not exceed :max characters.',
        ],
        'transaction_date' => [
            'required' => 'Transaction date is required.',
            'date' => 'Transaction date is invalid.',
        ],
        'notes' => [
            'string' => 'Notes must be a string.',
            'max' => 'Notes must not exceed :max characters.',
        ],
        'status' => [
            'in' => 'Status is invalid. Only accepts: pending, completed, cancelled.',
        ],
    ],
];

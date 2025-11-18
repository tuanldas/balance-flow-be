<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Messages (EN)
    |--------------------------------------------------------------------------
    */

    // Authentication messages
    'auth' => [
        'register_success' => 'Registration successful',
        'login_success' => 'Login successful',
        'logout_success' => 'Logout successful',
        'login_failed' => 'Email or password is incorrect',
        'unauthorized' => 'You are not authorized to access this resource',
        'forbidden' => 'Access denied',
        'token_expired' => 'Token has expired',
        'token_invalid' => 'Token is invalid',
        'refresh_token_required' => 'Refresh token is required',
        'refresh_token_invalid' => 'Refresh token is invalid',
        'refresh_token_expired' => 'Refresh token has expired',
        'refresh_success' => 'Token refreshed successfully',
        'refresh_failed' => 'Failed to refresh token',
        'change_password_success' => 'Password changed successfully',
        'incorrect_current_password' => 'Current password is incorrect',
        'forgot_send_success' => 'Password reset email sent',
        'forgot_send_failed' => 'Failed to send password reset email',
        'reset_success' => 'Password has been reset successfully',
        'reset_failed' => 'Password reset failed or token is invalid',
        'verification_sent' => 'Verification email has been sent',
        'already_verified' => 'Email is already verified',
        'verify_success' => 'Email verified successfully',
        'verify_failed' => 'Email verification failed or link invalid',
        'verification_required' => 'Please verify your email to continue',
    ],

    // General messages
    'success' => 'Success',
    'error' => 'An error occurred',
    'not_found' => 'Not found',
    'validation_error' => 'Validation error',
    'server_error' => 'Server error',
    'bad_request' => 'Bad request',

    // User messages
    'user' => [
        'created' => 'User has been created',
        'updated' => 'User has been updated',
        'deleted' => 'User has been deleted',
        'not_found' => 'User not found',
        'profile_updated' => 'Profile has been updated',
    ],

    // API messages
    'api' => [
        'welcome' => 'Welcome to the API',
        'maintenance' => 'The system is under maintenance',
        'rate_limited' => 'You have exceeded the request limit',
    ],

    // Category messages
    'category' => [
        'created' => 'Category has been created',
        'updated' => 'Category has been updated',
        'deleted' => 'Category has been deleted',
        'not_found' => 'Category not found',
        'unauthorized' => 'You are not authorized to modify this category',
        'has_transactions' => 'This category has :count transactions. Please transfer transactions to another category before deleting',
        'transfer_target_not_found' => 'Target category for transaction transfer not found',
        'transfer_target_unauthorized' => 'You are not authorized to transfer transactions to this category',
        'transfer_type_mismatch' => 'Cannot transfer transactions to a category with a different type',
        'validation' => [
            'name_required' => 'Category name is required',
            'name_string' => 'Category name must be a string',
            'name_max' => 'Category name must not exceed 255 characters',
            'type_required' => 'Category type is required',
            'type_invalid' => 'Invalid category type. Only "income" or "expense" are accepted',
            'icon_required' => 'Icon is required',
            'icon_string' => 'Icon must be an SVG string',
            'transfer_category_invalid' => 'Invalid target category ID',
        ],
    ],

    // Category names (system categories)
    'categories' => [
        'income' => [
            'salary' => 'Salary',
            'bonus' => 'Bonus',
            'investment' => 'Investment',
            'freelance' => 'Freelance',
            'gift' => 'Gift',
            'other' => 'Other Income',
        ],
        'expense' => [
            'food' => 'Food & Dining',
            'transportation' => 'Transportation',
            'housing' => 'Housing',
            'utilities' => 'Utilities',
            'healthcare' => 'Healthcare',
            'entertainment' => 'Entertainment',
            'shopping' => 'Shopping',
            'education' => 'Education',
            'insurance' => 'Insurance',
            'savings' => 'Savings',
            'other' => 'Other Expenses',
        ],
    ],

];

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

];



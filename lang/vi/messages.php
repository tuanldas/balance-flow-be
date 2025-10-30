<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Custom Messages
    |--------------------------------------------------------------------------
    |
    | The following language lines contain custom messages for the application.
    | These messages are used throughout the application for various purposes.
    |
    */

    // Authentication messages
    'auth' => [
        'register_success' => 'Đăng ký thành công',
        'login_success' => 'Đăng nhập thành công',
        'logout_success' => 'Đăng xuất thành công',
        'login_failed' => 'Email hoặc mật khẩu không đúng',
        'unauthorized' => 'Bạn không có quyền truy cập',
        'forbidden' => 'Truy cập bị từ chối',
        'token_expired' => 'Token đã hết hạn',
        'token_invalid' => 'Token không hợp lệ',
        'refresh_token_required' => 'Refresh token là bắt buộc',
        'refresh_token_invalid' => 'Refresh token không hợp lệ',
        'refresh_token_expired' => 'Refresh token đã hết hạn',
        'refresh_success' => 'Làm mới token thành công',
        'refresh_failed' => 'Làm mới token thất bại',
        'change_password_success' => 'Đổi mật khẩu thành công',
        'incorrect_current_password' => 'Mật khẩu hiện tại không đúng',
        'forgot_send_success' => 'Đã gửi email đặt lại mật khẩu',
        'forgot_send_failed' => 'Gửi email đặt lại mật khẩu thất bại',
        'reset_success' => 'Đặt lại mật khẩu thành công',
        'reset_failed' => 'Đặt lại mật khẩu thất bại hoặc token không hợp lệ',
    ],

    // General messages
    'success' => 'Thành công',
    'error' => 'Có lỗi xảy ra',
    'not_found' => 'Không tìm thấy',
    'validation_error' => 'Lỗi xác thực dữ liệu',
    'server_error' => 'Lỗi máy chủ',
    'bad_request' => 'Yêu cầu không hợp lệ',

    // User messages
    'user' => [
        'created' => 'Người dùng đã được tạo',
        'updated' => 'Người dùng đã được cập nhật',
        'deleted' => 'Người dùng đã được xóa',
        'not_found' => 'Không tìm thấy người dùng',
        'profile_updated' => 'Hồ sơ đã được cập nhật',
    ],

    // API messages
    'api' => [
        'welcome' => 'Chào mừng đến với API',
        'maintenance' => 'Hệ thống đang bảo trì',
        'rate_limited' => 'Bạn đã vượt quá giới hạn yêu cầu',
    ],

];

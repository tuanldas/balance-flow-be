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
        'verification_sent' => 'Đã gửi email xác minh',
        'already_verified' => 'Email đã được xác minh',
        'verify_success' => 'Xác minh email thành công',
        'verify_failed' => 'Xác minh email thất bại hoặc liên kết không hợp lệ',
        'verification_required' => 'Vui lòng xác minh email trước khi tiếp tục',
    ],

    // General messages
    'success' => 'Thành công',
    'error' => 'Có lỗi xảy ra',
    'not_found' => 'Không tìm thấy',
    'validation_error' => 'Lỗi xác thực dữ liệu',
    'server_error' => 'Lỗi máy chủ',
    'bad_request' => 'Yêu cầu không hợp lệ',

    // Pagination messages
    'pagination' => [
        'invalid_per_page' => 'Số items mỗi trang phải từ 1 đến 100',
    ],

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

    // Category messages
    'category' => [
        'created' => 'Danh mục đã được tạo',
        'updated' => 'Danh mục đã được cập nhật',
        'deleted' => 'Danh mục đã được xóa',
        'not_found' => 'Không tìm thấy danh mục',
        'unauthorized' => 'Bạn không có quyền chỉnh sửa danh mục này',
        'has_transactions' => 'Danh mục này có :count giao dịch. Vui lòng chuyển giao dịch sang danh mục khác trước khi xóa',
        'transfer_target_not_found' => 'Không tìm thấy danh mục đích để chuyển giao dịch',
        'transfer_target_unauthorized' => 'Bạn không có quyền chuyển giao dịch sang danh mục này',
        'transfer_type_mismatch' => 'Không thể chuyển giao dịch sang danh mục có loại khác',
        'validation' => [
            'name_required' => 'Tên danh mục là bắt buộc',
            'name_string' => 'Tên danh mục phải là chuỗi',
            'name_max' => 'Tên danh mục không được vượt quá 255 ký tự',
            'type_required' => 'Loại danh mục là bắt buộc',
            'type_invalid' => 'Loại danh mục không hợp lệ. Chỉ chấp nhận "income" hoặc "expense"',
            'icon_required' => 'Biểu tượng là bắt buộc',
            'icon_string' => 'Biểu tượng phải là chuỗi SVG',
            'transfer_category_invalid' => 'ID danh mục đích không hợp lệ',
        ],
    ],

    // Category names (system categories)
    'categories' => [
        'income' => [
            'salary' => 'Lương',
            'bonus' => 'Thưởng',
            'investment' => 'Đầu tư',
            'freelance' => 'Làm tự do',
            'gift' => 'Quà tặng',
            'other' => 'Thu nhập khác',
        ],
        'expense' => [
            'food' => 'Ăn uống',
            'transportation' => 'Di chuyển',
            'housing' => 'Nhà ở',
            'utilities' => 'Tiện ích',
            'healthcare' => 'Y tế',
            'entertainment' => 'Giải trí',
            'shopping' => 'Mua sắm',
            'education' => 'Giáo dục',
            'insurance' => 'Bảo hiểm',
            'savings' => 'Tiết kiệm',
            'other' => 'Chi phí khác',
        ],
    ],

];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Accounts Language Lines - Vietnamese
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for account-related
    | messages that we need to display to the user.
    |
    */

    // List & Retrieve
    'list_success' => 'Lấy danh sách tài khoản thành công.',
    'list_failed' => 'Lấy danh sách tài khoản thất bại.',
    'retrieved_success' => 'Lấy thông tin tài khoản thành công.',
    'not_found' => 'Không tìm thấy tài khoản.',

    // Create
    'created_success' => 'Tạo tài khoản thành công.',
    'create_failed' => 'Tạo tài khoản thất bại.',

    // Update
    'updated_success' => 'Cập nhật tài khoản thành công.',
    'update_failed' => 'Cập nhật tài khoản thất bại.',

    // Delete
    'deleted_success' => 'Xóa tài khoản thành công.',
    'delete_failed' => 'Xóa tài khoản thất bại.',
    'has_transactions' => 'Không thể xóa tài khoản có giao dịch.',

    // Toggle
    'toggle_success' => 'Thay đổi trạng thái tài khoản thành công.',

    // Balance
    'balance_updated' => 'Cập nhật số dư thành công.',
    'insufficient_balance' => 'Số dư không đủ.',

    // Authorization
    'unauthorized' => 'Bạn không có quyền thực hiện thao tác này.',

    // Validation messages
    'validation' => [
        'account_type_id' => [
            'required' => 'Loại tài khoản là bắt buộc.',
            'uuid' => 'ID loại tài khoản không hợp lệ.',
            'exists' => 'Loại tài khoản không tồn tại.',
        ],
        'name' => [
            'required' => 'Tên tài khoản là bắt buộc.',
            'string' => 'Tên tài khoản phải là chuỗi ký tự.',
            'max' => 'Tên tài khoản không được vượt quá :max ký tự.',
        ],
        'balance' => [
            'numeric' => 'Số dư phải là số.',
            'min' => 'Số dư không được âm.',
            'max' => 'Số dư quá lớn.',
        ],
        'currency' => [
            'string' => 'Mã tiền tệ phải là chuỗi ký tự.',
            'size' => 'Mã tiền tệ phải có đúng 3 ký tự.',
            'in' => 'Mã tiền tệ không hợp lệ. Chỉ chấp nhận: VND, USD, EUR, GBP, JPY.',
        ],
        'icon' => [
            'string' => 'Icon phải là chuỗi ký tự.',
            'max' => 'Icon không được vượt quá :max ký tự.',
        ],
        'color' => [
            'string' => 'Màu sắc phải là chuỗi ký tự.',
            'size' => 'Màu sắc phải có đúng 7 ký tự.',
            'regex' => 'Màu sắc phải có định dạng hex (#RRGGBB).',
        ],
        'description' => [
            'string' => 'Mô tả phải là chuỗi ký tự.',
            'max' => 'Mô tả không được vượt quá :max ký tự.',
        ],
        'is_active' => [
            'boolean' => 'Trạng thái hoạt động phải là true hoặc false.',
        ],
    ],
];

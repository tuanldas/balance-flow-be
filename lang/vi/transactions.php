<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Transactions Language Lines - Vietnamese
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for transaction-related
    | messages that we need to display to the user.
    |
    */

    // List & Retrieve
    'list_success' => 'Lấy danh sách giao dịch thành công.',
    'list_failed' => 'Lấy danh sách giao dịch thất bại.',
    'retrieved_success' => 'Lấy thông tin giao dịch thành công.',
    'not_found' => 'Không tìm thấy giao dịch.',

    // Create
    'created_success' => 'Tạo giao dịch thành công.',
    'create_failed' => 'Tạo giao dịch thất bại.',

    // Update
    'updated_success' => 'Cập nhật giao dịch thành công.',
    'update_failed' => 'Cập nhật giao dịch thất bại.',

    // Delete
    'deleted_success' => 'Xóa giao dịch thành công.',
    'delete_failed' => 'Xóa giao dịch thất bại.',

    // Category
    'category_not_found' => 'Không tìm thấy danh mục.',
    'category_not_accessible' => 'Bạn không có quyền sử dụng danh mục này.',

    // Authorization
    'unauthorized' => 'Bạn không có quyền thực hiện thao tác này.',

    // Summary
    'summary_success' => 'Lấy tổng hợp giao dịch thành công.',

    // Validation messages
    'validation' => [
        'category_id' => [
            'required' => 'Danh mục là bắt buộc.',
            'uuid' => 'ID danh mục không hợp lệ.',
            'exists' => 'Danh mục không tồn tại.',
        ],
        'amount' => [
            'required' => 'Số tiền là bắt buộc.',
            'numeric' => 'Số tiền phải là số.',
            'min' => 'Số tiền phải lớn hơn 0.',
            'max' => 'Số tiền quá lớn.',
        ],
        'merchant_name' => [
            'string' => 'Tên người nhận/nơi giao dịch phải là chuỗi ký tự.',
            'max' => 'Tên người nhận/nơi giao dịch không được vượt quá :max ký tự.',
        ],
        'transaction_date' => [
            'required' => 'Ngày giao dịch là bắt buộc.',
            'date' => 'Ngày giao dịch không hợp lệ.',
        ],
        'notes' => [
            'string' => 'Ghi chú phải là chuỗi ký tự.',
            'max' => 'Ghi chú không được vượt quá :max ký tự.',
        ],
        'status' => [
            'in' => 'Trạng thái không hợp lệ. Chỉ chấp nhận: đang chờ, hoàn thành, đã hủy.',
        ],
    ],
];

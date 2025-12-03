<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Categories Language Lines - Vietnamese
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for category-related
    | messages that we need to display to the user.
    |
    */

    // List & Retrieve
    'list_success' => 'Lấy danh sách danh mục thành công.',
    'list_failed' => 'Lấy danh sách danh mục thất bại.',
    'retrieved_success' => 'Lấy thông tin danh mục thành công.',
    'not_found' => 'Không tìm thấy danh mục.',

    // Create
    'created_success' => 'Tạo danh mục thành công.',
    'create_failed' => 'Tạo danh mục thất bại.',

    // Update
    'updated_success' => 'Cập nhật danh mục thành công.',
    'update_failed' => 'Cập nhật danh mục thất bại.',
    'cannot_update_system' => 'Không thể cập nhật danh mục hệ thống.',

    // Delete
    'deleted_success' => 'Xóa danh mục thành công.',
    'delete_failed' => 'Xóa danh mục thất bại.',
    'cannot_delete_system' => 'Không thể xóa danh mục hệ thống.',
    'cannot_delete_has_children' => 'Không thể xóa danh mục có danh mục con.',

    // Parent-Child
    'parent_not_found' => 'Không tìm thấy danh mục cha.',
    'parent_type_mismatch' => 'Loại danh mục con phải trùng với danh mục cha.',
    'subcategories_retrieved' => 'Lấy danh sách danh mục con thành công.',
    'parent_not_accessible' => 'Bạn không có quyền truy cập danh mục cha.',
    'cannot_set_self_as_parent' => 'Không thể đặt danh mục làm cha của chính nó.',
    'cannot_delete_has_transactions' => 'Không thể xóa danh mục có giao dịch hoặc danh mục con.',

    // Authorization
    'unauthorized' => 'Bạn không có quyền thực hiện thao tác này.',

    // Validation
    'invalid_type' => 'Loại danh mục không hợp lệ. Chỉ chấp nhận: income, expense.',
    'invalid_color' => 'Mã màu không hợp lệ.',
];

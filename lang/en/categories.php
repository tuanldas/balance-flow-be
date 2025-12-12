<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Categories Language Lines - English
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for category-related
    | messages that we need to display to the user.
    |
    */

    // List & Retrieve
    'list_success' => 'Categories retrieved successfully.',
    'list_failed' => 'Failed to retrieve categories.',
    'retrieved_success' => 'Category retrieved successfully.',
    'not_found' => 'Category not found.',

    // Create
    'created_success' => 'Category created successfully.',
    'create_failed' => 'Failed to create category.',

    // Update
    'updated_success' => 'Category updated successfully.',
    'update_failed' => 'Failed to update category.',
    'cannot_update_system' => 'Cannot update system category.',

    // Delete
    'deleted_success' => 'Category deleted successfully.',
    'delete_failed' => 'Failed to delete category.',
    'cannot_delete_system' => 'Cannot delete system category.',
    'cannot_delete_has_children' => 'Cannot delete category that has subcategories.',

    // Parent-Child
    'parent_not_found' => 'Parent category not found.',
    'parent_type_mismatch' => 'Child category type must match parent category type.',
    'subcategories_retrieved' => 'Subcategories retrieved successfully.',
    'parent_not_accessible' => 'You do not have access to the parent category.',
    'cannot_set_self_as_parent' => 'Cannot set category as its own parent.',
    'cannot_delete_has_transactions' => 'Cannot delete category that has transactions or subcategories.',
    'max_depth_exceeded' => 'Categories can only have a maximum of 2 levels (parent and child).',
    'cannot_make_parent_subcategory' => 'Cannot convert a category with subcategories into a subcategory.',

    // Authorization
    'unauthorized' => 'You are not authorized to perform this action.',

    // Validation
    'invalid_type' => 'Invalid category type. Only accepts: income, expense.',
    'invalid_color' => 'Invalid color code.',

    // Icon
    'icon_not_found' => 'Icon not found.',
    'icon_invalid_type' => 'Invalid icon file. Only accepts: SVG, PNG, JPG.',
    'icon_too_large' => 'Icon file too large. Maximum size: 512KB.',
    'icon_upload_failed' => 'Failed to upload icon.',
];

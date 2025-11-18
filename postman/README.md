# BalanceFlow API - Postman Collection

Postman collection cho BalanceFlow API với đầy đủ authentication và category management endpoints.

## 📦 Import Collection

### Bước 1: Import Collection
1. Mở Postman
2. Click **Import** ở góc trên bên trái
3. Chọn file `BalanceFlow-API.postman_collection.json`
4. Click **Import**

### Bước 2: Import Environment (Optional)
1. Click **Import** lần nữa
2. Chọn file `BalanceFlow-Local.postman_environment.json`
3. Click **Import**
4. Chọn environment "BalanceFlow - Local" từ dropdown ở góc trên bên phải

## 🔐 Authentication Flow

### Quick Start
1. **Register**: Tạo tài khoản mới
   - Endpoint sẽ tự động lưu `access_token` và `refresh_token` vào collection variables

2. **Login**: Đăng nhập với email/password
   - Tokens được tự động lưu vào variables
   - Sử dụng tokens này cho các requests tiếp theo

3. **Me**: Test authentication bằng endpoint `/api/me`

### Token Management
- **Access Token**: Tự động thêm vào header `Authorization: Bearer {{access_token}}`
- **Refresh Token**: Dùng endpoint "Refresh Token" để lấy token mới
- **Logout**: Xóa tất cả tokens khỏi variables

## 📂 Category Endpoints

### 1. List Categories
```
GET /api/categories
```
Lấy tất cả categories (system + user's own)

**Query Parameters:**
- `type`: Filter theo loại (income/expense)

**Examples:**
- Tất cả categories: `/api/categories`
- Income only: `/api/categories?type=income`
- Expense only: `/api/categories?type=expense`

### 2. Create Category
```
POST /api/categories
```
Tạo category mới cho user

**Body:**
```json
{
  "name": "Coffee & Breakfast",
  "type": "expense",
  "icon_svg": "<svg>...</svg>"
}
```

**Notes:**
- `category_id` tự động lưu vào collection variables sau khi tạo
- Icon phải là SVG string (25x25px)
- Type chỉ chấp nhận: `income` hoặc `expense`

### 3. Get Category by ID
```
GET /api/categories/{{category_id}}
```
Lấy thông tin chi tiết một category

### 4. Update Category
```
PUT /api/categories/{{category_id}}
```
Cập nhật category (chỉ user's own category)

**Body (partial update):**
```json
{
  "name": "Updated Name"
}
```

**Restrictions:**
- ❌ Không thể update system categories
- ❌ Không thể update categories của users khác

### 5. Get Transaction Count
```
GET /api/categories/{{category_id}}/transactions-count
```
Đếm số lượng transactions của category

**Response:**
```json
{
  "success": true,
  "data": {
    "count": 5
  }
}
```

### 6. Delete Category (No Transactions)
```
DELETE /api/categories/{{category_id}}
```
Xóa category không có transactions

### 7. Delete Category (With Transfer)
```
DELETE /api/categories/{{category_id}}
```
Xóa category và chuyển transactions sang category khác

**Body:**
```json
{
  "transfer_to_category_id": "uuid-of-target-category"
}
```

**Notes:**
- Required nếu category có transactions
- Target category phải cùng type (income → income, expense → expense)
- Có thể transfer sang system category

## 🔄 Workflow Examples

### Example 1: Create and Use Category
1. Run "Login" để lấy access token
2. Run "Create Category" → `category_id` tự động lưu
3. Run "Get Category by ID" để xem category vừa tạo
4. Run "Update Category" để sửa thông tin
5. Run "Get Transaction Count" để check số transactions
6. Run "Delete Category" để xóa

### Example 2: Filter Categories
1. Run "Login"
2. Run "List Categories (Filter by Income)" → chỉ income categories
3. Run "List Categories (Filter by Expense)" → chỉ expense categories
4. Run "List Categories" → tất cả

### Example 3: Delete with Transfer
1. Create 2 categories cùng type (e.g., expense)
2. Copy ID của category đầu tiên vào `category_id`
3. Copy ID của category thứ hai vào `transfer_category_id`
4. Run "Delete Category (With Transaction Transfer)"

## 🌐 Multi-language Support

Thêm header `Accept-Language` để nhận response bằng ngôn ngữ mong muốn:

```
Accept-Language: vi    # Vietnamese
Accept-Language: en    # English
```

**Default**: Vietnamese (vi)

## 🔧 Environment Variables

### Collection Variables (Auto-saved)
- `access_token`: Saved sau Login/Register
- `refresh_token`: Saved sau Login/Register/Refresh
- `category_id`: Saved sau Create Category

### Manual Variables
- `baseUrl`: API base URL (default: http://localhost:8083)
- `transfer_category_id`: Target category ID cho delete with transfer

## 📝 Response Format

Tất cả responses follow format:

**Success:**
```json
{
  "success": true,
  "message": "Success message",
  "data": {
    // Response data
  }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error message"
}
```

**Validation Error:**
```json
{
  "success": false,
  "message": "Validation error message",
  "errors": {
    "field": ["Error message"]
  }
}
```

## 🐛 Troubleshooting

### 401 Unauthorized
- Token hết hạn → Run "Refresh Token"
- Chưa login → Run "Login"

### 403 Forbidden
- Trying to modify system category
- Trying to modify other user's category
- Email chưa verify

### 422 Validation Error
- Check request body format
- Verify required fields
- Check type values (income/expense only)

### 400 Bad Request
- Deleting category with transactions without transfer
- Transfer to category of different type
- Transfer to non-existent category

## 📚 API Documentation

### Category Object Structure
```json
{
  "id": "uuid-v7",
  "name": "Category Name",
  "original_name": "categories.income.salary",  // For system categories
  "type": "income",                            // or "expense"
  "icon_svg": "<svg>...</svg>",
  "is_system": false,
  "user_id": "uuid-v7",
  "created_at": "2025-11-18T00:00:00.000000Z",
  "updated_at": "2025-11-18T00:00:00.000000Z"
}
```

### Default System Categories

**Income (6 categories):**
- Salary (Lương)
- Bonus (Thưởng)
- Investment (Đầu tư)
- Freelance (Làm tự do)
- Gift (Quà tặng)
- Other Income (Thu nhập khác)

**Expense (11 categories):**
- Food & Dining (Ăn uống)
- Transportation (Di chuyển)
- Housing (Nhà ở)
- Utilities (Tiện ích)
- Healthcare (Y tế)
- Entertainment (Giải trí)
- Shopping (Mua sắm)
- Education (Giáo dục)
- Insurance (Bảo hiểm)
- Savings (Tiết kiệm)
- Other Expenses (Chi phí khác)

## 📞 Support

Nếu gặp vấn đề, check:
1. Docker containers đang chạy: `docker-compose ps`
2. API server responding: `curl http://localhost:8083/up`
3. Database migrated: `docker-compose exec app php artisan migrate:status`
4. Seeder run: Check categories table có data chưa

## 🚀 Next Steps

Sau khi test xong Category APIs, bạn có thể:
1. Implement Transaction management APIs
2. Add dashboard/analytics endpoints
3. Add budget management features
4. Add export/import functionality

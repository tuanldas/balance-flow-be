# Quick Start - Test Category API trong 5 phút ⚡

## Option 1: Sử dụng Postman (Khuyến nghị) 👍

### Bước 1: Import Collection (30s)
1. Mở Postman
2. Click **Import** → Chọn `BalanceFlow-API.postman_collection.json`
3. Click **Import** → Chọn `BalanceFlow-Local.postman_environment.json`
4. Select environment "BalanceFlow - Local" ở dropdown góc trên phải

### Bước 2: Test Authentication (1 phút)
1. Expand folder **Auth**
2. Run **Login** request
3. ✅ Check: `access_token` đã được save vào Variables tab

### Bước 3: Test Categories (3 phút)
1. Expand folder **Categories**
2. Run **List Categories** → Thấy 17 default system categories
3. Run **Create Category** → Tạo category của bạn
4. ✅ Check: `category_id` đã được save
5. Run **Get Category by ID** → Xem category vừa tạo
6. Run **Update Category** → Sửa tên
7. Run **Delete Category** → Xóa category

### 🎉 Xong! Bạn đã test thành công tất cả Category endpoints!

---

## Option 2: Sử dụng cURL (Nhanh hơn) 🚀

```bash
# Step 1: Login và lấy token
TOKEN=$(curl -s -X POST http://localhost:8083/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"tuanldas@gmail.com","password":"123123"}' \
  | jq -r '.data.access_token')

echo "Token: $TOKEN"

# Step 2: List categories
curl -s http://localhost:8083/api/categories \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Step 3: Create category
CATEGORY=$(curl -s -X POST http://localhost:8083/api/categories \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Category",
    "type": "expense",
    "icon_svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"25\" height=\"25\"><circle cx=\"12\" cy=\"12\" r=\"10\"/></svg>"
  }')

CATEGORY_ID=$(echo $CATEGORY | jq -r '.data.category.id')
echo "Category ID: $CATEGORY_ID"

# Step 4: Get category
curl -s "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Step 5: Update category
curl -s -X PUT "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Updated Test Category"}' | jq '.'

# Step 6: Delete category
curl -s -X DELETE "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
```

**Requirements:** `jq` (install: `brew install jq`)

---

## 🔍 Verify Everything Works

### 1. Check Server Running
```bash
curl http://localhost:8083/up
# Should return: {"status":"ok"}
```

### 2. Check System Categories Seeded
```bash
# Login first
TOKEN=$(curl -s -X POST http://localhost:8083/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"tuanldas@gmail.com","password":"123123"}' \
  | jq -r '.data.access_token')

# Check categories
curl -s http://localhost:8083/api/categories \
  -H "Authorization: Bearer $TOKEN" | jq '.data.categories | length'

# Should return: 17 (if you haven't created any user categories yet)
```

### 3. Test Filter
```bash
# Income categories (should be 6)
curl -s "http://localhost:8083/api/categories?type=income" \
  -H "Authorization: Bearer $TOKEN" | jq '.data.categories | length'

# Expense categories (should be 11)
curl -s "http://localhost:8083/api/categories?type=expense" \
  -H "Authorization: Bearer $TOKEN" | jq '.data.categories | length'
```

---

## 📋 All Category Endpoints Checklist

- [ ] `GET /api/categories` - List all
- [ ] `GET /api/categories?type=income` - Filter income
- [ ] `GET /api/categories?type=expense` - Filter expense
- [ ] `POST /api/categories` - Create
- [ ] `GET /api/categories/{id}` - Get by ID
- [ ] `PUT /api/categories/{id}` - Update
- [ ] `DELETE /api/categories/{id}` - Delete
- [ ] `GET /api/categories/{id}/transactions-count` - Count transactions
- [ ] `DELETE /api/categories/{id}` (with body) - Delete with transfer

---

## 🚨 Troubleshooting

### Docker not running?
```bash
docker-compose ps
# If services not running:
docker-compose up -d
```

### No system categories?
```bash
docker-compose exec app php artisan db:seed --class=CategorySeeder
```

### Invalid credentials?
```bash
# Register a new account
curl -X POST http://localhost:8083/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "123123",
    "password_confirmation": "123123"
  }'
```

### 401 Unauthorized?
- Token expired → Login again
- Token not saved → Check Postman test scripts

### 403 Forbidden?
- Trying to modify system category → Use user category
- Email not verified → Check verification status

---

## 📚 Next Steps

1. ✅ Tested Category API → **You're here!**
2. 📖 Read full docs → `README.md`
3. 🔧 Advanced examples → `CURL_EXAMPLES.md`
4. 🏗️ Build Transaction API → Next feature
5. 🎨 Build Frontend → Connect to API

---

## 💡 Pro Tips

1. **Save commonly used IDs**: Postman auto-saves `category_id` for you
2. **Use environment variables**: Switch between local/staging/production easily
3. **Check test results**: Green checkmarks = automation working
4. **Use Collections Runner**: Run all requests sequentially
5. **Export environments**: Share with team members

---

## 📞 Need Help?

- Check logs: `docker-compose logs -f app`
- Check database: `docker-compose exec pgsql psql -U sail -d balance_flow`
- Run tests: `docker-compose exec app php artisan test --filter=CategoryTest`
- API docs: See `README.md` for detailed documentation

Happy Testing! 🚀

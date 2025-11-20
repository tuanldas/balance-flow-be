# cURL Examples - Category API

Quick reference cho việc test Category API endpoints bằng cURL.

## 🔐 Setup

Đầu tiên, login để lấy access token:

```bash
# Login
curl -X POST http://localhost:8083/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "tuanldas@gmail.com",
    "password": "123123"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502...",
    "token_type": "Bearer",
    "expires_in": 1296000
  }
}
```

Lưu access token vào biến môi trường:
```bash
export TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."
```

## 📋 Category Endpoints

### 1. List All Categories

```bash
curl -X GET http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 2. List Categories - Filter by Income

```bash
curl -X GET "http://localhost:8083/api/categories?type=income" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 3. List Categories - Filter by Expense

```bash
curl -X GET "http://localhost:8083/api/categories?type=expense" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 4. List Categories - Custom Pagination

```bash
# 20 items per page, page 2
curl -X GET "http://localhost:8083/api/categories?per_page=20&page=2" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 5. List Categories - Sort by Name (Descending)

```bash
# Sort A-Z descending
curl -X GET "http://localhost:8083/api/categories?sort_by=name&sort_direction=desc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 6. List Categories - Sort by Type

```bash
# Sort by type (expense, then income), with secondary sort by name
curl -X GET "http://localhost:8083/api/categories?sort_by=type&sort_direction=asc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 7. List Categories - Sort by Created Date (Newest First)

```bash
# Sort by creation date descending
curl -X GET "http://localhost:8083/api/categories?sort_by=created_at&sort_direction=desc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 8. List Categories - Combined Filters

```bash
# Income categories, 10 items per page, sorted by created date (newest)
curl -X GET "http://localhost:8083/api/categories?type=income&per_page=10&page=1&sort_by=created_at&sort_direction=desc" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 9. Create Category

```bash
curl -X POST http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Coffee & Breakfast",
    "type": "expense",
    "icon_svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"25\" height=\"25\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\"><circle cx=\"12\" cy=\"12\" r=\"10\"/><path d=\"M12 6v6l4 2\"/></svg>"
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Category has been created",
  "data": {
    "category": {
      "id": "018d1234-5678-7abc-def0-123456789abc",
      "name": "Coffee & Breakfast",
      "original_name": "Coffee & Breakfast",
      "type": "expense",
      "icon_svg": "<svg>...</svg>",
      "is_system": false,
      "user_id": "018d...",
      "created_at": "2025-11-18T00:00:00.000000Z",
      "updated_at": "2025-11-18T00:00:00.000000Z"
    }
  }
}
```

Lưu category ID:
```bash
export CATEGORY_ID="018d1234-5678-7abc-def0-123456789abc"
```

### 10. Get Category by ID

```bash
curl -X GET "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 11. Update Category

```bash
curl -X PUT "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Morning Coffee Updated",
    "type": "expense"
  }'
```

### 12. Get Transaction Count

```bash
curl -X GET "http://localhost:8083/api/categories/$CATEGORY_ID/transactions-count" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "count": 5
  }
}
```

### 13. Delete Category (No Transactions)

```bash
curl -X DELETE "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### 14. Delete Category (With Transaction Transfer)

```bash
# Lưu target category ID
export TARGET_CATEGORY_ID="018d9876-5432-1abc-def0-987654321abc"

curl -X DELETE "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d "{
    \"transfer_to_category_id\": \"$TARGET_CATEGORY_ID\"
  }"
```

## 🌐 Multi-language Examples

### Vietnamese (default)
```bash
curl -X GET http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Accept-Language: vi" \
  -H "Authorization: Bearer $TOKEN"
```

### English
```bash
curl -X GET http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Accept-Language: en" \
  -H "Authorization: Bearer $TOKEN"
```

## 🧪 Test Scenarios

### Scenario 1: Create, Update, Delete Category

```bash
# 1. Create
RESPONSE=$(curl -s -X POST http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Test Category",
    "type": "income",
    "icon_svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"25\" height=\"25\"><circle cx=\"12\" cy=\"12\" r=\"10\"/></svg>"
  }')

# Extract category ID (requires jq)
CATEGORY_ID=$(echo $RESPONSE | jq -r '.data.category.id')
echo "Created category: $CATEGORY_ID"

# 2. Update
curl -X PUT "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Updated Test Category"
  }'

# 3. Get details
curl -X GET "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"

# 4. Delete
curl -X DELETE "http://localhost:8083/api/categories/$CATEGORY_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### Scenario 2: Test Validation Errors

```bash
# Missing required fields
curl -X POST http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{}'

# Invalid type
curl -X POST http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Test",
    "type": "invalid_type",
    "icon_svg": "<svg></svg>"
  }'

# Invalid filter type
curl -X GET "http://localhost:8083/api/categories?type=invalid" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### Scenario 3: Test Authorization

```bash
# Try to update system category (should fail)
SYSTEM_CATEGORY_ID="..." # Get from list categories

curl -X PUT "http://localhost:8083/api/categories/$SYSTEM_CATEGORY_ID" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Trying to update system category"
  }'

# Expected: 403 Forbidden
```

## 💡 Tips

### Pretty Print JSON (với jq)
```bash
curl -X GET http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '.'
```

### Save Response to File
```bash
curl -X GET http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -o categories.json
```

### Show Response Headers
```bash
curl -i -X GET http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### Verbose Output (for debugging)
```bash
curl -v -X GET http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

### Silent Mode (no progress bar)
```bash
curl -s -X GET http://localhost:8083/api/categories \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN"
```

## 🔄 Automation Script Example

```bash
#!/bin/bash

# Configuration
BASE_URL="http://localhost:8083"
EMAIL="tuanldas@gmail.com"
PASSWORD="123123"

# Login and get token
echo "Logging in..."
LOGIN_RESPONSE=$(curl -s -X POST "$BASE_URL/api/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"email\":\"$EMAIL\",\"password\":\"$PASSWORD\"}")

TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.access_token')

if [ "$TOKEN" == "null" ]; then
  echo "Login failed!"
  exit 1
fi

echo "Token: $TOKEN"

# List all categories
echo -e "\nListing all categories..."
curl -s -X GET "$BASE_URL/api/categories" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# Create a test category
echo -e "\nCreating test category..."
CREATE_RESPONSE=$(curl -s -X POST "$BASE_URL/api/categories" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{
    "name": "Automated Test Category",
    "type": "expense",
    "icon_svg": "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"25\" height=\"25\"><circle cx=\"12\" cy=\"12\" r=\"10\"/></svg>"
  }')

CATEGORY_ID=$(echo $CREATE_RESPONSE | jq -r '.data.category.id')
echo "Created category ID: $CATEGORY_ID"

# Clean up - delete test category
echo -e "\nCleaning up..."
curl -s -X DELETE "$BASE_URL/api/categories/$CATEGORY_ID" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer $TOKEN" | jq '.'

echo "Done!"
```

Save as `test-api.sh`, make executable: `chmod +x test-api.sh`, then run: `./test-api.sh`

## 📚 Additional Resources

- **jq**: JSON processor - https://stedolan.github.io/jq/
- **HTTPie**: Modern alternative to cURL - https://httpie.io/
- **Postman**: GUI tool - Import the collection from `/postman` folder

# API Documentation - Balance Flow Backend

## Overview

The Balance Flow API provides RESTful endpoints for managing financial balances, transactions, and user accounts. This documentation covers authentication, endpoints, request/response formats, and error handling.

**Base URL**: `http://localhost/api/v1`
**API Version**: 1.0
**Response Format**: JSON
**Authentication**: Laravel Sanctum (Token-based)

---

## Table of Contents

1. [Authentication](#authentication)
2. [API Endpoints](#api-endpoints)
3. [Request/Response Format](#requestresponse-format)
4. [Error Handling](#error-handling)
5. [Rate Limiting](#rate-limiting)
6. [Pagination](#pagination)
7. [Filtering & Sorting](#filtering--sorting)
8. [Versioning](#versioning)

---

## Authentication

### Overview

The API uses **Laravel Sanctum** for token-based authentication.

### Authentication Flow

```
1. User registers/logs in
   ↓
2. Server returns access token
   ↓
3. Client includes token in subsequent requests
   ↓
4. Server validates token
```

### Obtaining a Token

#### Register a New User

```http
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "SecurePassword123",
  "password_confirmation": "SecurePassword123"
}
```

**Response (201 Created)**:
```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2025-11-13T10:00:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz123456"
  },
  "message": "User registered successfully"
}
```

#### Login

```http
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "SecurePassword123"
}
```

**Response (200 OK)**:
```json
{
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "2|zyxwvutsrqponmlkjihgfedcba654321"
  },
  "message": "Login successful"
}
```

### Using the Token

Include the token in the `Authorization` header:

```http
GET /api/v1/user
Authorization: Bearer 2|zyxwvutsrqponmlkjihgfedcba654321
```

### Logout

```http
POST /api/v1/auth/logout
Authorization: Bearer {token}
```

**Response (200 OK)**:
```json
{
  "message": "Logged out successfully"
}
```

---

## API Endpoints

### Authentication Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/auth/register` | Register new user | No |
| POST | `/api/v1/auth/login` | Login user | No |
| POST | `/api/v1/auth/logout` | Logout user | Yes |
| POST | `/api/v1/auth/refresh` | Refresh token | Yes |
| GET | `/api/v1/auth/me` | Get authenticated user | Yes |

### User Endpoints

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/users` | List all users | Yes (Admin) |
| GET | `/api/v1/users/{id}` | Get user details | Yes |
| PUT | `/api/v1/users/{id}` | Update user | Yes |
| DELETE | `/api/v1/users/{id}` | Delete user | Yes |
| GET | `/api/v1/user` | Get current user | Yes |
| PUT | `/api/v1/user/profile` | Update profile | Yes |
| PUT | `/api/v1/user/password` | Change password | Yes |

### Account Endpoints (Planned)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/accounts` | List user accounts | Yes |
| POST | `/api/v1/accounts` | Create account | Yes |
| GET | `/api/v1/accounts/{id}` | Get account details | Yes |
| PUT | `/api/v1/accounts/{id}` | Update account | Yes |
| DELETE | `/api/v1/accounts/{id}` | Delete account | Yes |
| GET | `/api/v1/accounts/{id}/balance` | Get account balance | Yes |

### Transaction Endpoints (Planned)

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| GET | `/api/v1/transactions` | List transactions | Yes |
| POST | `/api/v1/transactions` | Create transaction | Yes |
| GET | `/api/v1/transactions/{id}` | Get transaction details | Yes |
| PUT | `/api/v1/transactions/{id}` | Update transaction | Yes |
| DELETE | `/api/v1/transactions/{id}` | Delete transaction | Yes |

---

## Request/Response Format

### Standard Response Structure

All API responses follow this structure:

#### Success Response

```json
{
  "data": {
    // Response data object or array
  },
  "message": "Success message",
  "meta": {
    // Metadata (pagination, etc.)
  }
}
```

#### Error Response

```json
{
  "error": {
    "message": "Error message",
    "code": "ERROR_CODE",
    "details": {
      // Additional error details
    }
  }
}
```

### Example: Get User

#### Request

```http
GET /api/v1/users/1
Authorization: Bearer {token}
Accept: application/json
```

#### Response (200 OK)

```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "email_verified_at": "2025-11-13T10:00:00.000000Z",
    "created_at": "2025-11-13T09:00:00.000000Z",
    "updated_at": "2025-11-13T10:00:00.000000Z"
  }
}
```

### Example: Create Account (Planned)

#### Request

```http
POST /api/v1/accounts
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Savings Account",
  "type": "savings",
  "currency": "USD",
  "initial_balance": 1000.00
}
```

#### Response (201 Created)

```json
{
  "data": {
    "id": 1,
    "user_id": 1,
    "name": "Savings Account",
    "type": "savings",
    "currency": "USD",
    "balance": 1000.00,
    "created_at": "2025-11-13T10:30:00.000000Z",
    "updated_at": "2025-11-13T10:30:00.000000Z"
  },
  "message": "Account created successfully"
}
```

### Example: List Transactions (Planned)

#### Request

```http
GET /api/v1/transactions?page=1&per_page=15&sort=-created_at
Authorization: Bearer {token}
```

#### Response (200 OK)

```json
{
  "data": [
    {
      "id": 1,
      "account_id": 1,
      "type": "income",
      "amount": 500.00,
      "description": "Salary",
      "category": "salary",
      "date": "2025-11-01",
      "created_at": "2025-11-01T10:00:00.000000Z"
    },
    {
      "id": 2,
      "account_id": 1,
      "type": "expense",
      "amount": 50.00,
      "description": "Groceries",
      "category": "food",
      "date": "2025-11-05",
      "created_at": "2025-11-05T14:30:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 42
  },
  "links": {
    "first": "http://localhost/api/v1/transactions?page=1",
    "last": "http://localhost/api/v1/transactions?page=3",
    "prev": null,
    "next": "http://localhost/api/v1/transactions?page=2"
  }
}
```

---

## Error Handling

### HTTP Status Codes

| Code | Status | Description |
|------|--------|-------------|
| 200 | OK | Successful request |
| 201 | Created | Resource created successfully |
| 204 | No Content | Successful request with no content |
| 400 | Bad Request | Invalid request format |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 422 | Unprocessable Entity | Validation error |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |
| 503 | Service Unavailable | Service temporarily unavailable |

### Error Response Format

```json
{
  "error": {
    "message": "Human-readable error message",
    "code": "ERROR_CODE",
    "details": {}
  }
}
```

### Common Error Codes

| Code | Description |
|------|-------------|
| `UNAUTHORIZED` | Invalid or missing authentication token |
| `FORBIDDEN` | Insufficient permissions |
| `VALIDATION_ERROR` | Request validation failed |
| `NOT_FOUND` | Resource not found |
| `RATE_LIMIT_EXCEEDED` | Too many requests |
| `INTERNAL_ERROR` | Server error |

### Validation Error Example

#### Request

```http
POST /api/v1/accounts
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "",
  "type": "invalid_type",
  "currency": "INVALID"
}
```

#### Response (422 Unprocessable Entity)

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "type": [
      "The selected type is invalid."
    ],
    "currency": [
      "The currency must be a valid 3-letter ISO code."
    ]
  }
}
```

### Authentication Error Example

#### Request

```http
GET /api/v1/user
Authorization: Bearer invalid_token
```

#### Response (401 Unauthorized)

```json
{
  "error": {
    "message": "Unauthenticated",
    "code": "UNAUTHORIZED"
  }
}
```

### Not Found Error Example

#### Request

```http
GET /api/v1/users/9999
Authorization: Bearer {token}
```

#### Response (404 Not Found)

```json
{
  "error": {
    "message": "User not found",
    "code": "NOT_FOUND"
  }
}
```

---

## Rate Limiting

### Default Limits

| Endpoint Type | Limit | Window |
|--------------|-------|--------|
| Authenticated | 60 requests | 1 minute |
| Unauthenticated | 10 requests | 1 minute |
| Login attempts | 5 requests | 1 minute |

### Rate Limit Headers

```http
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1699876543
```

### Rate Limit Exceeded Response

```http
HTTP/1.1 429 Too Many Requests
Content-Type: application/json

{
  "error": {
    "message": "Too many requests. Please try again later.",
    "code": "RATE_LIMIT_EXCEEDED",
    "details": {
      "retry_after": 30
    }
  }
}
```

---

## Pagination

### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number |
| `per_page` | integer | 15 | Items per page (max 100) |

### Example

```http
GET /api/v1/transactions?page=2&per_page=20
```

### Response Metadata

```json
{
  "data": [...],
  "meta": {
    "current_page": 2,
    "from": 21,
    "last_page": 5,
    "per_page": 20,
    "to": 40,
    "total": 95
  },
  "links": {
    "first": "http://localhost/api/v1/transactions?page=1",
    "last": "http://localhost/api/v1/transactions?page=5",
    "prev": "http://localhost/api/v1/transactions?page=1",
    "next": "http://localhost/api/v1/transactions?page=3"
  }
}
```

---

## Filtering & Sorting

### Filtering

Use query parameters to filter results:

```http
GET /api/v1/transactions?type=expense&category=food&date_from=2025-11-01&date_to=2025-11-30
```

**Common Filter Parameters**:
- `type`: Filter by transaction type
- `category`: Filter by category
- `date_from`: Start date (YYYY-MM-DD)
- `date_to`: End date (YYYY-MM-DD)
- `amount_min`: Minimum amount
- `amount_max`: Maximum amount

### Sorting

Use the `sort` parameter to sort results:

```http
GET /api/v1/transactions?sort=-created_at
```

**Sort Syntax**:
- `sort=created_at`: Ascending order
- `sort=-created_at`: Descending order (prefix with `-`)
- `sort=amount,-created_at`: Multiple fields

**Sortable Fields**:
- `id`: Resource ID
- `created_at`: Creation date
- `updated_at`: Last update date
- `amount`: Transaction amount (for transactions)
- `name`: Name (for accounts, users)

---

## Versioning

### URL Versioning

The API uses URL versioning:

```
/api/v1/...  (Current version)
/api/v2/...  (Future version)
```

### Version Support Policy

- **Current version**: Full support
- **Previous version**: Supported for 6 months after new version release
- **Deprecated versions**: 3-month notice before removal

### Deprecation Headers

When using deprecated endpoints:

```http
Warning: 299 - "This API version is deprecated. Please migrate to v2 by 2026-05-13"
Sunset: Wed, 13 May 2026 00:00:00 GMT
```

---

## Request Examples

### Using cURL

#### Get User

```bash
curl -X GET "http://localhost/api/v1/user" \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json"
```

#### Create Account

```bash
curl -X POST "http://localhost/api/v1/accounts" \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "Savings Account",
    "type": "savings",
    "currency": "USD",
    "initial_balance": 1000.00
  }'
```

### Using JavaScript (Axios)

```javascript
// Set default headers
axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
axios.defaults.headers.common['Accept'] = 'application/json';

// Get user
const user = await axios.get('/api/v1/user');

// Create account
const account = await axios.post('/api/v1/accounts', {
  name: 'Savings Account',
  type: 'savings',
  currency: 'USD',
  initial_balance: 1000.00
});

// List transactions with filters
const transactions = await axios.get('/api/v1/transactions', {
  params: {
    page: 1,
    per_page: 20,
    type: 'expense',
    sort: '-created_at'
  }
});
```

### Using PHP (Guzzle)

```php
use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'http://localhost/api/v1/',
    'headers' => [
        'Authorization' => 'Bearer ' . $token,
        'Accept' => 'application/json',
    ]
]);

// Get user
$response = $client->get('user');
$user = json_decode($response->getBody(), true);

// Create account
$response = $client->post('accounts', [
    'json' => [
        'name' => 'Savings Account',
        'type' => 'savings',
        'currency' => 'USD',
        'initial_balance' => 1000.00
    ]
]);
```

---

## Best Practices

### 1. Always Use HTTPS in Production

```
✅ https://api.balanceflow.com/v1/users
❌ http://api.balanceflow.com/v1/users
```

### 2. Include Accept Header

```http
Accept: application/json
```

### 3. Handle Errors Gracefully

```javascript
try {
  const response = await axios.get('/api/v1/user');
} catch (error) {
  if (error.response.status === 401) {
    // Redirect to login
  } else if (error.response.status === 422) {
    // Display validation errors
  } else {
    // Show generic error message
  }
}
```

### 4. Use Pagination for Lists

Always paginate large datasets to improve performance.

### 5. Cache Responses (when appropriate)

Use cache headers for static or infrequently changing data.

### 6. Validate Input Client-Side

Reduce unnecessary API calls by validating input before submission.

---

## Webhooks (Planned)

Future support for webhooks to notify external systems of events:

**Supported Events**:
- `account.created`
- `account.updated`
- `transaction.created`
- `transaction.updated`

---

## OpenAPI Specification

Full OpenAPI (Swagger) specification available at:

```
http://localhost/api/documentation
```

**Download**:
- JSON: `http://localhost/api/documentation.json`
- YAML: `http://localhost/api/documentation.yaml`

---

## Support & Contact

**Documentation**: [https://github.com/tuanldas/balance-flow-be/docs](https://github.com/tuanldas/balance-flow-be/docs)
**Issues**: [https://github.com/tuanldas/balance-flow-be/issues](https://github.com/tuanldas/balance-flow-be/issues)
**Email**: support@balanceflow.com (if applicable)

---

**API Version**: 1.0
**Last Updated**: 2025-11-13
**Maintainer**: Development Team

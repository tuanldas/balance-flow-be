# Database Documentation - Balance Flow Backend

## Overview

Balance Flow Backend uses **PostgreSQL 17** as the primary database. This document covers the database schema, relationships, indexes, migrations, and best practices.

**Database**: PostgreSQL 17-alpine
**Charset**: UTF8
**Collation**: en_US.utf8
**Connection Pool**: PDO (PHP Data Objects)

---

## Table of Contents

1. [Database Schema](#database-schema)
2. [Table Relationships](#table-relationships)
3. [Indexes](#indexes)
4. [Migrations](#migrations)
5. [Seeders & Factories](#seeders--factories)
6. [Query Optimization](#query-optimization)
7. [Backup & Recovery](#backup--recovery)
8. [Database Conventions](#database-conventions)

---

## Database Schema

### Entity Relationship Diagram

```
┌─────────────────────┐
│       users         │
│─────────────────────│
│ id (UUID PK)        │
│ name                │
│ email (UNIQUE)      │
│ email_verified_at   │
│ password            │
│ remember_token      │
│ created_at          │
│ updated_at          │
└──────────┬──────────┘
           │
           │ 1:N
           │
           ▼
┌─────────────────────┐          ┌─────────────────────┐
│  oauth_clients      │          │  oauth_auth_codes   │
│─────────────────────│          │─────────────────────│
│ id (UUID PK)        │◄─────────┤ client_id (FK)      │
│ owner_id (FK)       │          │ user_id (FK)        │
│ owner_type          │          │ id (VARCHAR PK)     │
│ name                │          │ scopes              │
│ secret              │          │ revoked             │
│ provider            │          │ expires_at          │
│ redirect_uris       │          └─────────────────────┘
│ grant_types         │
│ revoked             │
│ created_at          │
│ updated_at          │
└──────────┬──────────┘
           │
           │ 1:N
           │
           ▼
┌─────────────────────┐
│ oauth_access_tokens │
│─────────────────────│
│ id (VARCHAR PK)     │
│ user_id (UUID FK)   │
│ client_id (UUID FK) │
│ name                │
│ scopes              │
│ revoked             │
│ created_at          │
│ updated_at          │
│ expires_at          │
└─────────────────────┘
           │
           │ 1:1
           │
           ▼
┌──────────────────────┐
│ oauth_refresh_tokens │
│──────────────────────│
│ id (VARCHAR PK)      │
│ access_token_id (FK) │
│ revoked              │
│ expires_at           │
└──────────────────────┘

┌─────────────────────┐
│  sessions           │
│─────────────────────│
│ id (VARCHAR PK)     │
│ user_id (FK)        │
│ ip_address          │
│ user_agent          │
│ payload             │
│ last_activity       │
└─────────────────────┘

┌──────────────────────────┐
│ password_reset_tokens    │
│──────────────────────────│
│ email (VARCHAR PK)       │
│ token                    │
│ created_at               │
└──────────────────────────┘

┌─────────────────────┐
│      cache          │
│─────────────────────│
│ key (VARCHAR PK)    │
│ value               │
│ expiration          │
└─────────────────────┘

┌─────────────────────┐
│       jobs          │
│─────────────────────│
│ id (BIGINT PK)      │
│ queue               │
│ payload             │
│ attempts            │
│ reserved_at         │
│ available_at        │
│ created_at          │
└─────────────────────┘

┌──────────────────────┐
│  oauth_device_codes  │
│──────────────────────│
│ id (UUID PK)         │
│ user_id (UUID FK)    │
│ client_id (UUID FK)  │
│ device_code          │
│ user_code            │
│ scopes               │
│ expires_at           │
└──────────────────────┘
```

---

## Core Tables

### `users`

**Purpose**: Store user account information

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Unique identifier (UUID v7) |
| `name` | VARCHAR(255) | NOT NULL | User's full name |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | User's email address |
| `email_verified_at` | TIMESTAMP | NULLABLE | Email verification timestamp |
| `password` | VARCHAR(255) | NOT NULL | Hashed password |
| `remember_token` | VARCHAR(100) | NULLABLE | "Remember me" token |
| `created_at` | TIMESTAMP | NOT NULL | Record creation time |
| `updated_at` | TIMESTAMP | NOT NULL | Last update time |

**Indexes**:
- PRIMARY KEY on `id`
- UNIQUE INDEX on `email`

**Sample Data**:
```sql
INSERT INTO users (id, name, email, email_verified_at, password, created_at, updated_at)
VALUES (
    '01934567-89ab-7cde-f012-3456789abcde',
    'John Doe',
    'john@example.com',
    NOW(),
    '$2y$12$...',
    NOW(),
    NOW()
);
```

**Related Models**: `App\Models\User`

**Migration**: `0001_01_01_000000_create_users_table.php` + `2025_10_29_032352_convert_users_id_to_uuid_v7.php`

---

### `password_reset_tokens`

**Purpose**: Store password reset tokens for account recovery

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `email` | VARCHAR(255) | PRIMARY KEY | User's email |
| `token` | VARCHAR(255) | NOT NULL | Reset token hash |
| `created_at` | TIMESTAMP | NULLABLE | Token creation time |

**Indexes**:
- PRIMARY KEY on `email`

**Token Expiry**: 60 minutes (configurable in `config/auth.php`)

**Migration**: `0001_01_01_000000_create_users_table.php`

---

### `sessions`

**Purpose**: Store user session data for web authentication

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | VARCHAR(255) | PRIMARY KEY | Session ID |
| `user_id` | BIGINT | FOREIGN KEY, NULLABLE | User ID (if authenticated) |
| `ip_address` | VARCHAR(45) | NULLABLE | Client IP address |
| `user_agent` | TEXT | NULLABLE | Browser user agent |
| `payload` | LONGTEXT | NOT NULL | Serialized session data |
| `last_activity` | INTEGER | NOT NULL | Last activity timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `user_id`
- INDEX on `last_activity`

**Session Driver**: Database (configurable to Redis)

**Migration**: `0001_01_01_000000_create_users_table.php`

---

## OAuth Tables (Laravel Passport)

### `oauth_clients`

**Purpose**: OAuth 2.0 client applications

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Client ID |
| `owner_id` | BIGINT | NULLABLE | Owner user ID (polymorphic) |
| `owner_type` | VARCHAR(255) | NULLABLE | Owner model type |
| `name` | VARCHAR(255) | NOT NULL | Client name |
| `secret` | VARCHAR(255) | NULLABLE | Client secret |
| `provider` | VARCHAR(255) | NULLABLE | OAuth provider |
| `redirect_uris` | TEXT | NOT NULL | Allowed redirect URIs (JSON) |
| `grant_types` | TEXT | NOT NULL | Allowed grant types (JSON) |
| `revoked` | BOOLEAN | NOT NULL | Whether client is revoked |
| `created_at` | TIMESTAMP | NOT NULL | Creation timestamp |
| `updated_at` | TIMESTAMP | NOT NULL | Last update timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `owner_id`, `owner_type` (polymorphic)

**Migration**: `2025_10_29_030921_create_oauth_clients_table.php`

---

### `oauth_access_tokens`

**Purpose**: OAuth 2.0 access tokens for API authentication

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | VARCHAR(100) | PRIMARY KEY | Token ID |
| `user_id` | UUID | FOREIGN KEY, NULLABLE | User ID |
| `client_id` | UUID | FOREIGN KEY, NOT NULL | Client ID |
| `name` | VARCHAR(255) | NULLABLE | Token name |
| `scopes` | TEXT | NULLABLE | Token scopes (JSON) |
| `revoked` | BOOLEAN | NOT NULL | Whether token is revoked |
| `created_at` | TIMESTAMP | NOT NULL | Creation timestamp |
| `updated_at` | TIMESTAMP | NOT NULL | Last update timestamp |
| `expires_at` | TIMESTAMP | NULLABLE | Expiration timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `user_id`

**Token Lifetime**: Configurable (default: 1 year)

**Migration**: `2025_10_29_030919_create_oauth_access_tokens_table.php`

---

### `oauth_refresh_tokens`

**Purpose**: OAuth 2.0 refresh tokens for token renewal

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | VARCHAR(100) | PRIMARY KEY | Refresh token ID |
| `access_token_id` | VARCHAR(100) | FOREIGN KEY, NOT NULL | Associated access token |
| `revoked` | BOOLEAN | NOT NULL | Whether token is revoked |
| `expires_at` | TIMESTAMP | NULLABLE | Expiration timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `access_token_id`

**Migration**: `2025_10_29_030920_create_oauth_refresh_tokens_table.php`

---

### `oauth_auth_codes`

**Purpose**: OAuth 2.0 authorization codes (authorization code flow)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | VARCHAR(100) | PRIMARY KEY | Auth code ID |
| `user_id` | UUID | FOREIGN KEY, NOT NULL | User ID |
| `client_id` | UUID | FOREIGN KEY, NOT NULL | Client ID |
| `scopes` | TEXT | NULLABLE | Requested scopes (JSON) |
| `revoked` | BOOLEAN | NOT NULL | Whether code is revoked |
| `expires_at` | TIMESTAMP | NULLABLE | Expiration timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `user_id`

**Code Lifetime**: 10 minutes

**Migration**: `2025_10_29_030918_create_oauth_auth_codes_table.php`

---

### `oauth_device_codes`

**Purpose**: OAuth 2.0 device codes (device flow for limited-input devices)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | UUID | PRIMARY KEY | Device code record ID |
| `user_id` | UUID | FOREIGN KEY, NULLABLE | User ID (after authorization) |
| `client_id` | UUID | FOREIGN KEY, NOT NULL | Client ID |
| `device_code` | VARCHAR(255) | NOT NULL | Device verification code |
| `user_code` | VARCHAR(255) | NOT NULL | User-friendly code |
| `scopes` | TEXT | NULLABLE | Requested scopes (JSON) |
| `expires_at` | TIMESTAMP | NULLABLE | Expiration timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `user_id`

**Migration**: `2025_10_29_030922_create_oauth_device_codes_table.php`

---

## System Tables

### `cache`

**Purpose**: Application cache storage (when using database driver)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `key` | VARCHAR(255) | PRIMARY KEY | Cache key |
| `value` | MEDIUMTEXT | NOT NULL | Cached value (serialized) |
| `expiration` | INTEGER | NOT NULL | Expiration timestamp |

**Indexes**:
- PRIMARY KEY on `key`
- INDEX on `expiration`

**Migration**: `0001_01_01_000001_create_cache_table.php`

---

### `jobs`

**Purpose**: Queue job storage (when using database driver)

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| `id` | BIGINT | PRIMARY KEY AUTO_INCREMENT | Job ID |
| `queue` | VARCHAR(255) | NOT NULL | Queue name |
| `payload` | LONGTEXT | NOT NULL | Serialized job data |
| `attempts` | TINYINT | NOT NULL | Number of attempts |
| `reserved_at` | INTEGER | NULLABLE | Time job was reserved |
| `available_at` | INTEGER | NOT NULL | Time job becomes available |
| `created_at` | INTEGER | NOT NULL | Job creation timestamp |

**Indexes**:
- PRIMARY KEY on `id`
- INDEX on `queue`

**Migration**: `0001_01_01_000002_create_jobs_table.php`

---

## Table Relationships

### User Relationships

```php
// User has many OAuth clients
User::hasMany(OAuthClient::class, 'owner_id')

// User has many OAuth access tokens
User::hasMany(OAuthAccessToken::class)

// User has many sessions
User::hasMany(Session::class)
```

### OAuth Client Relationships

```php
// OAuth client belongs to owner (polymorphic)
OAuthClient::morphTo('owner')

// OAuth client has many access tokens
OAuthClient::hasMany(OAuthAccessToken::class, 'client_id')

// OAuth client has many auth codes
OAuthClient::hasMany(OAuthAuthCode::class, 'client_id')
```

### Access Token Relationships

```php
// Access token belongs to user
OAuthAccessToken::belongsTo(User::class)

// Access token belongs to client
OAuthAccessToken::belongsTo(OAuthClient::class, 'client_id')

// Access token has one refresh token
OAuthAccessToken::hasOne(OAuthRefreshToken::class, 'access_token_id')
```

---

## Indexes

### Existing Indexes

| Table | Index Name | Columns | Type | Purpose |
|-------|------------|---------|------|---------|
| `users` | `users_pkey` | `id` | PRIMARY KEY | Unique identifier |
| `users` | `users_email_unique` | `email` | UNIQUE | Prevent duplicate emails |
| `sessions` | `sessions_pkey` | `id` | PRIMARY KEY | Session lookup |
| `sessions` | `sessions_user_id_index` | `user_id` | INDEX | User session queries |
| `sessions` | `sessions_last_activity_index` | `last_activity` | INDEX | Session cleanup |
| `oauth_clients` | `oauth_clients_pkey` | `id` | PRIMARY KEY | Client lookup |
| `oauth_clients` | `oauth_clients_owner_index` | `owner_id`, `owner_type` | INDEX | Polymorphic queries |
| `oauth_access_tokens` | `oauth_access_tokens_pkey` | `id` | PRIMARY KEY | Token lookup |
| `oauth_access_tokens` | `oauth_access_tokens_user_id_index` | `user_id` | INDEX | User token queries |
| `cache` | `cache_pkey` | `key` | PRIMARY KEY | Cache lookup |
| `cache` | `cache_expiration_index` | `expiration` | INDEX | Expired cache cleanup |
| `jobs` | `jobs_pkey` | `id` | PRIMARY KEY | Job ID |
| `jobs` | `jobs_queue_index` | `queue` | INDEX | Queue processing |

### Recommended Additional Indexes (Future)

```sql
-- For user queries
CREATE INDEX idx_users_created_at ON users(created_at DESC);

-- For OAuth token expiry cleanup
CREATE INDEX idx_oauth_access_tokens_expires_at
  ON oauth_access_tokens(expires_at)
  WHERE revoked = false;

-- For session cleanup queries
CREATE INDEX idx_sessions_last_activity
  ON sessions(last_activity DESC);
```

---

## Migrations

### Migration Files

| File | Description | Date |
|------|-------------|------|
| `0001_01_01_000000_create_users_table.php` | Initial users, sessions, password resets | Initial |
| `0001_01_01_000001_create_cache_table.php` | Cache storage | Initial |
| `0001_01_01_000002_create_jobs_table.php` | Queue jobs storage | Initial |
| `2025_10_29_030918_create_oauth_auth_codes_table.php` | OAuth authorization codes | 2025-10-29 |
| `2025_10_29_030919_create_oauth_access_tokens_table.php` | OAuth access tokens | 2025-10-29 |
| `2025_10_29_030920_create_oauth_refresh_tokens_table.php` | OAuth refresh tokens | 2025-10-29 |
| `2025_10_29_030921_create_oauth_clients_table.php` | OAuth clients | 2025-10-29 |
| `2025_10_29_030922_create_oauth_device_codes_table.php` | OAuth device codes | 2025-10-29 |
| `2025_10_29_032352_convert_users_id_to_uuid_v7.php` | Convert user IDs to UUID v7 | 2025-10-29 |
| `2025_10_29_032406_alter_passport_user_columns_to_uuid.php` | Update OAuth tables for UUID users | 2025-10-29 |

### Running Migrations

```bash
# Run all migrations
php artisan migrate

# Run migrations with seed data
php artisan migrate --seed

# Rollback last batch
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Refresh database (rollback + migrate)
php artisan migrate:refresh

# Fresh database (drop + migrate)
php artisan migrate:fresh

# Check migration status
php artisan migrate:status
```

### Migration Best Practices

1. **Never modify existing migrations** after they've been run in production
2. **Create new migrations** for schema changes
3. **Include both `up()` and `down()` methods**
4. **Test rollbacks** before deploying
5. **Use transactions** for data migrations

---

## Seeders & Factories

### User Factory

**Location**: `database/factories/UserFactory.php`

```php
UserFactory::new()->create([
    'name' => 'Test User',
    'email' => 'test@example.com',
]);
```

**Attributes**:
- `name`: Random name
- `email`: Unique safe email
- `email_verified_at`: Current timestamp
- `password`: Hashed "password"
- `remember_token`: Random string

### Database Seeder

**Location**: `database/seeders/DatabaseSeeder.php`

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=UserSeeder
```

---

## Query Optimization

### N+1 Query Prevention

**Problem**:
```php
// ❌ Bad: N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->oauthClients->count();
}
```

**Solution**:
```php
// ✅ Good: Eager loading
$users = User::with('oauthClients')->get();
foreach ($users as $user) {
    echo $user->oauthClients->count();
}
```

### Query Scopes

```php
// Define scope in User model
public function scopeVerified($query)
{
    return $query->whereNotNull('email_verified_at');
}

// Usage
$verifiedUsers = User::verified()->get();
```

### Database Transactions

```php
DB::transaction(function () {
    $user = User::create([...]);
    $client = OAuthClient::create([...]);
});
```

---

## Backup & Recovery

### Backup Commands

```bash
# Backup using Docker
docker compose exec postgres pg_dump -U balance_flow balance_flow > backup.sql

# Restore from backup
docker compose exec -T postgres psql -U balance_flow balance_flow < backup.sql
```

### Automated Backups (Recommended)

```bash
# Add to crontab for daily backups
0 2 * * * /path/to/backup-script.sh
```

**Backup Script Example**:
```bash
#!/bin/bash
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
docker compose exec postgres pg_dump -U balance_flow balance_flow > "backups/backup_${TIMESTAMP}.sql"
# Compress
gzip "backups/backup_${TIMESTAMP}.sql"
# Keep last 30 days
find backups/ -type f -mtime +30 -delete
```

---

## Database Conventions

### Naming Conventions

1. **Tables**: Plural, snake_case (`users`, `oauth_clients`)
2. **Columns**: Singular, snake_case (`user_id`, `created_at`)
3. **Primary Keys**: `id` (UUID or BIGINT)
4. **Foreign Keys**: `{table}_id` (`user_id`, `client_id`)
5. **Timestamps**: `created_at`, `updated_at`
6. **Soft Deletes**: `deleted_at`
7. **Pivot Tables**: Alphabetical order (`post_user`, not `user_post`)

### Data Types

| Laravel Type | PostgreSQL Type | Usage |
|--------------|-----------------|-------|
| `id()` | BIGSERIAL | Auto-incrementing ID |
| `uuid()` | UUID | UUID primary key |
| `string()` | VARCHAR(255) | Short text |
| `text()` | TEXT | Long text |
| `integer()` | INTEGER | Numbers |
| `boolean()` | BOOLEAN | True/false |
| `timestamp()` | TIMESTAMP | Date and time |
| `json()` | JSON | JSON data |

### UUID v7 Benefits

- **Sortable**: Time-ordered like auto-increment
- **Unique**: Globally unique identifiers
- **Distributed**: Safe for distributed systems
- **Secure**: Non-sequential, harder to enumerate

---

## Database Configuration

### Connection Settings

**File**: `config/database.php`

```php
'connections' => [
    'pgsql' => [
        'driver' => 'pgsql',
        'host' => env('DB_HOST', 'postgres'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'balance_flow'),
        'username' => env('DB_USERNAME', 'balance_flow'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8'),
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
    ],
],
```

### Environment Variables

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=balance_flow
DB_USERNAME=balance_flow
DB_PASSWORD=secure_password_here
```

---

## Performance Monitoring

### Query Logging

Enable in development:

```php
// In AppServiceProvider boot()
if (app()->environment('local')) {
    DB::listen(function ($query) {
        Log::info($query->sql, $query->bindings);
    });
}
```

### Slow Query Detection

```php
DB::whenQueryingForLongerThan(500, function ($connection, $event) {
    // Log slow queries (>500ms)
    Log::warning("Slow query detected: {$event->sql}");
});
```

---

## Future Schema Enhancements

### Planned Tables

1. **`accounts`**: Financial accounts
2. **`transactions`**: Balance transactions
3. **`categories`**: Transaction categories
4. **`budgets`**: Budget planning
5. **`notifications`**: User notifications

### Sample Future Schema

```sql
CREATE TABLE accounts (
    id UUID PRIMARY KEY,
    user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(50) NOT NULL,
    currency CHAR(3) NOT NULL DEFAULT 'USD',
    balance DECIMAL(15, 2) NOT NULL DEFAULT 0.00,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    INDEX idx_accounts_user_id (user_id)
);

CREATE TABLE transactions (
    id UUID PRIMARY KEY,
    account_id UUID NOT NULL REFERENCES accounts(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL,
    amount DECIMAL(15, 2) NOT NULL,
    description TEXT,
    category VARCHAR(100),
    date DATE NOT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP NOT NULL,
    INDEX idx_transactions_account_id (account_id),
    INDEX idx_transactions_date (date DESC)
);
```

---

**Database Version**: PostgreSQL 17
**Last Updated**: 2025-11-13
**Maintainer**: Development Team

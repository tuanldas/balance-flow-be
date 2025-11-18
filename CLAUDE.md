# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Balance Flow Backend is a Laravel 12 API application for **personal finance management**. The system uses the Service-Repository pattern with Laravel Passport for OAuth2 authentication. The application supports multi-language responses (Vietnamese and English) and follows clean architecture principles.

**Core Features:**
- 🔐 Authentication & Authorization (Laravel Passport OAuth2)
- 📁 Category Management (System + User categories)
- 💰 Transaction Management (Income/Expense tracking)
- 🌐 Multi-language Support (Vietnamese/English)
- 🧪 Comprehensive Test Coverage (PHPUnit)

## Common Development Commands

### Local Development (Docker)
```bash
# Start all services
docker-compose up -d

# Stop services
docker-compose down

# Run artisan commands
docker-compose exec app php artisan <command>

# Access application container
docker-compose exec app bash

# Install dependencies
docker-compose exec app composer install
```

### Local Development (Without Docker)
```bash
# Run all development services concurrently (server, queue, logs, vite)
composer run dev

# Run tests
php artisan test
composer run test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run specific test by name
php artisan test --filter=testName

# Code formatting
vendor/bin/pint --dirty

# Database operations
php artisan migrate
php artisan migrate:fresh --seed
php artisan db:seed

# Tinker (REPL)
php artisan tinker
```

### Passport Setup (First Time)
```bash
# Install Passport
php artisan passport:install

# Create password grant client and add credentials to .env
# PASSPORT_PASSWORD_CLIENT_ID=<client-id>
# PASSPORT_PASSWORD_CLIENT_SECRET=<client-secret>
```

### Seeding Data
```bash
# Seed all data
docker-compose exec app php artisan db:seed

# Seed specific seeder
docker-compose exec app php artisan db:seed --class=CategorySeeder

# Fresh migration with seed
docker-compose exec app php artisan migrate:fresh --seed
```

## Architecture

### Service-Repository Pattern

The application strictly follows a layered architecture pattern:

```
┌─────────────────────────────────────────────┐
│  Controllers (HTTP Layer)                   │
│  - Handle requests/responses only           │
│  - Depend on Service Interfaces             │
└─────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────┐
│  Services (Business Logic Layer)            │
│  - Implement business rules                 │
│  - Orchestrate operations                   │
│  - Depend on Repository & Adapter Interfaces│
└─────────────────────────────────────────────┘
                    ↓
┌───────────────────────┬─────────────────────┐
│  Repositories         │  Adapters           │
│  (Data Access)        │  (External Systems) │
│  - Database ops       │  - OAuth tokens     │
│  - Eloquent queries   │  - External APIs    │
└───────────────────────┴─────────────────────┘
```

#### Key Principles
- **Controllers** are `final` classes that inject service interfaces via constructor
- **Services** are `final readonly` classes that inject repository/adapter interfaces
- **Repositories** are `final readonly` classes handling database operations only
- **Adapters** are `final readonly` classes handling external system integration
- All implementations have corresponding interfaces in `Contracts/` subfolders
- Service bindings registered as singletons in `app/Providers/AppServiceProvider.php`

#### Directory Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/              # API controllers
│   │       ├── AuthController.php
│   │       ├── CategoryController.php
│   │       └── EmailVerificationController.php
│   ├── Middleware/           # Custom middleware (SetLocale)
│   ├── Requests/             # Form Request validation classes
│   │   ├── StoreCategoryRequest.php
│   │   ├── UpdateCategoryRequest.php
│   │   └── DeleteCategoryRequest.php
│   └── Resources/            # API Resources
│       └── CategoryResource.php
├── Services/
│   ├── Contracts/            # Service interfaces
│   │   ├── AuthServiceInterface.php
│   │   ├── CategoryServiceInterface.php
│   │   └── EmailVerificationServiceInterface.php
│   ├── AuthService.php
│   ├── CategoryService.php
│   └── EmailVerificationService.php
├── Repositories/
│   ├── Contracts/            # Repository interfaces
│   │   ├── UserRepositoryInterface.php
│   │   ├── CategoryRepositoryInterface.php
│   │   └── TransactionRepositoryInterface.php
│   ├── UserRepository.php
│   ├── CategoryRepository.php
│   └── TransactionRepository.php
├── Adapters/
│   ├── Contracts/            # Adapter interfaces
│   │   └── TokenAdapterInterface.php
│   └── PassportTokenAdapter.php
├── Models/                   # Eloquent models with UUID v7
│   ├── User.php
│   ├── Category.php
│   └── Transaction.php
└── Providers/
    └── AppServiceProvider.php  # Service bindings
```

### Authentication Flow

The application uses **Laravel Passport** with Password Grant for token-based authentication:

1. **Registration**: `POST /api/register` → creates user, sends verification email, returns tokens
2. **Email Verification**: `GET /api/verify-email/{id}/{hash}` → verifies email via signed URL
3. **Login**: `POST /api/login` → validates credentials + email verification, returns tokens
4. **Token Refresh**: `POST /api/refresh` → exchanges refresh token for new access token
5. **Password Reset**:
   - `POST /api/forgot-password` → sends reset email
   - `POST /api/reset-password` → resets password with token

**Important**: Login requires email verification. Unverified users receive `403` with `messages.auth.verification_required`.

### Category Management

The application implements a **dual category system** for flexible financial categorization:

#### Category Types
1. **System Categories**: Pre-defined categories created by admin
   - Read-only for regular users
   - Cannot be edited or deleted by users
   - Shared across all users
   - Uses translation keys for multi-language support
   - **17 default categories** (6 income + 11 expense)

2. **User Categories**: Custom categories created by users
   - Fully manageable by the owner
   - Can be edited and deleted
   - Private to each user
   - Uses actual names (not translation keys)

#### Category Classification
- **Income Categories**: For revenue tracking (salary, bonus, investment, etc.)
- **Expense Categories**: For spending tracking (food, housing, transportation, etc.)

#### Category Features
- **SVG Icons**: Each category has a 25x25px SVG icon
- **Transaction Relationship**: Categories track related transactions
- **Safe Deletion**:
  - Prevents deletion if category has transactions
  - Supports transferring transactions to another category before deletion
  - Type-safe: Can only transfer between categories of the same type

#### API Endpoints
```
GET    /api/categories                           # List accessible categories
GET    /api/categories?type=income               # Filter by type
POST   /api/categories                           # Create user category
GET    /api/categories/{id}                      # Get category details
PUT    /api/categories/{id}                      # Update user category
DELETE /api/categories/{id}                      # Delete category
GET    /api/categories/{id}/transactions-count   # Count transactions
```

#### Default System Categories

**Income (6):**
- Salary (Lương)
- Bonus (Thưởng)
- Investment (Đầu tư)
- Freelance (Làm tự do)
- Gift (Quà tặng)
- Other Income (Thu nhập khác)

**Expense (11):**
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

### Multi-language Support

The API supports Vietnamese (vi) and English (en) via the `SetLocale` middleware:

- **Header**: Send `Accept-Language: vi` or `Accept-Language: en`
- **Query Parameter**: `?locale=vi` or `?locale=en`
- **Default**: Vietnamese (vi)
- **Translation Files**: `lang/en/messages.php` and `lang/vi/messages.php`

All API responses use `__('messages.auth.key')` for localized messages.

## Configuration Notes

### Environment Variables (.env)

**Required for Passport:**
```env
PASSPORT_PASSWORD_CLIENT_ID=<from passport:install>
PASSPORT_PASSWORD_CLIENT_SECRET=<from passport:install>
OAUTH_SERVER_URL="${APP_URL}"
```

**Frontend Integration:**
```env
FRONTEND_URL=http://localhost:3000
```
Used for email verification and password reset links that redirect to frontend.

**CORS:**
```env
FRONTEND_URL=http://localhost:3000
```
Configured in `config/cors.php` to allow credentials from frontend domain.

### Laravel 12 Specifics

- **No `app/Http/Kernel.php`**: Middleware registered in `bootstrap/app.php`
- **No `app/Console/Kernel.php`**: Commands auto-register from `app/Console/Commands/`
- Model casts use `casts()` method instead of `$casts` property
- Use `php artisan make:` commands for scaffolding (controllers, models, etc.)

## Testing Guidelines

### General Principles
- **Use PHPUnit** (not Pest) for all tests
- Create tests via: `php artisan make:test <name>` (feature) or `php artisan make:test <name> --unit`
- Mock interfaces (not concrete classes) when testing services
- Use model factories for test data
- Run minimal test set with `--filter` when iterating
- Always run related tests before finalizing changes

### Test Structure
```
tests/
├── Feature/              # Integration tests
│   ├── CategoryTest.php  # 22 category endpoint tests
│   └── ...
├── Unit/                 # Unit tests
│   └── ...
└── TestCase.php          # Base test class
```

### Running Tests
```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test file
docker-compose exec app php artisan test --filter=CategoryTest

# Run specific test method
docker-compose exec app php artisan test --filter=test_user_can_create_category

# Run with coverage (if xdebug enabled)
docker-compose exec app php artisan test --coverage
```

### Test Coverage Requirements
- **Feature Tests**: All API endpoints must have tests
- **Authorization**: Test unauthorized access, forbidden actions
- **Validation**: Test validation errors for all inputs
- **Business Logic**: Test edge cases (e.g., delete with transactions)
- **Database State**: Use assertions to verify DB changes

### Factory Usage
```php
// Use factories for test data
$user = User::factory()->create();
$category = Category::factory()->income()->create(['user_id' => $user->id]);
$systemCategory = Category::factory()->system()->expense()->create();
$transaction = Transaction::factory()->income()->create([
    'user_id' => $user->id,
    'category_id' => $category->id,
]);
```

### Authentication in Tests
```php
use Laravel\Passport\Passport;

// Authenticate as user
Passport::actingAs($user);

// Make authenticated request
$response = $this->getJson('/api/categories');
```

### Test Example
```php
public function test_user_can_create_category(): void
{
    Passport::actingAs($this->user);

    $response = $this->postJson('/api/categories', [
        'name' => 'Test Category',
        'type' => 'income',
        'icon_svg' => '<svg>...</svg>',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.category.name', 'Test Category');

    $this->assertDatabaseHas('categories', [
        'name' => 'Test Category',
        'user_id' => $this->user->id,
    ]);
}
```

## Code Style

### PHP Standards
- **Strict Types**: All PHP files must start with `declare(strict_types=1);`
- **Type Hints**: Explicit return types required on all methods and properties
- **Constructor Promotion**: Use PHP 8+ constructor property promotion where possible
- **Formatting**: Run `vendor/bin/pint --dirty` before committing
- **No Empty Constructors**: Remove constructors with zero parameters

### Class Modifiers
- **Final Classes**: Controllers, Services, Repositories, Adapters must be `final`
  ```php
  final class CategoryController extends Controller { }
  ```
- **Readonly Classes**: Services, Repositories, Adapters use `readonly` modifier
  ```php
  final readonly class CategoryService implements CategoryServiceInterface { }
  ```

### Architecture Patterns
- **Validation**: Use Form Request classes (never inline validation in controllers)
  ```php
  // ✅ Good
  public function store(StoreCategoryRequest $request) { }

  // ❌ Bad
  public function store(Request $request) {
      $request->validate([...]);
  }
  ```

- **Dependency Injection**: Inject interfaces, not concrete classes
  ```php
  // ✅ Good
  public function __construct(
      private readonly CategoryServiceInterface $categoryService,
  ) {}

  // ❌ Bad
  public function __construct(
      private readonly CategoryService $categoryService,
  ) {}
  ```

- **Service Layer**: Business logic belongs in services, not controllers
  ```php
  // ✅ Good - Controller
  public function destroy(string $id): JsonResponse {
      $this->categoryService->deleteCategory($id, auth()->id());
      return response()->json([...]);
  }

  // ❌ Bad - Controller
  public function destroy(string $id): JsonResponse {
      $category = Category::find($id);
      if ($category->transactions()->count() > 0) {
          // Business logic in controller
      }
  }
  ```

### Naming Conventions
- **Controllers**: Singular resource name + `Controller` (e.g., `CategoryController`)
- **Services**: Singular resource name + `Service` (e.g., `CategoryService`)
- **Repositories**: Singular resource name + `Repository` (e.g., `CategoryRepository`)
- **Interfaces**: End with `Interface` (e.g., `CategoryServiceInterface`)
- **Form Requests**: Verb + Resource + `Request` (e.g., `StoreCategoryRequest`)
- **Resources**: Resource name + `Resource` (e.g., `CategoryResource`)
- **Test Methods**: `test_` + descriptive_name (e.g., `test_user_can_create_category`)

### Documentation
- **PHPDoc**: Use for complex methods, especially with arrays
  ```php
  /**
   * Create a new category
   *
   * @param array<string, mixed> $data
   * @return Category
   */
  public function create(array $data): Category
  ```

- **Comments**: Use for business logic explanations, not obvious code
  ```php
  // ✅ Good - Explains WHY
  // Ensure the category is not marked as system to prevent privilege escalation
  $data['is_system'] = false;

  // ❌ Bad - States WHAT (obvious from code)
  // Set is_system to false
  $data['is_system'] = false;
  ```

## Database

### Connection & Schema
- **Connection**: PostgreSQL 17 (Alpine)
- **Database**: `balance_flow` (configured in `.env`)
- **Character Set**: UTF-8
- **Timezone**: UTC

### Migrations
- **Auto-run**: Via `composer run setup`
- **Manual**: `php artisan migrate`
- **Fresh with seed**: `php artisan migrate:fresh --seed`
- **Status check**: `php artisan migrate:status`

### Database Tables
```
users                    # User accounts (UUID v7 primary key)
categories               # Financial categories (system + user)
transactions             # Financial transactions
password_reset_tokens    # Password reset tokens
oauth_*                  # Laravel Passport tables
```

### Models & UUID v7

All models use **UUID v7** as primary keys for better performance and uniqueness:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Ramsey\Uuid\Uuid;

class Category extends Model
{
    use HasUuids;

    public function newUniqueId(): string
    {
        return Uuid::uuid7()->toString();
    }

    public function uniqueIds(): array
    {
        return ['id'];
    }
}
```

**Benefits of UUID v7:**
- Time-ordered for better database performance
- Globally unique across distributed systems
- No auto-increment collision issues
- Compatible with Laravel's UUID trait

### Model Patterns

#### Relationships
```php
// One-to-Many
public function categories(): HasMany
{
    return $this->hasMany(Category::class);
}

// Belongs To
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}
```

#### Scopes
```php
// Query scopes for reusable queries
public function scopeAccessibleByUser($query, string $userId)
{
    return $query->where(function ($q) use ($userId) {
        $q->where('is_system', true)
            ->orWhere('user_id', $userId);
    });
}

// Usage: Category::accessibleByUser($userId)->get();
```

#### Accessors
```php
// Computed attributes
public function getTranslatedNameAttribute(): string
{
    if ($this->is_system) {
        return __($this->name);
    }
    return $this->name;
}

// Usage: $category->translated_name
```

#### Casts (Laravel 12 Syntax)
```php
// Use casts() method instead of $casts property
protected function casts(): array
{
    return [
        'is_system' => 'boolean',
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'email_verified_at' => 'datetime',
    ];
}
```

### Seeders
```
database/seeders/
├── DatabaseSeeder.php      # Main seeder
└── CategorySeeder.php      # Seeds 17 default system categories
```

**Run seeders:**
```bash
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan db:seed --class=CategorySeeder
```

### Factories
```
database/factories/
├── UserFactory.php         # User test data
├── CategoryFactory.php     # Category test data with states
└── TransactionFactory.php  # Transaction test data
```

**Factory states:**
```php
// Category factory
Category::factory()->system()->income()->create();
Category::factory()->expense()->create(['user_id' => $user->id]);

// Transaction factory
Transaction::factory()->income()->create();
Transaction::factory()->expense()->create();
```

## API Documentation & Testing

### Postman Collection
Complete Postman collection with all endpoints available in `/postman` directory:

```
postman/
├── BalanceFlow-API.postman_collection.json       # Main collection (18 requests)
├── BalanceFlow-Local.postman_environment.json    # Environment variables
├── README.md                                      # Full documentation
├── QUICKSTART.md                                  # 5-minute quick start guide
└── CURL_EXAMPLES.md                               # cURL command reference
```

**Import to Postman:**
1. Open Postman → Import
2. Select `BalanceFlow-API.postman_collection.json`
3. Select `BalanceFlow-Local.postman_environment.json`
4. Choose "BalanceFlow - Local" environment

**Features:**
- ✅ Auto-save tokens after login
- ✅ Auto-save category IDs after creation
- ✅ Test scripts for validation
- ✅ Multi-language examples
- ✅ All CRUD operations covered

### API Response Format

All endpoints follow consistent response structure:

**Success Response:**
```json
{
  "success": true,
  "message": "Operation successful",
  "data": {
    // Response data
  }
}
```

**Error Response:**
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
  "message": "Validation error",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

### Available Endpoints

**Authentication (9 endpoints):**
- `POST /api/register` - Register new user
- `POST /api/login` - Login with credentials
- `GET /api/me` - Get current user
- `POST /api/refresh` - Refresh access token
- `POST /api/logout` - Logout user
- `POST /api/change-password` - Change password
- `POST /api/forgot-password` - Request password reset
- `POST /api/reset-password` - Reset password with token
- `GET /api/verify-email/{id}/{hash}` - Verify email

**Categories (9 endpoints):**
- `GET /api/categories` - List accessible categories
- `GET /api/categories?type=income` - Filter by type
- `POST /api/categories` - Create user category
- `GET /api/categories/{id}` - Get category details
- `PUT /api/categories/{id}` - Update category
- `DELETE /api/categories/{id}` - Delete category
- `GET /api/categories/{id}/transactions-count` - Count transactions

### Quick API Test

```bash
# 1. Login
TOKEN=$(curl -s -X POST http://localhost:8083/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' \
  | jq -r '.data.access_token')

# 2. List categories
curl -s http://localhost:8083/api/categories \
  -H "Authorization: Bearer $TOKEN" | jq '.'

# 3. Create category
curl -s -X POST http://localhost:8083/api/categories \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Category",
    "type": "expense",
    "icon_svg": "<svg>...</svg>"
  }' | jq '.'
```

## Important Notes

- **Token Management**: Password changes and resets revoke all existing tokens for security
- **Email Configuration**: Set up `.env` mail config for verification/reset emails to work
- **Docker Ports**: Default `APP_PORT=80`, `FORWARD_DB_PORT=5432`, `FORWARD_REDIS_PORT=6379`
- **API Prefix**: All routes under `/api` (defined in `routes/api.php`)
- **Health Check**: Built-in Laravel health endpoint at `/up`
- **Rate Limiting**: Email verification resend throttled to 6 requests per minute
- **UUID v7**: All primary keys use UUID v7 for better performance
- **Test Coverage**: 22 tests for category endpoints, all passing
- **Code Formatting**: Always run `vendor/bin/pint --dirty` before committing

## Development Workflow

### Adding a New Feature

1. **Create Migration**
   ```bash
   docker-compose exec app php artisan make:migration create_feature_table
   ```

2. **Create Model with Factory**
   ```bash
   docker-compose exec app php artisan make:model Feature
   docker-compose exec app php artisan make:factory FeatureFactory
   ```

3. **Create Repository**
   - Create interface: `app/Repositories/Contracts/FeatureRepositoryInterface.php`
   - Create implementation: `app/Repositories/FeatureRepository.php`

4. **Create Service**
   - Create interface: `app/Services/Contracts/FeatureServiceInterface.php`
   - Create implementation: `app/Services/FeatureService.php`

5. **Register in AppServiceProvider**
   ```php
   $this->app->singleton(FeatureRepositoryInterface::class, FeatureRepository::class);
   $this->app->singleton(FeatureServiceInterface::class, FeatureService::class);
   ```

6. **Create Form Requests**
   ```bash
   docker-compose exec app php artisan make:request StoreFeatureRequest
   docker-compose exec app php artisan make:request UpdateFeatureRequest
   ```

7. **Create Resource**
   ```bash
   docker-compose exec app php artisan make:resource FeatureResource
   ```

8. **Create Controller**
   ```bash
   docker-compose exec app php artisan make:controller Api/FeatureController
   ```

9. **Add Routes** in `routes/api.php`

10. **Create Tests**
    ```bash
    docker-compose exec app php artisan make:test FeatureTest
    ```

11. **Run Tests & Format**
    ```bash
    docker-compose exec app php artisan test --filter=FeatureTest
    docker-compose exec app vendor/bin/pint --dirty
    ```

12. **Update Postman Collection** in `/postman` directory

13. **Update CLAUDE.md** if needed

## Troubleshooting

### Common Issues

**Database Connection Error:**
```bash
# Check if database container is running
docker-compose ps

# Restart services
docker-compose down && docker-compose up -d
```

**Migration Issues:**
```bash
# Check migration status
docker-compose exec app php artisan migrate:status

# Rollback and re-run
docker-compose exec app php artisan migrate:rollback
docker-compose exec app php artisan migrate
```

**Passport Issues:**
```bash
# Reinstall Passport
docker-compose exec app php artisan passport:install --force
```

**Test Failures:**
```bash
# Clear cache
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear

# Re-run tests
docker-compose exec app php artisan test
```

**Code Style Issues:**
```bash
# Auto-fix code style
docker-compose exec app vendor/bin/pint
```

### Logs

```bash
# Application logs
docker-compose logs -f app

# Database logs
docker-compose logs -f pgsql

# All services
docker-compose logs -f
```

## Resources

- **Laravel 12 Documentation**: https://laravel.com/docs/12.x
- **Laravel Passport**: https://laravel.com/docs/12.x/passport
- **PHPUnit**: https://phpunit.de/documentation.html
- **Postman**: Import collection from `/postman` directory
- **PostgreSQL**: https://www.postgresql.org/docs/17/

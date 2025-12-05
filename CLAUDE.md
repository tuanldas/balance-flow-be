# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

---

## ⚠️ IMPORTANT RULES FOR CLAUDE CODE

**CRITICAL - READ FIRST:**

### Git Commit Policy

**🚫 NEVER commit without explicit user approval**

- ❌ DO NOT run `git commit` automatically after completing tasks
- ❌ DO NOT commit even if tests pass or code works perfectly
- ❌ DO NOT assume the user wants changes committed
- ❌ DO NOT commit when user says "ok", "done", "good", "perfect", etc. - these are NOT commit commands
- ✅ ONLY commit when user EXPLICITLY says: "commit", "git commit", "commit this", "commit changes"
- ✅ ALWAYS ask "Bạn có muốn tôi commit không?" before committing
- ✅ WAIT for explicit confirmation with the word "commit"
- ✅ Show summary of changes and ASK before committing

**🚫 NEVER add Claude Code attribution to commit messages**

- ❌ DO NOT add "🤖 Generated with [Claude Code](https://claude.com/claude-code)" to commits
- ❌ DO NOT add "Co-Authored-By: Claude <noreply@anthropic.com>" to commits
- ✅ Keep commit messages clean and professional
- ✅ Follow the project's commit message format only (see below)

**Correct workflow:**
```
1. Complete the requested task
2. Run tests (if applicable)
3. Show summary of changes
4. ASK: "Bạn có muốn tôi commit những thay đổi này không?"
5. WAIT for user confirmation with the word "commit"
6. Only then: git commit (WITHOUT Claude Code attribution)
```

**IMPORTANT:** "commit" is the ONLY command that allows committing. Words like "ok", "yes", "good", "done", "perfect" mean the user is satisfied with the work, but DO NOT mean they want to commit.

---

## Project Overview

Laravel 12 backend using PHP 8.2+ with PostgreSQL database, Docker containerization, Vite + Tailwind CSS v4.

**Tech Stack:**
- Laravel 12, PHP 8.2-FPM, PostgreSQL 16
- Docker & Docker Compose, Nginx, Supervisor
- Vite + Tailwind CSS v4, PHPUnit, Laravel Pint

**Docker Services:**
- **app**: PHP 8.2-FPM with Supervisor (PHP-FPM, Queue Workers, Scheduler)
- **nginx**: Web server
- **db**: PostgreSQL 16

---

## API Testing with Postman

Comprehensive Postman collection available in `postman_collection.json`.

### Import Instructions

1. Download Postman from [postman.com](https://www.postman.com/downloads/)
2. `File → Import → Select postman_collection.json`
3. Configure variables: `base_url` (default: http://localhost:8080), `access_token` (auto-populated)

### Endpoints

| Category | Method | Endpoint | Description |
|----------|--------|----------|-------------|
| **Categories** | GET | `/api/categories` | List (paginated) |
| | GET | `/api/categories?type=income` | Filter by type |
| | POST | `/api/categories` | Create |
| | GET | `/api/categories/{id}` | Get details |
| | PUT | `/api/categories/{id}` | Update |
| | DELETE | `/api/categories/{id}` | Delete |
| | GET | `/api/categories/{id}/subcategories` | Get subcategories |
| **Auth** | POST | `/api/auth/register` | Register |
| | POST | `/api/auth/login` | Login |
| | GET | `/api/auth/me` | Get current user |
| | PUT | `/api/auth/profile` | Update profile |
| | PUT | `/api/auth/password` | Change password |
| | POST | `/api/auth/logout` | Logout (current device) |
| | POST | `/api/auth/logout-all` | Logout all devices |
| | POST | `/api/auth/forgot-password` | Password reset request |
| | POST | `/api/auth/reset-password` | Reset password |

**Response Format:**
```json
{
  "success": true,
  "data": { ... },
  "pagination": { "current_page": 1, "per_page": 15, ... }
}
```

---

## Multi-Language Support

API supports Vietnamese (vi, default) and English (en) via `Accept-Language` header.

**Usage:**
```
Accept-Language: vi
```

**Postman Environments:**
- `postman_environment_vietnamese.json`
- `postman_environment_english.json`

**Backend:**
- Language files: `lang/en/`, `lang/vi/`
- Middleware: `SetLocale` auto-detects locale
- Usage: `__('categories.created_success')`

---

## Git Flow Workflow

### Branch Structure

| Branch | Purpose | Example |
|--------|---------|---------|
| `main` | Production-ready code | - |
| `dev` | Main development branch | - |
| `feature/*` | New features | `feature/xac-thuc` |
| `bugfix/*` | Bug fixes from dev | `bugfix/validate` |
| `hotfix/*` | Emergency fixes from main | `hotfix/loi-bao-mat` |
| `release/*` | Prepare for production | `release/v1.0.0` |

### Commit Message Format

```
<type>(<scope>): <mô tả ngắn gọn>
```

**Types:** feat, fix, docs, style, refactor, test, chore, perf, ci, revert, hotfix

**Examples:**
```bash
feat(auth): thêm chức năng xác thực JWT
fix(user): sửa lỗi tạo UUID trong model User
docs(readme): cập nhật hướng dẫn cài đặt Docker
refactor(service): tái cấu trúc UserService
test(user): thêm unit test cho UserService
```

### Workflow Diagram

```
main (production)
  │◄─── hotfix/*
  ├──► release/* ──────┐
  │                     ▼
dev ◄────────────────merge
  │◄─── feature/*
  │◄─── bugfix/*
```

---

## Docker Setup

### Quick Start

```bash
# 1. Setup
cp .env.example .env
php artisan key:generate

# 2. Create Docker resources (one-time)
docker network create balance_flow_network
docker volume create balance_flow_postgres_data

# 3. Start containers
cp compose-dev.yml compose.override.yml
docker compose build
docker compose up -d

# 4. Install dependencies & migrate
docker compose exec app composer install
docker compose exec app npm install
docker compose exec app php artisan migrate

# Access: http://localhost:8080
```

### Common Commands

```bash
docker compose up -d              # Start
docker compose down               # Stop
docker compose logs -f app        # View logs
docker compose exec app bash     # Shell access
docker compose exec app supervisorctl status  # Check workers
```

---

## Development Commands

```bash
# Setup
composer setup                    # Full setup

# Development
composer dev                      # Run all services (server, queue, pail, vite)
php artisan serve                 # Dev server only

# Testing
./run-tests.sh                    # Recommended (isolated environment)
composer test                     # Alternative

# Code Quality
./vendor/bin/pint                 # Format code
./vendor/bin/pint --test          # Check only

# Database
php artisan migrate               # Run migrations
php artisan migrate:fresh         # Fresh migrations
php artisan db:seed              # Run seeders
```

---

## Architecture & Design Patterns

### Repository & Service Pattern

```
HTTP Request → Controller → Service → Repository → Model → Database
```

**Benefits:** Separation of concerns, testability, maintainability, DRY principle

### Project Structure

```
app/
├── Http/Controllers/       # HTTP layer
├── Services/              # Business logic
│   ├── Contracts/         # Interfaces
│   └── *.php             # Implementations
├── Repositories/          # Data access
│   ├── Contracts/
│   └── *.php
├── Adapters/             # External integrations
│   └── Auth/
├── Models/               # Eloquent models
└── Providers/            # Service providers
```

### Database Best Practices

**UUID v7 Primary Keys** (REQUIRED for main entities):

```php
// Migration
$table->uuid('id')->primary();
$table->foreignUuid('user_id')->constrained();

// Model
use HasUuids;
```

**Why UUID v7:**
- Time-ordered, optimized for indexing
- Distributed system support
- Security (non-sequential)
- Better than v4 (maintains order)

### Adapter Pattern

**Example: Auth Adapter**

```php
// Interface
interface AuthAdapterInterface {
    public function generateToken(User $user, string $tokenName): string;
    public function verifyCredentials(array $credentials): bool;
}

// Implementation
class SanctumAuthAdapter implements AuthAdapterInterface { ... }

// Service usage
class AuthService {
    public function __construct(protected AuthAdapterInterface $authAdapter) {}
}

// Binding in ServiceLayerProvider
$this->app->bind(AuthAdapterInterface::class, SanctumAuthAdapter::class);
```

**Common Adapters:** Payment (Stripe, PayPal), Storage (S3, Local), Cache (Redis, File)

---

## Repository & Service Pattern Guide

### Base Repository Methods

| Method | Parameters | Description |
|--------|------------|-------------|
| `all($columns, $relations)` | `['*']`, `[]` | Get all |
| `find($id, $columns, $relations)` | ID, columns, relations | Find by ID |
| `findOrFail($id, ...)` | ... | Find or throw |
| `create($data)` | Array | Create |
| `update($id, $data)` | ID, array | Update |
| `delete($id)` | ID | Delete |
| `findBy($criteria, ...)` | Criteria, ... | Find by criteria |
| `paginate($perPage, ...)` | 15, ... | Paginated |

**All methods support column selection and eager loading:**
```php
$repository->all(['id', 'name'], ['posts', 'profile']);
$repository->find(1, ['*'], ['posts.comments']);
$repository->paginate(20, ['id', 'name'], ['posts']);
```

---

## Creating New Features

### 7-Step Checklist

1. **Create Repository Interface** (`app/Repositories/Contracts/PostRepositoryInterface.php`)
```php
interface PostRepositoryInterface extends BaseRepositoryInterface {
    public function findBySlug(string $slug, array $columns = ['*'], array $relations = []): ?Post;
}
```

2. **Create Repository** (`app/Repositories/PostRepository.php`)
```php
class PostRepository extends BaseRepository implements PostRepositoryInterface {
    public function __construct(Post $model) { parent::__construct($model); }
    public function findBySlug(...) { ... }
}
```

3. **Create Service Interface** (`app/Services/Contracts/PostServiceInterface.php`)
```php
interface PostServiceInterface extends BaseServiceInterface {
    public function getPostBySlug(string $slug): ?Post;
}
```

4. **Create Service** (`app/Services/PostService.php`)
```php
class PostService extends BaseService implements PostServiceInterface {
    public function __construct(protected PostRepositoryInterface $repository) { ... }
}
```

5. **Register Repository Binding** (`app/Providers/RepositoryServiceProvider.php`)
```php
$this->app->bind(PostRepositoryInterface::class, PostRepository::class);
```

6. **Register Service Binding** (`app/Providers/ServiceLayerProvider.php`)
```php
$this->app->bind(PostServiceInterface::class, PostService::class);
```

7. **Create Controller** (`app/Http/Controllers/PostController.php`)
```php
class PostController extends Controller {
    public function __construct(protected PostServiceInterface $postService) {}
}
```

---

## Usage Examples

### Column Selection + Eager Loading

```php
// Get only needed columns with relationships
$users = $repository->all(['id', 'name', 'email'], ['posts', 'profile']);

// Nested relationships with column selection
$user = $repository->find(1, ['id', 'name'], ['posts:id,title,user_id', 'posts.comments']);

// Dynamic API
$relations = explode(',', $request->query('with', ''));
$fields = explode(',', $request->query('fields', '*'));
$users = $repository->paginate($request->query('per_page', 15), $fields, $relations);
```

### Service with Business Logic

```php
public function getUserDashboard(int $userId): array {
    $user = $this->userRepository->find($userId, ['id', 'name'], [
        'posts' => fn($q) => $q->where('status', 'published')->latest()->limit(5)
    ]);
    return ['user' => $user, 'total_posts' => $user->posts->count()];
}
```

---

## Testing Strategy

- **Unit tests** (`tests/Unit/`): Test individual classes in isolation
- **Feature tests** (`tests/Feature/`): Test HTTP endpoints end-to-end
- Isolated PostgreSQL test database (`balance_flow_test` in tmpfs)
- Use `run-tests.sh` for automated setup/cleanup

### Example Tests

```php
// Repository Test
public function test_can_find_with_relationships() {
    $user = User::factory()->create();
    $found = $this->repository->find($user->id, ['*'], ['posts']);
    $this->assertTrue($found->relationLoaded('posts'));
}

// Service Test with Mock
public function test_create_user_hashes_password() {
    $mockRepo = $this->mock(UserRepositoryInterface::class);
    $mockRepo->shouldReceive('create')->once()->andReturn(new User());
    $service = new UserService($mockRepo);
    $service->createUser(['password' => 'plain']);
}

// Feature Test
public function test_can_create_user() {
    $response = $this->postJson('/api/users', ['name' => 'John', 'email' => 'john@example.com', 'password' => 'password']);
    $response->assertStatus(201);
    $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
}
```

---

## Best Practices

### ✅ DO

- Select only needed columns: `$repo->all(['id', 'name'])`
- Use eager loading: `$repo->all(['*'], ['posts'])`
- Put business logic in services, database queries in repositories
- Use pagination for large datasets
- Inject interfaces: `__construct(UserServiceInterface $service)`
- Write tests for repositories and services

### ❌ DON'T

- Don't load all columns when you only need a few
- Don't put database queries in controllers
- Don't forget eager loading (causes N+1)
- Don't bypass repository layer in services
- Don't put business logic in repositories

---

## Development Roadmap

### ✅ Completed (100%)

| Module | Endpoints | Files | Tests | Postman |
|--------|-----------|-------|-------|---------|
| **Categories** | 7 endpoints | Repository, Service, Controller, Tests | 16 tests | ✅ |
| **Authentication** | 11 endpoints | AuthService, AuthController, 6 Requests | 33 tests | ✅ |

**Categories Endpoints:**
```
GET/POST /api/categories
GET/PUT/DELETE /api/categories/{id}
GET /api/categories/{id}/subcategories
```

**Auth Endpoints:**
```
POST /api/auth/register, /api/auth/login
GET /api/auth/me
PUT /api/auth/profile, /api/auth/password
POST /api/auth/logout, /api/auth/logout-all
POST /api/auth/forgot-password, /api/auth/reset-password
POST /api/auth/verify-email, /api/auth/resend-verification-email
```

### 🔲 TODO

**Phase 1 (Core):**
3. Account Types
4. Accounts
5. Transactions

**Phase 2 (Advanced):**
6. Recurring Transactions
7. Budgets
8. Goals

**Phase 3 (Nice to Have):**
9. Goal Contributions
10. Notifications
11. Analytics & Reports

**Implementation Checklist for Each Module:**
- [ ] Create migration with UUID v7 primary keys
- [ ] Create Model with `HasUuids` trait
- [ ] Create Repository Interface + Implementation
- [ ] Create Service Interface + Implementation
- [ ] Register bindings in Providers
- [ ] Create Controller with Request validation
- [ ] Write Factory + Tests (feature & unit)
- [ ] Add endpoints to Postman collection

---

## Key Files

- **composer.json**: PHP dependencies and scripts
- **package.json**: Node dependencies
- **.env.example**: Environment template
- **phpunit.xml**: Test configuration
- **run-tests.sh**: Test runner script
- **postman_collection.json**: API collection

---

**For complete examples, see `app/Repositories/UserRepository.php`, `app/Services/UserService.php`, `app/Http/Controllers/UserController.php`**

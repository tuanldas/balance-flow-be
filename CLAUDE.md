# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Table of Contents

- [Project Overview](#project-overview)
- [Docker Setup](#docker-setup)
- [Development Commands](#development-commands)
- [Architecture & Design Patterns](#architecture--design-patterns)
- [Repository & Service Pattern Guide](#repository--service-pattern-guide)
- [Creating New Features](#creating-new-features)
- [Usage Examples](#usage-examples)
- [Testing Strategy](#testing-strategy)
- [Best Practices](#best-practices)
- [Quick Reference](#quick-reference)

---

## Project Overview

This is a Laravel 12 backend application using PHP 8.2+ with PostgreSQL database. The project uses Docker for containerization and Vite for frontend asset building with Tailwind CSS v4.

**Key Technologies:**
- Laravel 12
- PHP 8.2-FPM
- PostgreSQL 16
- Docker & Docker Compose
- Nginx
- Supervisor (process management)
- Vite + Tailwind CSS v4
- PHPUnit for testing
- Laravel Pint for code formatting

**Docker Services:**
- **app**: PHP 8.2-FPM with Supervisor managing PHP-FPM, Queue Workers, and Scheduler
- **nginx**: Nginx web server
- **db**: PostgreSQL 16 database

---

## Docker Setup

### Prerequisites

- Docker & Docker Compose installed
- Git

### Quick Start

```bash
# 1. Clone repository
git clone <repository-url>
cd balance-flow-be

# 2. Setup environment
cp .env.example .env
php artisan key:generate  # Or manually set APP_KEY

# 3. Create external Docker resources (one-time setup)
docker network create balance_flow_network
docker volume create balance_flow_postgres_data

# For production, also create publish network:
docker network create npm_proxy

# 4. Copy environment-specific compose file
cp compose-dev.yml compose.override.yml    # Development
# OR
cp compose-prod.yml compose.override.yml   # Production

# 5. Build and start containers
docker compose build
docker compose up -d

# 6. Install dependencies (development only, production builds include them)
docker compose exec app composer install
docker compose exec app npm install

# 7. Run migrations
docker compose exec app php artisan migrate

# 8. Access application
# Development: http://localhost:8080
# Production: Configure domain in Nginx Proxy Manager
```

### Docker Environment Variables

Configure in `.env`:

```env
# Docker Configuration
DOCKER_VOLUME_POSTGRES=balance_flow_postgres_data
DOCKER_NETWORK_PUBLISH=npm_proxy
```

### Docker Commands

```bash
# Start containers
docker compose up -d

# Stop containers (keeps data)
docker compose down

# View logs
docker compose logs -f
docker compose logs -f app    # App only

# Execute commands in containers
docker compose exec app bash
docker compose exec app php artisan migrate
docker compose exec app composer install

# Supervisor management
docker compose exec app supervisorctl status
docker compose exec app supervisorctl restart laravel-worker:*

# Database access
docker compose exec db psql -U postgres -d balance_flow

# Rebuild containers
docker compose build --no-cache
docker compose up -d
```

### Docker Environments

#### Development (compose-dev.yml)
- Exposed ports: 8080 (nginx), 5432 (postgres)
- Code mounted from host (live reload)
- Debug enabled
- Uses `development` build target

#### Production (compose-prod.yml)
- No exposed ports (managed by Nginx Proxy Manager)
- Code baked into image
- Debug disabled
- Uses `production` build target
- Connected to `npm_proxy` network

#### Testing (compose-testing.yml)
- Exposed port: 8081
- Separate test database (in-memory)
- Isolated environment

### Dockerfile Multi-Stage Builds

The Dockerfile uses multi-stage builds:

- **base**: Common dependencies (PHP, Supervisor, etc.)
- **development**: For local development (no assets built)
- **production-build**: Builds assets with npm
- **production**: Final optimized image with built assets

Specify target in compose files:
```yaml
build:
  target: development  # or production
```

---

## Development Commands

### Initial Setup
```bash
composer setup
```
This runs the complete setup: installs dependencies, copies .env.example to .env (if needed), generates app key, runs migrations, installs npm packages, and builds assets.

### Development Server
```bash
composer dev
```
Runs a full development environment with:
- PHP development server (http://localhost:8000)
- Queue worker (listening for jobs)
- Laravel Pail (real-time log viewer)
- Vite dev server (hot module reloading)

All services run concurrently and are killed together when stopped.

Alternatively, run services individually:
```bash
php artisan serve              # Development server only
npm run dev                    # Vite dev server only
php artisan queue:listen       # Queue worker only
php artisan pail              # Log viewer only
```

### Testing
```bash
composer test                  # Run all tests
./vendor/bin/phpunit          # Direct PHPUnit invocation
./vendor/bin/phpunit --filter TestName  # Run specific test
./vendor/bin/phpunit tests/Unit         # Run only unit tests
./vendor/bin/phpunit tests/Feature      # Run only feature tests
```

Tests use an in-memory SQLite database (DB_DATABASE=testing) configured in phpunit.xml.

### Code Quality
```bash
./vendor/bin/pint              # Format code (Laravel Pint - opinionated PHP CS Fixer)
./vendor/bin/pint --test       # Check formatting without fixing
```

### Build
```bash
npm run build                  # Build frontend assets for production
```

### Database
```bash
php artisan migrate            # Run migrations
php artisan migrate:fresh      # Drop all tables and re-run migrations
php artisan migrate:rollback   # Rollback last batch
php artisan db:seed           # Run seeders
```

**Note:** When using Docker, run these commands inside the app container:
```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

Default database is PostgreSQL running in Docker container.

### Other Useful Commands
```bash
php artisan tinker            # Interactive REPL
php artisan route:list        # List all registered routes
php artisan config:cache      # Cache configuration
php artisan config:clear      # Clear configuration cache
php artisan cache:clear       # Clear application cache
php artisan optimize          # Cache config, routes, and views
php artisan optimize:clear    # Clear all cached files
```

---

## Architecture & Design Patterns

This project implements the **Repository and Service Pattern** for clean, maintainable, and testable code architecture.

### Design Pattern Overview

```
HTTP Request
     ↓
Controller (validates, returns responses)
     ↓
Service Interface (defines business methods)
     ↓
Service Implementation (business logic, transactions)
     ↓
Repository Interface (defines data access methods)
     ↓
Repository Implementation (queries, eager loading)
     ↓
Eloquent Model
     ↓
Database
```

### Benefits

1. **Separation of Concerns**: Business logic in services, data access in repositories
2. **Testability**: Easy to mock interfaces for unit testing
3. **Maintainability**: Changes isolated to specific layers
4. **Scalability**: Consistent pattern across entire application
5. **Performance**: Built-in column selection and eager loading
6. **DRY Principle**: Reusable base classes eliminate code duplication
7. **Interface-based**: Easy dependency injection and swapping implementations

### Project Structure

```
app/
├── Http/Controllers/              # HTTP layer (validation, responses)
│   └── UserController.php        # Example: inject services
├── Services/                      # Business logic layer
│   ├── Contracts/                # Service interfaces
│   │   ├── BaseServiceInterface.php
│   │   └── UserServiceInterface.php
│   ├── BaseService.php           # Abstract base with common operations
│   └── UserService.php           # Specific service implementation
├── Repositories/                  # Data access layer
│   ├── Contracts/                # Repository interfaces
│   │   ├── BaseRepositoryInterface.php
│   │   └── UserRepositoryInterface.php
│   ├── BaseRepository.php        # Abstract base with CRUD operations
│   └── UserRepository.php        # Specific repository implementation
├── Models/                        # Eloquent ORM models
│   └── User.php
├── Providers/                     # Service providers
│   ├── AppServiceProvider.php
│   ├── RepositoryServiceProvider.php  # Repository bindings
│   └── ServiceLayerProvider.php       # Service bindings
routes/
├── web.php                       # Web routes
└── console.php                   # Console commands
database/
├── migrations/                   # Database schema
├── factories/                    # Model factories
└── seeders/                      # Database seeders
tests/
├── Feature/                      # Feature tests (full HTTP)
└── Unit/                         # Unit tests (isolated classes)
resources/
├── views/                        # Blade templates
├── js/                          # JavaScript
└── css/                         # CSS (Tailwind)
public/                           # Web root
config/                           # Configuration files
```

### Database Configuration

Default setup uses PostgreSQL in Docker. Configuration in `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=db                    # Docker service name
DB_PORT=5432
DB_DATABASE=balance_flow
DB_USERNAME=postgres
DB_PASSWORD=postgres
```

**Docker Volume:**
- Database data is stored in external volume: `balance_flow_postgres_data`
- Data persists even after `docker compose down`
- To reset database: `docker volume rm balance_flow_postgres_data`

**Access PostgreSQL:**
```bash
# Via docker compose
docker compose exec db psql -U postgres -d balance_flow

# Via host (if port exposed in dev)
psql -h localhost -p 5432 -U postgres -d balance_flow
```

### Queue System

Default queue connection is `database` (stored in database tables).

**With Docker:**
Queue workers are automatically managed by Supervisor in the app container. Check status:
```bash
docker compose exec app supervisorctl status laravel-worker:*
docker compose exec app supervisorctl restart laravel-worker:*
```

**Without Docker:**
Run queue worker manually:
```bash
php artisan queue:work
php artisan queue:listen --tries=1
```

### Frontend Assets

Vite is configured to compile:
- **resources/css/app.css** → Tailwind CSS v4
- **resources/js/app.js** → JavaScript

Built assets are stored in `public/build/` and referenced in Blade templates using `@vite()` directive.

---

## Repository & Service Pattern Guide

### Base Repository Methods

All retrieval methods support **column selection** and **eager loading relationships**:

| Method | Parameters | Description |
|--------|------------|-------------|
| `all($columns, $relations)` | `['*']`, `[]` | Get all records |
| `find($id, $columns, $relations)` | `$id`, `['*']`, `[]` | Find by ID |
| `findOrFail($id, $columns, $relations)` | `$id`, `['*']`, `[]` | Find or throw exception |
| `create($data)` | `$data` | Create new record |
| `update($id, $data)` | `$id`, `$data` | Update existing record |
| `delete($id)` | `$id` | Delete record |
| `findBy($criteria, $columns, $relations)` | `$criteria`, `['*']`, `[]` | Find by criteria |
| `findOneBy($criteria, $columns, $relations)` | `$criteria`, `['*']`, `[]` | Find one by criteria |
| `paginate($perPage, $columns, $relations)` | `15`, `['*']`, `[]` | Paginated results |
| `count()` | - | Count all records |

**Parameters:**
- `$columns`: Array of column names to select (default: `['*']` for all)
- `$relations`: Array of relationships to eager load (default: `[]` for none)

### Base Service Methods

Services delegate to repositories and add business logic:

- `getAll()` - Get all records
- `findById($id)` - Find by ID
- `create($data)` - Create new record
- `update($id, $data)` - Update existing record
- `delete($id)` - Delete record
- `getPaginated($perPage)` - Get paginated results

### Example: User Implementation

The project includes a complete User implementation as reference:

**Files:**
- `app/Repositories/Contracts/UserRepositoryInterface.php`
- `app/Repositories/UserRepository.php`
- `app/Services/Contracts/UserServiceInterface.php`
- `app/Services/UserService.php`
- `app/Http/Controllers/UserController.php`

**Controller Example:**
```php
<?php

namespace App\Http\Controllers;

use App\Services\Contracts\UserServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected UserServiceInterface $userService
    ) {}

    public function index(): JsonResponse
    {
        // Get users with specific columns and relationships
        $users = $this->userService->repository->paginate(
            15,
            ['id', 'name', 'email'],
            ['posts']
        );

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = $this->userService->createUser($validated);

        return response()->json($user, 201);
    }
}
```

---

## Creating New Features

### Step-by-Step Checklist

Follow these steps to create a new feature (example: Post):

#### Step 1: Create Repository Interface

**File:** `app/Repositories/Contracts/PostRepositoryInterface.php`

```php
<?php

namespace App\Repositories\Contracts;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

interface PostRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find post by slug
     */
    public function findBySlug(
        string $slug,
        array $columns = ['*'],
        array $relations = []
    ): ?Post;

    /**
     * Get published posts
     */
    public function getPublished(
        array $columns = ['*'],
        array $relations = []
    ): Collection;
}
```

#### Step 2: Create Repository Implementation

**File:** `app/Repositories/PostRepository.php`

```php
<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function findBySlug(
        string $slug,
        array $columns = ['*'],
        array $relations = []
    ): ?Post {
        $query = $this->model->select($columns)->where('slug', $slug);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }

    public function getPublished(
        array $columns = ['*'],
        array $relations = []
    ): Collection {
        $query = $this->model->select($columns)
            ->where('status', 'published')
            ->where('published_at', '<=', now());

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->get();
    }
}
```

#### Step 3: Create Service Interface

**File:** `app/Services/Contracts/PostServiceInterface.php`

```php
<?php

namespace App\Services\Contracts;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;

interface PostServiceInterface extends BaseServiceInterface
{
    public function getPostBySlug(string $slug): ?Post;
    public function getPublishedPosts(): Collection;
    public function publishPost(int $id): bool;
}
```

#### Step 4: Create Service Implementation

**File:** `app/Services/PostService.php`

```php
<?php

namespace App\Services;

use App\Models\Post;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Services\Contracts\PostServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class PostService extends BaseService implements PostServiceInterface
{
    protected PostRepositoryInterface $postRepository;

    public function __construct(PostRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->postRepository = $repository;
    }

    public function getPostBySlug(string $slug): ?Post
    {
        return $this->postRepository->findBySlug(
            $slug,
            ['*'],
            ['author', 'comments']
        );
    }

    public function getPublishedPosts(): Collection
    {
        return $this->postRepository->getPublished(
            ['id', 'title', 'slug', 'excerpt', 'published_at'],
            ['author:id,name,avatar']
        );
    }

    public function publishPost(int $id): bool
    {
        return $this->postRepository->update($id, [
            'status' => 'published',
            'published_at' => now()
        ]);
    }
}
```

#### Step 5: Register Repository Binding

**File:** `app/Providers/RepositoryServiceProvider.php`

```php
public function register(): void
{
    // Existing bindings...

    $this->app->bind(
        \App\Repositories\Contracts\PostRepositoryInterface::class,
        \App\Repositories\PostRepository::class
    );
}
```

#### Step 6: Register Service Binding

**File:** `app/Providers/ServiceLayerProvider.php`

```php
public function register(): void
{
    // Existing bindings...

    $this->app->bind(
        \App\Services\Contracts\PostServiceInterface::class,
        \App\Services\PostService::class
    );
}
```

#### Step 7: Create Controller

**File:** `app/Http/Controllers/PostController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Services\Contracts\PostServiceInterface;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function __construct(
        protected PostServiceInterface $postService
    ) {}

    public function index(): JsonResponse
    {
        $posts = $this->postService->getPublishedPosts();
        return response()->json($posts);
    }

    public function show(string $slug): JsonResponse
    {
        $post = $this->postService->getPostBySlug($slug);

        if (!$post) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($post);
    }

    public function publish(int $id): JsonResponse
    {
        $this->postService->publishPost($id);
        return response()->json(['message' => 'Published successfully']);
    }
}
```

---

## Usage Examples

### Basic Usage

```php
// Get all records with default settings (all columns, no relationships)
$users = $repository->all();

// Find by ID
$user = $repository->find(1);

// Find by criteria
$activeUsers = $repository->findBy(['status' => 'active']);
```

### Column Selection

Select only needed columns to optimize performance:

```php
// Get only specific columns
$users = $repository->all(['id', 'name', 'email']);

// Find with specific columns
$user = $repository->find(1, ['id', 'name', 'email']);

// Paginate with specific columns
$users = $repository->paginate(15, ['id', 'name', 'email']);
```

### Eager Loading Relationships

Prevent N+1 query problems:

```php
// Load single relationship
$users = $repository->all(['*'], ['posts']);

// Load multiple relationships
$user = $repository->find(1, ['*'], ['posts', 'profile', 'roles']);

// Nested relationships
$users = $repository->all(['*'], ['posts.comments', 'profile']);

// Select specific columns for relationships
$users = $repository->all(['*'], ['posts:id,title,user_id']);
```

### Combined: Columns + Relationships

```php
$users = $repository->all(
    ['id', 'name', 'email'],        // Only these columns
    ['posts', 'profile']            // Eager load these relationships
);

$user = $repository->find(
    1,
    ['id', 'name', 'email', 'avatar'],  // Specific columns
    ['posts.comments', 'roles']          // Nested relationships
);
```

### Dynamic API with Query Parameters

```php
public function index(Request $request): JsonResponse
{
    // Example: /api/users?with=posts,profile&fields=id,name,email&per_page=20

    $relations = $request->query('with')
        ? explode(',', $request->query('with'))
        : [];

    $fields = $request->query('fields')
        ? explode(',', $request->query('fields'))
        : ['*'];

    $perPage = $request->query('per_page', 15);

    $users = $this->userRepository->paginate($perPage, $fields, $relations);

    return response()->json($users);
}
```

### Service with Business Logic

```php
public function getUserDashboard(int $userId): array
{
    $user = $this->userRepository->find(
        $userId,
        ['id', 'name', 'email', 'avatar'],
        ['posts' => function($query) {
            $query->where('status', 'published')
                  ->latest()
                  ->limit(5);
        }]
    );

    return [
        'user' => $user,
        'total_posts' => $user->posts->count(),
        'recent_posts' => $user->posts,
    ];
}
```

### Transactions

```php
use Illuminate\Support\Facades\DB;

public function createUserWithProfile(array $userData, array $profileData): User
{
    return DB::transaction(function () use ($userData, $profileData) {
        $user = $this->userRepository->create($userData);

        $profileData['user_id'] = $user->id;
        $this->profileRepository->create($profileData);

        return $this->userRepository->find($user->id, ['*'], ['profile']);
    });
}
```

---

## Testing Strategy

- **Unit tests** (tests/Unit/): Test individual classes and methods in isolation
- **Feature tests** (tests/Feature/): Test HTTP endpoints and application features end-to-end
- Tests run with separate environment: in-memory SQLite, array cache, sync queues
- Use factories for generating test data
- Database is automatically reset between tests

### Testing with Repository & Service Pattern

The pattern makes testing easier through interface mocking.

#### Repository Testing

```php
use Tests\TestCase;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserRepositoryInterface::class);
    }

    public function test_can_find_user_with_relationships()
    {
        $user = User::factory()->create();

        $found = $this->repository->find($user->id, ['*'], ['posts']);

        $this->assertNotNull($found);
        $this->assertTrue($found->relationLoaded('posts'));
    }

    public function test_can_select_specific_columns()
    {
        $user = User::factory()->create();

        $found = $this->repository->find($user->id, ['id', 'name', 'email']);

        $this->assertEquals($user->id, $found->id);
        $this->assertEquals($user->name, $found->name);
    }

    public function test_can_paginate_results()
    {
        User::factory()->count(30)->create();

        $paginated = $this->repository->paginate(10);

        $this->assertEquals(10, $paginated->count());
        $this->assertEquals(30, $paginated->total());
    }
}
```

#### Service Testing with Mocks

```php
use Tests\TestCase;
use App\Models\User;
use App\Services\UserService;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserServiceTest extends TestCase
{
    public function test_create_user_hashes_password()
    {
        $mockRepo = $this->mock(UserRepositoryInterface::class);

        $mockRepo->shouldReceive('create')
            ->once()
            ->with(\Mockery::on(function ($data) {
                return isset($data['password']) &&
                       $data['password'] !== 'plain-password';
            }))
            ->andReturn(new User());

        $service = new UserService($mockRepo);

        $service->createUser([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'plain-password'
        ]);
    }

    public function test_get_user_by_email()
    {
        $mockRepo = $this->mock(UserRepositoryInterface::class);
        $expectedUser = new User(['email' => 'test@example.com']);

        $mockRepo->shouldReceive('findByEmail')
            ->once()
            ->with('test@example.com', ['*'], [])
            ->andReturn($expectedUser);

        $service = new UserService($mockRepo);
        $result = $service->getUserByEmail('test@example.com');

        $this->assertEquals($expectedUser, $result);
    }
}
```

#### Controller/Feature Testing

```php
use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_users()
    {
        User::factory()->count(3)->create();

        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function test_can_create_user()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ];

        $response = $this->postJson('/api/users', $data);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'name', 'email']);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com'
        ]);
    }
}
```

---

## Best Practices

### Repository & Service Pattern

#### ✅ DO

- **Always select only needed columns**
  ```php
  $users = $repository->all(['id', 'name', 'email']);
  ```

- **Use eager loading to prevent N+1 queries**
  ```php
  $users = $repository->all(['*'], ['posts', 'profile']);
  ```

- **Put business logic in services, not controllers**
  ```php
  // Service
  public function publishPost(int $id): bool {
      // Business logic here
  }
  ```

- **Put database queries in repositories, not services**
  ```php
  // Repository
  public function findActive(): Collection {
      return $this->model->where('active', true)->get();
  }
  ```

- **Use pagination for large datasets**
  ```php
  $users = $repository->paginate(20);
  ```

- **Write tests for repositories and services**

- **Use dependency injection with interfaces**
  ```php
  public function __construct(UserServiceInterface $service) {}
  ```

- **Follow the established pattern for new features**

#### ❌ DON'T

- **Don't load all columns when you only need a few**
  ```php
  // Bad
  $users = $repository->all(); // then only use ->name
  ```

- **Don't put database queries directly in controllers**
  ```php
  // Bad
  public function index() {
      $users = User::where('active', true)->get(); // Use repository!
  }
  ```

- **Don't forget to register bindings in service providers**

- **Don't skip eager loading when accessing relationships**
  ```php
  // Bad - causes N+1
  $users = $repository->all();
  foreach ($users as $user) {
      echo $user->posts->count(); // Query on each iteration!
  }
  ```

- **Don't bypass the repository layer in services**
  ```php
  // Bad
  public function getUsers() {
      return User::all(); // Use repository!
  }
  ```

- **Don't put business logic in repositories**
  ```php
  // Bad - in repository
  public function publishPost($id) {
      // Send notification, update cache, etc. // This is business logic!
  }
  ```

### Code Organization

#### Controllers
- Keep controllers thin - delegate to services
- Only handle HTTP concerns (validation, responses)
- Inject service interfaces, not implementations
- Return appropriate HTTP status codes

```php
class UserController extends Controller
{
    public function __construct(protected UserServiceInterface $service) {}

    public function index(): JsonResponse
    {
        return response()->json($this->service->getAll());
    }
}
```

#### Services
- Contain business logic and workflows
- Orchestrate multiple repositories if needed
- Handle transactions when updating multiple models
- Validate business rules
- Transform data if needed

```php
class PostService extends BaseService
{
    public function publishPost(int $id): bool
    {
        // Business logic
        $post = $this->repository->find($id);

        if ($post->status === 'published') {
            throw new \Exception('Already published');
        }

        // Update and notify
        return DB::transaction(function () use ($id) {
            $result = $this->repository->update($id, [
                'status' => 'published',
                'published_at' => now()
            ]);

            // Trigger events, notifications, etc.

            return $result;
        });
    }
}
```

#### Repositories
- Handle data access only
- No business logic
- Return models or collections
- Support column selection and eager loading
- Keep queries optimized

```php
class PostRepository extends BaseRepository
{
    public function findBySlug(
        string $slug,
        array $columns = ['*'],
        array $relations = []
    ): ?Post {
        $query = $this->model->select($columns)->where('slug', $slug);

        if (!empty($relations)) {
            $query->with($relations);
        }

        return $query->first();
    }
}
```

### Performance Optimization

#### Query Optimization

```php
// ❌ Bad - N+1 query problem
$users = $repository->all();
foreach ($users as $user) {
    echo $user->posts->count(); // Queries database each iteration
}

// ✅ Good - Eager loading
$users = $repository->all(['*'], ['posts']);
foreach ($users as $user) {
    echo $user->posts->count(); // No additional queries
}
```

#### Column Selection

```php
// ❌ Bad - Loads all columns including large text fields
$users = $repository->all();

// ✅ Good - Only needed columns
$users = $repository->all(['id', 'name', 'email']);
```

#### Pagination

```php
// ❌ Bad - Loads everything into memory
$allUsers = $repository->all();

// ✅ Good - Paginated results
$users = $repository->paginate(20);
```

#### Relationship Column Selection

```php
// ✅ Excellent - Select specific columns for relationships too
$users = $repository->all(
    ['id', 'name', 'email'],
    ['posts:id,user_id,title', 'profile:id,user_id,avatar']
);
```

---

## Quick Reference

### Common Commands

```bash
# Development
composer dev                       # Start all services
composer test                      # Run tests
./vendor/bin/pint                  # Format code

# Database
php artisan migrate                # Run migrations
php artisan migrate:fresh          # Fresh migrations
php artisan db:seed               # Run seeders

# Artisan
php artisan tinker                 # REPL
php artisan route:list            # List routes
php artisan optimize:clear        # Clear caches
```

### Base Repository Methods Quick Reference

```php
// All methods support: $columns = ['*'], $relations = []

$repository->all($columns, $relations);
$repository->find($id, $columns, $relations);
$repository->findOrFail($id, $columns, $relations);
$repository->create($data);
$repository->update($id, $data);
$repository->delete($id);
$repository->findBy($criteria, $columns, $relations);
$repository->findOneBy($criteria, $columns, $relations);
$repository->paginate($perPage, $columns, $relations);
$repository->count();
```

### File Templates

#### Repository Interface
```php
<?php
namespace App\Repositories\Contracts;

interface YourModelRepositoryInterface extends BaseRepositoryInterface
{
    public function customMethod(array $columns = ['*'], array $relations = []): Collection;
}
```

#### Repository Implementation
```php
<?php
namespace App\Repositories;

class YourModelRepository extends BaseRepository implements YourModelRepositoryInterface
{
    public function __construct(YourModel $model)
    {
        parent::__construct($model);
    }

    public function customMethod(array $columns = ['*'], array $relations = []): Collection
    {
        $query = $this->model->select($columns)->where('condition', 'value');
        if (!empty($relations)) $query->with($relations);
        return $query->get();
    }
}
```

#### Service Interface
```php
<?php
namespace App\Services\Contracts;

interface YourModelServiceInterface extends BaseServiceInterface
{
    public function customBusinessMethod(): mixed;
}
```

#### Service Implementation
```php
<?php
namespace App\Services;

class YourModelService extends BaseService implements YourModelServiceInterface
{
    public function __construct(protected YourModelRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    public function customBusinessMethod(): mixed
    {
        // Business logic here
        return $this->repository->customMethod();
    }
}
```

---

## Key Files

- **composer.json**: PHP dependencies and custom scripts
- **package.json**: Node dependencies and build scripts
- **.env.example**: Environment variable template
- **phpunit.xml**: PHPUnit test configuration
- **vite.config.js**: Vite build configuration
- **artisan**: CLI entry point for Laravel commands
- **bootstrap/providers.php**: Service provider registration

---

## Code Style

This project uses Laravel Pint for code formatting, which follows Laravel's opinionated style guide. Always run `./vendor/bin/pint` before committing to ensure consistent formatting.

### Formatting Rules
- PSR-12 compliant
- Laravel-specific conventions
- Automatic fixing of common issues
- Consistent across the entire codebase

```bash
./vendor/bin/pint              # Auto-fix all files
./vendor/bin/pint --test       # Check without fixing
./vendor/bin/pint path/to/file # Fix specific file
```

---

**For more information, refer to the example implementations in `app/Repositories/UserRepository.php`, `app/Services/UserService.php`, and `app/Http/Controllers/UserController.php`.**

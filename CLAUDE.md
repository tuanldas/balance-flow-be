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
- ✅ ALWAYS ask "Bạn có muốn tôi commit không?" before committing
- ✅ WAIT for explicit confirmation: "commit", "yes", "ok", etc.
- ✅ Show summary of changes and ASK before committing

**Why this rule exists:**
- User may want to review changes first
- User may want to modify commit message
- User may want to add more changes before committing
- User may want to test manually before committing

**Correct workflow:**
```
1. Complete the requested task
2. Run tests (if applicable)
3. Show summary of changes
4. ASK: "Bạn có muốn tôi commit những thay đổi này không?"
5. WAIT for user confirmation
6. Only then: git commit
```

**Example - CORRECT:**
```
Assistant: Tôi đã hoàn thành refactoring. Tất cả tests pass (14/14).

Changes:
- Created app/Adapters/Auth/
- Updated AuthService.php
- All tests passing

Bạn có muốn tôi commit những thay đổi này không?

User: commit
Assistant: [proceeds with git commit]
```

**Example - WRONG:**
```
Assistant: Tôi đã hoàn thành refactoring.
[automatically runs git commit without asking]  ❌ WRONG
```

---

## Table of Contents

- [⚠️ IMPORTANT RULES FOR CLAUDE CODE](#️-important-rules-for-claude-code)
- [Project Overview](#project-overview)
- [API Testing with Postman](#api-testing-with-postman)
- [Git Flow Workflow](#git-flow-workflow)
- [Docker Setup](#docker-setup)
- [Development Commands](#development-commands)
- [Architecture & Design Patterns](#architecture--design-patterns)
  - [Database Design Best Practices](#database-design-best-practices)
- [Repository & Service Pattern Guide](#repository--service-pattern-guide)
- [Creating New Features](#creating-new-features)
- [Usage Examples](#usage-examples)
- [Testing Strategy](#testing-strategy)
- [Best Practices](#best-practices)
- [Quick Reference](#quick-reference)
- [Development Roadmap](#development-roadmap)

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

## API Testing with Postman

A comprehensive Postman collection is available for testing all API endpoints.

### Postman Collection File

**Location:** `postman_collection.json` (root directory)

**What's Included:**
- All implemented API endpoints with sample requests
- Authentication setup with Bearer token
- Environment variables for easy configuration
- Automatic token and ID management
- Detailed descriptions and documentation
- Request/response examples
- Validation rules and constraints

### Import Instructions

1. **Open Postman**
   - Download from [postman.com](https://www.postman.com/downloads/) if not installed

2. **Import Collection**
   ```
   File → Import → Select postman_collection.json
   ```

3. **Configure Environment Variables**
   - `base_url`: Default is `http://localhost:8080` (Docker)
   - Change to `http://localhost:8000` for local PHP serve
   - `access_token`: Auto-populated after login
   - `category_id`: Auto-populated after creating a category

### Collection Structure

#### 1. Categories (✅ Fully Implemented)
```
GET    /api/categories              - List all categories (paginated)
GET    /api/categories?type=income  - Filter by type
GET    /api/categories?type=expense - Filter by type
POST   /api/categories              - Create new category
GET    /api/categories/{id}         - Get category details
PUT    /api/categories/{id}         - Update category
PATCH  /api/categories/{id}         - Partial update
DELETE /api/categories/{id}         - Delete category
GET    /api/categories/{id}/subcategories - Get subcategories
```

**Features:**
- Pagination with `per_page` parameter
- Filter by category type (income/expense)
- Support for parent-child relationships
- Automatic validation
- Vietnamese error messages

#### 2. Authentication (✅ Fully Implemented)
```
POST   /api/auth/register           - User registration
POST   /api/auth/login              - User login
GET    /api/auth/me                 - Get current user
PUT    /api/auth/profile            - Update user profile
PUT    /api/auth/password           - Change password
POST   /api/auth/logout             - User logout (current device)
POST   /api/auth/logout-all         - Logout from all devices
POST   /api/auth/forgot-password    - Password reset request
POST   /api/auth/reset-password     - Reset password with token
```

**Features:**
- Laravel Sanctum token-based authentication
- Token naming format: `email_dd/mm/yyyy` (e.g., `user@example.com_28/11/2025`)
- Auto-save access token after register/login
- Automatic validation with Vietnamese error messages
- Password hashing with bcrypt
- Multi-device logout support

**Public Endpoints (no auth required):**
- register, login, forgot-password, reset-password

**Protected Endpoints (require Bearer token):**
- me, profile, password, logout, logout-all

#### 3. Future Endpoints (🔲 TODO)
Placeholders included for:
- Account Types
- Accounts
- Transactions
- Recurring Transactions
- Budgets
- Goals
- Goal Contributions
- Notifications
- Reports & Analytics

### Using the Collection

#### Step 1: Authentication
1. **Register** or **Login** → Access token auto-saves to `{{access_token}}`
2. All subsequent requests automatically use this token
3. Token format: `email_dd/mm/yyyy` for easy tracking

#### Step 2: Testing Categories
1. **List Categories**: See all system + user categories
2. **Create Category**: Creates a new category (auto-saves ID)
3. **Get Details**: View specific category using saved ID
4. **Update Category**: Modify category name, icon, color
5. **Delete Category**: Remove user-created categories
6. **Create Subcategory**: Create child category under parent

#### Step 3: Query Parameters
Supported parameters:
- `per_page`: Number of items per page (default: 15)
- `type`: Filter by income or expense

Example:
```
GET /api/categories?type=income&per_page=20
```

### Automated Scripts

The collection includes JavaScript test scripts for automation:

**Login Request:**
```javascript
// Auto-saves access token to collection variable
if (pm.response.code === 200) {
    const response = pm.response.json();
    pm.collectionVariables.set('access_token', response.access_token);
}
```

**Create Category Request:**
```javascript
// Auto-saves category ID for subsequent requests
if (pm.response.code === 201) {
    const response = pm.response.json();
    pm.collectionVariables.set('category_id', response.data.id);
}
```

### Response Format

All API responses follow a consistent format:

**Success Response:**
```json
{
    "success": true,
    "data": { ... },
    "pagination": {
        "current_page": 1,
        "per_page": 15,
        "total": 20,
        "last_page": 2,
        "from": 1,
        "to": 15
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Error message in Vietnamese"
}
```

### Tips for Testing

1. **Start with Categories**: Fully implemented and ready to test
2. **Create Test Data**: Use "Create Category" to populate test categories
3. **Test Validation**: Try invalid data to see validation messages
4. **Test Permissions**: Try updating/deleting system categories (should fail)
5. **Test Relationships**: Create subcategories under parent categories
6. **Test Pagination**: Use different `per_page` values
7. **Test Filtering**: Filter by income/expense types

### Environment Switching

For different environments, update `base_url`:

```
Development (Docker):  http://localhost:8080
Development (Local):   http://localhost:8000
Staging:               https://staging.example.com
Production:            https://api.example.com
```

### Testing Workflow Example

```bash
1. Import postman_collection.json into Postman
2. Ensure API is running (docker compose up -d)
3. Test "List All Categories" - should see system categories
4. Test "Create Category" - creates new user category
5. Test "Get Category Details" - uses saved category_id
6. Test "Update Category" - modifies the category
7. Test "Create Subcategory" - adds child category
8. Test "Get Subcategories" - lists children
9. Test "Delete Category" - removes the category
```

### Common Issues & Solutions

**401 Unauthorized:**
- Ensure access_token is set in collection variables
- Check token expiration
- Re-login to get fresh token

**404 Not Found:**
- Verify API is running on correct port
- Check base_url in collection variables
- Ensure route exists in routes/api.php

**422 Validation Error:**
- Check request body format
- Verify required fields are provided
- Check field types and constraints

**500 Internal Server Error:**
- Check Laravel logs: `docker compose logs -f app`
- Verify database connection
- Check for migration errors

---

## Git Flow Workflow

Dự án sử dụng Git Flow với nhánh phát triển tên là `dev` và commit message được viết bằng tiếng Việt.

### Cấu Trúc Nhánh

**Nhánh Chính:**
- `main` - Code production-ready, luôn ổn định và có thể deploy
- `dev` - Nhánh phát triển chính, tích hợp các tính năng mới

**Nhánh Tính Năng:**
- `feature/*` - Phát triển tính năng mới
  - Ví dụ: `feature/xac-thuc-nguoi-dung`, `feature/quan-ly-bai-viet`

**Nhánh Sửa Lỗi:**
- `bugfix/*` - Sửa lỗi trong quá trình phát triển (từ `dev`)
  - Ví dụ: `bugfix/loi-dang-nhap`, `bugfix/validate-form`
- `hotfix/*` - Sửa lỗi khẩn cấp trên production (từ `main`)
  - Ví dụ: `hotfix/loi-bao-mat`, `hotfix/loi-thanh-toan`

**Nhánh Phát Hành:**
- `release/*` - Chuẩn bị cho production
  - Ví dụ: `release/v1.0.0`, `release/v1.1.0`

### Quy Trình Làm Việc

#### 1. Phát Triển Tính Năng Mới

```bash
# Cập nhật nhánh dev
git checkout dev
git pull origin dev

# Tạo nhánh feature mới
git checkout -b feature/ten-tinh-nang

# Làm việc và commit
git add .
git commit -m "feat: thêm chức năng xác thực JWT"

# Push và tạo Pull Request vào dev
git push origin feature/ten-tinh-nang
```

#### 2. Sửa Lỗi

**Bugfix (từ dev):**
```bash
git checkout dev
git pull origin dev
git checkout -b bugfix/ten-loi

# Sửa lỗi và commit
git add .
git commit -m "fix: sửa lỗi validate email"

# Push và tạo Pull Request vào dev
git push origin bugfix/ten-loi
```

**Hotfix (từ main - khẩn cấp):**
```bash
git checkout main
git pull origin main
git checkout -b hotfix/ten-loi-khan-cap

# Sửa lỗi khẩn cấp
git add .
git commit -m "hotfix: sửa lỗi bảo mật SQL injection"

# Push và tạo Pull Request vào main VÀ dev
git push origin hotfix/ten-loi-khan-cap
```

#### 3. Release

```bash
# Tạo nhánh release từ dev
git checkout dev
git pull origin dev
git checkout -b release/v1.0.0

# Kiểm tra cuối cùng, sửa lỗi nhỏ nếu có
git add .
git commit -m "chore: chuẩn bị release v1.0.0"

# Merge vào main
git checkout main
git merge release/v1.0.0
git tag -a v1.0.0 -m "Phát hành phiên bản 1.0.0"
git push origin main --tags

# Merge ngược lại vào dev
git checkout dev
git merge release/v1.0.0
git push origin dev

# Xóa nhánh release (tùy chọn)
git branch -d release/v1.0.0
git push origin --delete release/v1.0.0
```

### Quy Ước Commit Message (Tiếng Việt)

Sử dụng format **Conventional Commits** nhưng viết bằng tiếng Việt:

```
<type>(<scope>): <mô tả ngắn gọn>

<nội dung chi tiết (tùy chọn)>

<footer (tùy chọn)>
```

#### Types (Loại Commit):

- **feat**: Tính năng mới
- **fix**: Sửa lỗi
- **docs**: Cập nhật tài liệu
- **style**: Format code, không ảnh hưởng logic
- **refactor**: Tái cấu trúc code
- **test**: Thêm hoặc sửa tests
- **chore**: Cập nhật build tools, dependencies
- **perf**: Cải thiện hiệu suất
- **ci**: Thay đổi CI/CD configuration
- **revert**: Hoàn tác commit trước đó
- **hotfix**: Sửa lỗi khẩn cấp trên production

#### Ví Dụ Commit Message:

```bash
# Tính năng mới
git commit -m "feat(auth): thêm chức năng xác thực JWT"
git commit -m "feat(user): thêm API quản lý người dùng"

# Sửa lỗi
git commit -m "fix(user): sửa lỗi tạo UUID trong model User"
git commit -m "fix(auth): sửa lỗi validate email"

# Tài liệu
git commit -m "docs(readme): cập nhật hướng dẫn cài đặt Docker"
git commit -m "docs(api): thêm tài liệu API endpoints"

# Refactor
git commit -m "refactor(service): tái cấu trúc UserService"
git commit -m "refactor(repository): tối ưu query trong BaseRepository"

# Test
git commit -m "test(user): thêm unit test cho UserService"

# Chore
git commit -m "chore(deps): cập nhật Laravel lên v12.1"
git commit -m "chore(docker): cấu hình multi-stage build"

# Hotfix
git commit -m "hotfix(security): vá lỗi bảo mật XSS"
```

#### Commit Message Chi Tiết:

```bash
git commit -m "feat(post): thêm chức năng đăng bài viết

- Tạo PostRepository và PostService
- Thêm validation cho dữ liệu bài viết
- Implement eager loading cho quan hệ author
- Thêm unit test và feature test

Resolves: #123"
```

### Branch Protection Rules

Thiết lập bảo vệ cho các nhánh quan trọng trên GitHub/GitLab:

#### Nhánh `main`:
- ✅ Require pull request reviews (tối thiểu 1 reviewer)
- ✅ Require status checks to pass (tests, linting)
- ✅ Require branches to be up to date before merging
- ✅ Include administrators
- ✅ Không cho phép force push
- ✅ Không cho phép xóa nhánh

#### Nhánh `dev`:
- ✅ Require pull request reviews (tối thiểu 1 reviewer)
- ✅ Require status checks to pass
- ✅ Require branches to be up to date before merging

### Workflow Diagram

```
main (production)
  │
  │◄─── hotfix/loi-bao-mat
  │
  ├──► release/v1.0.0 ──────┐
  │                          │
  │                          ▼
dev ◄────────────────────── merge
  │
  │◄─── feature/xac-thuc ───┤
  │◄─── feature/bai-viet ───┤
  │◄─── bugfix/validate ────┤
  │
  └─────────────────────────┘
```

### Quy Tắc Làm Việc

1. **KHÔNG BAO GIỜ commit trực tiếp vào `main` hoặc `dev`**
   - Luôn tạo nhánh feature/bugfix/hotfix

2. **Luôn pull trước khi tạo nhánh mới**
   ```bash
   git checkout dev
   git pull origin dev
   git checkout -b feature/ten-tinh-nang
   ```

3. **Rebase thay vì merge khi cập nhật nhánh**
   ```bash
   git checkout feature/ten-tinh-nang
   git fetch origin
   git rebase origin/dev
   ```

4. **Squash commits trước khi merge PR (tùy chọn)**
   - Giữ lịch sử Git sạch đẹp

5. **Xóa nhánh sau khi merge**
   ```bash
   git branch -d feature/ten-tinh-nang
   git push origin --delete feature/ten-tinh-nang
   ```

6. **Chạy tests và lint trước khi push**
   ```bash
   composer test
   ./vendor/bin/pint
   ```

7. **Pull Request phải có:**
   - Mô tả rõ ràng về thay đổi
   - Link đến issue/ticket (nếu có)
   - Screenshots (nếu có thay đổi UI)
   - Test coverage

### Git Hooks (Khuyến nghị)

#### Pre-commit Hook

Tạo file `.git/hooks/pre-commit`:

```bash
#!/bin/bash

echo "Đang chạy Laravel Pint..."
./vendor/bin/pint

echo "Đang chạy tests..."
./vendor/bin/phpunit

if [ $? -ne 0 ]; then
    echo "Tests failed! Commit bị hủy."
    exit 1
fi
```

#### Commit-msg Hook

Tạo file `.git/hooks/commit-msg`:

```bash
#!/bin/bash

commit_msg=$(cat "$1")
pattern="^(feat|fix|docs|style|refactor|test|chore|perf|ci|revert|hotfix)(\(.+\))?: .{1,}"

if ! echo "$commit_msg" | grep -qE "$pattern"; then
    echo "Lỗi: Commit message không đúng format!"
    echo "Format: <type>(<scope>): <mô tả>"
    echo "Ví dụ: feat(auth): thêm chức năng đăng nhập"
    exit 1
fi
```

Cấp quyền thực thi:
```bash
chmod +x .git/hooks/pre-commit
chmod +x .git/hooks/commit-msg
```

### Các Lệnh Git Hữu Ích

```bash
# Xem lịch sử commit đẹp
git log --oneline --graph --decorate --all

# Xem thay đổi chưa commit
git diff

# Xem thay đổi đã stage
git diff --staged

# Hoàn tác commit cuối (giữ thay đổi)
git reset --soft HEAD~1

# Hoàn tác commit cuối (xóa thay đổi)
git reset --hard HEAD~1

# Xem danh sách nhánh
git branch -a

# Xóa nhánh local
git branch -d ten-nhanh

# Xóa nhánh remote
git push origin --delete ten-nhanh

# Stash thay đổi tạm thời
git stash
git stash pop

# Cherry-pick commit từ nhánh khác
git cherry-pick <commit-hash>
```

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

# Docker Ports (Development only)
DOCKER_PORT_NGINX_DEV=8080          # Nginx port for development environment
DOCKER_PORT_POSTGRES_DEV=5432       # PostgreSQL port for development
```

**Port Configuration:**
- **Development**: Access API at `http://localhost:${DOCKER_PORT_NGINX_DEV}` (default: 8080)
  - PostgreSQL exposed at `localhost:${DOCKER_PORT_POSTGRES_DEV}` (default: 5432)
  - Ports are configurable via environment variables
- **Testing**: No exposed ports (internal network only)
  - Testing environment managed by `run-tests.sh` script
  - Uses isolated test database in tmpfs (in-memory)
  - Runs on internal Docker network without port exposure
- **Production**: No exposed ports (managed by Nginx Proxy Manager via `npm_proxy` network)

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

**Recommended: Using Isolated Test Environment**
```bash
./run-tests.sh                          # Run all tests with proper environment
./run-tests.sh --filter=AuthTest        # Run specific test
./run-tests.sh --testsuite=Feature      # Run feature tests only
```

The `run-tests.sh` script automatically:
1. Stops all running services
2. Starts isolated testing environment (with `db_test` container)
3. Runs fresh migrations + seeders
4. Executes tests
5. Cleans up and restarts development environment

**Alternative: Direct PHPUnit (requires manual setup)**
```bash
composer test                  # Run all tests
./vendor/bin/phpunit          # Direct PHPUnit invocation
./vendor/bin/phpunit --filter TestName  # Run specific test
./vendor/bin/phpunit tests/Unit         # Run only unit tests
./vendor/bin/phpunit tests/Feature      # Run only feature tests
```

**Testing Database:**
- Uses PostgreSQL `balance_flow_test` database (isolated from development)
- Test database runs in tmpfs (in-memory) for speed
- Automatically migrated fresh before each test run
- Configured in `phpunit.xml` and `compose-testing.yml`

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
├── Adapters/                      # Infrastructure layer (external integrations)
│   └── Auth/                     # Authentication adapters
│       ├── Contracts/
│       │   └── AuthAdapterInterface.php
│       └── SanctumAuthAdapter.php
├── Models/                        # Eloquent ORM models
│   └── User.php
├── Providers/                     # Service providers
│   ├── AppServiceProvider.php
│   ├── RepositoryServiceProvider.php  # Repository bindings
│   └── ServiceLayerProvider.php       # Service & Adapter bindings
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

### Database Design Best Practices

#### Primary Keys: Use UUID v7 for Main Entity Tables

**IMPORTANT**: All main entity tables (users, posts, products, orders, etc.) MUST use UUID version 7 as primary keys instead of auto-incrementing integers.

**Why UUID v7?**
- **Time-ordered**: UUID v7 includes a timestamp component, making them naturally sortable and optimized for database indexing
- **Distributed systems**: Generate IDs without database coordination or conflicts
- **Security**: Non-sequential IDs prevent enumeration attacks (e.g., guessing `/users/1`, `/users/2`)
- **Merging data**: No ID conflicts when merging databases or syncing data across environments
- **Better than UUID v4**: Unlike random v4, v7 maintains chronological order for better B-tree index performance

**Implementation Pattern:**

1. **Migration**: Use `uuid()` for primary key
```php
Schema::create('posts', function (Blueprint $table) {
    $table->uuid('id')->primary();  // UUID v7 primary key
    $table->string('title');
    $table->text('content');
    $table->foreignUuid('user_id')->constrained(); // Foreign key reference
    $table->timestamps();
});
```

2. **Model**: Add `HasUuids` trait
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, HasUuids;  // HasUuids enables automatic UUID v7 generation

    protected $fillable = ['title', 'content', 'user_id'];
}
```

3. **Foreign Keys**: Use `foreignUuid()` instead of `foreignId()`
```php
// ✅ Correct - UUID foreign key
$table->foreignUuid('user_id')->constrained();

// ❌ Wrong - Integer foreign key
$table->foreignId('user_id')->constrained();
```

**When NOT to use UUID:**
- Pivot tables (use composite keys)
- Tables with composite primary keys
- High-volume logging/analytics tables where performance is critical
- Join tables that reference two foreign keys

**Reference Implementation:**
See `app/Models/User.php` and `database/migrations/0001_01_01_000000_create_users_table.php` for complete UUID v7 implementation example.

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

### Adapter Pattern for External Integrations

The project uses **Adapter Pattern** to decouple business logic from external service implementations. This allows easy switching between different providers (e.g., Sanctum ↔ JWT, Stripe ↔ PayPal) without changing business logic.

#### Architecture

```
Service (Business Logic)
      ↓
AdapterInterface (Contract)
      ↓
┌─────────────┬──────────────┬──────────────┐
│             │              │              │
Adapter A     Adapter B      Adapter C      Custom
(Default)     (Alternative)  (Alternative)  (Your own)
```

#### Current Adapters

**Authentication Adapters** (`app/Adapters/Auth/`):
- `SanctumAuthAdapter` - Laravel Sanctum (default)
- Token-based authentication with multi-device support

#### Folder Structure

```
app/
├── Adapters/                      # Infrastructure layer (external integrations)
│   └── Auth/                      # Authentication adapters
│       ├── Contracts/
│       │   └── AuthAdapterInterface.php
│       └── SanctumAuthAdapter.php
├── Services/                      # Business logic layer
│   └── AuthService.php            # Uses AuthAdapterInterface
```

#### Benefits

1. **Separation of Concerns**: Business logic separate from implementation details
2. **Easy to Switch**: Change provider by updating one line in ServiceLayerProvider
3. **Testability**: Easy to mock adapters in tests
4. **Open/Closed Principle**: Extend with new adapters without modifying services
5. **Dependency Inversion**: Services depend on abstractions, not concrete classes

#### Example: Auth Adapter

**Interface** (`app/Adapters/Auth/Contracts/AuthAdapterInterface.php`):
```php
interface AuthAdapterInterface
{
    public function generateToken(User $user, string $tokenName): string;
    public function revokeCurrentToken(User $user): bool;
    public function revokeAllTokens(User $user): bool;
    public function verifyCredentials(array $credentials): bool;
    public function getCurrentUser(): ?User;
}
```

**Implementation** (`app/Adapters/Auth/SanctumAuthAdapter.php`):
```php
class SanctumAuthAdapter implements AuthAdapterInterface
{
    public function generateToken(User $user, string $tokenName): string
    {
        return $user->createToken($tokenName)->plainTextToken;
    }
    // ... other methods
}
```

**Usage in Service** (`app/Services/AuthService.php`):
```php
class AuthService implements AuthServiceInterface
{
    public function __construct(
        protected AuthAdapterInterface $authAdapter
    ) {}

    public function login(array $credentials): array
    {
        if (!$this->authAdapter->verifyCredentials($credentials)) {
            throw new AuthenticationException('Invalid credentials');
        }

        $user = $this->authAdapter->getCurrentUser();
        $token = $this->authAdapter->generateToken($user, $tokenName);

        return ['user' => $user, 'token' => $token];
    }
}
```

**Binding** (`app/Providers/ServiceLayerProvider.php`):
```php
// Default: Sanctum
$this->app->bind(
    \App\Adapters\Auth\Contracts\AuthAdapterInterface::class,
    \App\Adapters\Auth\SanctumAuthAdapter::class
);

// To switch to JWT: Just change one line
// $this->app->bind(
//     \App\Adapters\Auth\Contracts\AuthAdapterInterface::class,
//     \App\Adapters\Auth\JwtAuthAdapter::class
// );
```

#### Creating New Adapters

1. **Create Interface** in `app/Adapters/{Domain}/Contracts/`
2. **Implement Adapter** in `app/Adapters/{Domain}/`
3. **Bind in ServiceLayerProvider**
4. **Inject in Service** via constructor

Example domains for future adapters:
- `app/Adapters/Payment/` - Stripe, PayPal, VNPay
- `app/Adapters/Storage/` - S3, Google Cloud, Local
- `app/Adapters/Cache/` - Redis, Memcached
- `app/Adapters/Queue/` - SQS, RabbitMQ

#### Adapter Layer Best Practices

**IMPORTANT**: Follow these guidelines when implementing new adapters to ensure consistency and maintainability.

##### 1. Interface Design

**✅ DO:**
- Keep interfaces small and focused (Interface Segregation Principle)
- Use clear, descriptive method names
- Return consistent types (avoid mixed return types)
- Document parameters and return values
- Use type hints for all parameters and returns

**❌ DON'T:**
- Put business logic in interface
- Mix multiple responsibilities in one interface
- Use generic names like `process()` or `handle()`
- Return different types based on conditions

**Example - Good Interface:**
```php
interface PaymentAdapterInterface
{
    /**
     * Process payment transaction
     *
     * @param float $amount Amount in VND
     * @param string $currency Currency code (default: VND)
     * @param array $metadata Additional data
     * @return PaymentResult
     * @throws PaymentException
     */
    public function charge(float $amount, string $currency = 'VND', array $metadata = []): PaymentResult;

    /**
     * Refund a payment
     *
     * @param string $transactionId Original transaction ID
     * @param float $amount Amount to refund (null = full refund)
     * @return RefundResult
     * @throws RefundException
     */
    public function refund(string $transactionId, ?float $amount = null): RefundResult;

    /**
     * Get payment status
     *
     * @param string $transactionId Transaction ID
     * @return PaymentStatus
     * @throws PaymentException
     */
    public function getStatus(string $transactionId): PaymentStatus;
}
```

**Example - Bad Interface:**
```php
// ❌ BAD: Too generic, unclear return types
interface PaymentAdapterInterface
{
    public function process($data); // What data? What returns?
    public function get($id); // Get what?
    public function handle($request); // Handle what?
}
```

##### 2. Adapter Implementation

**✅ DO:**
- Implement ALL interface methods
- Handle errors gracefully and throw consistent exceptions
- Use dependency injection for external libraries
- Log important operations
- Keep adapter stateless when possible
- Use configuration from .env or config files

**❌ DON'T:**
- Hardcode credentials or API keys
- Put business logic in adapter (that belongs in Service)
- Catch exceptions without re-throwing or logging
- Use static methods (breaks testability)
- Mix multiple provider implementations in one class

**Example - Good Adapter:**
```php
namespace App\Adapters\Payment;

use App\Adapters\Payment\Contracts\PaymentAdapterInterface;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripePaymentAdapter implements PaymentAdapterInterface
{
    protected StripeClient $stripe;

    public function __construct()
    {
        // Get config from .env via config file
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    public function charge(float $amount, string $currency = 'VND', array $metadata = []): PaymentResult
    {
        try {
            $intent = $this->stripe->paymentIntents->create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => strtolower($currency),
                'metadata' => $metadata,
            ]);

            Log::info('Payment charged', [
                'transaction_id' => $intent->id,
                'amount' => $amount,
                'currency' => $currency,
            ]);

            return new PaymentResult(
                transactionId: $intent->id,
                status: PaymentStatus::from($intent->status),
                amount: $amount,
                currency: $currency
            );
        } catch (ApiErrorException $e) {
            Log::error('Stripe payment failed', [
                'error' => $e->getMessage(),
                'amount' => $amount,
            ]);

            throw new PaymentException("Payment failed: {$e->getMessage()}", 0, $e);
        }
    }

    // ... other methods
}
```

##### 3. Service Integration

**✅ DO:**
- Inject adapter via interface in Service constructor
- Let Service handle business logic and validation
- Use adapter only for external integration
- Wrap adapter calls in try-catch for error handling
- Transform adapter responses to domain objects if needed

**❌ DON'T:**
- Call adapter directly from Controller
- Put validation logic in adapter
- Let adapter exceptions bubble up to Controller
- Mix Service business logic with adapter implementation

**Example - Good Service:**
```php
namespace App\Services;

use App\Adapters\Payment\Contracts\PaymentAdapterInterface;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected PaymentAdapterInterface $paymentAdapter
    ) {}

    public function processPayment(Order $order): bool
    {
        // Business logic validation
        if ($order->isPaid()) {
            throw new \Exception('Order already paid');
        }

        if ($order->total <= 0) {
            throw new \Exception('Invalid order total');
        }

        // Use adapter for external integration
        DB::transaction(function () use ($order) {
            try {
                $result = $this->paymentAdapter->charge(
                    amount: $order->total,
                    currency: 'VND',
                    metadata: ['order_id' => $order->id]
                );

                // Business logic: Update order status
                $order->update([
                    'payment_status' => 'paid',
                    'transaction_id' => $result->transactionId,
                    'paid_at' => now(),
                ]);

            } catch (PaymentException $e) {
                // Log and handle gracefully
                Log::error('Payment processing failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);

                throw new \Exception('Payment failed. Please try again.');
            }
        });

        return true;
    }
}
```

##### 4. Configuration Management

**✅ DO:**
- Store adapter-specific config in `config/services.php`
- Use environment variables for credentials
- Support multiple environments (dev, staging, prod)
- Allow runtime configuration override when needed

**Example - config/services.php:**
```php
return [
    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'public' => env('STRIPE_PUBLIC_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'vnpay' => [
        'merchant_id' => env('VNPAY_MERCHANT_ID'),
        'hash_secret' => env('VNPAY_HASH_SECRET'),
        'url' => env('VNPAY_URL', 'https://sandbox.vnpayment.vn'),
    ],
];
```

**Example - .env:**
```env
# Stripe (default payment adapter)
STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# VNPay (alternative payment adapter)
VNPAY_MERCHANT_ID=
VNPAY_HASH_SECRET=
VNPAY_URL=
```

##### 5. Provider Binding

**✅ DO:**
- Bind interface to concrete implementation in ServiceLayerProvider
- Use config to determine which adapter to bind
- Support environment-specific adapters
- Document how to switch adapters

**Example - ServiceLayerProvider.php:**
```php
public function register(): void
{
    // Bind payment adapter based on config
    $this->app->bind(
        \App\Adapters\Payment\Contracts\PaymentAdapterInterface::class,
        function ($app) {
            $provider = config('payment.default', 'stripe');

            return match($provider) {
                'stripe' => $app->make(\App\Adapters\Payment\StripePaymentAdapter::class),
                'vnpay' => $app->make(\App\Adapters\Payment\VNPayPaymentAdapter::class),
                'paypal' => $app->make(\App\Adapters\Payment\PayPalPaymentAdapter::class),
                default => throw new \Exception("Unknown payment provider: {$provider}"),
            };
        }
    );
}
```

**Example - config/payment.php:**
```php
return [
    'default' => env('PAYMENT_PROVIDER', 'stripe'),

    'providers' => [
        'stripe' => [
            'enabled' => env('STRIPE_ENABLED', true),
        ],
        'vnpay' => [
            'enabled' => env('VNPAY_ENABLED', false),
        ],
    ],
];
```

##### 6. Testing Adapters

**✅ DO:**
- Create fake/mock adapters for testing
- Test each adapter implementation separately
- Mock external API calls in tests
- Test error scenarios
- Use factories for test data

**Example - FakePaymentAdapter for testing:**
```php
namespace App\Adapters\Payment;

use App\Adapters\Payment\Contracts\PaymentAdapterInterface;

class FakePaymentAdapter implements PaymentAdapterInterface
{
    protected array $charges = [];
    protected bool $shouldFail = false;

    public function charge(float $amount, string $currency = 'VND', array $metadata = []): PaymentResult
    {
        if ($this->shouldFail) {
            throw new PaymentException('Fake payment failed');
        }

        $transactionId = 'fake_' . uniqid();
        $this->charges[$transactionId] = compact('amount', 'currency', 'metadata');

        return new PaymentResult(
            transactionId: $transactionId,
            status: PaymentStatus::SUCCEEDED,
            amount: $amount,
            currency: $currency
        );
    }

    public function simulateFailure(): void
    {
        $this->shouldFail = true;
    }

    public function getCharges(): array
    {
        return $this->charges;
    }
}
```

**Example - Test:**
```php
public function test_order_payment_success()
{
    // Arrange
    $fakeAdapter = new FakePaymentAdapter();
    $this->app->instance(PaymentAdapterInterface::class, $fakeAdapter);

    $order = Order::factory()->create(['total' => 100000]);
    $service = app(OrderService::class);

    // Act
    $result = $service->processPayment($order);

    // Assert
    $this->assertTrue($result);
    $this->assertEquals('paid', $order->fresh()->payment_status);
    $this->assertCount(1, $fakeAdapter->getCharges());
}
```

##### 7. Error Handling

**✅ DO:**
- Create custom exception classes for each adapter domain
- Wrap third-party exceptions in your custom exceptions
- Log errors with context
- Provide user-friendly error messages
- Include original exception for debugging

**Example - Custom Exceptions:**
```php
namespace App\Exceptions;

class PaymentException extends \Exception
{
    public function __construct(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null,
        protected ?array $context = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getContext(): ?array
    {
        return $this->context;
    }
}

class RefundException extends PaymentException {}
class PaymentProviderException extends PaymentException {}
```

##### 8. Documentation

**✅ DO:**
- Document interface methods with PHPDoc
- Include usage examples in comments
- Document exceptions that can be thrown
- Keep CLAUDE.md updated with adapter patterns
- Add inline comments for complex logic

**Example - Well-documented Interface:**
```php
/**
 * Storage adapter interface for file operations
 *
 * Allows switching between different storage providers
 * (Local, S3, Google Cloud, etc.) without changing business logic
 *
 * @example
 * ```php
 * $storage->store('uploads/file.jpg', $fileContent);
 * $url = $storage->getUrl('uploads/file.jpg');
 * ```
 */
interface StorageAdapterInterface
{
    /**
     * Store file content at specified path
     *
     * @param string $path Relative path including filename
     * @param mixed $content File content (string or resource)
     * @param array $options Provider-specific options
     * @return bool True if stored successfully
     * @throws StorageException If storage operation fails
     */
    public function store(string $path, mixed $content, array $options = []): bool;

    // ... other methods
}
```

##### 9. Common Adapter Patterns

**Payment Adapters:**
- Interface: `PaymentAdapterInterface`
- Methods: `charge()`, `refund()`, `getStatus()`, `createCustomer()`
- Implementations: Stripe, PayPal, VNPay, Momo

**Storage Adapters:**
- Interface: `StorageAdapterInterface`
- Methods: `store()`, `get()`, `delete()`, `exists()`, `getUrl()`
- Implementations: Local, S3, Google Cloud, Azure Blob

**Cache Adapters:**
- Interface: `CacheAdapterInterface`
- Methods: `get()`, `set()`, `delete()`, `has()`, `clear()`
- Implementations: Redis, Memcached, File, Array (testing)

**Notification Adapters:**
- Interface: `NotificationAdapterInterface`
- Methods: `send()`, `sendBatch()`, `getStatus()`
- Implementations: Email, SMS, Push, Slack, Telegram

##### 10. Migration Strategy

When switching from one adapter to another:

1. **Implement new adapter** following interface
2. **Add configuration** for new provider
3. **Write tests** for new adapter
4. **Deploy side-by-side** (both adapters available)
5. **Feature flag** to gradually switch users
6. **Monitor and validate** new adapter performance
7. **Deprecate old adapter** after migration complete

**Example - Gradual Migration:**
```php
// ServiceLayerProvider.php
public function register(): void
{
    $this->app->bind(
        PaymentAdapterInterface::class,
        function ($app) {
            // Gradual migration: 10% users use new provider
            if (rand(1, 100) <= 10) {
                return $app->make(VNPayPaymentAdapter::class);
            }

            return $app->make(StripePaymentAdapter::class);
        }
    );
}
```

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
- Tests run with isolated PostgreSQL database (`balance_flow_test` in tmpfs)
- Separate testing environment using `compose-testing.yml`
- Use `run-tests.sh` script for automated setup, execution, and cleanup
- Use factories for generating test data
- Database is automatically migrated fresh before each test run
- Array cache and sync queues for faster execution

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
- **phpunit.xml**: PHPUnit test configuration (PostgreSQL testing)
- **compose-testing.yml**: Docker compose override for testing environment
- **run-tests.sh**: Automated test runner script with environment management
- **vite.config.js**: Vite build configuration
- **artisan**: CLI entry point for Laravel commands
- **bootstrap/providers.php**: Service provider registration
- **postman_collection.json**: Complete Postman API collection for testing all endpoints

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

## Development Roadmap

This section tracks the implementation status of all features based on the system design documents in the `docs/` folder.

### ✅ Completed Features

#### 1. Categories Module (100% Complete)
**Status:** Fully implemented with tests
**Files:**
- Models: `app/Models/Category.php`
- Repository: `app/Repositories/CategoryRepository.php`
- Service: `app/Services/CategoryService.php`
- Controller: `app/Http/Controllers/CategoryController.php`
- Tests: `tests/Feature/CategoryTest.php`

**Features:**
- ✅ CRUD operations for categories
- ✅ Support for parent-child relationships (subcategories)
- ✅ System vs user-defined categories
- ✅ Income/expense category types
- ✅ Pagination for list endpoints
- ✅ Validation with Vietnamese messages
- ✅ 16 comprehensive tests (all passing)
- ✅ Complete Postman collection for API testing

**API Endpoints:**
```
GET    /api/categories              - List categories (paginated)
GET    /api/categories?type=income  - Filter by type (paginated)
POST   /api/categories              - Create category
GET    /api/categories/{id}         - Get category details
PUT    /api/categories/{id}         - Update category
DELETE /api/categories/{id}         - Delete category
GET    /api/categories/{id}/subcategories - Get subcategories
```

**Database:**
- Migration: `2025_11_25_150144_create_categories_table.php`
- Seeder: `CategorySeeder.php` (6 income + 11 expense categories)

**Testing:**
- Postman Collection: All endpoints available in `postman_collection.json`
- PHPUnit Tests: 16 tests covering all functionality
- Manual Testing: Import Postman collection and test all endpoints

---

#### 2. Authentication Module (100% Complete)
**Status:** Fully implemented with tests
**Files:**
- Models: `app/Models/User.php` (with `HasApiTokens` trait)
- Service: `app/Services/AuthService.php`
- Service Interface: `app/Services/Contracts/AuthServiceInterface.php`
- Controller: `app/Http/Controllers/AuthController.php`
- Request Classes: `app/Http/Requests/Auth/` (6 validation classes)
- Tests: `tests/Feature/AuthTest.php`
- Config: `config/sanctum.php`
- Migration: `2025_11_28_094341_create_personal_access_tokens_table.php`

**Features:**
- ✅ Laravel Sanctum token-based authentication
- ✅ User registration with auto-login
- ✅ Login with token generation
- ✅ Token naming: `email_dd/mm/yyyy` format (e.g., `user@example.com_28/11/2025`)
- ✅ Get current user profile
- ✅ Update user profile (name, email)
- ✅ Change password with validation
- ✅ Logout from current device
- ✅ Logout from all devices
- ✅ Password reset request (forgot password)
- ✅ Password reset with token
- ✅ Validation with Vietnamese messages
- ✅ 14 comprehensive tests (all passing)
- ✅ Complete Postman collection with auto-save token

**API Endpoints:**
```
# Public (no auth required)
POST   /api/auth/register           - User registration
POST   /api/auth/login              - User login
POST   /api/auth/forgot-password    - Password reset request
POST   /api/auth/reset-password     - Reset password with token

# Protected (require Bearer token)
GET    /api/auth/me                 - Get current user
PUT    /api/auth/profile            - Update user profile
PUT    /api/auth/password           - Change password
POST   /api/auth/logout             - Logout (current device)
POST   /api/auth/logout-all         - Logout from all devices
```

**Database:**
- Migration: `2025_11_28_094341_create_personal_access_tokens_table.php`
- Uses `uuidMorphs` for tokenable relationship (compatible with UUID v7)

**Testing:**
- Postman Collection: All endpoints with auto-save token
- PHPUnit Tests: 14 tests covering all authentication flows
- Test Database: PostgreSQL (`balance_flow_test` in tmpfs)

---

### 🔲 TODO: Remaining Features

The following features need to be implemented according to the design documents in `docs/`:

#### 3. Account Types Module
**Priority:** High
**Description:** Types of accounts (Cash, Bank, Credit Card, E-Wallet, Investment)
**Status:** Not started

**Tasks:**
- [ ] Create `account_types` migration
- [ ] Create `AccountType` model with UUID v7
- [ ] Create Repository + Service layer
- [ ] Create Controller with CRUD APIs
- [ ] Add validation (Request classes)
- [ ] Write comprehensive tests
- [ ] Seed default account types

**API Endpoints to Implement:**
```
GET    /api/account-types           - List account types (paginated)
POST   /api/account-types           - Create account type (admin only?)
GET    /api/account-types/{id}      - Get account type details
PUT    /api/account-types/{id}      - Update account type
DELETE /api/account-types/{id}      - Delete account type
```

---

#### 4. Accounts Module
**Priority:** High
**Description:** User's financial accounts (wallets, bank accounts, etc.)
**Status:** Not started
**Dependencies:** Account Types must be completed first

**Tasks:**
- [ ] Create `accounts` migration with foreign key to `account_types`
- [ ] Create `Account` model with UUID v7
- [ ] Create Repository + Service layer
- [ ] Implement balance calculation logic
- [ ] Create Controller with CRUD APIs
- [ ] Add validation for balance constraints
- [ ] Write comprehensive tests
- [ ] Handle soft deletes for accounts with transactions

**API Endpoints to Implement:**
```
GET    /api/accounts                - List user's accounts (paginated)
POST   /api/accounts                - Create account
GET    /api/accounts/{id}           - Get account details
PUT    /api/accounts/{id}           - Update account
DELETE /api/accounts/{id}           - Delete/archive account
GET    /api/accounts/{id}/balance   - Get current balance
GET    /api/accounts/summary        - Get summary of all accounts
```

**Business Logic:**
- Balance can be negative for credit cards
- Balance is in VND (Vietnamese Dong)
- Track balance changes through transactions

---

#### 5. Transactions Module
**Priority:** High
**Description:** Income and expense transactions
**Status:** Not started
**Dependencies:** Accounts and Categories must be completed first

**Tasks:**
- [ ] Create `transactions` migration
- [ ] Create `Transaction` model with UUID v7
- [ ] Create Repository + Service layer
- [ ] Implement transaction creation with balance updates
- [ ] Handle transfer between accounts (2 transactions pattern)
- [ ] Create Controller with CRUD APIs
- [ ] Add validation and business rules
- [ ] Write comprehensive tests
- [ ] Implement filtering (by date range, category, account, type)
- [ ] Implement search functionality

**API Endpoints to Implement:**
```
GET    /api/transactions                    - List transactions (paginated, filtered)
POST   /api/transactions                    - Create transaction
POST   /api/transactions/transfer           - Transfer between accounts
GET    /api/transactions/{id}               - Get transaction details
PUT    /api/transactions/{id}               - Update transaction
DELETE /api/transactions/{id}               - Delete transaction
GET    /api/transactions/summary            - Get summary statistics
GET    /api/transactions/by-category        - Group by category
GET    /api/transactions/by-date            - Group by date
```

**Business Logic:**
- Amount must be positive
- Transaction types: income, expense (no transfer type)
- Transfer = 1 expense + 1 income transaction
- Update account balance on create/update/delete
- Support tags, notes, receipt attachments
- Date-based filtering and grouping

---

#### 6. Recurring Transactions Module
**Priority:** Medium
**Description:** Automatic recurring transactions (salary, rent, subscriptions)
**Status:** Not started
**Dependencies:** Transactions must be completed first

**Tasks:**
- [ ] Create `recurring_transactions` migration
- [ ] Create `RecurringTransaction` model with UUID v7
- [ ] Create Repository + Service layer
- [ ] Implement frequency logic (daily, weekly, monthly, yearly)
- [ ] Create background job for auto-generation
- [ ] Create Controller with CRUD APIs
- [ ] Add validation for recurrence rules
- [ ] Write comprehensive tests

**API Endpoints to Implement:**
```
GET    /api/recurring-transactions          - List recurring transactions (paginated)
POST   /api/recurring-transactions          - Create recurring transaction
GET    /api/recurring-transactions/{id}     - Get details
PUT    /api/recurring-transactions/{id}     - Update recurring transaction
DELETE /api/recurring-transactions/{id}     - Delete recurring transaction
POST   /api/recurring-transactions/{id}/skip - Skip next occurrence
POST   /api/recurring-transactions/{id}/generate - Manually generate transaction
```

**Business Logic:**
- Frequencies: daily, weekly, monthly, yearly
- Support custom intervals (e.g., every 2 weeks)
- Auto-generate transactions on schedule
- Track last and next generation dates

---

#### 7. Budgets Module
**Priority:** Medium
**Description:** Budget planning and tracking
**Status:** Not started
**Dependencies:** Categories and Transactions must be completed first

**Tasks:**
- [ ] Create `budgets` migration
- [ ] Create `Budget` model with UUID v7
- [ ] Create Repository + Service layer
- [ ] Implement budget tracking logic
- [ ] Create Controller with CRUD APIs
- [ ] Add budget vs actual comparison
- [ ] Implement budget alerts
- [ ] Write comprehensive tests

**API Endpoints to Implement:**
```
GET    /api/budgets                         - List budgets (paginated)
POST   /api/budgets                         - Create budget
GET    /api/budgets/{id}                    - Get budget details
PUT    /api/budgets/{id}                    - Update budget
DELETE /api/budgets/{id}                    - Delete budget
GET    /api/budgets/{id}/progress           - Get budget progress
GET    /api/budgets/summary                 - Get all budgets summary
GET    /api/budgets/alerts                  - Get budget alerts
```

**Business Logic:**
- Periods: weekly, monthly, quarterly, yearly
- Track spending vs budget limit
- Alert when reaching thresholds (e.g., 80%, 100%)
- Can be category-specific or overall

---

#### 8. Goals Module
**Priority:** Medium
**Description:** Financial goals (savings, debt payment, spending limits)
**Status:** Not started
**Dependencies:** Accounts must be completed first

**Tasks:**
- [ ] Create `goals` migration
- [ ] Create `Goal` model with UUID v7
- [ ] Create Repository + Service layer
- [ ] Implement goal progress tracking
- [ ] Create Controller with CRUD APIs
- [ ] Add goal status management
- [ ] Write comprehensive tests

**API Endpoints to Implement:**
```
GET    /api/goals                           - List goals (paginated)
POST   /api/goals                           - Create goal
GET    /api/goals/{id}                      - Get goal details
PUT    /api/goals/{id}                      - Update goal
DELETE /api/goals/{id}                      - Delete goal
GET    /api/goals/{id}/progress             - Get goal progress
POST   /api/goals/{id}/contribute           - Add contribution
GET    /api/goals/summary                   - Get goals summary
```

**Business Logic:**
- Goal types: saving, debt_payment, spending_limit
- Track progress: current_amount vs target_amount
- Status: in_progress, completed, cancelled, paused
- Optional link to specific account or category

---

#### 9. Goal Contributions Module
**Priority:** Low
**Description:** Track contributions to financial goals
**Status:** Not started
**Dependencies:** Goals must be completed first

**Tasks:**
- [ ] Create `goal_contributions` migration
- [ ] Create `GoalContribution` model with UUID v7
- [ ] Create Repository + Service layer
- [ ] Link to transactions (optional)
- [ ] Create Controller with CRUD APIs
- [ ] Write comprehensive tests

**API Endpoints to Implement:**
```
GET    /api/goals/{goalId}/contributions    - List contributions for a goal
POST   /api/goals/{goalId}/contributions    - Add contribution
GET    /api/contributions/{id}              - Get contribution details
PUT    /api/contributions/{id}              - Update contribution
DELETE /api/contributions/{id}              - Delete contribution
```

---

#### 10. Notifications Module
**Priority:** Low
**Description:** System notifications for users
**Status:** Not started

**Tasks:**
- [ ] Create `notifications` migration
- [ ] Create `Notification` model with UUID v7
- [ ] Create Repository + Service layer
- [ ] Implement notification types
- [ ] Create Controller with APIs
- [ ] Add mark as read functionality
- [ ] Write comprehensive tests

**API Endpoints to Implement:**
```
GET    /api/notifications                   - List notifications (paginated)
GET    /api/notifications/unread            - List unread notifications
PUT    /api/notifications/{id}/read         - Mark as read
PUT    /api/notifications/read-all          - Mark all as read
DELETE /api/notifications/{id}              - Delete notification
```

**Notification Types:**
- Budget warnings and exceeded alerts
- Goal achievement notifications
- Recurring transaction reminders
- Low account balance alerts
- Anomaly detection alerts

---

#### 11. Analytics & Reports Module
**Priority:** Low
**Description:** Financial reports and analytics
**Status:** Not started
**Dependencies:** All transaction-related modules must be completed

**Tasks:**
- [ ] Implement income vs expense reports
- [ ] Implement spending by category analysis
- [ ] Implement monthly/yearly summaries
- [ ] Implement trends and insights
- [ ] Create APIs for data visualization
- [ ] Add export functionality (CSV, PDF)
- [ ] Write comprehensive tests

**API Endpoints to Implement:**
```
GET    /api/reports/summary                 - Overall financial summary
GET    /api/reports/income-expense          - Income vs expense report
GET    /api/reports/by-category             - Spending by category
GET    /api/reports/trends                  - Spending trends
GET    /api/reports/monthly                 - Monthly summary
GET    /api/reports/yearly                  - Yearly summary
POST   /api/reports/export                  - Export report
```

---

### 📊 Implementation Priority

**Phase 1: Core Functionality (Must Have)**
1. ✅ Categories (Complete)
2. ✅ Authentication & User Management (Complete)
3. 🔲 Account Types
4. 🔲 Accounts
5. 🔲 Transactions

**Phase 2: Advanced Features (Should Have)**
6. 🔲 Recurring Transactions
7. 🔲 Budgets
8. 🔲 Goals

**Phase 3: Nice to Have**
9. 🔲 Goal Contributions
10. 🔲 Notifications
11. 🔲 Analytics & Reports

---

### 📝 Implementation Guidelines

When implementing each module, follow this checklist:

**Database Layer:**
- [ ] Create migration with UUID v7 primary keys
- [ ] Add proper indexes and foreign keys
- [ ] Add constraints and validations
- [ ] Create seeder for default data (if applicable)

**Model Layer:**
- [ ] Add `HasUuids` trait
- [ ] Define `$fillable` properties
- [ ] Add `$casts` for data types
- [ ] Define relationships
- [ ] Add query scopes
- [ ] Add business logic methods

**Repository Layer:**
- [ ] Create Repository Interface
- [ ] Implement Repository with base CRUD
- [ ] Add custom query methods
- [ ] Support column selection
- [ ] Support eager loading
- [ ] Support pagination

**Service Layer:**
- [ ] Create Service Interface
- [ ] Implement Service with business logic
- [ ] Add validation rules
- [ ] Handle transactions
- [ ] Add error handling

**Controller Layer:**
- [ ] Create Request validation classes
- [ ] Implement API endpoints
- [ ] Add proper HTTP status codes
- [ ] Return consistent JSON responses
- [ ] Add query parameter support

**Testing:**
- [ ] Create Factory for model
- [ ] Write feature tests for all APIs
- [ ] Write unit tests for complex logic
- [ ] Test edge cases and error scenarios
- [ ] Ensure 100% pass rate
- [ ] Add endpoints to Postman collection

**Documentation:**
- [ ] Update API documentation
- [ ] Add code comments
- [ ] Update CLAUDE.md roadmap
- [ ] Add usage examples
- [ ] Update Postman collection with new endpoints

---

**For more information, refer to the example implementations in `app/Repositories/UserRepository.php`, `app/Services/UserService.php`, and `app/Http/Controllers/UserController.php`.**

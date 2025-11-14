# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Balance Flow Backend is a Laravel 12 API application using the Service-Repository pattern with Laravel Passport for OAuth2 authentication. The application supports multi-language responses (Vietnamese and English) and follows clean architecture principles.

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
│   ├── Controllers/        # Thin controllers (HTTP layer)
│   ├── Middleware/        # Custom middleware (SetLocale)
│   └── Requests/          # Form Request validation classes
├── Services/
│   ├── Contracts/         # Service interfaces
│   └── *.php             # Service implementations
├── Repositories/
│   ├── Contracts/        # Repository interfaces
│   └── *.php            # Repository implementations
├── Adapters/
│   ├── Contracts/       # Adapter interfaces
│   └── *.php           # Adapter implementations
├── Models/              # Eloquent models
└── Providers/           # Service providers
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

- Use PHPUnit (not Pest) for all tests
- Create tests via: `php artisan make:test <name>` (feature) or `php artisan make:test <name> --unit`
- Mock interfaces (not concrete classes) when testing services
- Use model factories for test data
- Run minimal test set with `--filter` when iterating
- Always run related tests before finalizing changes

## Code Style

- **Strict Types**: All PHP files use `declare(strict_types=1);`
- **Type Hints**: Explicit return types required on all methods
- **Constructor Promotion**: Use PHP 8+ constructor property promotion
- **Formatting**: Run `vendor/bin/pint --dirty` before committing
- **No Empty Constructors**: Remove constructors with zero parameters
- **Final Classes**: Controllers, Services, Repositories, Adapters must be `final`
- **Readonly Services**: Services, Repositories, Adapters use `readonly` modifier
- **Validation**: Use Form Request classes (never inline validation in controllers)

## Database

- **Connection**: PostgreSQL 17
- **Migrations**: Auto-run via `composer run setup` or manually with `php artisan migrate`
- **Passport Tables**: OAuth tables created via `passport:install`
- **Password Resets**: Uses `password_reset_tokens` table

## Important Notes

- **Token Management**: Password changes and resets revoke all existing tokens for security
- **Email Configuration**: Set up `.env` mail config for verification/reset emails to work
- **Docker Ports**: Default `APP_PORT=80`, `FORWARD_DB_PORT=5432`, `FORWARD_REDIS_PORT=6379`
- **API Prefix**: All routes under `/api` (defined in `routes/api.php`)
- **Health Check**: Built-in Laravel health endpoint at `/up`
- **Rate Limiting**: Email verification resend throttled to 6 requests per minute

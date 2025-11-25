# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 backend application using PHP 8.2+ with SQLite as the default database. The project uses Vite for frontend asset building with Tailwind CSS v4.

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

Default database is SQLite at `database/database.sqlite`.

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

## Architecture

### Project Structure

- **app/Http/Controllers/**: HTTP request handlers
- **app/Models/**: Eloquent ORM models
- **app/Providers/**: Service providers for dependency injection and bootstrapping
- **routes/web.php**: Web routes (returning views)
- **routes/console.php**: Artisan console commands
- **database/migrations/**: Database schema migrations
- **database/factories/**: Model factories for testing/seeding
- **database/seeders/**: Database seeders
- **tests/Feature/**: Feature tests (test full HTTP requests)
- **tests/Unit/**: Unit tests (test individual classes/methods)
- **resources/views/**: Blade templates
- **resources/js/**: JavaScript source files
- **resources/css/**: CSS source files (compiled with Vite)
- **public/**: Web server document root (built assets go here)
- **config/**: Configuration files

### Database Configuration

Default setup uses SQLite (database/database.sqlite). To switch to MySQL:

1. Update .env:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

2. Run migrations: `php artisan migrate`

### Laravel Sail (Docker)

The project includes Laravel Sail configuration (compose.yaml) with:
- MySQL 8.0
- Redis
- Meilisearch (search engine)
- Mailpit (email testing)
- Selenium (browser testing)

To use Sail:
```bash
./vendor/bin/sail up -d        # Start containers
./vendor/bin/sail artisan migrate  # Run artisan commands in container
./vendor/bin/sail composer require package  # Install packages
./vendor/bin/sail down         # Stop containers
```

### Queue System

Default queue connection is `database` (stored in database tables). Queue jobs are processed by the queue worker included in `composer dev`, or run manually with:
```bash
php artisan queue:work
php artisan queue:listen --tries=1
```

### Frontend Assets

Vite is configured to compile:
- **resources/css/app.css** → Tailwind CSS v4
- **resources/js/app.js** → JavaScript

Built assets are stored in `public/build/` and referenced in Blade templates using `@vite()` directive.

## Key Files

- **composer.json**: PHP dependencies and custom scripts
- **package.json**: Node dependencies and build scripts
- **.env.example**: Environment variable template
- **phpunit.xml**: PHPUnit test configuration
- **vite.config.js**: Vite build configuration
- **artisan**: CLI entry point for Laravel commands

## Testing Strategy

- **Unit tests** (tests/Unit/): Test individual classes and methods in isolation
- **Feature tests** (tests/Feature/): Test HTTP endpoints and application features end-to-end
- Tests run with separate environment: in-memory SQLite, array cache, sync queues
- Use factories for generating test data
- Database is automatically reset between tests

## Code Style

This project uses Laravel Pint for code formatting, which follows Laravel's opinionated style guide. Always run `./vendor/bin/pint` before committing to ensure consistent formatting.

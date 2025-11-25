# Balance Flow Backend

Laravel 12 backend application with Docker, PostgreSQL, and Supervisor for process management.

## Features

- **Laravel 12** - Latest Laravel framework
- **PHP 8.2-FPM** - Modern PHP with FPM
- **PostgreSQL 16** - Robust relational database
- **Docker & Docker Compose** - Containerized deployment
- **Nginx** - High-performance web server
- **Supervisor** - Process management (PHP-FPM, Queue Workers, Scheduler)
- **Multi-stage Dockerfile** - Optimized builds for dev/prod
- **Repository & Service Pattern** - Clean architecture

## Quick Start

### Prerequisites

- Docker & Docker Compose
- Git

### Installation

```bash
# 1. Clone repository
git clone <repository-url>
cd balance-flow-be

# 2. Create external Docker resources (one-time setup)
docker network create balance_flow_network
docker volume create balance_flow_postgres_data

# 3. Setup environment
cp .env.example .env
# Edit .env if needed (APP_KEY, database credentials, etc.)

# 4. Choose environment
cp compose-dev.yml compose.override.yml    # Development
# OR
cp compose-prod.yml compose.override.yml   # Production

# 5. Build and start containers
docker compose build
docker compose up -d

# 6. Install dependencies (development only)
docker compose exec app composer install
docker compose exec app npm install

# 7. Run migrations
docker compose exec app php artisan migrate

# 8. Access application
# Development: http://localhost:8080
# Production: Configure domain in Nginx Proxy Manager
```

## Project Structure

```
.
├── app/
│   ├── Http/Controllers/      # HTTP layer
│   ├── Services/              # Business logic layer
│   │   └── Contracts/         # Service interfaces
│   ├── Repositories/          # Data access layer
│   │   └── Contracts/         # Repository interfaces
│   └── Models/                # Eloquent models
├── docker/
│   ├── nginx/                 # Nginx configuration
│   ├── php/                   # PHP configuration
│   └── supervisor/            # Supervisor configuration
├── compose.yml                # Base Docker Compose
├── compose-dev.yml            # Development overrides
├── compose-prod.yml           # Production overrides
├── Dockerfile                 # Multi-stage build
└── CLAUDE.md                  # Developer guide
```

## Docker Services

- **app**: PHP 8.2-FPM with Supervisor
  - PHP-FPM (web application)
  - Laravel Queue Workers (2 workers)
  - Laravel Scheduler (cron jobs)
- **nginx**: Nginx web server
- **db**: PostgreSQL 16 database

## Common Commands

### Container Management

```bash
# Start containers
docker compose up -d

# Stop containers (keeps data)
docker compose down

# View logs
docker compose logs -f
docker compose logs -f app

# Rebuild containers
docker compose build --no-cache
docker compose up -d
```

### Application Commands

```bash
# Shell access
docker compose exec app bash

# Artisan commands
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
docker compose exec app php artisan tinker

# Composer
docker compose exec app composer install
docker compose exec app composer update

# NPM
docker compose exec app npm install
docker compose exec app npm run build
```

### Supervisor (Process Management)

```bash
# Check status
docker compose exec app supervisorctl status

# Restart processes
docker compose exec app supervisorctl restart laravel-worker:*
docker compose exec app supervisorctl restart php-fpm

# View logs
docker compose exec app supervisorctl tail -f laravel-worker:laravel-worker_00
```

### Database

```bash
# Access PostgreSQL
docker compose exec db psql -U postgres -d balance_flow

# Backup
docker compose exec db pg_dump -U postgres balance_flow > backup.sql

# Restore
docker compose exec -T db psql -U postgres balance_flow < backup.sql
```

## Environments

### Development

```bash
cp compose-dev.yml compose.override.yml
docker compose up -d
```

- Port 8080 → Nginx
- Port 5432 → PostgreSQL
- Code mounted from host (live reload)
- Debug enabled

**Access:** http://localhost:8080

### Production

```bash
# Create publish network for Nginx Proxy Manager
docker network create npm_proxy

cp compose-prod.yml compose.override.yml
docker compose up -d
```

- No exposed ports
- Code baked into image
- Optimized build
- Connected to Nginx Proxy Manager

**Configure NPM:**
1. Add Proxy Host
2. Forward to: `nginx` (container)
3. Port: 80
4. Enable SSL

### Testing

```bash
cp compose-testing.yml compose.override.yml
docker compose up -d
```

- Port 8081
- Separate test database
- Isolated environment

## Architecture

### Repository & Service Pattern

```
Controller → Service Interface → Service Implementation
                ↓
         Repository Interface → Repository Implementation
                ↓
          Eloquent Model → Database
```

**Benefits:**
- Clean separation of concerns
- Easy to test with mocks
- Consistent pattern across app
- Reusable business logic

**Example:**

```php
// Controller
class UserController extends Controller
{
    public function __construct(
        protected UserServiceInterface $userService
    ) {}

    public function index()
    {
        return $this->userService->getAll();
    }
}

// Service
class UserService implements UserServiceInterface
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function getAll()
    {
        return $this->repository->all(['id', 'name', 'email'], ['posts']);
    }
}

// Repository
class UserRepository implements UserRepositoryInterface
{
    // Inherits from BaseRepository with common CRUD operations
}
```

## Configuration

### Environment Variables (.env)

```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080

# Database
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=balance_flow
DB_USERNAME=postgres
DB_PASSWORD=postgres

# Docker
DOCKER_VOLUME_POSTGRES=balance_flow_postgres_data
DOCKER_NETWORK_PUBLISH=npm_proxy
```

### Docker Networks

- **default** (balance_flow_network): All services communicate here
- **publish** (npm_proxy): Production only, nginx connects to NPM

### Data Persistence

Database data stored in external volume `balance_flow_postgres_data`:
- Persists after `docker compose down`
- Manual deletion required: `docker volume rm balance_flow_postgres_data`

## Development

### Code Style

```bash
./vendor/bin/pint              # Format code
./vendor/bin/pint --test       # Check formatting
```

### Testing

```bash
docker compose exec app php artisan test
./vendor/bin/phpunit
./vendor/bin/phpunit --filter TestName
```

### Database Migrations

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan migrate:fresh
docker compose exec app php artisan migrate:rollback
docker compose exec app php artisan db:seed
```

## Production Deployment

### 1. Prepare Server

```bash
# Create Docker resources
docker network create npm_proxy
docker network create balance_flow_network
docker volume create balance_flow_postgres_data
```

### 2. Deploy Application

```bash
git clone <repository>
cd balance-flow-be

# Setup environment
cp .env.example .env
# Edit .env: Set APP_ENV=production, APP_DEBUG=false, change passwords

# Deploy
cp compose-prod.yml compose.override.yml
docker compose build
docker compose up -d

# Run migrations
docker compose exec app php artisan migrate --force
```

### 3. Configure Nginx Proxy Manager

- Domain: your-domain.com
- Forward Hostname: `nginx` (container name)
- Forward Port: 80
- Enable SSL (Let's Encrypt)

### 4. Optimize

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

## Troubleshooting

### 502 Bad Gateway

```bash
# Check PHP-FPM status
docker compose exec app supervisorctl status php-fpm

# Restart PHP-FPM
docker compose exec app supervisorctl restart php-fpm
```

### Database Connection Failed

```bash
# Check database running
docker compose ps db

# Restart database
docker compose restart db
```

### Permission Issues

```bash
docker compose exec app chmod -R 775 storage bootstrap/cache
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Resources Not Found

```bash
# Create network
docker network create balance_flow_network

# Create volume
docker volume create balance_flow_postgres_data
```

## Documentation

- **[CLAUDE.md](CLAUDE.md)** - Comprehensive developer guide (for Claude Code)
- **[Laravel Docs](https://laravel.com/docs)** - Official Laravel documentation

## Tech Stack

- **Framework**: Laravel 12
- **Language**: PHP 8.2
- **Database**: PostgreSQL 16
- **Web Server**: Nginx
- **Process Manager**: Supervisor
- **Frontend**: Vite + Tailwind CSS v4
- **Testing**: PHPUnit
- **Code Style**: Laravel Pint
- **Containers**: Docker & Docker Compose

## License

This project is proprietary software. All rights reserved.

---

**Need help?** Check [CLAUDE.md](CLAUDE.md) for detailed developer guides and Docker documentation.

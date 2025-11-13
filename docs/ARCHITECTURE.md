# Architecture Documentation - Balance Flow Backend

## Overview

Balance Flow Backend is a Laravel 12 application designed to provide RESTful API services for financial balance tracking and flow management. The application follows modern Laravel architectural patterns with Docker containerization for development and deployment.

## Technology Stack

### Core Framework
- **Laravel Framework**: v12.x (Latest)
- **PHP**: 8.4.14
- **Composer**: 2.x

### Database
- **PostgreSQL**: 17-alpine
- **Redis**: 7-alpine (Cache & Queue)

### Frontend Build Tools
- **Vite**: Modern asset bundling
- **Alpine.js**: Lightweight JavaScript framework
- **TailwindCSS**: Utility-first CSS framework

### Development Tools
- **Laravel Pint**: Code formatting (PSR-12)
- **PHPUnit**: Testing framework (v11)
- **Laravel Tinker**: REPL for debugging
- **Laravel Boost**: MCP server for development

### Infrastructure
- **Docker & Docker Compose**: Containerization
- **Nginx**: Web server / Reverse proxy
- **Supervisor**: Process management for PHP-FPM

## System Architecture

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                        Client Layer                          │
│  (Web Browser, Mobile App, Third-party Services)            │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                    Nginx (Port 80/443)                       │
│              Reverse Proxy & Static Assets                   │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│                  Laravel Application                         │
│                    (PHP 8.4 FPM)                            │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Routes Layer (API v1, Web)                         │   │
│  └──────────────────────┬──────────────────────────────┘   │
│                         │                                    │
│  ┌──────────────────────▼──────────────────────────────┐   │
│  │  Middleware (Auth, CORS, Rate Limit, Validation)    │   │
│  └──────────────────────┬──────────────────────────────┘   │
│                         │                                    │
│  ┌──────────────────────▼──────────────────────────────┐   │
│  │  Controllers (API Resources, Form Requests)         │   │
│  └──────────────────────┬──────────────────────────────┘   │
│                         │                                    │
│  ┌──────────────────────▼──────────────────────────────┐   │
│  │  Business Logic (Services, Actions)                 │   │
│  └──────────────────────┬──────────────────────────────┘   │
│                         │                                    │
│  ┌──────────────────────▼──────────────────────────────┐   │
│  │  Data Layer (Eloquent Models, Repositories)         │   │
│  └──────────────────────┬──────────────────────────────┘   │
└────────────────────────┬────────────────────────────────────┘
                         │
           ┌─────────────┴─────────────┐
           │                           │
           ▼                           ▼
┌──────────────────────┐    ┌──────────────────────┐
│   PostgreSQL 17      │    │     Redis 7          │
│   (Primary DB)       │    │  (Cache & Queue)     │
└──────────────────────┘    └──────────────────────┘
```

### Container Architecture

```
Docker Compose Services:
├── app (Laravel PHP-FPM)
│   ├── PHP 8.4 FPM
│   ├── Supervisor (Process Manager)
│   ├── Composer dependencies
│   └── Application code
├── nginx (Web Server)
│   ├── Nginx 1.27
│   ├── Serves static assets
│   └── Proxies to PHP-FPM
├── postgres (Database)
│   ├── PostgreSQL 17-alpine
│   ├── Persistent volume: postgres_data
│   └── Port: 5432
└── redis (Cache/Queue)
    ├── Redis 7-alpine
    ├── Persistent volume: redis_data
    └── Port: 6379
```

## Directory Structure

```
balance-flow-be/
├── app/                         # Application core
│   ├── Console/                 # Artisan commands
│   │   └── Commands/            # Custom commands
│   ├── Exceptions/              # Exception handlers
│   ├── Http/                    # HTTP layer
│   │   ├── Controllers/         # Request handlers
│   │   │   ├── Api/            # API controllers (planned)
│   │   │   └── Web/            # Web controllers
│   │   ├── Middleware/          # HTTP middleware (planned)
│   │   ├── Requests/            # Form request validation (planned)
│   │   └── Resources/           # API resources (planned)
│   ├── Models/                  # Eloquent models
│   │   └── User.php
│   ├── Providers/               # Service providers
│   │   └── AppServiceProvider.php
│   └── Services/                # Business logic services (planned)
├── bootstrap/                   # Framework bootstrap
│   ├── app.php                  # Application bootstrap
│   ├── cache/                   # Bootstrap cache
│   └── providers.php            # Provider registration
├── config/                      # Configuration files
│   ├── app.php                  # App configuration
│   ├── database.php             # Database connections
│   ├── cache.php                # Cache configuration
│   └── ...                      # Other configs
├── database/                    # Database layer
│   ├── factories/               # Model factories
│   │   └── UserFactory.php
│   ├── migrations/              # Database migrations
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   └── 0001_01_01_000002_create_jobs_table.php
│   └── seeders/                 # Database seeders
│       └── DatabaseSeeder.php
├── docker/                      # Docker configurations
│   ├── configs/                 # Service configs
│   │   ├── nginx/              # Nginx configs
│   │   ├── php/                # PHP configs
│   │   └── supervisor/         # Supervisor configs
│   └── Dockerfile               # App container image
├── docs/                        # Documentation (this folder)
│   ├── ARCHITECTURE.md          # This file
│   ├── API.md                   # API documentation
│   ├── DATABASE.md              # Database schema
│   ├── DEPLOYMENT.md            # Deployment guide
│   └── CONTRIBUTING.md          # Contribution guide
├── public/                      # Public web root
│   ├── index.php                # Entry point
│   └── storage/                 # Symlink to storage/app/public
├── resources/                   # Frontend resources
│   ├── css/                     # Stylesheets
│   ├── js/                      # JavaScript
│   └── views/                   # Blade templates
├── routes/                      # Route definitions
│   ├── web.php                  # Web routes
│   ├── api.php                  # API routes (planned)
│   └── console.php              # Console routes
├── storage/                     # Application storage
│   ├── app/                     # Application files
│   ├── framework/               # Framework files
│   └── logs/                    # Application logs
├── tests/                       # Automated tests
│   ├── Feature/                 # Feature tests
│   └── Unit/                    # Unit tests
├── .env.example                 # Environment template
├── composer.json                # PHP dependencies
├── docker-compose.yml           # Docker orchestration
├── package.json                 # NPM dependencies
└── README.md                    # Project README
```

## Application Layers

### 1. Routes Layer

**Location**: `/routes/`

- **`web.php`**: Web application routes (Blade views)
- **`api.php`**: RESTful API routes (planned)
- **`console.php`**: Artisan command routes

**Responsibilities**:
- Define URL patterns
- Map URLs to controllers
- Apply route middleware
- Group related routes

### 2. Middleware Layer

**Location**: `/app/Http/Middleware/` (planned) and `/bootstrap/app.php`

**Common Middleware**:
- Authentication (`auth`, `auth:sanctum`)
- CSRF protection (`csrf`)
- CORS handling
- Rate limiting (`throttle`)
- Request validation
- Response transformation

**Configuration**: Bootstrap middleware in `/bootstrap/app.php`

### 3. Controller Layer

**Location**: `/app/Http/Controllers/`

**Structure**:
```
Controllers/
├── Api/                    # API controllers
│   └── V1/                # Version 1 API
│       ├── UserController.php
│       ├── BalanceController.php
│       └── TransactionController.php
└── Web/                    # Web controllers
    └── DashboardController.php
```

**Responsibilities**:
- Handle HTTP requests
- Validate input (via Form Requests)
- Delegate to services
- Return responses (via API Resources)

**Best Practices**:
- Keep controllers thin
- Use single responsibility principle
- Inject dependencies via constructor
- Return typed responses

### 4. Request Validation Layer

**Location**: `/app/Http/Requests/` (planned)

**Example**:
```php
class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
}
```

### 5. Business Logic Layer

**Location**: `/app/Services/` (planned)

**Purpose**: Complex business logic, orchestration, external API calls

**Example**:
```php
class BalanceService
{
    public function calculateBalance(User $user): float
    {
        // Complex business logic
    }
}
```

### 6. Data Access Layer

**Location**: `/app/Models/`

**Current Models**:
- `User.php`: User authentication and profile

**Planned Models**:
- `Account`: Financial accounts
- `Transaction`: Balance transactions
- `Category`: Transaction categories

**Responsibilities**:
- Database interactions via Eloquent ORM
- Define relationships
- Implement model scopes
- Handle events and observers

### 7. Resource Layer

**Location**: `/app/Http/Resources/` (planned)

**Purpose**: Transform models into JSON responses

**Example**:
```php
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
```

## Data Flow

### API Request Flow

```
1. Client sends HTTP request
   ↓
2. Nginx receives and forwards to PHP-FPM
   ↓
3. Laravel entry point (public/index.php)
   ↓
4. Route matching (routes/api.php)
   ↓
5. Middleware pipeline execution
   ↓
6. Controller method invocation
   ↓
7. Form Request validation
   ↓
8. Service layer processing
   ↓
9. Model/Repository data access
   ↓
10. Database query (PostgreSQL)
   ↓
11. Model hydration
   ↓
12. API Resource transformation
   ↓
13. JSON response
   ↓
14. Middleware post-processing
   ↓
15. Response sent to client
```

## Security Architecture

### Authentication

**Current**: Session-based (Laravel default)

**Planned**:
- **API**: Laravel Sanctum token-based authentication
- **Web**: Session-based with cookies

### Authorization

**Framework**: Laravel Gates and Policies

**Structure**:
```
app/Policies/
├── UserPolicy.php
├── AccountPolicy.php
└── TransactionPolicy.php
```

### Security Middleware

1. **CSRF Protection**: For web routes
2. **Rate Limiting**: Prevent abuse
3. **CORS**: Control cross-origin requests
4. **Input Sanitization**: XSS prevention
5. **SQL Injection Protection**: Via Eloquent ORM

### Environment Security

- Secrets in `.env` file (not committed)
- APP_KEY encryption
- Database credentials encrypted
- Session encryption enabled (planned)

## Caching Strategy

### Cache Layers

1. **Application Cache**: Redis
   - Configuration cache
   - Route cache
   - View cache

2. **Data Cache**: Redis
   - Query results
   - API responses
   - User sessions

3. **HTTP Cache**: Browser/CDN
   - Static assets (CSS, JS, images)
   - Public API responses

### Cache Keys Strategy

```
Pattern: {app}:{entity}:{id}:{attribute}

Examples:
- balance-flow:user:123:profile
- balance-flow:account:456:balance
- balance-flow:transactions:789:list
```

## Queue Architecture

### Queue Driver: Redis

**Queued Jobs**:
- Email notifications
- Report generation
- Data import/export
- External API calls

**Queue Configuration**:
```
Queues:
├── default (general jobs)
├── high (priority jobs)
├── low (background jobs)
└── emails (notification jobs)
```

**Worker Management**: Via Supervisor

## Performance Considerations

### Database Optimization

1. **Indexing**: Strategic indexes on frequently queried columns
2. **Eager Loading**: Prevent N+1 queries
3. **Query Optimization**: Use query builder efficiently
4. **Connection Pooling**: Managed by PostgreSQL

### Application Optimization

1. **Opcode Caching**: OPcache enabled
2. **Config Caching**: `php artisan config:cache`
3. **Route Caching**: `php artisan route:cache`
4. **View Caching**: Blade template compilation

### Asset Optimization

1. **Vite Build**: Minification and bundling
2. **Lazy Loading**: Images and components
3. **CDN**: Static asset delivery (production)

## Scalability Strategy

### Horizontal Scaling

```
Load Balancer
    ↓
┌───────┬───────┬───────┐
│ App 1 │ App 2 │ App 3 │  (Stateless Laravel instances)
└───┬───┴───┬───┴───┬───┘
    │       │       │
    └───────┴───────┘
         ↓
   Shared Services
   ├── PostgreSQL (Primary + Read Replicas)
   ├── Redis (Cluster)
   └── Storage (S3/Minio)
```

### Vertical Scaling

- Increase container resources (CPU, Memory)
- Optimize PHP-FPM worker processes
- PostgreSQL connection limits

## Monitoring & Logging

### Logging Strategy

**Channels**:
- `stack`: Multi-channel logging
- `single`: Development logging
- `daily`: Production daily logs
- `stderr`: Error logs

**Log Levels**: debug, info, notice, warning, error, critical

### Health Checks

**Endpoints** (planned):
- `/health`: Application health
- `/health/db`: Database connection
- `/health/redis`: Cache connection

### Metrics (planned)

- Request/response times
- Database query performance
- Cache hit ratios
- Queue processing times

## Development Workflow

### Local Development

```bash
# 1. Start Docker containers
docker compose up -d

# 2. Install dependencies
docker compose exec app composer install
docker compose exec app npm install

# 3. Setup environment
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate

# 4. Run migrations
docker compose exec app php artisan migrate

# 5. Build assets
docker compose exec app npm run dev
```

### Code Quality

1. **Linting**: Laravel Pint (`vendor/bin/pint`)
2. **Testing**: PHPUnit (`php artisan test`)
3. **Static Analysis**: (planned) Larastan/PHPStan

## Design Patterns Used

### Repository Pattern (Planned)

Abstraction layer between data access and business logic.

### Service Pattern

Encapsulate complex business logic in service classes.

### Factory Pattern

Model factories for testing and seeding.

### Observer Pattern

Model events and observers for side effects.

### Singleton Pattern

Service container bindings.

## API Versioning Strategy (Planned)

### URL Versioning

```
/api/v1/users
/api/v2/users
```

### Version Management

- Maintain backward compatibility for 2 versions
- Deprecation warnings in headers
- Documentation per version

## Deployment Architecture

### Environments

1. **Development**: Local Docker
2. **Staging**: Docker Swarm/Kubernetes (planned)
3. **Production**: Docker Swarm/Kubernetes (planned)

### CI/CD Pipeline (Planned)

```
1. Code Push → Git Repository
   ↓
2. CI Triggers (GitHub Actions / GitLab CI)
   ↓
3. Run Tests (PHPUnit)
   ↓
4. Code Quality Checks (Pint, PHPStan)
   ↓
5. Build Docker Image
   ↓
6. Push to Registry
   ↓
7. Deploy to Environment
   ↓
8. Health Check
   ↓
9. Notify Team
```

## Configuration Management

### Environment Variables

**Categories**:
1. **Application**: APP_NAME, APP_ENV, APP_DEBUG
2. **Database**: DB_CONNECTION, DB_HOST, DB_DATABASE
3. **Cache**: CACHE_STORE, REDIS_HOST
4. **Queue**: QUEUE_CONNECTION
5. **Mail**: MAIL_MAILER, MAIL_HOST
6. **External Services**: API keys, OAuth credentials

**Best Practice**: Never use `env()` outside config files.

## Dependencies Management

### PHP Dependencies (Composer)

**Production**:
- laravel/framework: ^12.0
- laravel/tinker: ^2.10

**Development**:
- laravel/pint: ^1.24
- phpunit/phpunit: ^11.5
- laravel/boost: ^1.5

### JavaScript Dependencies (NPM)

**Production**:
- alpinejs: ^3.14.8
- axios: ^1.8.0

**Development**:
- vite: ^6.0.0
- tailwindcss: ^3.4.17

## Future Architecture Enhancements

### Planned Improvements

1. **Microservices**: Split into domain-based services
2. **Event Sourcing**: Event-driven architecture
3. **CQRS**: Separate read/write models
4. **GraphQL**: Alternative to REST API
5. **WebSockets**: Real-time features (Laravel Echo)
6. **Service Mesh**: For microservices communication
7. **Message Queue**: RabbitMQ/Kafka for event streaming

### Technology Evaluations

- **Elasticsearch**: Full-text search
- **MinIO**: Self-hosted S3-compatible storage
- **Prometheus + Grafana**: Monitoring stack
- **Sentry**: Error tracking
- **New Relic/DataDog**: APM

## Conclusion

The Balance Flow Backend architecture is designed with scalability, maintainability, and security in mind. The current implementation provides a solid foundation for future enhancements while following Laravel and industry best practices.

---

**Document Version**: 1.0
**Last Updated**: 2025-11-13
**Maintainer**: Development Team

# Deployment Guide - Balance Flow Backend

## Overview

This guide covers deployment procedures for the Balance Flow Backend application across different environments: development, staging, and production.

**Deployment Methods**:
- Docker Compose (Development & Small Production)
- Docker Swarm (Production - Recommended)
- Kubernetes (Production - Enterprise)
- Traditional Server (VPS/Dedicated)

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Environment Setup](#environment-setup)
3. [Development Deployment](#development-deployment)
4. [Staging Deployment](#staging-deployment)
5. [Production Deployment](#production-deployment)
6. [SSL/TLS Configuration](#ssltls-configuration)
7. [Database Setup](#database-setup)
8. [Monitoring & Logging](#monitoring--logging)
9. [Backup Strategy](#backup-strategy)
10. [Rollback Procedures](#rollback-procedures)
11. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### System Requirements

**Minimum (Development)**:
- CPU: 2 cores
- RAM: 4 GB
- Storage: 20 GB
- Docker: 24.0+
- Docker Compose: 2.20+

**Recommended (Production)**:
- CPU: 4+ cores
- RAM: 8+ GB
- Storage: 100+ GB SSD
- Docker: 24.0+
- Docker Compose/Swarm: 2.20+

### Required Software

- **Docker**: Container runtime
- **Docker Compose**: Container orchestration
- **Git**: Version control
- **Make** (optional): Build automation

### Access Requirements

- SSH access to server
- Domain name (production)
- SSL certificate (production)
- Database credentials
- SMTP credentials (for emails)

---

## Environment Setup

### Environment Files

Create `.env` file from template:

```bash
cp .env.example .env
```

### Environment Variables

#### Application Settings

```env
APP_NAME="Balance Flow"
APP_ENV=production
APP_KEY=base64:GENERATE_THIS_WITH_php_artisan_key:generate
APP_DEBUG=false
APP_TIMEZONE=UTC
APP_URL=https://api.balanceflow.com
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US
APP_MAINTENANCE_DRIVER=file
```

#### Database Configuration

```env
DB_CONNECTION=pgsql
DB_HOST=postgres
DB_PORT=5432
DB_DATABASE=balance_flow
DB_USERNAME=balance_flow
DB_PASSWORD=STRONG_SECURE_PASSWORD_HERE
DB_CHARSET=utf8
DB_COLLATION=utf8_unicode_ci
```

#### Cache & Session

```env
CACHE_STORE=redis
CACHE_PREFIX=balance_flow_cache

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=.balanceflow.com
SESSION_SECURE_COOKIE=true
```

#### Queue Configuration

```env
QUEUE_CONNECTION=redis
```

#### Redis Configuration

```env
REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_PREFIX=balance_flow:
```

#### Mail Configuration (Production)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@balanceflow.com
MAIL_FROM_NAME="${APP_NAME}"
```

#### OAuth (Laravel Passport)

```env
PASSPORT_PRIVATE_KEY=
PASSPORT_PUBLIC_KEY=
```

#### Logging

```env
LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=warning
```

---

## Development Deployment

### Quick Start

```bash
# 1. Clone repository
git clone https://github.com/tuanldas/balance-flow-be.git
cd balance-flow-be

# 2. Setup environment
cp .env.example .env

# 3. Start Docker containers
docker compose up -d

# 4. Install dependencies
docker compose exec app composer install
docker compose exec app npm install

# 5. Generate application key
docker compose exec app php artisan key:generate

# 6. Run migrations
docker compose exec app php artisan migrate --seed

# 7. Install Passport
docker compose exec app php artisan passport:install

# 8. Build assets
docker compose exec app npm run dev
```

### Access Application

- **Web**: http://localhost
- **API**: http://localhost/api
- **Database**: localhost:5432
- **Redis**: localhost:6379

### Development Commands

```bash
# View logs
docker compose logs -f app

# Run tests
docker compose exec app php artisan test

# Code formatting
docker compose exec app vendor/bin/pint

# Clear cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
```

---

## Staging Deployment

### Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Add user to docker group
sudo usermod -aG docker $USER
```

### Deployment Process

```bash
# 1. Clone repository
git clone https://github.com/tuanldas/balance-flow-be.git
cd balance-flow-be

# 2. Checkout staging branch
git checkout staging

# 3. Create external volumes
docker volume create postgres_data
docker volume create redis_data

# 4. Setup environment
cp .env.staging .env
nano .env  # Edit configuration

# 5. Build and start services
docker compose -f docker-compose.staging.yml up -d --build

# 6. Setup application
docker compose exec app composer install --no-dev --optimize-autoloader
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --force
docker compose exec app php artisan passport:install --force
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache

# 7. Build production assets
docker compose exec app npm ci
docker compose exec app npm run build
```

---

## Production Deployment

### Option 1: Docker Compose (Simple)

**Best for**: Small to medium applications, single server

#### Production docker-compose.yml

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: docker/Dockerfile.prod
      target: production
    restart: always
    environment:
      - APP_ENV=production
    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
    networks:
      - app-network
    depends_on:
      - postgres
      - redis

  nginx:
    image: nginx:1.27-alpine
    restart: always
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./public:/var/www/html/public:ro
      - ./docker/configs/nginx/prod.conf:/etc/nginx/conf.d/default.conf:ro
      - ./ssl:/etc/nginx/ssl:ro
    networks:
      - app-network
    depends_on:
      - app

  postgres:
    image: postgres:17-alpine
    restart: always
    environment:
      POSTGRES_DB: ${DB_DATABASE}
      POSTGRES_USER: ${DB_USERNAME}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - postgres_data:/var/lib/postgresql/data
    networks:
      - app-network
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USERNAME}"]
      interval: 10s
      timeout: 5s
      retries: 5

  redis:
    image: redis:7-alpine
    restart: always
    command: redis-server --appendonly yes
    volumes:
      - redis_data:/data
    networks:
      - app-network
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 5s
      retries: 5

volumes:
  postgres_data:
    external: true
  redis_data:
    external: true

networks:
  app-network:
    driver: bridge
```

#### Production Dockerfile

Create `docker/Dockerfile.prod`:

```dockerfile
FROM php:8.4-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    postgresql-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    zip \
    pcntl \
    bcmath

# Install Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Production stage
FROM base AS production

# Copy application
COPY . .

# Install production dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Optimize Laravel
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

CMD ["php-fpm"]
```

#### Deploy to Production

```bash
# 1. Pull latest code
git pull origin main

# 2. Build production image
docker compose -f docker-compose.prod.yml build --no-cache

# 3. Start services with zero downtime
docker compose -f docker-compose.prod.yml up -d --remove-orphans

# 4. Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# 5. Clear and cache config
docker compose -f docker-compose.prod.yml exec app php artisan optimize

# 6. Restart queue workers
docker compose -f docker-compose.prod.yml restart app
```

### Option 2: Docker Swarm (Recommended)

**Best for**: High availability, load balancing, auto-scaling

#### Initialize Swarm

```bash
# On manager node
docker swarm init --advertise-addr <MANAGER-IP>

# On worker nodes (run the output command from above)
docker swarm join --token <TOKEN> <MANAGER-IP>:2377
```

#### Deploy Stack

```bash
# Deploy application stack
docker stack deploy -c docker-compose.prod.yml balance-flow

# List services
docker service ls

# View service logs
docker service logs -f balance-flow_app

# Scale service
docker service scale balance-flow_app=3
```

### Option 3: Kubernetes (Enterprise)

**Best for**: Large-scale applications, microservices

#### Deployment YAML

Create `k8s/deployment.yaml`:

```yaml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: balance-flow-app
  labels:
    app: balance-flow
spec:
  replicas: 3
  selector:
    matchLabels:
      app: balance-flow
  template:
    metadata:
      labels:
        app: balance-flow
    spec:
      containers:
      - name: app
        image: registry.example.com/balance-flow:latest
        ports:
        - containerPort: 9000
        env:
        - name: APP_ENV
          value: "production"
        - name: DB_PASSWORD
          valueFrom:
            secretKeyRef:
              name: db-secret
              key: password
        volumeMounts:
        - name: storage
          mountPath: /var/www/html/storage
      volumes:
      - name: storage
        persistentVolumeClaim:
          claimName: storage-pvc
---
apiVersion: v1
kind: Service
metadata:
  name: balance-flow-service
spec:
  selector:
    app: balance-flow
  ports:
  - protocol: TCP
    port: 80
    targetPort: 9000
  type: LoadBalancer
```

#### Deploy to Kubernetes

```bash
# Apply deployment
kubectl apply -f k8s/

# Check status
kubectl get pods
kubectl get services

# Scale deployment
kubectl scale deployment balance-flow-app --replicas=5

# View logs
kubectl logs -f deployment/balance-flow-app
```

---

## SSL/TLS Configuration

### Using Let's Encrypt (Certbot)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d api.balanceflow.com

# Auto-renewal (already configured)
sudo certbot renew --dry-run
```

### Nginx SSL Configuration

Create `docker/configs/nginx/ssl.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name api.balanceflow.com;

    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name api.balanceflow.com;

    # SSL configuration
    ssl_certificate /etc/nginx/ssl/fullchain.pem;
    ssl_certificate_key /etc/nginx/ssl/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    # Security headers
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;

    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Database Setup

### Production Database

#### Option 1: Self-Hosted PostgreSQL

```bash
# Create external volume
docker volume create postgres_data

# Run PostgreSQL container
docker run -d \
  --name postgres \
  --restart always \
  -e POSTGRES_DB=balance_flow \
  -e POSTGRES_USER=balance_flow \
  -e POSTGRES_PASSWORD=SECURE_PASSWORD \
  -v postgres_data:/var/lib/postgresql/data \
  -p 5432:5432 \
  postgres:17-alpine
```

#### Option 2: Managed Database (Recommended)

Use managed PostgreSQL services:
- **AWS RDS**: Automated backups, scaling
- **DigitalOcean Managed Database**: Simple, affordable
- **Google Cloud SQL**: Enterprise features
- **Azure Database for PostgreSQL**: Microsoft ecosystem

Update `.env`:
```env
DB_HOST=your-managed-db-host.com
DB_PORT=5432
DB_DATABASE=balance_flow
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_SSLMODE=require
```

### Database Migrations

```bash
# Run migrations (production)
docker compose exec app php artisan migrate --force

# Check migration status
docker compose exec app php artisan migrate:status

# Rollback (if needed)
docker compose exec app php artisan migrate:rollback --step=1
```

---

## Monitoring & Logging

### Application Logging

**Log Files**: `/storage/logs/laravel.log`

#### Centralized Logging (Recommended)

**Option 1: ELK Stack (Elasticsearch, Logstash, Kibana)**

```yaml
# Add to docker-compose.prod.yml
  elasticsearch:
    image: docker.elastic.co/elasticsearch/elasticsearch:8.11.0
    environment:
      - discovery.type=single-node
    volumes:
      - elasticsearch_data:/usr/share/elasticsearch/data

  logstash:
    image: docker.elastic.co/logstash/logstash:8.11.0
    volumes:
      - ./logstash.conf:/usr/share/logstash/pipeline/logstash.conf

  kibana:
    image: docker.elastic.co/kibana/kibana:8.11.0
    ports:
      - "5601:5601"
```

**Option 2: External Services**
- **Papertrail**: Simple log aggregation
- **Loggly**: Cloud-based logging
- **Datadog**: Full observability platform
- **Sentry**: Error tracking

### Performance Monitoring

#### Laravel Telescope (Development/Staging)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

#### Production APM

- **New Relic**: Application performance monitoring
- **Datadog APM**: Full-stack observability
- **Scout APM**: Laravel-focused monitoring
- **AppSignal**: Error tracking & performance

### Health Checks

Create `/health` endpoint:

```php
// routes/web.php
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        'cache' => Cache::get('health_check') !== null ? 'working' : 'failed',
        'timestamp' => now()->toIso8601String(),
    ]);
});
```

### Uptime Monitoring

Services:
- **UptimeRobot**: Free uptime monitoring
- **Pingdom**: Comprehensive monitoring
- **StatusCake**: Global monitoring
- **Better Uptime**: Modern uptime monitoring

---

## Backup Strategy

### Database Backups

#### Automated Backup Script

Create `/scripts/backup-db.sh`:

```bash
#!/bin/bash

# Configuration
BACKUP_DIR="/backups/database"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
DAYS_TO_KEEP=30
DB_CONTAINER="postgres"
DB_NAME="balance_flow"
DB_USER="balance_flow"

# Create backup directory
mkdir -p $BACKUP_DIR

# Perform backup
docker exec $DB_CONTAINER pg_dump -U $DB_USER $DB_NAME | gzip > "$BACKUP_DIR/backup_${TIMESTAMP}.sql.gz"

# Remove old backups
find $BACKUP_DIR -type f -name "backup_*.sql.gz" -mtime +$DAYS_TO_KEEP -delete

echo "Backup completed: backup_${TIMESTAMP}.sql.gz"
```

#### Cron Job

```bash
# Edit crontab
crontab -e

# Add daily backup at 2 AM
0 2 * * * /path/to/scripts/backup-db.sh >> /var/log/backup.log 2>&1
```

### File Storage Backups

```bash
# Backup storage directory
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Sync to remote storage (S3)
aws s3 sync storage/ s3://your-bucket/storage/ --delete
```

### Remote Backup Solutions

- **AWS S3**: Scalable object storage
- **Backblaze B2**: Affordable cloud storage
- **Restic**: Encrypted, incremental backups
- **Duplicati**: Free backup software

---

## Rollback Procedures

### Application Rollback

```bash
# 1. Identify previous stable version
git log --oneline

# 2. Checkout previous version
git checkout <previous-commit-hash>

# 3. Rebuild and restart
docker compose -f docker-compose.prod.yml up -d --build

# 4. Rollback migrations (if needed)
docker compose exec app php artisan migrate:rollback --step=1

# 5. Clear caches
docker compose exec app php artisan optimize:clear
```

### Database Rollback

```bash
# Restore from backup
docker exec -i postgres psql -U balance_flow balance_flow < backup.sql

# Or using Docker volume
docker run --rm \
  -v postgres_data:/var/lib/postgresql/data \
  -v $(pwd)/backups:/backups \
  postgres:17-alpine \
  psql -U balance_flow balance_flow < /backups/backup.sql
```

---

## Troubleshooting

### Common Issues

#### 1. Application Not Starting

```bash
# Check container logs
docker compose logs -f app

# Check container status
docker compose ps

# Restart containers
docker compose restart
```

#### 2. Database Connection Issues

```bash
# Test database connection
docker compose exec app php artisan tinker
>>> DB::connection()->getPdo();

# Check database container
docker compose exec postgres psql -U balance_flow -d balance_flow -c "SELECT 1"
```

#### 3. Permission Issues

```bash
# Fix storage permissions
docker compose exec app chown -R www-data:www-data storage bootstrap/cache
docker compose exec app chmod -R 775 storage bootstrap/cache
```

#### 4. High Memory Usage

```bash
# Check container stats
docker stats

# Optimize PHP-FPM (edit docker/configs/php/www.conf)
pm.max_children = 20
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 10
```

#### 5. Slow Performance

```bash
# Enable query logging
docker compose exec app php artisan tinker
>>> DB::enableQueryLog();

# Check cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
```

### Debug Mode

**WARNING**: Never enable debug mode in production

For urgent debugging:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```

Remember to disable after troubleshooting:
```env
APP_DEBUG=false
LOG_LEVEL=warning
```

---

## CI/CD Pipeline

### GitHub Actions Example

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
    - uses: actions/checkout@v3

    - name: Run tests
      run: |
        cp .env.example .env
        composer install
        php artisan key:generate
        php artisan test

    - name: Deploy to server
      uses: appleboy/ssh-action@master
      with:
        host: ${{ secrets.SERVER_HOST }}
        username: ${{ secrets.SERVER_USER }}
        key: ${{ secrets.SSH_PRIVATE_KEY }}
        script: |
          cd /var/www/balance-flow-be
          git pull origin main
          docker compose -f docker-compose.prod.yml up -d --build
          docker compose exec app php artisan migrate --force
          docker compose exec app php artisan optimize
```

---

## Security Checklist

- [ ] Enable HTTPS/SSL
- [ ] Set `APP_DEBUG=false`
- [ ] Use strong database passwords
- [ ] Configure firewall (UFW/iptables)
- [ ] Enable fail2ban
- [ ] Regular security updates
- [ ] Backup encryption
- [ ] Environment variables secured
- [ ] CORS configured properly
- [ ] Rate limiting enabled
- [ ] Security headers configured
- [ ] SQL injection prevention (via Eloquent)
- [ ] XSS protection
- [ ] CSRF protection

---

## Post-Deployment Checklist

- [ ] Application accessible via domain
- [ ] SSL certificate valid
- [ ] Database migrations applied
- [ ] Cron jobs configured
- [ ] Queue workers running
- [ ] Backups automated
- [ ] Monitoring configured
- [ ] Logs accessible
- [ ] Health checks passing
- [ ] Performance acceptable
- [ ] Error tracking enabled

---

**Deployment Version**: 1.0
**Last Updated**: 2025-11-13
**Maintainer**: DevOps Team

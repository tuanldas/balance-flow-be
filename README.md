# Docker Setup cho Balance Flow Backend

## Yêu cầu hệ thống
- Docker
- Docker Compose

## Cài đặt và chạy dự án

### 1. Clone và setup môi trường
```bash
# Clone repository
git clone <repository-url>
cd balance-flow-be

# Copy file environment cho Docker
cp .env.example .env

# Tạo key cho ứng dụng
php artisan key:generate
```

### 2. Chạy với Docker Compose
```bash
# Build và chạy containers
docker-compose up -d

# Hoặc build lại nếu có thay đổi
docker-compose up -d --build
```

### 3. Cài đặt dependencies
```bash
# Cài đặt Composer dependencies
docker-compose exec app composer install

# Cài đặt NPM dependencies (nếu cần)
docker-compose exec app npm install
```

### 4. Chạy migrations
```bash
# Chạy database migrations
docker-compose exec app php artisan migrate

# Chạy seeders (nếu có)
docker-compose exec app php artisan db:seed
```

## Truy cập ứng dụng

- **Ứng dụng**: http://localhost:80
- **Database**: localhost:5432
- **Redis**: localhost:6379
- **Mailpit**: http://localhost:8025

## Các lệnh hữu ích

### Laravel Commands
```bash
# Chạy artisan commands
docker-compose exec app php artisan <command>

# Ví dụ:
docker-compose exec app php artisan make:controller UserController
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
```

### Database
```bash
# Truy cập PostgreSQL
docker-compose exec pgsql psql -U sail -d balance_flow

# Backup database
docker-compose exec pgsql pg_dump -U sail balance_flow > backup.sql

# Restore database
docker-compose exec -T pgsql psql -U sail balance_flow < backup.sql
```

### Logs
```bash
# Xem logs của tất cả services
docker-compose logs

# Xem logs của service cụ thể
docker-compose logs app
docker-compose logs pgsql
docker-compose logs redis
```

### Debugging
```bash
# Truy cập container
docker-compose exec app bash

# Xem cấu hình PHP
docker-compose exec app php -i

# Test database connection
docker-compose exec app php artisan tinker
# Trong tinker: DB::connection()->getPdo();
```

## Cấu trúc Docker

### Services
- **app**: Laravel application với PHP 8.4
- **pgsql**: PostgreSQL 17 database
- **redis**: Redis cache
- **mailpit**: Email testing tool

### Volumes
- `sailpgsql`: PostgreSQL data
- `sailredis`: Redis data

### Networks
- `sail`: Bridge network cho tất cả services

## Troubleshooting

### Port conflicts
Nếu gặp lỗi port đã được sử dụng, thay đổi ports trong file `.env`:
```env
APP_PORT=8080
FORWARD_DB_PORT=5433
FORWARD_REDIS_PORT=6380
```

### Permission issues
```bash
# Fix permissions
sudo chown -R $USER:$USER .
```

### Rebuild containers
```bash
# Xóa containers và volumes
docker-compose down -v

# Build lại
docker-compose up -d --build
```

## Development

### Hot reload
Để enable hot reload cho development:
```bash
# Chạy Vite dev server
docker-compose exec app npm run dev
```

### Xdebug
Xdebug đã được cấu hình sẵn. Để sử dụng:
1. Cài đặt Xdebug extension trong IDE
2. Set breakpoints
3. Start debugging session

## Production

Để deploy production, cần:
1. Thay đổi `APP_ENV=production` trong `.env`
2. Set `APP_DEBUG=false`
3. Cấu hình database production
4. Setup SSL certificates
5. Cấu hình reverse proxy (nginx/apache)

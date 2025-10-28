# Multi-stage build: PHP-FPM + Nginx
FROM php:8.4-fpm-alpine AS app

LABEL maintainer="Balance Flow Team"

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    postgresql-dev \
    oniguruma-dev \
    libzip-dev \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/app

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application code
COPY . .

# Ensure required directories exist and set proper permissions
RUN mkdir -p /var/www/app/storage /var/www/app/bootstrap/cache \
    && chown -R www-data:www-data /var/www/app \
    && chmod -R 755 /var/www/app/storage \
    && chmod -R 755 /var/www/app/bootstrap/cache

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# PHP-FPM configuration
COPY docker/configs/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/configs/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

# Keep running as root for PHP-FPM (pool config runs as www-data)

EXPOSE 9000

CMD ["php-fpm"]

# Nginx stage
FROM nginx:alpine AS nginx

# Install wget for health checks
RUN apk add --no-cache wget

# Copy nginx configuration
COPY docker/configs/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/configs/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy public assets and necessary files
COPY --from=app /var/www/app/public /var/www/app/public
COPY --from=app /var/www/app/storage /var/www/app/storage
COPY --from=app /var/www/app/bootstrap /var/www/app/bootstrap

# Ensure cache directories exist with proper ownership
RUN mkdir -p /var/cache/nginx/client_temp && \
    chown -R nginx:nginx /var/cache/nginx

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]

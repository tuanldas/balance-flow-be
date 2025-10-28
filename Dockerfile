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

# Install PHP dependencies (include dev for local Docker environment)
RUN composer install --optimize-autoloader --no-scripts --no-interaction

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

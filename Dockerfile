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
    zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy application code
COPY . .

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Generate optimized autoloader
RUN composer dump-autoload --optimize

# PHP-FPM configuration
COPY docker/configs/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/configs/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini

EXPOSE 9000

CMD ["php-fpm"]

# Nginx stage
FROM nginx:alpine AS nginx

# Copy nginx configuration
COPY docker/configs/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/configs/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy public assets (if any)
COPY --from=app /var/www/html/public /var/www/html/public

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]

# =============================================================================
# Base Stage - Common dependencies for all environments
# =============================================================================
FROM php:8.2-fpm-alpine AS base

# Set working directory
WORKDIR /var/www/app

# Install system dependencies including bash, zsh and supervisor
RUN apk add --no-cache \
    bash \
    zsh \
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
    npm \
    supervisor

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    xml \
    pcntl \
    bcmath \
    gd \
    zip

# Install Redis extension
RUN apk add --no-cache pcre-dev $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del pcre-dev $PHPIZE_DEPS

# Get Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create necessary directories
RUN mkdir -p /var/log/supervisor /etc/supervisor/conf.d /usr/local/etc/php/conf.d

# Copy supervisor default configurations
COPY docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/supervisor/conf.d/*.conf /etc/supervisor/conf.d/

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# =============================================================================
# Development Stage - For local development and testing
# =============================================================================
FROM base AS development

# Set development environment
ENV APP_ENV=local

# Start supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

# =============================================================================
# Production Build Stage - Build assets
# =============================================================================
FROM base AS production-build

# Copy application files
COPY . /var/www/app

# Install Composer dependencies (production only, optimized)
RUN composer install \
    --no-interaction \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# Install Node dependencies and build assets
RUN npm install && \
    npm run build && \
    rm -rf node_modules

# =============================================================================
# Production Stage - Final production image
# =============================================================================
FROM base AS production

# Set production environment
ENV APP_ENV=production

# Copy application files from build stage
COPY --from=production-build /var/www/app /var/www/app

# Install production dependencies only
RUN cd /var/www/app && \
    composer install \
    --no-interaction \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --classmap-authoritative

# Set permissions
RUN chown -R www-data:www-data /var/www/app \
    && chmod -R 755 /var/www/app/storage \
    && chmod -R 755 /var/www/app/bootstrap/cache

# Start supervisord
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

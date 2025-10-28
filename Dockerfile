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
    npm \
    bash \
    zsh \
    supervisor

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

# Supervisor configuration
COPY docker/configs/supervisor/supervisord.conf /etc/supervisord.conf
RUN mkdir -p /var/log/supervisor

# Optional: install Oh My Zsh for a nicer shell experience
RUN set -eux; \
    if [ ! -d "/root/.oh-my-zsh" ]; then \
      apk add --no-cache ca-certificates; \
      update-ca-certificates; \
      curl -fsSL https://raw.githubusercontent.com/ohmyzsh/ohmyzsh/master/tools/install.sh -o /tmp/install-ohmyzsh.sh; \
      CHSH=no RUNZSH=no KEEP_ZSHRC=yes sh /tmp/install-ohmyzsh.sh --unattended; \
      rm -f /tmp/install-ohmyzsh.sh; \
    fi; \
    { echo 'export ZSH="/root/.oh-my-zsh"'; echo 'ZSH_THEME="frisk"'; echo 'plugins=(git)'; echo 'source $ZSH/oh-my-zsh.sh'; } >> /root/.zshrc

CMD ["supervisord", "-c", "/etc/supervisord.conf", "-n"]

# Multi-stage Dockerfile for YokaKit
# Optimized production stage with minimal dependencies

ARG ENVIRONMENT=production
ARG PHP_VERSION=8.2.27

# Base stage with common system dependencies and PHP extensions
FROM php:${PHP_VERSION}-apache AS base

# Install build dependencies and tools needed for compilation
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt \
    apt-get update && apt-get install -y \
    build-essential \
    curl \
    libfreetype6 \
    libfreetype6-dev \
    libjpeg62-turbo \
    libjpeg62-turbo-dev \
    libmariadb-dev \
    libmariadb-dev-compat \
    libmariadb3 \
    libonig-dev \
    libonig5 \
    libpng-dev \
    libpng16-16 \
    libwebp-dev \
    libwebp7 \
    libzip-dev \
    libzip4 \
    locales \
    pkg-config \
    unzip \
    zip

# Install PHP extensions
RUN set -ex; \
    docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp; \
    docker-php-ext-install -j$(nproc) gd mbstring pdo_mysql exif pcntl bcmath zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js for asset compilation
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    # Enable Apache rewrite module
    && a2enmod rewrite

WORKDIR /var/www/html

# Build stage - extends base with dependency installation and asset building
FROM base AS builder

# Copy dependency files first for better layer caching
COPY app/laravel/composer.json app/laravel/composer.lock ./
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies without scripts (cached layer if composer files unchanged)
RUN composer install --no-dev --optimize-autoloader --no-cache --no-scripts

# Copy package files and install npm dependencies
COPY app/laravel/package*.json ./
RUN npm install

# Copy source code and build assets
COPY app/laravel .
RUN npm run production \
    && composer dump-autoload --optimize \
    && rm -rf node_modules

# Production runtime stage - minimal dependencies
FROM base AS production

WORKDIR /var/www/html

# Copy built application from builder stage with proper ownership
COPY --from=builder --chown=www-data:www-data /var/www/html .

# Copy Apache configuration
COPY docker/app/apache/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy environment file
COPY .env /var/www/html/.env

# Set proper permissions for specific directories
RUN chmod -R 775 storage bootstrap/cache \
    # Generate application key
    && php artisan key:generate --force

# Copy and set up startup script
COPY app/laravel/app-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/app-entrypoint.sh

# Use startup script
CMD ["/usr/local/bin/docker-entrypoint.sh"]

EXPOSE 80

# Development stage - extends base with dev tools and dev dependencies
FROM base AS development

# Install development tools
RUN --mount=type=cache,target=/var/cache/apt \
    --mount=type=cache,target=/var/lib/apt \
    apt-get update && apt-get install -y \
    gifsicle \
    git \
    jpegoptim \
    optipng \
    pngquant \
    vim

# Install PCOV for code coverage
RUN pecl install pcov && docker-php-ext-enable pcov

# Copy dependency files and install with dev dependencies
COPY app/laravel/composer.json app/laravel/composer.lock ./
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --optimize-autoloader --no-cache --no-scripts

# Copy package files and install npm dependencies
COPY app/laravel/package*.json ./
RUN npm install

# Copy source code
COPY app/laravel .
RUN composer dump-autoload --optimize

# Copy development PHP configuration
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Development permissions (more permissive)
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 80

# Final stage selector
FROM ${ENVIRONMENT} AS final

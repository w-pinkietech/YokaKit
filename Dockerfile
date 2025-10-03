# Multi-stage Dockerfile for YokaKit
# Consolidates base and app stages with environment switching

ARG ENVIRONMENT=production
ARG PHP_VERSION=8.2.27

# Base stage with common dependencies
FROM php:${PHP_VERSION}-apache AS base

# パッケージインストールを1つのレイヤーに統合
RUN apt-get update && apt-get install -y \
    build-essential \
    curl \
    gifsicle \
    git \
    jpegoptim \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmariadb-dev \
    libmariadb-dev-compat \
    libonig-dev \
    libpng-dev \
    libwebp-dev \
    libzip-dev \
    locales \
    optipng \
    pkg-config \
    pngquant \
    unzip \
    vim \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# PHP拡張を1つのRUNコマンドに統合
RUN set -ex; \
    # mbstring拡張
    CFLAGS="-O0" docker-php-ext-configure mbstring; \
    docker-php-ext-install -j1 mbstring; \
    # gd拡張
    CFLAGS="-O1" docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg \
    --with-webp; \
    docker-php-ext-install -j1 gd; \
    # その他の拡張
    docker-php-ext-install pdo_mysql exif pcntl bcmath zip; \
    # PCOV for code coverage
    pecl install pcov; \
    docker-php-ext-enable pcov;

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Node.jsとnpmのインストールを統合
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

WORKDIR /var/www/html

# Development stage
FROM base AS development

# 依存関係ファイルのコピーを統合
COPY app/laravel/composer.* app/laravel/package*.json ./

# アプリケーションコードのコピー（依存関係インストール前に実行）
COPY app/laravel .

# Development dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -ex \
    && composer install --optimize-autoloader --no-cache \
    && npm install \
    && composer dump-autoload --optimize

# Copy development configuration
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Development permissions (less restrictive)
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 80

# Production stage
FROM base AS production

# 依存関係ファイルのコピーを統合
COPY app/laravel/composer.* app/laravel/package*.json ./

# アプリケーションコードのコピー（依存関係インストール前に実行）
COPY app/laravel .

# Production dependencies (install after copying app code to avoid artisan errors)
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN set -ex \
    && composer install --no-dev --optimize-autoloader --no-cache \
    && npm install \
    && composer dump-autoload --optimize

# Copy Apache configuration file
COPY docker/app/apache/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy .env file
COPY .env /var/www/html/.env

# Run npm production
RUN npm run production

# Set permissions for storage and bootstrap/cache directories
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Generate application key
RUN php artisan key:generate --force

# Add startup script
COPY app/laravel/app-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/app-entrypoint.sh

# Change CMD to use the startup script
CMD ["/usr/local/bin/docker-entrypoint.sh"]

EXPOSE 80

# Final stage - choose environment
FROM ${ENVIRONMENT} AS final
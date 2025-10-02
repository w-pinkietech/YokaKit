# Use the official PHP image with Apache as the base image
FROM php:8.2.11-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libonig-dev \
    libmariadb-dev-compat \
    libmariadb-dev \
    libzip-dev \
    pkg-config \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mysqli mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Node.js 20.x
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy the rest of the application code
COPY ./app/laravel .

# Copy composer files
# COPY ./app/laravel/composer.json ./app/laravel/composer.lock ./

# Composerにroot権限での実行を許可
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-autoloader

# Copy package.json and package-lock.json
# COPY ./app/laravel/package.json ./app/laravel/package-lock.json ./

# Install npm dependencies
#RUN npm ci --only=production
RUN npm install

# Generate autoloader
RUN composer dump-autoload --optimize

# Expose port 80
EXPOSE 80

# Copy Apache configuration file
COPY ./docker/app/apache/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy .env file (if not exists, use .env.example)
COPY .env* /var/www/html/
RUN if [ ! -f /var/www/html/.env ]; then cp /var/www/html/.env.example /var/www/html/.env; fi

# Run npm production
RUN npm run production

# Set permissions for storage and bootstrap/cache directories
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Generate application key
RUN php artisan key:generate --force

# Start Apache server
CMD ["apache2-foreground"]

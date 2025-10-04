#!/bin/bash
set -e

echo "Setting up YokaKit DevContainer environment..."

# Navigate to Laravel directory (we're already in /var/www/html)
cd /var/www/html

# Install PHP dependencies
echo "Installing Composer dependencies..."
composer install --no-interaction --optimize-autoloader

# Install Node.js dependencies
echo "Installing npm dependencies..."
npm install

# Setup environment file
echo "Setting up environment configuration..."
if [ ! -f .env ]; then
    cp /workspace/.env .env
fi

# Set proper permissions (script runs as www-data)
echo "Setting up file permissions..."
# Permissions already set correctly by Dockerfile and user mapping
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Generate application key if not exists
echo "Generating application key..."
php artisan key:generate --force

# Clear and cache configurations
echo "Optimizing Laravel application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Build frontend assets (one-off build, not watch mode)
echo "Building frontend assets..."
npm run production

echo "DevContainer setup completed successfully!"
echo "You can now start developing with YokaKit."
echo "Application available at http://localhost:18081"

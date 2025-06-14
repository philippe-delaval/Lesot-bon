#!/bin/bash

# Laravel Cloud Deployment Script
echo "🚀 Starting Laravel Cloud deployment..."

# Set error handling
set -e

# Install Composer dependencies
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Install NPM dependencies and build assets
echo "🎨 Building frontend assets..."
if [ -f "package-lock.json" ]; then
    npm ci --only=production
else
    npm install --only=production
fi

# Build Vite assets
echo "🏗️ Building Vite assets..."
npm run build

# Verify build output
echo "🔍 Verifying build output..."
if [ ! -f "public/build/manifest.json" ]; then
    echo "❌ Error: Vite manifest not found after build!"
    echo "📁 Contents of public/build/:"
    ls -la public/build/ || echo "Build directory does not exist"
    exit 1
fi

echo "✅ Vite manifest found: $(wc -l < public/build/manifest.json) lines"

# Generate application key if not set
echo "🔑 Generating application key..."
php artisan key:generate --force

# Run migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Clear and cache config
echo "🧹 Clearing and caching configuration..."
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan optimize

# Set proper permissions (for non-Laravel Cloud environments)
if [ "$LARAVEL_CLOUD" != "true" ]; then
    echo "🔐 Setting permissions..."
    chmod -R 755 storage bootstrap/cache
    if command -v chown &> /dev/null; then
        chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
    fi
fi

echo "✅ Deployment completed successfully!"
echo "📊 Final verification:"
echo "   - Manifest: $(ls -la public/build/manifest.json 2>/dev/null || echo 'NOT FOUND')"
echo "   - Assets: $(ls public/build/assets/ 2>/dev/null | wc -l || echo '0') files"
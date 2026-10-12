#!/bin/bash
set -e

echo "🚀 MaisuApp — Starting deployment..."

# Wait for database to be available (Render MySQL add-on)
echo "⏳ Waiting for database connection..."
max_tries=30
count=0
until php artisan db:monitor --databases=mysql 2>/dev/null || [ $count -ge $max_tries ]; do
    sleep 2
    count=$((count + 1))
    echo "  Attempt $count/$max_tries..."
done

# Run Laravel setup commands
echo "🔧 Clearing and caching config..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run migrations (safe with --force in production)
echo "📦 Running database migrations..."
php artisan migrate --force || echo "⚠️  Migration skipped (database may not be ready yet)"

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link || true

echo "✅ MaisuApp is ready!"

# Start supervisor (runs Nginx + PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

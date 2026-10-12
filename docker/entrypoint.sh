#!/bin/bash
set -e

echo "🚀 MaisuApp — Starting deployment..."

# Wait for database to be available
echo "⏳ Waiting for database connection..."
max_tries=30
count=0
until php -r "try { new PDO('mysql:host='.getenv('DB_HOST').';port='.(getenv('DB_PORT')?:'3306').';dbname='.getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Exception \$e) { exit(1); }" 2>/dev/null || [ $count -ge $max_tries ]; do
    sleep 2
    count=$((count + 1))
    echo "  Attempt $count/$max_tries..."
done

# Run Laravel setup commands
echo "🔧 Configuring Laravel caches..."
php artisan config:clear || true
php artisan cache:clear || true
php artisan config:cache || true
php artisan route:cache || (echo "⚠️ Route cache skipped, running dynamic routes" && php artisan route:clear || true)
php artisan view:cache || true

# Run migrations (safe with --force in production)
echo "📦 Running database migrations..."
php artisan migrate --force || echo "⚠️  Migration skipped"

# Create storage link
echo "🔗 Creating storage link..."
rm -f public/storage 2>/dev/null || true
php artisan storage:link || true

# Configure Nginx port dynamically
APP_PORT=${PORT:-10000}
echo "🌐 Configuring Nginx to listen on port ${APP_PORT}..."
sed -i "s/__PORT__/${APP_PORT}/g" /etc/nginx/http.d/default.conf

echo "✅ MaisuApp is ready!"

# Start supervisor (runs Nginx + PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

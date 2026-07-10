#!/bin/sh
set -e

echo "🔧 Running Laravel setup..."

# Create .env from environment if it doesn't exist (key:generate needs the file)
if [ ! -f .env ]; then
    echo "   Creating .env from environment..."
    env | grep -E '^(APP_|DB_|SESSION_|CACHE_|QUEUE_|LOG_|BROADCAST_|FILESYSTEM_|FRONTEND_URL|SANCTUM_|APP_URL|APP_NAME|APP_ENV|APP_DEBUG|APP_KEY|APP_LOCALE|APP_FALLBACK|APP_FAKER|APP_MAINTENANCE|BCRYPT_ROUNDS)' | sed 's/=/=/' > .env
fi

# Generate app key if not set
if [ -z "$APP_KEY" ]; then
    echo "   Generating application key..."
    php artisan key:generate --force
fi

# Create SQLite database if it doesn't exist
touch database/database.sqlite

# Run migrations
echo "   Running migrations..."
php artisan migrate --force

# Seed only if the users table is empty (seeder is not idempotent)
USER_COUNT=$(php artisan tinker --execute="echo \App\Models\User::count();" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "   Seeding database..."
    php artisan db:seed --force
else
    echo "   Database already seeded, skipping."
fi

# Cache config and routes
echo "   Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache 2>/dev/null || true

echo "   Setup complete."

# Start the server
exec php artisan serve --host=0.0.0.0 --port=8000

#!/bin/sh
set -eu

# --- Wait for the database to be reachable before running artisan commands ---
echo "[entrypoint] Waiting for database at ${DB_HOST:-db}:${DB_PORT:-3306}..."
tries=0
until php -r 'exit(@fsockopen(getenv("DB_HOST") ?: "db", (int)(getenv("DB_PORT") ?: 3306), $e, $s, 2) ? 0 : 1);' 2>/dev/null; do
    tries=$((tries + 1))
    if [ "$tries" -ge 60 ]; then
        echo "[entrypoint] Database not reachable after 120s; continuing anyway."
        break
    fi
    sleep 2
done
echo "[entrypoint] Database is reachable (or timeout reached)."

# --- Ensure required runtime directories exist and are writable ---
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache

# --- Laravel bootstrap ---
php artisan storage:link || true

# Apply any pending migrations (safe no-op when the imported dump is already current).
php artisan migrate --force || true

# Cache configuration/routes/views for performance. Falls back gracefully if config is missing.
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "[entrypoint] Starting Apache..."
exec apache2-foreground


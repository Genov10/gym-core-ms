#!/bin/sh
set -eu

cd /var/www/html

# Vite "npm run dev" creates public/hot with e.g. http://localhost:5174 — @vite() then
# points browsers at the dev server. On a VPS that breaks CSS/JS (requests go to user's PC).
if [ "${APP_ENV:-local}" = "production" ]; then
  rm -f public/hot 2>/dev/null || true
fi

# Install PHP deps into container volume if missing.
if [ ! -f vendor/autoload.php ]; then
  echo "vendor/autoload.php not found; running composer install..."
  composer install --no-interaction --prefer-dist
fi

# Ensure Laravel runtime dirs exist and are writable (dev-friendly on bind mounts).
mkdir -p \
  storage/app \
  storage/app/private \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/testing \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

# Prefer permissive perms for local Docker on Windows/macOS bind mounts.
chmod -R a+rwX storage bootstrap/cache || true

# Make sure system temp is writable (PHP uses it for tempnam()).
chmod 1777 /tmp 2>/dev/null || true

exec "$@"


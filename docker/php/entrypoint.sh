#!/bin/sh
set -eu

cd /var/www/html

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


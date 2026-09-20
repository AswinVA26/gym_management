#!/bin/sh
# Vercel container startup for GymHub.
# Runs as www-data. Every step logs to stderr so the Vercel runtime log shows
# exactly what happened. Where possible, failures are non-fatal: the web server
# still starts so the health check (/up) and runtime logs stay available.

set -eu

echo "[vercel] starting GymHub container" >&2

mkdir -p database/tenants storage/logs bootstrap/cache
touch database/database.sqlite

# Laravel encrypts cookies on every request, so a missing APP_KEY makes every
# page throw a 500. Generate one as a fallback for this instance (cookies will
# only stay valid while that single instance is alive), and warn clearly.
if [ -z "${APP_KEY:-}" ]; then
    KEY="$(php artisan key:generate --show --no-interaction 2>/dev/null || true)"
    if [ -n "$KEY" ]; then
        export APP_KEY="$KEY"
        echo "[vercel] WARNING: APP_KEY was not set in the environment." >&2
        echo "[vercel] Generated a temporary key for this instance: $KEY" >&2
        echo "[vercel] Add a stable APP_KEY to the Vercel project so sessions" >&2
        echo "[vercel] survive restarts and multiple instances." >&2
    else
        echo "[vercel] ERROR: APP_KEY missing and could not be generated." >&2
        echo "[vercel] Add APP_KEY to the project -> Settings ->" >&2
        echo "[vercel] Environment Variables, then redeploy." >&2
    fi
fi

echo "[vercel] running migrations" >&2
php artisan migrate --force --no-interaction \
    || echo "[vercel] migrate failed; continuing so the server stays up" >&2

echo "[vercel] seeding platform super admin" >&2
php artisan db:seed --force --no-interaction \
    || echo "[vercel] seed failed; continuing so the server stays up" >&2

echo "[vercel] starting FrankenPHP on PORT=${PORT:-80}" >&2
exec frankenphp run --config /etc/frankenphp/Caddyfile
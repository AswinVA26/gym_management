# ---------------------------------------------------------------------------
# Stage 1: Composer production dependencies
# ---------------------------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

# ---------------------------------------------------------------------------
# Stage 2: Frontend assets (Vite)
# ---------------------------------------------------------------------------
FROM node:20-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ---------------------------------------------------------------------------
# Stage 3: Runtime
# ---------------------------------------------------------------------------
FROM php:8.3-cli AS runtime
WORKDIR /var/www/html

# pdo_sqlite + sqlite3 are compiled into the official PHP image by default.

COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build
COPY . .

# Ensure runtime-writable directories exist (SQLite tenant DBs live here).
RUN mkdir -p database/tenants storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

EXPOSE 8080

CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}"]
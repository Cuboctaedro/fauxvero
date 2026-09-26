# Production image: nginx + PHP-FPM (serversideup/php), listening on port 8080.
# The same image runs the web app and the queue worker (see docker-compose.yml).

############################################
# Base: PHP with the extensions the app needs
############################################
FROM serversideup/php:8.4-fpm-nginx AS base

USER root
# intl: Filament · gd + exif: media library image conversions · bcmath: money math
RUN install-php-extensions intl gd exif bcmath
USER www-data

WORKDIR /var/www/html

############################################
# Composer dependencies
############################################
FROM base AS vendor

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader

############################################
# Frontend assets
############################################
FROM node:24-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY vite.config.js ./
COPY resources ./resources
# Tailwind scans the pagination views in vendor for class names.
COPY --from=vendor /var/www/html/vendor ./vendor
RUN npm run build

############################################
# Final image
############################################
FROM base

COPY --chown=www-data:www-data --from=vendor /var/www/html/vendor ./vendor
COPY --chown=www-data:www-data . .
COPY --chown=www-data:www-data --from=assets /app/public/build ./public/build

# Scripts are skipped: filament:upgrade would clear caches, and Filament's
# public assets are already committed.
RUN composer dump-autoload --no-dev --optimize --no-scripts \
    && php artisan package:discover --ansi

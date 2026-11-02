#!/bin/sh
# Caches the configuration and routes at start, since the settings come from
# the container's environment
set -e

php artisan config:cache
php artisan route:cache
chown -R www-data:www-data bootstrap/cache

exec docker-php-entrypoint "$@"

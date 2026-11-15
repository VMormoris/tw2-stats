#!/bin/sh
# Before PHP-FPM starts, caches the configuration and routes from the
# container's environment into APP_CONFIG_CACHE and APP_ROUTES_CACHE, since
# the code itself is read-only. Other commands, e.g. artisan in the cli
# service, read the environment directly.
set -e

if [ "$1" = php-fpm ]; then
    php artisan config:cache
    php artisan route:cache
fi

exec docker-php-entrypoint "$@"

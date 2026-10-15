#!/bin/sh
set -e

if [ ! -f vendor/autoload.php ] && [ -f /opt/vendor/autoload.php ]; then
    mkdir -p vendor
    cp -a /opt/vendor/. vendor/
    composer dump-autoload --no-interaction
fi

if [ "${RUN_MIGRATIONS:-1}" = "1" ]; then
    php artisan migrate --force
fi

exec "$@"

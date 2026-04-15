#!/bin/sh
set -e

mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

if [ ! -f vendor/autoload.php ]; then
    echo "vendor/ missing — running composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
    chown -R www-data:www-data vendor 2>/dev/null || true
fi

exec docker-php-entrypoint "$@"

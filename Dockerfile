FROM php:8.4-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    unzip \
    zip \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        pcntl \
        bcmath \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY --chown=www-data:www-data . .

# .env tidak ikut build context (lihat .dockerignore); pakai contoh untuk artisan saat build.
RUN cp .env.example .env \
    && php artisan key:generate --force \
    && composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && rm -f .env \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/php/docker-entrypoint.sh /usr/local/bin/docker-laravel-entrypoint
RUN chmod +x /usr/local/bin/docker-laravel-entrypoint

ENTRYPOINT ["/usr/local/bin/docker-laravel-entrypoint"]
CMD ["php-fpm"]

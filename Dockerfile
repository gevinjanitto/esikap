FROM dunglas/frankenphp:1-php8.3

RUN install-php-extensions pdo_mysql mbstring gd zip intl bcmath exif opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY esakip/ ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction --prefer-dist \
    && php artisan package:discover --ansi \
    && mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production \
    LOG_CHANNEL=stderr

EXPOSE 8080

CMD ["sh", "-c", "mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs && php artisan optimize && exec frankenphp php-server --root public --listen 0.0.0.0:${PORT:-8080}"]

# ─── Laravel 13 API Backend ───────────────────────────────────

FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    sqlite \
    sqlite-dev \
    bash \
    && docker-php-ext-install pdo pdo_sqlite

WORKDIR /var/www

COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --no-interaction --optimize-autoloader

RUN chmod +x entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["./entrypoint.sh"]

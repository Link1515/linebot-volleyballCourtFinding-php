FROM composer:2 AS deps
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

FROM php:8.2-fpm AS app
WORKDIR /var/www/html
COPY --from=deps /app/vendor ./vendor
COPY . .
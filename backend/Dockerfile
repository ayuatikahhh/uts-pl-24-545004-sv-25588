FROM php:8.5-cli-alpine

# ekstensi PHP yang dibutuhkan Laravel + MySQL
RUN docker-php-ext-install pdo_mysql bcmath

# Composer disalin dari image resmi (versi dipatok, bukan latest)
COPY --from=composer:2.9 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# 1) daftar dependensi DULU -> layer ini hanya diulang kalau composer.* berubah
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# 2) baru kodenya
COPY . .
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --optimize --no-dev --no-scripts

# php -S bawaan hanya satu proses; beberapa worker supaya tidak antre.
# Laravel hanya menghormati variabel ini bila dipasang --no-reload.
ENV PHP_CLI_SERVER_WORKERS=4

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
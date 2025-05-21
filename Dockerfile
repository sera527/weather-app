FROM php:8.4-fpm-alpine AS base

WORKDIR /var/www

RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    curl \
    unzip \
    zip \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    freetype-dev \
    mysql-client \
    dcron \
    build-base

RUN docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql mbstring exif pcntl bcmath zip opcache fileinfo intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY ./docker-compose/nginx/default.conf /etc/nginx/http.d/default.conf

COPY ./docker-compose/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY ./docker-compose/cron/laravel-cron /etc/crontabs/root
RUN chmod 0644 /etc/crontabs/root

COPY . /var/www

RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

COPY ./docker-compose/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

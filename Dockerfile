FROM php:8.4-fpm-alpine AS base

# Встановлюємо робочу директорію
WORKDIR /var/www

# Встановлюємо системні залежності
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

# Встановлюємо розширення PHP, необхідні для Laravel
RUN docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql mbstring exif pcntl bcmath zip opcache fileinfo intl

# Встановлюємо Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Копіюємо конфігурацію Nginx
COPY ./docker-compose/nginx/default.conf /etc/nginx/http.d/default.conf

# Копіюємо конфігурацію Supervisor
COPY ./docker-compose/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Копіюємо crontab
COPY ./docker-compose/cron/laravel-cron /etc/crontabs/root
RUN chmod 0644 /etc/crontabs/root

# Копіюємо файли застосунку
COPY . /var/www

# Встановлюємо права доступу для Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Відкриваємо порт 80 для Nginx
EXPOSE 80

# Копіюємо та робимо виконуваним скрипт запуску
COPY ./docker-compose/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
# Запускаємо Supervisor, який керуватиме Nginx, PHP-FPM та Cron
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

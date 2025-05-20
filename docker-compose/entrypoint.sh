#!/bin/sh
set -e

# Створюємо .env, якщо його немає, та генеруємо ключ
if [ ! -f ".env" ]; then
    echo "Creating .env file from .env.example"
    cp .env.example .env
    php artisan key:generate --ansi
fi

# Генеруємо ключ, якщо він порожній в .env
if grep -q '^APP_KEY=$' .env || ! grep -q '^APP_KEY=' .env ; then
    echo "APP_KEY is empty or not set, generating new one..."
    php artisan key:generate --ansi --force
fi

# Встановлюємо залежності Composer, якщо директорія vendor не існує
if [ ! -d "vendor" ]; then
    echo "Vendor directory not found. Running composer install..."
    composer install --no-progress --no-interaction
else
    echo "Vendor directory found. Skipping composer install."
fi

# Даємо час базі даних запуститися (опціонально, але може бути корисним)
# sleep 10

# Запускаємо міграції
echo "Running database migrations..."
php artisan migrate --force

# Виконуємо CMD з Dockerfile (запуск Supervisor)
exec "$@"

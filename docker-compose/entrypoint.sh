#!/bin/sh
set -e

if [ ! -f ".env" ]; then
    echo "Creating .env file from .env.example"
    cp .env.example .env
    php artisan key:generate --ansi
fi

if grep -q '^APP_KEY=$' .env || ! grep -q '^APP_KEY=' .env ; then
    echo "APP_KEY is empty or not set, generating new one..."
    php artisan key:generate --ansi --force
fi

if [ ! -d "vendor" ]; then
    echo "Vendor directory not found. Running composer install..."
    composer install --no-progress --no-interaction
else
    echo "Vendor directory found. Skipping composer install."
fi

echo "Running database migrations..."
php artisan migrate --force

exec "$@"

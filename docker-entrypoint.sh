#!/bin/sh
set -e

echo "Running Laravel initialization..."

# Composer scripts를 건너뛰었으므로 package discovery 실행
php artisan package:discover --ansi

php artisan config:clear
php artisan config:cache

php artisan view:cache
php artisan event:cache

php artisan route:clear
php artisan route:cache

php artisan migrate --force

exec "$@"

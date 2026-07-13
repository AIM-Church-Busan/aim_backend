FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev zip unzip nginx \
    libonig-dev libxml2-dev libgd-dev \
    && docker-php-ext-install pdo pdo_pgsql zip mbstring dom xml bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --ignore-platform-reqs

RUN php artisan config:cache && php artisan route:cache

EXPOSE 10000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]

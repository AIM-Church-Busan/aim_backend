FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git curl libpq-dev libzip-dev zip unzip nginx \
    libonig-dev libxml2-dev libgd-dev \
    && docker-php-ext-install pdo pdo_pgsql zip mbstring dom xml bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

# config:cache, route:cache를 여기서 빼고 entrypoint로 이동

COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]

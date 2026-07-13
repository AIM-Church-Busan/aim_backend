FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    libonig-dev \
    libxml2-dev \
    libgd-dev \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        mbstring \
        dom \
        xml \
        bcmath \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Composer 캐시 활용을 위해 먼저 복사
COPY composer.json composer.lock ./

# Composer 스크립트 실행하지 않음
RUN composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts

# 나머지 프로젝트 복사
COPY . .

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000

ENTRYPOINT ["docker-entrypoint.sh"]

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]

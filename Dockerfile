# syntax=docker/dockerfile:1
FROM php:8.2-fpm

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    ca-certificates \
    mariadb-client \
    netcat-openbsd \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql bcmath zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update \
    && apt-get install -y nodejs \
    && npm -v && node -v \
    && rm -rf /var/lib/apt/lists/*

COPY . .

RUN mkdir -p storage/tmp \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN composer install --no-dev --optimize-autoloader

# RUN npm install && npm run prod

RUN php artisan optimize || true

RUN php artisan storage:link || true

EXPOSE 3000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=3000"]

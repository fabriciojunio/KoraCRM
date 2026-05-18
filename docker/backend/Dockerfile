# ─── Estágio base ─────────────────────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS base

# Dependências do sistema
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    oniguruma-dev \
    mysql-client

# Extensões PHP
RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache

# Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

# ─── Estágio de desenvolvimento ───────────────────────────────────────────────
FROM base AS desenvolvimento

# Instala Xdebug para cobertura de código
RUN pecl install xdebug && docker-php-ext-enable xdebug

COPY . .

RUN composer install --no-interaction --no-scripts

RUN chown -R www-data:www-data /var/www/backend/storage
RUN chown -R www-data:www-data /var/www/backend/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]

# ─── Estágio de produção ──────────────────────────────────────────────────────
FROM base AS producao

COPY . .

RUN composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

RUN chown -R www-data:www-data /var/www/backend/storage
RUN chown -R www-data:www-data /var/www/backend/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]

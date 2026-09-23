# Imagem do backend: PHP-FPM com o Laravel.
#
# Três estágios. O primeiro instala as extensões e o Composer e é a base
# comum. O de desenvolvimento leva Xdebug e monta o código por volume. O de
# produção instala sem as dependências de desenvolvimento, gera os caches do
# framework e roda como www-data, sem Composer e sem Xdebug dentro.

# ------------------------------------------------------------------- base
FROM php:8.2-fpm-alpine AS base

RUN apk add --no-cache \
        curl \
        libpng-dev \
        libzip-dev \
        oniguruma-dev \
        mysql-client \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    && apk add --no-cache --virtual .construcao $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .construcao

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

# --------------------------------------------------------- desenvolvimento
FROM base AS desenvolvimento

RUN apk add --no-cache --virtual .construcao $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .construcao

COPY --chown=www-data:www-data . .

RUN composer install --no-interaction --no-scripts

USER www-data
EXPOSE 9000
CMD ["php-fpm"]

# ------------------------------------------------------------- dependências
FROM base AS dependencias

COPY composer.json composer.lock ./

RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

# ----------------------------------------------------------------- produção
FROM base AS producao

# O Composer só existia para montar a pasta vendor, que vem pronta do estágio
# anterior. Uma imagem de produção não precisa conseguir instalar pacote.
RUN rm -f /usr/bin/composer

COPY --chown=www-data:www-data . .
COPY --from=dependencias --chown=www-data:www-data /var/www/backend/vendor ./vendor

RUN php artisan config:cache \
    && php artisan route:cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
    CMD php -r 'exit(@fsockopen("127.0.0.1", 9000) ? 0 : 1);'

CMD ["php-fpm"]

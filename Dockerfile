FROM php:8.4-fpm

RUN apt update && apt install -y \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    locales \
    zip \
    unzip \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

RUN docker-php-ext-install pdo pdo_mysql

COPY ./assets /var/www/assets
COPY ./bin /var/www/bin
COPY ./config /var/www/config
COPY ./migrations /var/www/migrations
COPY ./public /var/www/public
COPY ./src /var/www/src
COPY ./templates /var/www/templates

COPY ./.env /var/www/.env
COPY ./.symfony.local.yaml /var/www/.symfony.local.yaml
COPY ./composer.json /var/www/composer.json
COPY ./composer.lock /var/www/composer.lock
COPY ./importmap.php /var/www/importmap.php
COPY ./symfony.lock /var/www/symfony.lock

COPY ./nginx.conf /etc/nginx/conf.d/default.conf

COPY ./docker-entrypoint.sh /usr/local/bin/

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]

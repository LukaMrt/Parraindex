FROM php:8.4-cli

RUN apt update \
    && apt install -y \
        git \
        libicu-dev \
        libzip-dev \
        libpq-dev \
        unzip \
        zip \
        wget \
        curl \
    && apt clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    bcmath \
    gd \
    intl \
    pdo_mysql \
    zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN wget https://get.symfony.com/cli/installer -O - | bash \
    && mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

COPY .env /Parraindex/.env

WORKDIR /Parraindex

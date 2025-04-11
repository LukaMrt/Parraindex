FROM php:8.4-fpm

WORKDIR /var/www/html

COPY ./docker/php-fpm/php.ini /usr/local/etc/php/

RUN apt-get update && apt-get install -y --no-install-recommends \
    acl \
    curl \
    g++ \
    git \
    gnupg \
    libfreetype6-dev \
    libicu-dev \
    libjpeg-dev \
    libonig-dev \
    libpng-dev \
    libxslt1-dev \
    libzip-dev \
    openssl \
    procps \
    unzip \
    zlib1g-dev && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg --with-freetype 
RUN docker-php-ext-install pdo pdo_mysql zip xsl gd intl opcache exif mbstring

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g sass

ENTRYPOINT [ "bash" , "./docker/php-fpm/entrypoint.sh" ]
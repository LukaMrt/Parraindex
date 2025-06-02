FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
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
    libssl-dev \
    openssl \
    ca-certificates \
    tzdata \
    procps \
    unzip \
    zlib1g-dev \
    nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg --with-freetype && \
    docker-php-ext-install \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pdo \
        pdo_mysql \
        sockets \
        xml \
        xsl \
        zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    npm install -g sass

WORKDIR /var/www
ENTRYPOINT [ "bash" , "./docker/php-fpm/entrypoint.sh" ]
EXPOSE 9000
# Stage 1: Composer dependencies
FROM composer:2 AS composer

# Stage 2: FrankenPHP runtime
FROM dunglas/frankenphp:1.5-php8.5-alpine AS frankenphp

# Copy Composer from stage 1
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Environment — SERVER_NAME can be overridden in compose.yaml (e.g. ":80" behind Nginx)
ENV SERVER_NAME=parraindex.com
ENV APP_RUNTIME=Runtime\\FrankenPhpSymfony\\Runtime
ENV FRANKENPHP_CONFIG="worker /app/public/index.php"
RUN echo "" > .env
ENV APP_ENV=prod
ENV APP_DEBUG=false

# Install netcat for database connection checking
RUN apk add --no-cache netcat-openbsd

# Install PHP extension intl
RUN apk add --no-cache icu-dev \
    && docker-php-ext-install -j$(nproc) intl \
        pdo \
        pdo_mysql \
        opcache

WORKDIR /app

# Copy application files
COPY assets assets
COPY bin/console bin/console
COPY config config
COPY migrations migrations
COPY public public
COPY src src
COPY composer.json composer.lock symfony.lock ./

# Run post-install scripts now that everything is present
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy and set up entrypoint
COPY docker-entrypoint.sh /usr/local/bin/custom-entrypoint.sh
RUN chmod +x /usr/local/bin/custom-entrypoint.sh

# Set the commands and entrypoint
CMD ["--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
ENTRYPOINT ["/usr/local/bin/custom-entrypoint.sh"]

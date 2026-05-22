# Stage 1: Build React frontend
FROM node:22-alpine AS frontend
WORKDIR /app
COPY frontend/package*.json ./
RUN npm ci
COPY frontend/ ./
RUN npm run build

# Stage 2: Composer binary
FROM composer:2 AS composer

# Stage 3: Common base (everything except the composer install)
FROM dunglas/frankenphp:1.12-php8.5-alpine AS base

COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV SERVER_NAME=":80"

RUN apk add --no-cache netcat-openbsd

RUN apk add --no-cache --virtual .build-deps autoconf build-base libpng-dev libjpeg-turbo-dev libwebp-dev icu-dev imagemagick-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql gd intl \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del .build-deps \
    && apk add --no-cache libpng libjpeg-turbo libwebp icu-libs imagemagick

WORKDIR /app

# Copy backend
COPY backend/assets assets
COPY backend/bin/console bin/console
COPY backend/config config
COPY backend/migrations migrations
COPY backend/public public
COPY backend/src src
COPY backend/templates templates
COPY backend/composer.json backend/composer.lock backend/symfony.lock ./
RUN echo "" > .env

# Copy built frontend assets
COPY --from=frontend /app/dist /app/front

# Custom Caddyfile
COPY docker/Caddyfile /etc/caddy/Caddyfile

# Entrypoint
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

CMD ["--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Stage 4a: Production image (no dev deps, optimized autoloader)
FROM base AS prod
ENV APP_ENV=prod
ENV APP_DEBUG=0
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Stage 4b: Test image (dev deps for fixtures/profiler, dev env)
FROM base AS test
ENV APP_ENV=dev
ENV APP_DEBUG=0
RUN composer install --no-scripts

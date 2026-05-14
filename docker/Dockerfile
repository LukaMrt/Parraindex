# Stage 1: Build React frontend
FROM node:22-alpine AS frontend
WORKDIR /app
COPY frontend/package*.json ./
RUN npm ci
COPY frontend/ ./
RUN npm run build

# Stage 2: Composer dependencies
FROM composer:2 AS composer

# Stage 3: FrankenPHP runtime
FROM dunglas/frankenphp:1.12-php8.5-alpine

COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV APP_ENV=prod
ENV APP_DEBUG=false
ENV SERVER_NAME=":80"

RUN apk add --no-cache netcat-openbsd

RUN apk add --no-cache icu-dev libpng-dev libjpeg-turbo-dev libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install intl pdo_mysql gd

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
RUN composer install --no-dev --optimize-autoloader --no-scripts

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

#!/bin/sh
set -e

# Wait for database with timeout
echo "Waiting for database at $DATABASE_HOST:$DATABASE_PORT..."
timeout=30
counter=0

while ! nc -z "$DATABASE_HOST" "$DATABASE_PORT"; do
    counter=$((counter + 1))
    if [ $counter -gt $timeout ]; then
        echo "Error: Database connection timeout after ${timeout} seconds"
        exit 1
    fi
    echo "Waiting for database to be ready... ($counter/$timeout)"
    sleep 1
done

echo "Database is ready!"

php bin/console cache:clear --no-warmup
php bin/console cache:warmup
php bin/console assets:install --symlink --relative
php bin/console importmap:install
php bin/console sass:build
php bin/console asset-map:compile

# Run migrations
php bin/console doctrine:migrations:migrate --no-interaction

# Execute the original FrankenPHP entrypoint with CMD arguments
echo "Starting FrankenPHP..."
exec docker-php-entrypoint "$@"

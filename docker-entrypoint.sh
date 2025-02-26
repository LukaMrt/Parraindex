#!/bin/bash

composer install --optimize-autoloader --no-dev

php bin/console doctrine:migrations:migrate --no-interaction

php bin/console cache:clear
php bin/console cache:warmup

php-fpm & nginx -g 'daemon off;'

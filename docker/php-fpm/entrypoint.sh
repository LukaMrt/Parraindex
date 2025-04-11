#!/bin/bash

composer install 

# Preparation for a future migration to Symfony

# php bin/console asset-map:compile
# php bin/console make:migration --no-interaction 

sass scss:public/css

php-fpm
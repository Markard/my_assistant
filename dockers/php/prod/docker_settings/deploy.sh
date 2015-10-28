#!/usr/bin/env bash
cd /var/www
SYMFONY_ENV=prod composer install --no-dev && chown -R php-fpm:php-fpm /var/www/
php /var/www/app/console doctrine:migrations:migrate --env=prod

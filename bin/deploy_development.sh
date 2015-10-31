#!/usr/bin/env bash

docker-compose -f development_compose.yml build

if [ ! -d /var/lib/mysql ]; then
    mkdir /var/lib/mysql
    docker run -d -v /var/lib/mysql:/var/lib/mysql myassistant_database /bin/bash -c "/usr/bin/mysql_install_db"
fi

if [ ! -d /var/mysql_backups ]; then
    mkdir /var/lib/mysql
fi

docker-compose -f development_compose.yml up -d

docker exec -it myassistant_php_1 /bin/bash -c "composer install -d /var/www && chown -R php-fpm:php-fpm /var/www/"
docker exec -it myassistant_php_1 /bin/bash -c "php /var/www/app/console doctrine:migrations:migrate"

docker exec -it myassistant_nodejs_1 /bin/bash -c "npm install && bower install --allow-root && gulp"
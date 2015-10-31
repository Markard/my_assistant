#!/usr/bin/env bash

docker-compose -f prod_compose.yml build

if ! [ -f /var/lib/mysql ]; then
    mkdir /var/lib/mysql
    docker run -d -v /var/lib/mysql:/var/lib/mysql myassistant_database /bin/bash -c "/usr/bin/mysql_install_db"
fi

if ! [ -f /var/mysql_backups ]; then
    mkdir /var/lib/mysql
fi

docker-compose -f prod_compose.yml up -d

docker exec -it myassistant_php_1 /bin/bash -c "composer install -d /var/www --no-dev && chown -R php-fpm:php-fpm /var/www/"
docker exec -it myassistant_php_1 /bin/bash -c "php /var/www/app/console doctrine:migrations:migrate --env=prod"

docker exec -it myassistant_nodejs_1 /bin/bash -c "npm install && bower install --allow-root && gulp && \
cat /var/www/web/static/js/vendor.js | gzip -9 > /var/www/web/static/js/vendor.js.gz && \
cat /var/www/web/static/js/app.js | gzip -9 > /var/www/web/static/js/app.js.gz && \
cat /var/www/web/static/css/bootstrap.css | gzip -9 > /var/www/web/static/css/bootstrap.css.gz
cat /var/www/web/static/css/app.css | gzip -9 > /var/www/web/static/css/app.css.gz
cat /var/www/web/static/css/vendor.css | gzip -9 > /var/www/web/static/css/vendor.css.gz
"
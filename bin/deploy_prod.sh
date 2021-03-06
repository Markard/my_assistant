#!/usr/bin/env bash

WORKDIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Prepare folders for mysql data and backups on host
IS_DATABASE_INITIALISED=1
if [ ! -d $WORKDIR/../mysql/data ]; then
    mkdir -p $WORKDIR/../mysql/data
    IS_DATABASE_INITIALISED=0
fi

if [ ! -d $WORKDIR/../mysql/backups ]; then
    mkdir -p $WORKDIR/../mysql/backups
fi

# We have to change current dir in order to properly map volumes to docker contaoiners
cd $WORKDIR/..

# Build all docker images
docker-compose -f $WORKDIR/../prod_compose.yml build

# Initialise mysql data
if [ $IS_DATABASE_INITIALISED == 0 ]; then
    docker run -d -v $WORKDIR/../mysql/data:/var/lib/mysql myassistant_database /bin/bash -c "/usr/bin/mysql_install_db"
fi

# Build docker containers
docker-compose -f prod_compose.yml up -d

# Prepare backend
docker exec -it myassistant_php_1 /bin/bash -c "composer install -d /var/www --no-dev && chown -R php-fpm:php-fpm /var/www/"

# Migrate database changes
docker exec -it myassistant_php_1 /bin/bash -c "php /var/www/app/console doctrine:migrations:migrate -n --env=prod"

# Prepare frontend
docker exec -it myassistant_nodejs_1 /bin/bash -c "npm install && bower install --allow-root --config.interactive=false && gulp && \
cat /var/www/web/static/js/vendor.js | gzip -9 > /var/www/web/static/js/vendor.js.gz && \
cat /var/www/web/static/js/app.js | gzip -9 > /var/www/web/static/js/app.js.gz && \
cat /var/www/web/static/css/bootstrap.css | gzip -9 > /var/www/web/static/css/bootstrap.css.gz
cat /var/www/web/static/css/app.css | gzip -9 > /var/www/web/static/css/app.css.gz
cat /var/www/web/static/css/vendor.css | gzip -9 > /var/www/web/static/css/vendor.css.gz
"
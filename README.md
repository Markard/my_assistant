Steps for production:

1 You have to create folder for database data on your host machine.
   mkdir /var/lib/mysql
   
2 Create folder for mysql backups
    mkdir /var/mysql_backups
     
3 At the first time you have initialized mysql in this folder.
   docker run -d -v /var/lib/mysql:/var/lib/mysql myassistant_database /bin/bash -c "/usr/bin/mysql_install_db"
    
4 You have to build docker images.
   docker-compose -f prod_compose.yml build
    
5 Create docker containers from images.
   docker-compose -f prod_compose.yml up -d
    
6 Update composer 
   docker exec -it myassistant_php_1 /bin/bash -c "composer install -d /var/www --no-dev && chown -R php-fpm:php-fpm /var/www/"
   
7 Migrate database
   docker exec -it myassistant_php_1 /bin/bash -c "php /var/www/app/console doctrine:migrations:migrate --env=prod"
   
8 Build frontend files
    docker exec -it myassistant_nodejs_1 /bin/bash -c "npm install && bower install --allow-root && gulp"
    docker exec -it myassistant_nodejs_1 /bin/bash -c "cat /var/www/web/static/js/vendor.js | gzip -9 > /var/www/web/static/js/vendor.js.gz"
    docker exec -it myassistant_nodejs_1 /bin/bash -c "cat /var/www/web/static/js/app.js | gzip -9 > /var/www/web/static/js/app.js.gz"
    docker exec -it myassistant_nodejs_1 /bin/bash -c "cat /var/www/web/static/css/bootstrap.css | gzip -9 > /var/www/web/static/css/bootstrap.css.gz"
    
How to backup database:    
1) Create folder /var/mysql_backups
2) docker exec -d myassistant_database_1 mysqldump site | gzip -9 > /tmp/backups/$(date +%Y-%m-%d-%H.%M.%S).sql.gz

How to restore database:
1) 2) docker exec -d myassistant_database_1 gzip < /tmp/backups/[backup_name].sql.gz | mysql site

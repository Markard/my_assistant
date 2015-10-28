Steps for production:

1 You have to create folder for database data on your host machine.
    sudo mkdir /var/lib/mysql
     
2 At the first time you have initialized mysql in this folder.
    sudo docker run -d -v /var/lib/mysql:/var/lib/mysql myassistant_database /bin/bash -c "/usr/bin/mysql_install_db"
    
3 You have to build docker images.
    sudo docker-compose -f prod_compose.yml build
    
4 Create docker containers from images.
    sudo docker-compose -f prod_compose.yml up -d
    
5 Execute deployment script. It downloads dependencies via composer and implements database migrations. 
    sudo docker exec -it myassistant_php_1 /usr/bin/deploy.sh
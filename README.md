This project writed with [Symfony2](https://symfony.com/) and [AnjularJs](https://angularjs.org/). Currently used for my own needs. Any help or suggestions would be greatly appreciated!
Currently the project separated on REST Api backend (Symfony2) and frontend (AngularJS).
Functionality:
* Authentication based on [Json Web Token](http://jwt.io/)
* CRUD for incomes and expenses

## What was used:
* **For development**
  * [Symfony2](https://symfony.com/download) - 2.7
  * [Doctrine](http://www.doctrine-project.org/projects/orm.html) - 2.4
  * [AnjularJs](https://angularjs.org/) - 1.3
* **For environments**
  * [Php](http://php.net/downloads.php) - 5.6.14
  * [MySql](https://dev.mysql.com/downloads/mysql/) - 5.6
  * [Nginx](http://nginx.org/ru/download.html) - 1.8.0
  * [NodeJs](https://nodejs.org/download/release/v0.12.7/) - 0.12

## Installation
### Requirements:
* docker (https://docs.docker.com/engine/installation/)
* docker-compose (https://docs.docker.com/compose/)
* **Keys** for jwt authentication. You should keep them in *source/app/config/keys/jwt/* folder:
```bash
cd source/app/config/keys/jwt/
openssl genrsa -out private.pem -aes256 4096
openssl rsa -pubout -in private.pem -out public.pem
```
* **Api key** and **Domain** from [mailgun](http://www.mailgun.com/) service.

### Quick start
Clone repository:
```bash
git clone https://github.com/Markard/my_assistant.git
```

Now you could use easy way and hard way:
#### Easy way (just use predefined script):
```bash
$ bin/deploy_development.sh
```
It creates folder *mysql/data* in your repository folder, initializes mysql data, installs dockers and setups your 
application in dockers.

There are two scripts, for development and production environment.

At first execution you'll be asked for

#### Hard way
Just do the steps in *bin/deploy_development.sh* or *bin/deploy_production.sh*

That's all. Now your server, database and php interpreter created and ready to work. 
You can access them through http://127.0.0.1

## Structure
* *bin* - helpful scripts for such work as db backup, db restore, gulp, gulp watch.
* *dockers* - dockers setup with server specific configurations like **php.ini** and other
* *source* - application code
 * *assets* - frontend application, writed on AngularJs

## Api documentation
http://127.0.0.1/api/doc

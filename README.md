This project writed with [Symfony2](https://symfony.com/) and [AnjularJs](https://angularjs.org/). Currrently used for my own needs. Any help or suggestions would be greatly appreciated!
Currently the project separated on REST Api backend and frontend wrote on AngularJS.

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
A project already contains docker settings so you only need any Linux distribution and docker. Currently docker settings was tested on Ubuntu >= 14.04
So you need:
* docker (https://docs.docker.com/engine/installation/)
* docker-compose (https://docs.docker.com/compose/)

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
It creates folder *mysql/data* in your repository folder. Initialized mysql data. Install dockers and setup your application in dockers.

There are two scripts, for development and production environment.

#### Hard way
Just do the steps in *bin/deploy_development.sh* or *bin/deploy_production.sh*

That's all. Now your webserver, database and interpreter created and ready to work. You can access the site on http://127.0.0.1

## Structure
* *bin* - helpful scripts for such work as db backup, db restore, gulp, gulp watch.
* *dockers* - dockers setup with server specific configurations like **php.ini** and other
* *source* - application code
 * *assets* - frontend application, writed on AngularJs

CREATE DATABASE IF NOT EXISTS `site` CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL PRIVILEGES ON `site`.* TO `admin` IDENTIFIED BY 'secret';

DROP DATABASE IF EXISTS test
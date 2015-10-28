CREATE USER 'site_admin'@'%' IDENTIFIED BY 'secretpwd';

CREATE DATABASE IF NOT EXISTS `site` CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL PRIVILEGES ON `site`.* TO `site_admin`;

CREATE DATABASE IF NOT EXISTS `site_test` CHARACTER SET utf8 COLLATE utf8_general_ci;
GRANT ALL PRIVILEGES ON `site_test`.* TO `site_admin`;

DROP DATABASE IF EXISTS test
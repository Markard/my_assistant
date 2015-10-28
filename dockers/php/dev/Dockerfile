FROM ubuntu:14.04

MAINTAINER "Dmitry Yuzhakov" <dmtry@gmail.com>

#-----------------------------------------------------------------------------------------------------------------------
# Update and upgrade system.
#-----------------------------------------------------------------------------------------------------------------------

RUN apt-get -y update && apt-get -y upgrade

#-----------------------------------------------------------------------------------------------------------------------
# Configure user
#-----------------------------------------------------------------------------------------------------------------------

## Configure user and group which will be used to run php-fpm
RUN groupadd -g 1000 php-fpm;
RUN useradd -s /sbin/nologin -u 1000 -g 1000 -m php-fpm;

#--------------------------------------------------------------------------------------------------------------------------------------
# Configure log folders
#--------------------------------------------------------------------------------------------------------------------------------------

RUN mkdir -p /var/log/php5-fpm/site/
RUN chown -R php-fpm:php-fpm /var/log/php5-fpm/site/

#-----------------------------------------------------------------------------------------------------------------------
# Installations
#-----------------------------------------------------------------------------------------------------------------------
RUN apt-get -y update

# Install prerequisites for PHP compile
RUN apt-get -y install libfcgi-dev libfcgi0ldbl libjpeg62-dbg libmcrypt-dev libssl-dev libbz2-dev libjpeg-dev \
    libfreetype6-dev libpng12-dev libxpm-dev libxml2-dev libpcre3-dev libbz2-dev libcurl4-openssl-dev \
    libjpeg-dev libpng12-dev libxpm-dev libfreetype6-dev libmysqlclient-dev libt1-dev libgd2-xpm-dev \
    libgmp-dev libsasl2-dev libmhash-dev unixodbc-dev freetds-dev libpspell-dev libsnmp-dev libtidy-dev \
    libxslt1-dev libmcrypt-dev libdb5.3-dev
RUN apt-get install -y wget make autoconf

# Install Mysql client for testing. We currently use it to restore our database.
RUN apt-get install -y mysql-client-5.6

# Download PHP source
RUN wget -O /var/tmp/php-5.6.14.tar.bz2 http://bg2.php.net/get/php-5.6.14.tar.bz2/from/this/mirror && \
    mkdir /tmp/php && \
    tar -xvjpf /var/tmp/php-5.6.14.tar.bz2 -C /tmp/php --strip-components=1

# Build PHP
WORKDIR /tmp/php

RUN ./configure \
        --prefix=/opt/php-5.6.14 \
        --sysconfdir=/etc/php-5.6.14/ \
        --localstatedir=/var \
        --datadir=/usr/share/php \
        --mandir=/usr/share/man \
        --enable-fpm \
        --with-fpm-user=php-fpm \
        --with-fpm-group=php-fpm \
        --with-config-file-path=/etc/php-5.6.14/ \
        --with-mcrypt \
        --enable-mbstring \
        --with-openssl \
        --with-pdo-mysql \
        --with-mysql \
        --with-mysql-sock \
        --with-gd \
        --with-jpeg-dir=/usr/lib \
        --enable-gd-native-ttf  \
        --with-libxml-dir=/usr/lib \
        --with-curl \
        --enable-zip  \
        --enable-sockets \
        --with-zlib \
        --enable-exif \
        --enable-ftp \
        --with-iconv \
        --with-gettext \
        --enable-opcache && \
        make && \
        make install

#-----------------------------------------------------------------------------------------------------------------------
# Configurations
#-----------------------------------------------------------------------------------------------------------------------
RUN mkdir -p /etc/php-5.6.14/
RUN mkdir -p /etc/php-5.6.14/conf.d
ADD docker_settings/php.ini /etc/php-5.6.14/php.ini
ADD docker_settings/php-fpm.conf /etc/php-5.6.14/php-fpm.conf
ADD docker_settings/conf.d/site.conf /etc/php-5.6.14/conf.d/site.conf

ENV PATH /opt/php-5.6.14/bin/:/opt/php-5.6.14/sbin/:$PATH

#Install composer
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/bin --filename=composer

#Install xdebug
RUN pecl install xdebug

EXPOSE 9000

WORKDIR /var/www

CMD ["php-fpm"]
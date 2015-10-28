FROM ubuntu:14.04

MAINTAINER "Dmitry Yuzhakov" <dmtry@gmail.com>

RUN apt-get -y update && apt-get -y upgrade

#-----------------------------------------------------------------------------------------------------------------------
# Configure user
#-----------------------------------------------------------------------------------------------------------------------

## Configure user and group which will be used to run nginx
RUN groupadd -g 1000 nginx;
RUN useradd -s /sbin/nologin -u 1000 -g 1000 -m nginx;

#-----------------------------------------------------------------------------------------------------------------------
# Configure log folders
#-----------------------------------------------------------------------------------------------------------------------

RUN mkdir -p /var/log/nginx/
RUN mkdir -p /var/log/nginx/site/
RUN touch /var/log/nginx/access.log
RUN touch /var/log/nginx/error.log
RUN chown -R nginx:nginx /var/log/nginx/

#-----------------------------------------------------------------------------------------------------------------------
# Installations
#-----------------------------------------------------------------------------------------------------------------------

#WORKDIR /tmp

# Install prerequisites for Nginx compile
RUN apt-get install -y wget tar gcc make build-essential libpcre3 libpcre3-dev libssl-dev

# Download Nginx and Nginx modules source
RUN wget http://nginx.org/download/nginx-1.8.0.tar.gz -O nginx.tar.gz && \
    mkdir /tmp/nginx && \
    tar -xzvf nginx.tar.gz -C /tmp/nginx --strip-components=1

# Build Nginx
WORKDIR /tmp/nginx

RUN ./configure \
        --user=www-data \
        --group=www-data \
        --prefix=/usr/share/nginx \
        --sbin-path=/usr/sbin/nginx \
        --conf-path=/etc/nginx/nginx.conf \
        --pid-path=/run/nginx.pid \
        --lock-path=/run/lock/subsys/nginx \
        --error-log-path=/var/log/nginx/error.log \
        --http-log-path=/var/log/nginx/access.log \
        --with-http_gzip_static_module \
        --with-http_stub_status_module \
        --with-http_ssl_module \
        --with-pcre \
        --with-http_gunzip_module && \
    make && \
    make install

# Cleanup after Nginx build
RUN apt-get autoremove -y && \
    rm -rf /tmp/*

#-----------------------------------------------------------------------------------------------------------------------
# Prepare configurations
#-----------------------------------------------------------------------------------------------------------------------

ADD docker_settings/nginx.conf /etc/nginx/nginx.conf
ADD docker_settings/conf.d/site.conf /etc/nginx/sites-enabled/site.conf

EXPOSE 80 443

WORKDIR /var/www

CMD ["/usr/sbin/nginx", "-c", "/etc/nginx/nginx.conf"]
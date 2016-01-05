FROM ubuntu:14.04

#-----------------------------------------------------------------------------------------------------------------------
# Installations
#-----------------------------------------------------------------------------------------------------------------------

RUN apt-get install -y curl
RUN curl -sL https://deb.nodesource.com/setup | sudo bash -
RUN apt-get install -y  nodejs build-essential git
RUN apt-get -y update && apt-get -y upgrade

#Install gulp and bower globally
RUN npm install gulp -g
RUN npm install bower -g

WORKDIR /var/www

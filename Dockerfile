# build:
#
# docker build -t registry.cn-hongkong.aliyuncs.com/lopy-dev/docker-dev-php7.0-cli-swoole1.10 .

# This dockerfile uses the ubuntu image
# VERSION 2 - EDITION 1
# Author: docker_user
# Command format: Instruction [arguments / command ] ..

# Base image to use, this nust be set as the first line
FROM php:7.0-cli

# Maintainer: docker_user <docker_user at email.com> (@docker_user)
MAINTAINER zengyu 284141050@qq.com

#

RUN echo "deb http://deb.debian.org/debian stretch main" >/etc/apt/sources.list \
    && echo "deb http://security.debian.org/debian-security stretch/updates main" >>/etc/apt/sources.list \
    && echo "deb http://deb.debian.org/debian stretch-updates main" >>/etc/apt/sources.list \
    && echo "deb http://mirrors.aliyun.com/debian stretch main non-free contrib" >>/etc/apt/sources.list \
    && echo "deb-src http://mirrors.aliyun.com/debian stretch main non-free contrib" >>/etc/apt/sources.list \
    && echo "deb http://mirrors.aliyun.com/debian stretch-updates main non-free contrib" >>/etc/apt/sources.list \
    && echo "deb-src http://mirrors.aliyun.com/debian stretch-updates main non-free contrib" >>/etc/apt/sources.list \
    && apt-get update \
    && apt-get install -y sudo \ 
    && apt-get install -y unzip \ 
    && apt-get install -y unrar \ 
    && apt-get clean && apt-get autoclean \
    && ls /var/cache/apt/archives


# mysql
RUN docker-php-ext-install -j$(nproc) pdo_mysql

# inotify
RUN pecl install inotify && docker-php-ext-enable inotify

RUN mkdir /var/www \ 
    && chown -R www-data /var/www \
    && cd /usr/local/bin \
    && curl -sS https://getcomposer.org/installer | php 

#RUN sudo -u www-data composer.phar global require 'composer/composer:dev-master'
RUN sudo -u www-data composer.phar global require 'codeception/codeception'

ADD extension /tmp/extension

# apcu
RUN php /tmp/extension/ExtInstaller.php -n apcu

# swoole
RUN php /tmp/extension/ExtInstaller.php -n swoole

# support zh-cn
ENV LANG C.UTF-8


# Commands when creating a new container
CMD ["php","-a"]

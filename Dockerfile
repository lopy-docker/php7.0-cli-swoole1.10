# build:
#
# docker build -t registry.cn-hongkong.aliyuncs.com/lopy-dev/docker-dev-php7.0-cli-swoole1.10 .

# This dockerfile uses the ubuntu image
# VERSION 2 - EDITION 1
# Author: docker_user
# Command format: Instruction [arguments / command ] ..

# Base image to use, this nust be set as the first line
FROM php:7.0-cli-stretch

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
    && apt-get install -y sudo unzip unrar apt-utils procps inetutils-ping \ 
    && apt-get clean && apt-get autoclean \
    && ls /var/cache/apt/archives

############################################  install ext start  ############################################

# mysql
RUN docker-php-ext-install -j$(nproc) pdo_mysql

# inotify
RUN pecl install inotify && docker-php-ext-enable inotify


ADD extension /tmp/extension

# apcu
RUN php /tmp/extension/ExtInstaller.php -n apcu

# swoole
RUN php /tmp/extension/ExtInstaller.php -n swoole

############################################  install ext end  ############################################

# add a default user named debian
RUN useradd debian  -s /bin/bash -m -k /etc/skel \
    && echo "debian  ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

############################################  install composer start  ############################################
# composer
RUN mkdir /var/www \ 
    && chown -R www-data /var/www \
    && cd /usr/local/bin \
    && curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar composer \
    && echo "update env" \
    && echo "export PATH=\$PATH:/root/.composer/vendor/bin" >> /root/.bashrc \
    && echo "export PATH=\$PATH:/root/.composer/vendor/bin" >> /root/.profile \
    && echo "export PATH=\$PATH:/root/.composer/vendor/bin" >> /etc/profile \
    && echo "export PATH=\$PATH:/home/debian/.composer/vendor/bin" >> /home/debian/.bashrc \
    && echo "export PATH=\$PATH:/home/debian/.composer/vendor/bin" >> /home/debian/.profile \
    && echo "export PATH=\$PATH:/home/debian/.composer/vendor/bin" >> /etc/profile \
    && chown -R debian:debian /home/debian
############################################  install composer end  ############################################

# support zh-cn
ENV LANG C.UTF-8

    
USER debian
RUN composer global require 'codeception/codeception' \
    && composer global require 'phpstan/phpstan' \
    && composer global require 'friendsofphp/php-cs-fixer'


USER root

# Commands when creating a new container
CMD ["php","-a"]

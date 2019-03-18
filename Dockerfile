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

# RUN echo "deb http://deb.debian.org/debian jessie main" >/etc/apt/source.list \
#     && echo "deb http://security.debian.org/debian-security jessie/updates main" >/etc/apt/source.list \
#     && echo "deb http://deb.debian.org/debian jessie-updates main" >/etc/apt/source.list \
#     && echo "deb http://mirrors.aliyun.com/debian jessie main non-free contrib" >/etc/apt/source.list \
#     && echo "deb-src http://mirrors.aliyun.com/debian jessie main non-free contrib" >/etc/apt/source.list \
#     && echo "deb http://mirrors.aliyun.com/debian jessie-updates main non-free contrib" >/etc/apt/source.list \
#     && echo "deb-src http://mirrors.aliyun.com/debian jessie-updates main non-free contrib" >/etc/apt/source.list \
#     && apt-get update \
#     && apt-get install -y unzip \ 
#     && apt-get clean && apt-get autoclean \
#     && ls /var/cache/apt/archives


# mysql
RUN docker-php-ext-install -j$(nproc) pdo_mysql

# inotify
RUN pecl install inotify && docker-php-ext-enable inotify

ADD extension /tmp/extension

# apcu
RUN php /tmp/extension/ExtInstaller.php -n apcu

# swoole
RUN php /tmp/extension/ExtInstaller.php -n swoole

# add a default user
RUN useradd debian  -s /bin/bash -m -k /etc/skel \
    && echo "debian  ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Commands when creating a new container
CMD ["php","-a"]

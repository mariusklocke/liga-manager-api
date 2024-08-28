#!/bin/sh
set -xe

apk update
apk upgrade
apk add --no-cache \
    fcgi \
    php83 \
    php83-apcu \
    php83-bcmath \
    php83-dom \
    php83-fpm \
    php83-gmp \
    php83-mbstring \
    php83-opcache \
    php83-pcntl \
    php83-pdo_mysql \
    php83-phar \
    php83-redis \
    php83-simplexml \
    php83-sockets \
    php83-tokenizer \
    php83-xdebug \
    php83-xml \
    php83-xmlwriter
rm -rf /var/cache/*

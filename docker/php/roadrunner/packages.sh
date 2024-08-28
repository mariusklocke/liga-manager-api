#!/bin/sh
set -xe

apk update
apk upgrade
apk add --no-cache php83 php83-bcmath php83-gmp php83-opcache php83-pcntl php83-pdo_mysql php83-sockets
apk add --no-cache php83-pecl-apcu php83-pecl-redis php83-pecl-xdebug
rm -rf /tmp/* /var/cache/*

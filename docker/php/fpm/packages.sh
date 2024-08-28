#!/bin/sh

PHP_EXTENSIONS="apcu bcmath dom fpm gmp mbstring opcache pcntl pdo_mysql phar redis simplexml sockets tokenizer xdebug xml xmlwriter"
PHP_MAIN_PACKAGE="php83"
PACKAGES="fcgi ${PHP_MAIN_PACKAGE}"

for PHP_EXTENSION in $PHP_EXTENSIONS; do
    PACKAGES="${PACKAGES} ${PHP_MAIN_PACKAGE}-${PHP_EXTENSION}"
done

set -ex
apk update
apk upgrade
apk add --no-cache ${PACKAGES}
rm -rf /var/cache/*

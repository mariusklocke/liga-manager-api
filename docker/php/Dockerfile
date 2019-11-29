FROM php:7.4-fpm-alpine

# Simplify local development by adding developer user with id 1000
RUN set -xe \
    && apk add --no-cache shadow \
    && useradd -u 1000 dev \
    && apk del shadow

# persistent / runtime deps
RUN apk add --no-cache --virtual .persistent-deps gmp ncurses gnupg

# Install PHP extensions
RUN set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS gmp-dev \
    && docker-php-ext-install bcmath gmp mysqli pcntl pdo_mysql opcache \
    && pecl install apcu \
    && pecl install redis \
    && pecl install xdebug-beta \
    && apk del .build-deps \
    && rm -rf /tmp/*

# Configure PHP
ARG XDEBUG=0
COPY docker/php/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini
RUN if [ "$XDEBUG" = "1" ] ; then echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini ; fi

# Override entrypoint
COPY docker/php/docker-php-entrypoint.sh /usr/local/bin/docker-php-entrypoint

# Install application sources
ENV APP_HOME="/var/www/api"
RUN mkdir -p ${APP_HOME}
WORKDIR ${APP_HOME}
COPY . .
RUN chmod +x bin/* \
    && bin/install.sh \
    && rm -r docker

USER www-data
CMD php-fpm

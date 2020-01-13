FROM php:7.4-fpm-alpine

ENV RUN_DEPS="gmp ncurses gnupg" \
    BUILD_DEPS="${PHPIZE_DEPS} gmp-dev shadow" \
    PHP_EXTS="bcmath gmp mysqli pcntl pdo_mysql opcache"

# Install PHP extensions
RUN set -xe \
    && apk update \
    && apk upgrade \
    && apk add --no-cache $RUN_DEPS \
    && apk add --no-cache --virtual .build-deps $BUILD_DEPS \
    && useradd -u 1000 dev \
    && docker-php-ext-install $PHP_EXTS \
    && pecl channel-update pecl.php.net \
    && pecl install apcu \
    && pecl install redis \
    && pecl install xdebug-beta \
    && docker-php-ext-enable apcu redis \
    && pecl clear-cache \
    && apk del .build-deps \
    && rm -rf /tmp/*

# Configure PHP
ARG XDEBUG=0
COPY docker/php/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini
RUN if [ "$XDEBUG" = "1" ] ; then docker-php-ext-enable xdebug ; fi

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

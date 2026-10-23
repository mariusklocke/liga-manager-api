ARG APP_VERSION=latest
ARG PHP_VERSION=8.5

# Start build stage from upstream PHP
FROM php:$PHP_VERSION-fpm-alpine AS build

# Build PHP extensions
RUN set -ex \
    && apk update \
    && apk upgrade \
    && apk add --no-cache $PHPIZE_DEPS gmp-dev icu-dev libjpeg-turbo-dev jpeg-dev libpng-dev libpq-dev libwebp-dev linux-headers \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j $(nproc) bcmath gd gmp intl pcntl pdo_mysql pdo_pgsql sockets \
    && MAKEFLAGS="-j $(nproc)" pecl install apcu redis xdebug \
    && rm -rf /usr/local/etc /var/cache/* /var/log/*

# Start final stage from alpine
FROM alpine:3.24 AS final
ARG APP_VERSION
ARG PHP_VERSION

# Define versions
ENV APP_VERSION=${APP_VERSION} PHP_VERSION=${PHP_VERSION}

# Install libraries
RUN set -ex \
    && apk update \
    && apk upgrade \
    && apk add --no-cache argon2-libs curl gmp gnu-libiconv-libs icu-data-full icu-libs libjpeg libjpeg-turbo libpng libpq libwebp libxml2 oniguruma readline sqlite-libs \
    && rm -rf /var/cache/* /var/log/* \
    && mkdir -p /usr/local/etc/php

# Copy files from build stage
COPY --from=build /etc/group /etc/group
COPY --from=build /etc/passwd /etc/passwd
COPY --from=build /etc/shadow /etc/shadow
COPY --from=build /usr/local/bin/php /usr/local/bin/php
COPY --from=build /usr/local/sbin/php-fpm /usr/local/sbin/php-fpm
COPY --from=build /usr/local/lib/php/extensions /usr/local/lib/php/extensions

# Copy config files
COPY docker/php/fpm/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Add composer, healthcheck and entrypoint
ADD --chmod=755 https://getcomposer.org/download/latest-stable/composer.phar /usr/local/bin/composer
COPY --chmod=755 docker/php/fpm/healthcheck.sh /usr/local/bin/docker-php-healthcheck
COPY --chmod=755 docker/php/fpm/entrypoint.sh /usr/local/bin/docker-php-entrypoint

# Create application directories
ENV APP_HOME="/var/www/api" \
    APP_LOGOS_PATH="/var/www/logos" \
    APP_SERVER_INTERNAL="fcgi://127.0.0.1:9000"
RUN mkdir -p ${APP_HOME} ${APP_LOGOS_PATH}
WORKDIR ${APP_HOME}

# Install composer packages
COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --no-cache --no-dev --no-progress

# Install application sources
COPY openapi.yml ./
COPY src src/
COPY public public/
COPY locales locales/
COPY config config/
COPY --chmod=755 bin bin/

ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
EXPOSE 9000
ENTRYPOINT ["docker-php-entrypoint"]
CMD ["php-fpm","-F"]

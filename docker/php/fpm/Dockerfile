ARG APP_VERSION=latest
ARG PHP_VERSION=8.5

FROM composer/composer:latest AS composer

COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --no-cache --no-dev --no-progress

FROM php:$PHP_VERSION-fpm-alpine AS final
ARG APP_VERSION
ARG PHP_VERSION

# Define versions
ENV APP_VERSION=${APP_VERSION}

# Install PHP extensions
RUN set -ex \
    && apk update \
    && apk upgrade \
    && apk add --no-cache curl gmp icu icu-data-full libjpeg libjpeg-turbo libpng libpq libwebp \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS gmp-dev icu-dev libjpeg-turbo-dev jpeg-dev libpng-dev libpq-dev libwebp-dev linux-headers \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j $(nproc) bcmath gd gmp intl pcntl pdo_mysql pdo_pgsql sockets \
    && MAKEFLAGS="-j $(nproc)" pecl install apcu redis xdebug \
    && apk del .build-deps \
    && rm -rf /usr/local/etc /var/cache/* /var/log/* /usr/src/* /usr/local/include/* \
    && mkdir -p /usr/local/etc/php

# Configure PHP
COPY docker/php/fpm/php-fpm.conf /usr/local/etc/php-fpm.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Add healthcheck
COPY --chmod=755 docker/php/fpm/healthcheck.sh /usr/local/bin/docker-php-healthcheck

# Add entrypoint
COPY --chmod=755 docker/php/fpm/entrypoint.sh /usr/local/bin/docker-php-entrypoint

# Create application directories
ENV APP_HOME="/var/www/api" \
    APP_LOGOS_PATH="/var/www/logos" \
    APP_SERVER_INTERNAL="fcgi://127.0.0.1:9000"
RUN mkdir -p ${APP_HOME} ${APP_LOGOS_PATH}
WORKDIR ${APP_HOME}

# Install application sources
COPY openapi.yml ./
COPY --from=composer /app/vendor vendor/
COPY src src/
COPY public public/
COPY locales locales/
COPY config config/
COPY --chmod=755 bin bin/

ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
EXPOSE 9000
ENTRYPOINT ["docker-php-entrypoint"]
CMD ["php-fpm","-F"]

ARG APP_VERSION=latest
ARG PHP_VERSION=8.5

FROM composer/composer:latest AS composer
COPY composer.json composer.lock ./
RUN composer install --ignore-platform-reqs --no-cache --no-dev --no-progress

FROM ghcr.io/roadrunner-server/roadrunner:2025.1.15 AS roadrunner

FROM php:$PHP_VERSION-cli-alpine AS final
ARG APP_VERSION
ARG PHP_VERSION

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

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
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Configure RoadRunner
COPY docker/php/roadrunner/.rr.yaml /usr/local/etc/rr.yaml

# Add healthcheck
COPY --chmod=755 docker/php/roadrunner/healthcheck.sh /usr/local/bin/docker-php-healthcheck

# Add entrypoint
COPY --chmod=755 docker/php/roadrunner/entrypoint.sh /usr/local/bin/docker-php-entrypoint

# Create application directories
ENV APP_HOME="/var/www/api" \
    APP_LOGOS_PATH="/var/www/logos" \
    APP_SERVER_INTERNAL="http://127.0.0.1:8080" \
    METRICS_PUBLISH_URL="tcp://127.0.0.1:6001" \
    METRICS_EXPORT_URL="http://127.0.0.1:8081"
RUN mkdir -p ${APP_HOME} ${APP_LOGOS_PATH}
WORKDIR ${APP_HOME}

# Install application sources
COPY openapi.yml ./
COPY --from=composer /app/vendor vendor/
COPY src src/
COPY locales locales/
COPY config config/
COPY --chmod=755 bin bin/

ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
EXPOSE 8080
ENTRYPOINT ["docker-php-entrypoint"]
CMD ["rr","serve","--config","/usr/local/etc/rr.yaml","--silent"]

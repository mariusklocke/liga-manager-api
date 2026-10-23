ARG APP_VERSION=latest
ARG PHP_VERSION=8.5

# Start build stage from upstream PHP
FROM php:$PHP_VERSION-cli-alpine AS build

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
COPY --from=build /usr/local/lib/php/extensions /usr/local/lib/php/extensions

# Copy roadrunner executable
COPY --from=ghcr.io/roadrunner-server/roadrunner:3.0.1 /usr/bin/rr /usr/local/bin/rr

# Copy config files
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/roadrunner/.rr.yaml /usr/local/etc/rr.yaml

# Add composer, healthcheck and entrypoint
COPY --from=composer:latest --chmod=755 /usr/bin/composer /usr/local/bin/composer
COPY --chmod=755 docker/php/roadrunner/healthcheck.sh /usr/local/bin/docker-php-healthcheck
COPY --chmod=755 docker/php/roadrunner/entrypoint.sh /usr/local/bin/docker-php-entrypoint

# Create application directories
ENV APP_HOME="/var/www/api" \
    APP_LOGOS_PATH="/var/www/logos" \
    APP_SERVER_INTERNAL="http://127.0.0.1:8080" \
    METRICS_PUBLISH_URL="tcp://127.0.0.1:6001" \
    METRICS_EXPORT_URL="http://127.0.0.1:8081"
RUN mkdir -p ${APP_HOME} ${APP_LOGOS_PATH}
WORKDIR ${APP_HOME}

# Install composer packages
COPY composer.json composer.lock ./
RUN composer install --no-cache --no-dev --no-progress

# Install application sources
COPY openapi.yml ./
COPY src src/
COPY locales locales/
COPY config config/
COPY --chmod=755 bin bin/

ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
EXPOSE 8080
ENTRYPOINT ["docker-php-entrypoint"]
CMD ["rr","serve","--config","/usr/local/etc/rr.yaml","--silent"]

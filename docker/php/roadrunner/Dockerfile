ARG PHP_VERSION
ARG APP_VERSION
FROM ghcr.io/roadrunner-server/roadrunner:2024.1 AS roadrunner
FROM php:${PHP_VERSION}-cli-alpine

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

# Install packages & PHP extensions
RUN set -xe \
    && apk update \
    && apk upgrade \
    && apk add --no-cache gmp gnupg ncurses \
    && apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS} gmp-dev linux-headers \
    && docker-php-ext-install bcmath gmp pcntl pdo_mysql opcache sockets > /tmp/ext-install.log 2>&1 || (cat /tmp/ext-install.log; exit 1) \
    && pecl channel-update pecl.php.net \
    && pecl install apcu > /dev/null \
    && pecl install redis > /dev/null \
    && pecl install xdebug > /dev/null \
    && docker-php-ext-enable apcu redis \
    && pecl clear-cache \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/* /usr/src/*

# Install PHAR tools
COPY docker/php/install-phar.sh /usr/local/bin/install-phar.sh
RUN chmod +x /usr/local/bin/install-phar.sh \
    && install-phar.sh \
    && rm /usr/local/bin/install-phar.sh

# Configure PHP
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Add healthcheck
COPY docker/php/roadrunner/healthcheck.sh /usr/local/bin/docker-php-healthcheck
RUN chmod +x /usr/local/bin/docker-php-healthcheck

# Add entrypoint
COPY docker/php/roadrunner/entrypoint.sh /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint

# Configure RoadRunner
COPY docker/php/roadrunner/.rr.yaml /usr/local/etc/rr.yaml

# Prepare logos directory
ENV APP_LOGOS_PATH="/var/www/logos"
RUN mkdir ${APP_LOGOS_PATH}

# Prepare application directory
ENV APP_HOME="/var/www/api"
RUN mkdir ${APP_HOME}
WORKDIR ${APP_HOME}

# Install dependencies first
ENV COMPOSER_ALLOW_SUPERUSER="1"
COPY composer.lock composer.json ./
RUN composer install --optimize-autoloader --no-cache --no-dev --no-progress

# Install own application sources
COPY templates templates/
COPY src src/
COPY migrations migrations/
COPY config config/
COPY bin bin/

# Apply version branding
RUN sed -i "s/VERSION = 'development';/VERSION = '$APP_VERSION';/" \
    src/Infrastructure/API/Application.php \
    src/Infrastructure/CLI/Application.php

# Configure ownership and permissions
RUN chown www-data:www-data ${APP_LOGOS_PATH} && chmod +x bin/*

ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
EXPOSE 8080
HEALTHCHECK --interval=15s --timeout=1s CMD docker-php-healthcheck || exit 1
CMD lima migrations:migrate -n && rr serve -c /usr/local/etc/rr.yaml

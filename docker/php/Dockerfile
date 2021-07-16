FROM php:7.4-fpm-alpine3.13

# git: required by php-coveralls.phar
ENV RUN_DEPS="gmp ncurses gnupg git" \
    BUILD_DEPS="${PHPIZE_DEPS} gmp-dev" \
    PHP_EXTS="bcmath gmp mysqli pcntl pdo_mysql opcache"

# Install packages & PHP extensions
RUN set -xe \
    && apk update \
    && apk upgrade \
    && apk add --no-cache $RUN_DEPS \
    && apk add --no-cache --virtual .build-deps $BUILD_DEPS \
    && docker-php-ext-install $PHP_EXTS > /dev/null \
    && pecl channel-update pecl.php.net \
    && pecl install apcu > /dev/null \
    && pecl install redis > /dev/null \
    && pecl install xdebug > /dev/null \
    && docker-php-ext-enable apcu redis \
    && pecl clear-cache \
    && apk del .build-deps \
    && rm -rf /tmp/*

# Configure PHP
COPY docker/php/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Prepare application source directory
ENV APP_HOME="/var/www/api"
RUN mkdir -p ${APP_HOME} && chown www-data:www-data ${APP_HOME}
WORKDIR ${APP_HOME}

# Install dependencies first
COPY --chown=www-data:www-data bin bin/
COPY --chown=www-data:www-data composer.lock composer.json depfile.yml migrations.php phpunit.xml ./
RUN chmod +x bin/*
USER www-data
ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
RUN bin/install

# Install own application sources
COPY --chown=www-data:www-data tests tests/
COPY --chown=www-data:www-data templates templates/
COPY --chown=www-data:www-data src src/
COPY --chown=www-data:www-data public public/
COPY --chown=www-data:www-data migrations migrations/
COPY --chown=www-data:www-data config config/

CMD init-container.sh && php-fpm

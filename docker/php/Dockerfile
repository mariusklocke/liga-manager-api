ARG PHP_IMAGE
FROM ${PHP_IMAGE}

ARG APP_VERSION
ENV RUN_DEPS="gmp ncurses gnupg" \
    BUILD_DEPS="${PHPIZE_DEPS} gmp-dev linux-headers" \
    PHP_EXTS="bcmath gmp pcntl pdo_mysql opcache"

# Install packages & PHP extensions
RUN set -xe \
    && apk update \
    && apk upgrade \
    && apk add --no-cache $RUN_DEPS \
    && apk add --no-cache --virtual .build-deps $BUILD_DEPS \
    && docker-php-ext-install $PHP_EXTS > /tmp/ext-install.log 2>&1 || (cat /tmp/ext-install.log; exit 1) \
    && pecl channel-update pecl.php.net \
    && pecl install apcu > /dev/null \
    && pecl install redis > /dev/null \
    && pecl install xdebug > /dev/null \
    && docker-php-ext-enable apcu redis \
    && pecl clear-cache \
    && apk del .build-deps \
    && rm -rf /tmp/* /var/cache/*

# Configure PHP
COPY docker/php/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Prepare application source directory
ENV APP_HOME="/var/www/api"
RUN mkdir -p ${APP_HOME} && chown www-data:www-data ${APP_HOME}
WORKDIR ${APP_HOME}

# Install dependencies first
COPY --chown=www-data:www-data bin bin/
COPY --chown=www-data:www-data composer.lock composer.json deptrac.yaml phpunit.xml ./
RUN chmod +x bin/*
USER www-data
ENV PATH="${PATH}:${APP_HOME}/bin"
RUN bin/lima install
RUN echo "$APP_VERSION" > VERSION

# Install own application sources
COPY --chown=www-data:www-data tests tests/
COPY --chown=www-data:www-data templates templates/
COPY --chown=www-data:www-data src src/
COPY --chown=www-data:www-data public public/
COPY --chown=www-data:www-data migrations migrations/
COPY --chown=www-data:www-data config config/

CMD lima migrations:migrate -n && php-fpm

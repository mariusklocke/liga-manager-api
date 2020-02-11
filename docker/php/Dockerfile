FROM php:7.4-fpm-alpine

# git: required by php-coveralls.phar
ENV RUN_DEPS="gmp ncurses gnupg git" \
    BUILD_DEPS="${PHPIZE_DEPS} gmp-dev shadow" \
    PHP_EXTS="bcmath gmp mysqli pcntl pdo_mysql opcache"

# Install PHP extensions
RUN set -xe \
    && apk update \
    && apk upgrade \
    && apk add --no-cache $RUN_DEPS \
    && apk add --no-cache --virtual .build-deps $BUILD_DEPS \
    && useradd -u 1000 dev \
    && mkdir /home/dev \
    && chown dev:dev /home/dev \
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
COPY docker/php/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Install application sources
ENV APP_HOME="/var/www/api"
RUN mkdir -p ${APP_HOME} && chown www-data:www-data ${APP_HOME}
WORKDIR ${APP_HOME}
COPY --chown=www-data:www-data . .
ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
RUN chmod +x bin/* \
    && rm -r docker \
    && bin/install
CMD init.sh && php-fpm

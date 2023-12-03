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

# Install PHAR tools
COPY docker/php/install-phar.sh /usr/local/bin/install-phar.sh
RUN chmod +x /usr/local/bin/install-phar.sh \
    && install-phar.sh /usr/local/bin/composer https://github.com/composer/composer/releases/download/2.6.5/composer.phar 566a6d1cf4be1cc3ac882d2a2a13817ffae54e60f5aa7c9137434810a5809ffc \
    && install-phar.sh /usr/local/bin/gdpr-dump https://github.com/Smile-SA/gdpr-dump/releases/download/4.0.2/gdpr-dump.phar 50266d719d3967d5e029102c351aa3796ebfbe5ff78b050468522a44c86cbfc6 \
    && rm /usr/local/bin/install-phar.sh

# Configure PHP
COPY docker/php/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini

# Add entrypoint
COPY docker/php/entrypoint.sh /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint

# Prepare application source directory
ENV APP_HOME="/var/www/api"
RUN mkdir ${APP_HOME}
WORKDIR ${APP_HOME}

# Install dependencies first
COPY composer.lock composer.json ./
RUN composer install --optimize-autoloader --no-cache --no-dev --no-progress
RUN echo "$APP_VERSION" > VERSION

# Install own application sources
COPY templates templates/
COPY src src/
COPY public public/
COPY migrations migrations/
COPY config config/
COPY bin bin/

# Configure ownership and permissions
RUN chown -R www-data:www-data ${APP_HOME} && chmod +x bin/*
ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"

CMD lima migrations:migrate -n && php-fpm

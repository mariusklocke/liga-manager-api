FROM php:7.3-fpm-alpine

# Simplify local development by adding developer user with id 1000
RUN set -xe \
    && apk add --no-cache shadow \
    && useradd -u 1000 dev \
    && apk del shadow

ENV PHPIZE_DEPS \
    autoconf \
    cmake \
    file \
    g++ \
    gcc \
    gmp-dev \
    libc-dev \
    pcre-dev \
    make \
    pkgconf \
    re2c

# persistent / runtime deps
RUN apk add --no-cache --virtual .persistent-deps gmp

# Install PHP extensions
RUN set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure mysqli --with-mysqli \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-install bcmath mysqli pcntl pdo_mysql mbstring opcache gmp \
    && pecl install apcu \
    && pecl install redis \
    && pecl install xdebug-beta \
    && apk del .build-deps \
    && rm -rf /tmp/*

# Install PHAR tools
RUN wget -O /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-8.2.5.phar \
    && wget -O /usr/local/bin/composer https://getcomposer.org/composer.phar \
    && chmod +x /usr/local/bin/phpunit /usr/local/bin/composer

# Configure PHP
ARG XDEBUG=0
COPY docker/php/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini
RUN if [ "$XDEBUG" = "1" ] ; then echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini ; fi

# Copy init script
COPY docker/php/init-container.sh /usr/bin/init-container.sh
RUN chmod +x /usr/bin/init-container.sh

# Install application sources
ENV APP_HOME="/var/www/api"
RUN mkdir -p ${APP_HOME}
WORKDIR ${APP_HOME}
COPY migrations.php composer.json composer.lock phpunit.xml ./
RUN composer install --prefer-dist --no-dev --optimize-autoloader --ignore-platform-reqs
COPY bin bin
RUN chmod +x bin/*
COPY config config
COPY migrations migrations
COPY public public
COPY src src
COPY templates templates
COPY tests tests

USER www-data
CMD init-container.sh && php-fpm

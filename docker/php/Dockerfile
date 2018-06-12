FROM php:7.2-fpm-alpine

ENV PHPIZE_DEPS \
    autoconf \
    cmake \
    file \
    g++ \
    gcc \
    libc-dev \
    pcre-dev \
    make \
    pkgconf \
    re2c
ENV APP_HOME="/var/www/api"
ENV JWT_SECRET_PATH="/etc/jwt"

# persistent / runtime deps
RUN apk add --no-cache --virtual .persistent-deps openssh-keygen

# Install PHP extensions
RUN set -xe \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure mysqli --with-mysqli \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-install bcmath mysqli pcntl pdo_mysql mbstring \
    && pecl install apcu \
    && pecl install redis \
    && pecl install xdebug \
    && apk del .build-deps \
    && rm -rf /tmp/*

# Install PHAR tools
RUN wget -O /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-7.0.2.phar \
    && wget -O /usr/local/bin/composer https://getcomposer.org/download/1.6.3/composer.phar \
    && chmod +x /usr/local/bin/phpunit /usr/local/bin/composer

# Workaround for writing logs to stdout
# https://bugs.php.net/bug.php?id=71880
ENV LOG_STREAM="/tmp/stdout"
RUN mkfifo $LOG_STREAM && chmod 777 $LOG_STREAM

# Install application sources
RUN mkdir -p ${APP_HOME}
WORKDIR ${APP_HOME}
COPY composer.json composer.lock phpunit.xml ./
RUN composer install --prefer-dist --no-dev --optimize-autoloader
COPY bin bin
COPY config config
COPY migrations migrations
COPY public public
COPY src src
COPY templates templates
COPY tests tests
COPY docker/php/entrypoint.sh bin/entrypoint.sh
RUN chmod +x bin/*
ENTRYPOINT ["bin/entrypoint.sh"]
CMD php bin/console.php orm:generate-proxies && php-fpm | tail -f $LOG_STREAM

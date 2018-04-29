FROM php:7.2-fpm-alpine

# persistent / runtime deps
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

RUN apk add --no-cache --virtual .persistent-deps \
    openssh-keygen

RUN set -xe \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
    && docker-php-ext-configure bcmath --enable-bcmath \
    && docker-php-ext-configure mysqli --with-mysqli \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-configure pdo_mysql --with-pdo-mysql \
    && docker-php-ext-configure mbstring --enable-mbstring \
    && docker-php-ext-install \
        bcmath \
        mysqli \
        pcntl \
        pdo_mysql \
        mbstring

RUN pecl install apcu
RUN pecl install mongodb
RUN echo "extension=apcu.so" > /usr/local/etc/php/conf.d/apcu.ini
RUN echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini
RUN apk del .build-deps
RUN rm -rf /tmp/* && rm -rf ${APP_HOME} && mkdir -p ${APP_HOME}
RUN wget -O /usr/local/bin/phpunit https://phar.phpunit.de/phpunit-7.0.2.phar
RUN wget -O /usr/local/bin/composer https://getcomposer.org/download/1.6.3/composer.phar
RUN chmod +x /usr/local/bin/*

# Workaround https://bugs.php.net/bug.php?id=71880
ENV LOG_STREAM="/tmp/stdout"
RUN mkfifo $LOG_STREAM && chmod 777 $LOG_STREAM

ADD . ${APP_HOME}

# Possible values for ext-name:
# bcmath bz2 calendar ctype curl dba dom enchant exif fileinfo filter ftp gd gettext gmp hash iconv imap interbase intl
# json ldap mbstring mcrypt mssql mysql mysqli oci8 odbc opcache pcntl pdo pdo_dblib pdo_firebird pdo_mysql pdo_oci
# pdo_odbc pdo_pgsql pdo_sqlite pgsql phar posix pspell readline recode reflection session shmop simplexml snmp soap
# sockets spl standard sybase_ct sysvmsg sysvsem sysvshm tidy tokenizer wddx xml xmlreader xmlrpc xmlwriter xsl zip

WORKDIR ${APP_HOME}
RUN composer install --prefer-dist --no-dev --optimize-autoloader
COPY docker/php/start.sh /usr/local/bin/start.sh
RUN chmod +x ${APP_HOME}/bin/* /usr/local/bin/start.sh
CMD ["/bin/sh", "-c", "start.sh | tail -f $LOG_STREAM"]

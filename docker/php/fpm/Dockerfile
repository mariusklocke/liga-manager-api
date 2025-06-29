FROM alpine:3.22
ARG APP_VERSION
ARG PHP_VERSION

# Define versions
ENV APP_VERSION=${APP_VERSION}
ENV PHP_VERSION=${PHP_VERSION}

# Install packages for PHP and extensions
RUN PHP_EXTENSIONS="apcu bcmath ctype curl dom fileinfo fpm gmp iconv intl mbstring opcache pcntl pdo_mysql pdo_pgsql phar redis simplexml sockets tokenizer xdebug xml xmlreader xmlwriter" \
    && PHP_PACKAGE="php${PHP_VERSION/./}" \
    && PHP_SUBPACKAGES="" \
    && for EXTENSION in ${PHP_EXTENSIONS}; do \
         PHP_SUBPACKAGES="${PHP_SUBPACKAGES} ${PHP_PACKAGE}-${EXTENSION}"; \
       done \
    && set -ex \
    && apk update \
    && apk upgrade \
    && apk add --no-cache curl fcgi icu-data-full ${PHP_PACKAGE} ${PHP_SUBPACKAGES} \
    && ln -s /etc/${PHP_PACKAGE} /etc/php \
    && rm -rf /etc/php/php-fpm.d \
    && rm -rf /etc/php/conf.d \
    && test -e /usr/sbin/php-fpm || ln -s /usr/sbin/php-fpm${PHP_VERSION/./} /usr/sbin/php-fpm \
    && test -e /usr/bin/php || ln -s /usr/bin/${PHP_PACKAGE} /usr/bin/php \
    && adduser -u 82 -D -S -G www-data www-data \
    && rm -rf /var/cache/*

# Configure PHP
COPY docker/php/fpm/php-fpm.conf /etc/php/php-fpm.conf
COPY docker/php/php.ini /etc/php/php.ini

# Add healthcheck
COPY --chmod=755 docker/php/fpm/healthcheck.sh /usr/local/bin/docker-php-healthcheck

# Add entrypoint
COPY --chmod=755 docker/php/fpm/entrypoint.sh /usr/local/bin/docker-php-entrypoint

# Create application directories
ENV APP_HOME="/var/www/api"
ENV APP_LOGOS_PATH="/var/www/logos"
ENV APP_SERVER_INTERNAL="fcgi://127.0.0.1:9000"
RUN mkdir -p ${APP_HOME} ${APP_LOGOS_PATH}
WORKDIR ${APP_HOME}

# Install application sources
COPY openapi.yml ./
COPY vendor vendor/
COPY src src/
COPY public public/
COPY locales locales/
COPY config config/
COPY bin bin/

# Configure permissions
RUN chmod +x bin/*

ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
EXPOSE 9000
ENTRYPOINT ["docker-php-entrypoint"]
CMD ["php-fpm","-F"]

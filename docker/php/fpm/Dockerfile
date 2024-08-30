ARG APP_VERSION
FROM alpine:3.20

# Define PHP version
ENV PHP_VERSION="8.3"

# Install packages for PHP and extensions
RUN PHP_EXTENSIONS="apcu bcmath dom fpm gmp mbstring opcache pcntl pdo_mysql phar redis simplexml sockets tokenizer xdebug xml xmlwriter" \
    && case "${PHP_VERSION}" in \
         8.3) PHP_PACKAGE="php83" ;; \
         8.2) PHP_PACKAGE="php82" ;; \
         *) echo "Unsupported PHP version: ${PHP_VERSION}"; exit 1 ;; \
       esac \
    && PHP_SUBPACKAGES="" \
    && for EXTENSION in ${PHP_EXTENSIONS}; do \
         PHP_SUBPACKAGES="${PHP_SUBPACKAGES} ${PHP_PACKAGE}-${EXTENSION}"; \
       done \
    && set -ex \
    && apk update \
    && apk upgrade \
    && apk add --no-cache fcgi ${PHP_PACKAGE} ${PHP_SUBPACKAGES} \
    && ln -s /etc/${PHP_PACKAGE} /etc/php \
    && test -e /usr/sbin/php-fpm || ln -s /usr/sbin/php-fpm83 /usr/sbin/php-fpm \
    && test -e /usr/bin/php || ln -s /usr/bin/${PHP_PACKAGE} /usr/bin/php \
    && ln -s /etc/${PHP_PACKAGE} /etc/php \
    && adduser -u 82 -D -S -G www-data www-data \
    && rm -rf /var/cache/*

# Install xdebug helper
COPY docker/php/xdebug.sh /usr/local/bin/xdebug
RUN chmod +x /usr/local/bin/xdebug

# Configure PHP
COPY docker/php/fpm/docker.conf /etc/php/php-fpm.d/docker.conf
COPY docker/php/php.ini /etc/php/php.ini
RUN xdebug off

# Install PHAR tools
COPY docker/php/install-phar.sh /usr/local/bin/install-phar.sh
RUN chmod +x /usr/local/bin/install-phar.sh \
    && install-phar.sh \
    && rm /usr/local/bin/install-phar.sh

# Add healthcheck
COPY docker/php/fpm/healthcheck.sh /usr/local/bin/docker-php-healthcheck
RUN chmod +x /usr/local/bin/docker-php-healthcheck

# Add entrypoint
COPY docker/php/fpm/entrypoint.sh /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint

# Create application directories
ENV APP_HOME="/var/www/api"
ENV APP_LOGOS_PATH="/var/www/logos"
RUN mkdir -p ${APP_HOME} ${APP_LOGOS_PATH}
WORKDIR ${APP_HOME}

# Install dependencies first
ENV COMPOSER_ALLOW_SUPERUSER="1"
COPY composer.lock composer.json ./
RUN composer install --optimize-autoloader --no-cache --no-dev --no-progress

# Install own application sources
COPY templates templates/
COPY src src/
COPY public public/
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
HEALTHCHECK --interval=15s --timeout=1s CMD docker-php-healthcheck || exit 1
CMD lima migrations:migrate -n && php-fpm -F

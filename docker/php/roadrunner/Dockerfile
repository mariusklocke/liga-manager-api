FROM ghcr.io/roadrunner-server/roadrunner:2025.1.1 AS roadrunner
FROM alpine:3.21
ARG APP_VERSION
ARG PHP_VERSION

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

# Define versions
ENV APP_VERSION=${APP_VERSION}
ENV PHP_VERSION=${PHP_VERSION}

# Install packages for PHP and extensions
RUN PHP_EXTENSIONS="apcu bcmath ctype curl dom fileinfo gmp iconv intl mbstring opcache pcntl pdo_mysql pdo_pgsql phar redis simplexml sockets tokenizer xdebug xml xmlreader xmlwriter" \
    && PHP_PACKAGE="php${PHP_VERSION/./}" \
    && PHP_SUBPACKAGES="" \ 
    && for EXTENSION in ${PHP_EXTENSIONS}; do \
         PHP_SUBPACKAGES="${PHP_SUBPACKAGES} ${PHP_PACKAGE}-${EXTENSION}"; \
       done \
    && set -ex \
    && apk update \
    && apk upgrade \
    && apk add --no-cache curl icu-data-full ${PHP_PACKAGE} ${PHP_SUBPACKAGES} \
    && ln -s /etc/${PHP_PACKAGE} /etc/php \
    && test -e /usr/bin/php || ln -s /usr/bin/${PHP_PACKAGE} /usr/bin/php \
    && adduser -u 82 -D -S -G www-data www-data \
    && curl -sSL -o /usr/local/bin/composer https://getcomposer.org/download/latest-stable/composer.phar \
    && chmod +x /usr/local/bin/composer \
    && rm -rf /var/cache/*

# Configure PHP
COPY docker/php/php.ini /etc/php/php.ini

# Configure RoadRunner
COPY docker/php/roadrunner/.rr.yaml /usr/local/etc/rr.yaml

# Add healthcheck
COPY docker/php/roadrunner/healthcheck.sh /usr/local/bin/docker-php-healthcheck
RUN chmod +x /usr/local/bin/docker-php-healthcheck

# Add entrypoint
COPY docker/php/roadrunner/entrypoint.sh /usr/local/bin/docker-php-entrypoint
RUN chmod +x /usr/local/bin/docker-php-entrypoint

# Add extension helper script
COPY docker/php/php-ext-configure.sh /usr/local/bin/php-ext-configure
RUN chmod +x /usr/local/bin/php-ext-configure

# Create application directories
ENV APP_HOME="/var/www/api"
ENV APP_LOGOS_PATH="/var/www/logos"
ENV APP_SERVER_INTERNAL="http://127.0.0.1:8080"
ENV METRICS_PUBLISH_URL="tcp://127.0.0.1:6001"
ENV METRICS_EXPORT_URL="http://127.0.0.1:8081"
RUN mkdir -p ${APP_HOME} ${APP_LOGOS_PATH}
WORKDIR ${APP_HOME}

# Install dependencies first
ENV COMPOSER_ALLOW_SUPERUSER="1"
COPY composer.lock composer.json openapi.yml ./
RUN composer install --optimize-autoloader --no-cache --no-dev --no-progress

# Install own application sources
COPY src src/
COPY locales locales/
COPY config config/
COPY bin bin/

# Configure permissions
RUN chmod +x bin/*

ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
EXPOSE 8080
ENTRYPOINT ["docker-php-entrypoint"]
CMD ["rr","serve","--config","/usr/local/etc/rr.yaml","--silent"]

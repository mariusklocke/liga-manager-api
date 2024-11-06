FROM alpine:3.20
ARG APP_VERSION

# Define PHP version
ENV PHP_VERSION="8.3"

# Install packages for PHP and extensions
RUN PHP_EXTENSIONS="apcu bcmath ctype curl dom fileinfo fpm gmp mbstring opcache pcntl pdo_mysql phar redis simplexml sockets tokenizer xdebug xml xmlreader xmlwriter" \
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
    && apk add --no-cache composer fcgi ${PHP_PACKAGE} ${PHP_SUBPACKAGES} \
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
COPY docker/php/fpm/docker.conf /etc/php/php-fpm.d/zz-docker.conf
COPY docker/php/php.ini /etc/php/php.ini
RUN xdebug off

# Add healthcheck
COPY docker/php/healthcheck.php /usr/local/bin/docker-php-healthcheck
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
RUN set -e \
    && composer install --optimize-autoloader --no-cache --no-dev --no-progress \
    && composer config version ${APP_VERSION}

# Install own application sources
COPY templates templates/
COPY src src/
COPY public public/
COPY migrations migrations/
COPY config config/
COPY bin bin/

# Configure permissions
RUN chmod +x bin/*

ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
EXPOSE 9000
ENTRYPOINT ["docker-php-entrypoint"]
CMD lima orm:generate-proxies && lima migrations:migrate -n && php-fpm -F

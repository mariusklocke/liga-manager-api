ARG PHP_VERSION
ARG APP_VERSION
FROM alpine:3.20

# Add www-data user
RUN adduser -u 82 -D -S -G www-data www-data

# Install packages for PHP and extensions
COPY docker/php/fpm/packages.sh /usr/local/bin/install-packages.sh
RUN chmod +x /usr/local/bin/install-packages.sh \
    && install-packages.sh \
    && rm /usr/local/bin/install-packages.sh

# Install xdebug helper
COPY docker/php/fpm/xdebug.sh /usr/local/bin/xdebug
RUN chmod +x /usr/local/bin/xdebug

# Install PHAR tools
COPY docker/php/install-phar.sh /usr/local/bin/install-phar.sh
RUN chmod +x /usr/local/bin/install-phar.sh \
    && install-phar.sh \
    && rm /usr/local/bin/install-phar.sh

# Configure PHP
COPY docker/php/fpm/docker.conf /usr/local/etc/php-fpm.d/docker.conf
COPY docker/php/php.ini /usr/local/etc/php/php.ini
RUN xdebug off

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
CMD lima migrations:migrate -n && php-fpm

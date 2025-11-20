FROM ghcr.io/roadrunner-server/roadrunner:2025.1.4 AS roadrunner
FROM alpine:3.22
ARG APP_VERSION
ARG PHP_VERSION

COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr

# Define versions
ENV APP_VERSION=${APP_VERSION}
ENV PHP_VERSION=${PHP_VERSION}

# Install packages for PHP and extensions
RUN PHP_EXTENSIONS="apcu bcmath ctype curl dom fileinfo gd gmp iconv intl mbstring pcntl pdo_mysql pdo_pgsql phar redis simplexml sockets tokenizer xdebug xml xmlreader xmlwriter" \
    && PHP_PACKAGE="php${PHP_VERSION/./}" \
    && PHP_SUBPACKAGES="" \ 
    && for EXTENSION in ${PHP_EXTENSIONS}; do \
         PHP_SUBPACKAGES="${PHP_SUBPACKAGES} ${PHP_PACKAGE}-${EXTENSION}"; \
       done \
    && set -ex \
    && echo "https://dl-cdn.alpinelinux.org/alpine/edge/main" > /etc/apk/repositories \
    && echo "https://dl-cdn.alpinelinux.org/alpine/edge/community" >> /etc/apk/repositories \
    && apk update \
    && apk upgrade \
    && apk add --no-cache curl icu-data-full ${PHP_PACKAGE} ${PHP_SUBPACKAGES} \
    && ln -s /etc/${PHP_PACKAGE} /etc/php \
    && rm -rf /etc/php/conf.d \
    && test -e /usr/bin/php || ln -s /usr/bin/${PHP_PACKAGE} /usr/bin/php \
    && adduser -u 82 -D -S -G www-data -h /var/www www-data \
    && rm -rf /etc/alpine-release /etc/apk /etc/crontabs /etc/init.d /etc/logrotate.d /etc/modprobe.d /etc/opt /etc/periodic /etc/secfixes.d /etc/ssl/*.dist \
              /etc/group- /etc/passwd- /etc/shadow- \
              /etc/sysctl.conf /etc/sysctl.d /etc/udhcpc \
              /etc/protocols /etc/services \
              /etc/fstab /etc/inittab /etc/motd \
              /lib/apk /lib/firmware /lib/modules-load.d /lib/sysctl.d \
              /root/.ash_history \
              /sbin/apk \
              /usr/lib/libapk.so.* /usr/include/php84 /usr/share/apk /usr/share/vim /usr/share/X11 \
              /var/cache/*

# Configure PHP
COPY docker/php/php.ini /etc/php/php.ini

# Configure RoadRunner
COPY docker/php/roadrunner/.rr.yaml /usr/local/etc/rr.yaml

# Add healthcheck
COPY --chmod=755 docker/php/roadrunner/healthcheck.sh /usr/local/bin/docker-php-healthcheck

# Add entrypoint
COPY --chmod=755 docker/php/roadrunner/entrypoint.sh /usr/local/bin/docker-php-entrypoint

# Create application directories
ENV APP_HOME="/var/www/api"
ENV APP_LOGOS_PATH="/var/www/logos"
ENV APP_SERVER_INTERNAL="http://127.0.0.1:8080"
ENV METRICS_PUBLISH_URL="tcp://127.0.0.1:6001"
ENV METRICS_EXPORT_URL="http://127.0.0.1:8081"
RUN mkdir -p ${APP_HOME} ${APP_LOGOS_PATH}
WORKDIR ${APP_HOME}

# Install application sources
COPY openapi.yml ./
COPY vendor vendor/
COPY src src/
COPY locales locales/
COPY config config/
COPY --chmod=755 bin bin/

ENV PATH="${PATH}:${APP_HOME}/bin:${APP_HOME}/vendor/bin"
EXPOSE 8080
ENTRYPOINT ["docker-php-entrypoint"]
CMD ["rr","serve","--config","/usr/local/etc/rr.yaml","--silent"]

#!/bin/sh
set -e

echo "extension=apcu.so" > /usr/local/etc/php/php.ini
echo "extension=redis.so" >> /usr/local/etc/php/php.ini
if [ "x$ENABLE_XDEBUG" != "x" ]; then
  echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

exec "$@"
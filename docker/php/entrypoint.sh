#!/bin/sh
set -e

echo "error_reporting=E_ALL" > /usr/local/etc/php/php.ini
echo "error_log=${LOG_STREAM}" >> /usr/local/etc/php/php.ini
echo "log_errors=On" >> /usr/local/etc/php/php.ini
echo "extension=apcu.so" >> /usr/local/etc/php/php.ini
echo "extension=redis.so" >> /usr/local/etc/php/php.ini
if [ "x$ENABLE_XDEBUG" != "x" ]; then
  echo "zend_extension=xdebug.so" >> /usr/local/etc/php/php.ini
fi

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

exec "$@"
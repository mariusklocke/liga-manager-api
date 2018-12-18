#!/bin/sh
set -e

echo "error_reporting=E_ALL" > /usr/local/etc/php/php.ini
echo "error_log=/var/log/php-error.log" >> /usr/local/etc/php/php.ini
echo "log_errors=On" >> /usr/local/etc/php/php.ini
echo "expose_php=Off" >> /usr/local/etc/php/php.ini
echo "short_open_tag=Off" >> /usr/local/etc/php/php.ini
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
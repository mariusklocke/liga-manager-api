#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ -e /mnt/logos ]; then
    chown www-data:www-data /mnt/logos
fi

exec "$@"

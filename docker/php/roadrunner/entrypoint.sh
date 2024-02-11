#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php "$@"
fi

if [ -e /var/www/logos ]; then
    chown www-data:www-data /var/www/logos
fi

exec "$@"

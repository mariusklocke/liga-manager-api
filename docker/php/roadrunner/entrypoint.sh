#!/bin/sh
set -e

if [ "$1" = "rr" ]; then
    chown www-data:www-data /var/www/logos
    lima orm:generate-proxies --quiet
    lima app:db:migrate --no-interaction
fi

exec "$@"

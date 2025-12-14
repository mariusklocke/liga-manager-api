#!/bin/sh
set -e

if [ "$1" = "rr" ]; then
    chown www-data:www-data /var/www/logos
    lima app:db:migrate
fi

exec "$@"

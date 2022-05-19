#!/bin/sh
set -e

lima app:health --skip=fpm --retries=10
lima migrations:migrate -n

exec "$@"

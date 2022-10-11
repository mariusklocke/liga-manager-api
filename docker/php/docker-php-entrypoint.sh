#!/bin/sh
set -e

lima migrations:migrate -n

exec "$@"

#!/bin/sh
set -e

lima app:maintenance --mode=on
lima app:health --skip=fpm --retries=10
lima migrations:migrate -n
lima app:maintenance --mode=off

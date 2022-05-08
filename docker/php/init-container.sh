#!/bin/sh
set -e

lima app:maintenance --mode=on
lima app:health --skip=fpm --retries=20
lima migrations:migrate -n
lima app:maintenance --mode=off

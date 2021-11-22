#!/bin/sh
set -e

lima app:maintenance --mode=on
wait-for ${MYSQL_HOST} 3306
doctrine orm:generate-proxies --quiet
doctrine-migrations migrations:migrate -n
lima app:maintenance --mode=off

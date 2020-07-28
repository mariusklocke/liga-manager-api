#!/bin/sh
set -e
source wait-for.sh

wait_for ${MYSQL_HOST} 3306
doctrine orm:generate-proxies --quiet
doctrine-migrations migrations:migrate -n

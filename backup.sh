#!/usr/bin/env bash

source .env
docker-compose exec mariadb sh -c "exec mysqldump -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE}" > $1

#!/usr/bin/env bash

source .env
docker-compose exec -T mariadb sh -c "exec mysql -u${MYSQL_USER} -p${MYSQL_PASSWORD} ${MYSQL_DATABASE}" < $1

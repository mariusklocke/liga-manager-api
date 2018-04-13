#!/usr/bin/env bash

if [[ -z "${TAG}" ]]; then
  TAG="latest"
fi
if [[ -z "${JWT_SECRET_PATH}" ]]; then
  JWT_SECRET_PATH="/etc/jwt"
fi
docker run -d --name mariadb --env-file .env mariadb > /dev/null
docker run --link mariadb --rm dadarek/wait-for-dependencies mariadb:3306
docker run --link mariadb --env-file .env --rm -v $JWT_SECRET_PATH:/etc/jwt mklocke/liga-manager-api:$TAG sh -c "bin/console.php orm:schema-tool:create && bin/console.php app:load-fixtures && phpunit"
docker stop mariadb > /dev/null
docker rm mariadb > /dev/null
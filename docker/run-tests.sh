#!/usr/bin/env bash

if [[ -z "${TAG}" ]]; then
  TAG="latest"
fi
if [[ -z "${JWT_SECRET_PATH}" ]]; then
  JWT_SECRET_PATH="/etc/jwt"
fi
trap 'rc=$?' ERR
docker run -d --name mariadb --env-file .env mariadb > /dev/null
docker run -d --name mongo mongo > /dev/null
docker run --link mariadb --rm dadarek/wait-for-dependencies mariadb:3306
docker run --link mongo --rm dadarek/wait-for-dependencies mongo:27017
docker run --link mariadb --link mongo --env-file .env --rm mklocke/liga-manager-api:$TAG sh -c "bin/generate-jwt-key.sh && bin/console.php orm:schema-tool:create && bin/console.php app:load-fixtures && phpunit"
docker stop mariadb mongo > /dev/null
docker rm mariadb mongo > /dev/null
exit ${rc}
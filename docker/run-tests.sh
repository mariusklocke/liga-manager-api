#!/usr/bin/env bash

if [[ -z "${TAG}" ]]; then
  TAG="latest"
fi
if [[ -z "${JWT_SECRET_PATH}" ]]; then
  JWT_SECRET_PATH="/etc/jwt"
fi
trap 'rc=$?' ERR
docker run -d --name mariadb \
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes -e MYSQL_DATABASE=test -e MYSQL_USER=test -e MYSQL_PASSWORD=test \
    mariadb > /dev/null
docker run -d --name redis redis:4-alpine > /dev/null
docker run --link redis --rm dadarek/wait-for-dependencies redis:6379
docker run --link mariadb --rm dadarek/wait-for-dependencies mariadb:3306
docker run --link mariadb --link redis --rm \
    -e MYSQL_HOST=mariadb -e MYSQL_DATABASE=test -e MYSQL_USER=test -e MYSQL_PASSWORD=test \
    -e REDIS_HOST=redis \
    mklocke/liga-manager-api:$TAG sh -c "bin/generate-jwt-key.sh && bin/console.php orm:schema-tool:create && bin/console.php app:load-fixtures && bin/console.php orm:generate-proxies && phpunit"
docker stop mariadb redis > /dev/null
docker rm mariadb redis > /dev/null
exit ${rc}
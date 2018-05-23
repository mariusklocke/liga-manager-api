#!/usr/bin/env bash

if [[ -z "${TAG}" ]]; then
  TAG="latest"
fi
if [[ -z "${JWT_SECRET_PATH}" ]]; then
  JWT_SECRET_PATH="/etc/jwt"
fi
REPORT_PATH=$(mktemp -d)

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
    mklocke/liga-manager-api:${TAG} sh -c "bin/generate-jwt-key.sh && bin/init-db.sh && bin/console.php orm:generate-proxies && phpunit"
docker run --link mariadb --link redis --rm \
    -e MYSQL_HOST=mariadb -e MYSQL_DATABASE=test -e MYSQL_USER=test -e MYSQL_PASSWORD=test \
    -e REDIS_HOST=redis \
    -v ${REPORT_PATH}:/report \
    mklocke/liga-manager-api:${TAG}-xdebug sh -c "bin/generate-jwt-key.sh && bin/init-db.sh && bin/console.php orm:generate-proxies && phpunit"
xdg-open file://${REPORT_PATH}/index.html > /dev/null 2>&1
docker rm -f mariadb redis > /dev/null
exit ${rc}
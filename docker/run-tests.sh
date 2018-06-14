#!/usr/bin/env bash

if [[ -z "${TAG}" ]]; then
  TAG="latest"
fi
if [[ -z "${JWT_SECRET_PATH}" ]]; then
  JWT_SECRET_PATH="/etc/jwt"
fi

trap 'rc=$?' ERR

MYSQL_ENV_ARGS="-e MYSQL_ALLOW_EMPTY_PASSWORD=yes -e MYSQL_HOST=mariadb -e MYSQL_DATABASE=test -e MYSQL_USER=test -e MYSQL_PASSWORD=test"

docker run -d --name mariadb ${MYSQL_ENV_ARGS} mariadb > /dev/null
docker run -d --name redis redis:4-alpine > /dev/null
docker run --link redis --rm dadarek/wait-for-dependencies redis:6379
docker run --link mariadb --rm dadarek/wait-for-dependencies mariadb:3306
docker run --link mariadb --link redis --rm ${MYSQL_ENV_ARGS} -e REDIS_HOST=redis \
    mklocke/liga-manager-api:${TAG} sh -c "bin/install.sh && phpunit"
docker run --link mariadb --link redis --rm ${MYSQL_ENV_ARGS} -e REDIS_HOST=redis -e ENABLE_XDEBUG=1 \
    mklocke/liga-manager-api:${TAG} sh -c "bin/install.sh && phpunit"
docker rm -f mariadb redis > /dev/null
exit ${rc}
#!/usr/bin/env bash
set -eux

docker run --rm -v $PWD:/app -u $(id -u):$(id -g) --userns host \
    composer install --ignore-platform-reqs --no-cache --no-dev --no-progress

docker build \
    --build-arg APP_VERSION=${APP_VERSION} \
    --build-arg PHP_VERSION=${PHP_VERSION} \
    --file "docker/php/${APP_RUNTIME}/Dockerfile" \
    --pull \
    --tag "mklocke/liga-manager-api:${APP_TAG}" \
    .

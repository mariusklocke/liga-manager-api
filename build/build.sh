#!/usr/bin/env bash
set -eux

docker build \
    --build-arg APP_VERSION=${APP_VERSION} \
    --build-arg PHP_VERSION=${PHP_VERSION} \
    --file "docker/php/${APP_RUNTIME}/Dockerfile" \
    --pull \
    --tag "mklocke/liga-manager-api:${APP_TAG}" \
    .
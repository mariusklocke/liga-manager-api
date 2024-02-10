#!/usr/bin/env bash

if [[ -z "${PHP_VERSION}" ]]; then
    PHP_VERSION="8.3"
fi
if [[ -z "${MARIADB_VERSION}" ]]; then
    MARIADB_VERSION="10.11"
fi
if [[ -z "${REDIS_VERSION}" ]]; then
    REDIS_VERSION="6"
fi

PHP_IMAGE="php:${PHP_VERSION}-cli-alpine"

if [[ $GITHUB_REF == *"refs/tags"* ]]; then
    TAG=${GITHUB_REF##refs/tags/}
else
    TAG="latest"
fi

TARGET_IMAGE="mklocke/liga-manager-api:${TAG}"

# Enable strict error handling
set -e

echo "Pulling image ${PHP_IMAGE} ..."
docker pull --quiet ${PHP_IMAGE}
echo "Building image ${TARGET_IMAGE} ..."
DOCKER_BUILDKIT=1 docker build \
    -f docker/php/Dockerfile \
    -t ${TARGET_IMAGE} \
    --build-arg PHP_IMAGE=$PHP_IMAGE \
    --build-arg APP_VERSION=$TAG \
    --cache-from ${TARGET_IMAGE} .

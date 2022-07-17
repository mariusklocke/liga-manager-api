#!/usr/bin/env bash

if [[ -z "${PHP_VERSION}" ]]; then
    PHP_VERSION="8.1"
fi
if [[ -z "${MARIADB_VERSION}" ]]; then
    MARIADB_VERSION="10.4"
fi
if [[ -z "${REDIS_VERSION}" ]]; then
    REDIS_VERSION="5"
fi

IMAGE="mklocke/liga-manager-api"
if [[ $GITHUB_REF == *"refs/tags"* ]]; then
  TAG=$(sed 's#refs/tags/##' <<< "${GITHUB_REF}")
else
  TAG="latest"
fi

cleanup() {
    docker rm -f php mariadb redis > /dev/null
    docker network rm build > /dev/null
}

# Make sure we clean up running containers in case of error
trap cleanup EXIT

# Enable strict error handling and writing commands to stdout
set -ex

# Creating build network
docker network create build

# Start MariaDB
docker pull --quiet mariadb:$MARIADB_VERSION
docker run -d --name=mariadb --network=build \
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    -e MYSQL_DATABASE=test \
    -e MYSQL_USER=test \
    -e MYSQL_PASSWORD=test \
    mariadb:$MARIADB_VERSION

# Start Redis
docker pull --quiet redis:$REDIS_VERSION-alpine
docker run -d --name=redis --network=build redis:$REDIS_VERSION-alpine

# Build target image
docker pull --quiet $IMAGE:latest
DOCKER_BUILDKIT=1 docker build \
    -f docker/php/Dockerfile \
    -t $IMAGE:$TAG \
    --build-arg PHP_VERSION=$PHP_VERSION \
    --cache-from $IMAGE:latest .

# Start built image
docker run -d --name=php --network=build \
     -e ALLOW_TESTS=1 \
     -e ADMIN_EMAIL=admin@example.com \
     -e ADMIN_PASSWORD=123456 \
     -e LOG_LEVEL=warning \
     -e REDIS_HOST=redis \
     -e JWT_SECRET=a194be3811fc \
     -e MYSQL_HOST=mariadb \
     -e MYSQL_DATABASE=test \
     -e MYSQL_USER=test \
     -e MYSQL_PASSWORD=test \
     -e EMAIL_URL=null://localhost \
     -e EMAIL_SENDER_ADDRESS=noreply@example.com \
     -e EMAIL_SENDER_NAME=noreply \
     -v $PWD/.git:/var/www/api/.git \
     $IMAGE:$TAG

# Run deptrac
docker exec -t php bin/deptrac.phar --no-progress

# Test gdpr-dump config
docker exec -t php gdpr-dump.phar config/gdpr-dump.yml > /dev/null

# Run phpunit without coverage
docker exec -t php phpunit.phar --testdox

# Install git && enable xdebug
docker exec -t -u root php sh -c "apk add git && docker-php-ext-enable xdebug"

# Run tests with coverage
docker exec -t php phpunit.phar --coverage-clover /tmp/clover.xml

# Disable printing the following commands to prevent credential leaking
set +x

if [[ -n "${UPLOAD_COVERAGE}" ]]; then
    # Workaround for git's "dubious ownership" issue
    docker exec -t php git config --global --add safe.directory /var/www/api

    # Upload coverage report to coveralls.io
    docker exec -t -e COVERALLS_RUN_LOCALLY -e COVERALLS_REPO_TOKEN php \
        php-coveralls.phar -v -x /tmp/clover.xml -o /tmp/coveralls.json
fi

if [[ -n "${PUBLISH_IMAGE}" ]]; then
    # Login to docker hub
    echo "$DOCKER_TOKEN" | docker login -u "$DOCKER_USER" --password-stdin

    # Push image to docker hub
    docker push --quiet $IMAGE:$TAG
fi

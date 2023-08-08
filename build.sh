#!/usr/bin/env bash

if [[ -z "${PHP_VERSION}" ]]; then
    PHP_VERSION="8.1"
fi
if [[ -z "${MARIADB_VERSION}" ]]; then
    MARIADB_VERSION="10.6"
fi
if [[ -z "${REDIS_VERSION}" ]]; then
    REDIS_VERSION="6"
fi

IMAGE="mklocke/liga-manager-api"
if [[ $GITHUB_REF == *"refs/tags"* ]]; then
  TAG=$(sed 's#refs/tags/##' <<< "${GITHUB_REF}")
else
  TAG="latest"
fi

cleanup() {
    echo "Removing containers ..."
    docker rm -f php mariadb redis > /dev/null
    echo "Removing network ..."
    docker network rm build > /dev/null
    echo "Cleanup completed"
}

# Make sure we clean up containers and networks in case of error
trap cleanup EXIT

# Enable strict error handling
set -e

echo "Creating network ..."
docker network create build

echo "Pulling MariaDB image ..."
docker pull --quiet mariadb:$MARIADB_VERSION
echo "Starting MariaDB container ..."
docker run -d --name=mariadb --network=build \
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    -e MYSQL_DATABASE=test \
    -e MYSQL_USER=test \
    -e MYSQL_PASSWORD=test \
    mariadb:$MARIADB_VERSION

echo "Pulling Redis image ..."
docker pull --quiet redis:$REDIS_VERSION-alpine
echo "Starting Redis container ..."
docker run -d --name=redis --network=build redis:$REDIS_VERSION-alpine

echo "Pulling the target image ..."
docker pull --quiet $IMAGE:latest
echo "Building the target image ..."
DOCKER_BUILDKIT=1 docker build \
    -f docker/php/Dockerfile \
    -t $IMAGE:$TAG \
    --build-arg PHP_VERSION=$PHP_VERSION \
    --cache-from $IMAGE:latest .

echo "Starting the target image ..."
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
     -v $PWD/.git:/var/www/api/.git \
     $IMAGE:$TAG

attempt=0
while [ $attempt -le 10 ]; do
    attempt=$(( $attempt + 1 ))
    echo "Waiting until containers are ready ... Attempt $attempt"
    if docker exec -t php pgrep -o php-fpm > /dev/null ; then
        echo "Containers are ready for testing"
        break
    fi
    sleep 2
done

echo "Running deptrac ..."
docker exec -t php bin/deptrac.phar --no-progress

echo "Testing gdpr-dump config ..."
docker exec -t php gdpr-dump.phar config/gdpr-dump.yml > /dev/null

echo "Running phpunit tests ..."
docker exec -t php phpunit.phar --display-deprecations

echo "Enabling xdebug ..."
docker exec -t -u root php docker-php-ext-enable xdebug

echo "Running phpunit tests with coverage ..."
docker exec -t php phpunit.phar --coverage-clover /tmp/clover.xml --display-deprecations

if [[ -n "${UPLOAD_COVERAGE}" ]]; then
    echo "Installing git ..."
    docker exec -t -u root php apk add git

    echo "Applying fix for git's dubious ownership issue ..."
    docker exec -t php git config --global --add safe.directory /var/www/api

    echo "Uploading coverage report to coveralls.io ..."
    docker exec -t -e COVERALLS_RUN_LOCALLY -e COVERALLS_REPO_TOKEN php \
        php-coveralls.phar -v -x /tmp/clover.xml -o /tmp/coveralls.json
fi

if [[ -n "${PUBLISH_IMAGE}" ]]; then
    echo "Logging in to docker hub ..."
    echo "$DOCKER_TOKEN" | docker login -u "$DOCKER_USER" --password-stdin

    echo "Pushing image to docker hub ..."
    docker push --quiet $IMAGE:$TAG
fi

echo "Build completed"

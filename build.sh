#!/usr/bin/env bash
set -e

if [[ -z "${PHP_VERSION}" ]]; then
    PHP_VERSION="8.0"
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

echo "Building ${IMAGE}:${TAG} with:"
echo "GITHUB_REF: ${GITHUB_REF}"
echo "PHP_VERSION: ${PHP_VERSION}"
echo "MARIADB_VERSION: ${MARIADB_VERSION}"
echo "REDIS_VERSION: ${REDIS_VERSION}"

# Pull for having a cache base
docker pull $IMAGE:latest

# Build images
DOCKER_BUILDKIT=1 docker build -f docker/php/Dockerfile -t $IMAGE:$TAG --build-arg PHP_VERSION=$PHP_VERSION --cache-from $IMAGE:latest .

cleanup() {
    echo 'Cleanup: Removing containers ...'
    docker rm -f php mariadb redis
    docker network rm build
}

# Make sure we clean up running containers in case of error
trap cleanup EXIT

# Launch containers
docker network create build
docker run -d --name=mariadb --network=build --pull=always --env-file=build.env mariadb:$MARIADB_VERSION
docker run -d --name=redis --network=build --pull=always redis:$REDIS_VERSION-alpine
docker run -d --name=php --network=build --env-file=build.env $IMAGE:$TAG

# Run deptrac
docker exec -t php bin/deptrac.phar --no-progress

# Wait until FPM is ready
docker exec -t php wait-for 127.0.0.1 9000

# Run phpunit without coverage
docker exec -t php phpunit.phar --testdox

if [[ -n "${UPLOAD_COVERAGE}" ]]; then
    # Enable xdebug
    docker exec -t -u root php docker-php-ext-enable xdebug

    # Run tests with coverage
    docker exec -t php phpunit.phar --coverage-clover /tmp/clover.xml

    # Upload coverage report to coveralls.io
    docker exec -t -e COVERALLS_RUN_LOCALLY -e COVERALLS_REPO_TOKEN php php-coveralls.phar -v -x /tmp/clover.xml -o /tmp/coveralls.json
fi

if [[ -n "${PUBLISH_IMAGE}" ]]; then
    # Login to docker hub
    echo "$DOCKER_PASS" | docker login -u "$DOCKER_USER" --password-stdin

    # Push image to docker hub
    docker push $IMAGE:$TAG
fi

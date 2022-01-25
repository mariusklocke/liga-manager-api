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
    rm -rf coverage
}

# Make sure we clean up running containers in case of error
trap cleanup EXIT

# Launch containers
docker network create build
docker run -d --name=mariadb --network=build --pull=always \
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    -e MYSQL_DATABASE=test \
    -e MYSQL_USER=test \
    -e MYSQL_PASSWORD=test \
    mariadb:$MARIADB_VERSION
docker run -d --name=redis --network=build --pull=always redis:$REDIS_VERSION-alpine
docker run -d --name=php --network=build -v "${PWD}/coverage:/coverage" \
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
     $IMAGE:$TAG

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
    docker exec -t php phpunit.phar --coverage-clover /coverage/clover.xml

    # Build codecov uploader container
    docker build -f docker/codecov/Dockerfile -t codecov:latest

    # Upload coverage report to codecov.io
    docker run -t -e CODECOV_TOKEN -v "${PWD}:/app" -w /app codecov:latest codecov -f coverage/clover.xml
fi

if [[ -n "${PUBLISH_IMAGE}" ]]; then
    # Login to docker hub
    echo "$DOCKER_PASS" | docker login -u "$DOCKER_USER" --password-stdin

    # Push image to docker hub
    docker push $IMAGE:$TAG
fi

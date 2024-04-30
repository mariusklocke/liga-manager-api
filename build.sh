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
if [[ -z "${TARGET_TYPE}" ]]; then
    TARGET_TYPE="fpm"
fi

MARIADB_IMAGE="mariadb:${MARIADB_VERSION}"
REDIS_IMAGE="redis:${REDIS_VERSION}-alpine"

if [[ "${GITHUB_REF_TYPE}" == "tag" ]]; then
    TAG="${GITHUB_REF_NAME}"
else
    TAG="latest"
fi

TARGET_IMAGE="mklocke/liga-manager-api:${TAG}"
if [[ "${TARGET_TYPE}" != "fpm" ]]; then
    TARGET_IMAGE="${TARGET_IMAGE}-${TARGET_TYPE}"
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

echo "Starting MariaDB container from ${MARIADB_IMAGE} ..."
docker run -d --name=mariadb --network=build --pull=always \
    -e MYSQL_ALLOW_EMPTY_PASSWORD=yes \
    -e MYSQL_DATABASE=test \
    -e MYSQL_USER=test \
    -e MYSQL_PASSWORD=test \
    ${MARIADB_IMAGE}

echo "Starting Redis container from ${REDIS_IMAGE} ..."
docker run -d --name=redis --network=build --pull=always ${REDIS_IMAGE}

echo "Building image ${TARGET_IMAGE} ..."
DOCKER_BUILDKIT=1 docker build \
    -f docker/php/${TARGET_TYPE}/Dockerfile \
    -t ${TARGET_IMAGE} \
    --build-arg "PHP_VERSION=$PHP_VERSION" \
    --build-arg "APP_VERSION=$TAG" \
    --cache-from ${TARGET_IMAGE} . \
    --pull

echo "Starting container from image ${TARGET_IMAGE} ..."
docker run -d --name=php --network=build \
     -e ADMIN_EMAIL=admin@example.com \
     -e ADMIN_PASSWORD=123456 \
     -e LOG_LEVEL=warning \
     -e REDIS_HOST=redis \
     -e JWT_SECRET=a194be3811fc \
     -e MYSQL_HOST=mariadb \
     -e MYSQL_DATABASE=test \
     -e MYSQL_USER=test \
     -e MYSQL_PASSWORD=test \
     -v "$PWD/.git:/var/www/api/.git" \
     -v "$PWD/tests:/var/www/api/tests" \
     ${TARGET_IMAGE}

attempt=0
while [ $attempt -le 10 ]; do
    attempt=$(( $attempt + 1 ))
    echo "Waiting for PHP container to be become healthy ... Attempt $attempt"
    if docker exec -t php docker-php-healthcheck > /dev/null ; then
        echo "PHP container is healthy"
        break
    fi
    sleep 2
done

echo "Install dev dependencies ..."
docker exec -t php composer install --no-cache --no-progress

echo "Running deptrac ..."
docker exec -t php deptrac --no-progress --config-file=config/deptrac.yaml

echo "Testing gdpr-dump config ..."
docker exec -t php gdpr-dump config/gdpr-dump.yml > /dev/null

echo "Running phpunit tests ..."
docker exec -t php phpunit -c config/phpunit.xml --display-deprecations

echo "Enabling xdebug ..."
docker exec -t -u root php docker-php-ext-enable xdebug

echo "Running phpunit tests with coverage ..."
docker exec -t php phpunit -c config/phpunit.xml --coverage-clover clover.xml --display-deprecations

if [[ -n "${UPLOAD_COVERAGE}" ]]; then
    echo "Installing git ..."
    docker exec -t -u root php apk add git

    echo "Applying fix for git's dubious ownership issue ..."
    docker exec -t php git config --global --add safe.directory /var/www/api

    echo "Uploading coverage report to coveralls.io ..."
    docker exec -t -e COVERALLS_RUN_LOCALLY -e COVERALLS_REPO_TOKEN php php-coveralls -v -x clover.xml -o coveralls.json
fi

if [[ -n "${PUBLISH_IMAGE}" ]]; then
    echo "Logging in to docker hub ..."
    echo "$DOCKER_TOKEN" | docker login -u "$DOCKER_USER" --password-stdin

    echo "Pushing image ${TARGET_IMAGE} to docker hub ..."
    docker push --quiet ${TARGET_IMAGE}
fi

echo "Build completed"

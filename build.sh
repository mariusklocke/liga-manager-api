#!/usr/bin/env bash
set -e

if [[ -z "${DOCKER_REPO}" ]]; then
  DOCKER_REPO="mklocke/liga-manager-api"
fi

if [[ -z "${TAG}" ]]; then
  TAG="latest"
fi

cleanup() {
    echo 'Cleaning temporary containers ...'
    docker rm -f mariadb redis > /dev/null
}

# Build image
docker build -f docker/php/Dockerfile -t $DOCKER_REPO:$TAG . > /dev/null

# Run deptrac
docker run --rm ${DOCKER_REPO}:${TAG} bin/deptrac.phar --no-progress

# Make sure we clean up running containers in case of error
trap cleanup EXIT

# Launch MariaDB and Redis containers
docker run -d --name mariadb --env-file .env.test mariadb > /dev/null
docker run -d --name redis redis:5-alpine > /dev/null

# Run tests
docker run --link mariadb --link redis --rm --env-file .env.test ${DOCKER_REPO}:${TAG} run-tests.sh

if [[ $1 = "-c" ]]; then

    # Run tests with coverage
    docker run \
        --link mariadb --link redis --rm --env-file .env.test -e COVERAGE=1 -e TRAVIS -e TRAVIS_JOB_ID \
        ${DOCKER_REPO}:${TAG} \
        run-tests.sh

fi

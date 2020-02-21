#!/usr/bin/env bash

if [[ -z "${DOCKER_REPO}" ]]; then
  DOCKER_REPO="mklocke/liga-manager-api"
fi

if [[ -z "${TAG}" ]]; then
  TAG="latest"
fi

trap 'rc=$?' ERR

# Build image
docker build -f docker/php/Dockerfile -t $DOCKER_REPO:$TAG . > /dev/null

# Launch MariaDB and Redis containers
docker run -d --name mariadb --env-file .env.test mariadb > /dev/null
docker run -d --name redis redis:5-alpine > /dev/null

# Run deptrac
docker run --link mariadb --link redis --rm --env-file .env.test ${DOCKER_REPO}:${TAG} bin/deptrac.phar --no-progress

# Run tests
docker run --link mariadb --link redis --rm --env-file .env.test ${DOCKER_REPO}:${TAG} run-tests.sh

if [[ $1 = "-c" ]]; then

    # Run tests with coverage
    docker run \
        --link mariadb --link redis --rm --env-file .env.test -e COVERAGE=1 -e TRAVIS -e TRAVIS_JOB_ID \
        ${DOCKER_REPO}:${TAG} \
        run-tests.sh

fi

# Cleanup
docker rm -f mariadb redis > /dev/null

exit ${rc}
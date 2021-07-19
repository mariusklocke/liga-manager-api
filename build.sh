#!/usr/bin/env bash
set -e

IMAGE="mklocke/liga-manager-api"

if [[ $GITHUB_REF == *"refs/tags"* ]]; then
  TAG=$(sed 's#refs/tags/##' <<< "${GITHUB_REF}")
else
  TAG="latest"
fi

echo "GITHUB_REF: ${GITHUB_REF}"
echo "IMAGE: ${IMAGE}"
echo "TAG: ${TAG}"

# Pull images
docker pull mariadb:10.4
docker pull redis:5-alpine
docker pull $IMAGE:latest

# Build images
docker build -f docker/php/Dockerfile -t $IMAGE:$TAG --cache-from $IMAGE:latest .

cleanup() {
    echo 'Cleanup: Removing containers ...'
    docker rm -f php mariadb redis
    docker network rm build
}

# Make sure we clean up running containers in case of error
trap cleanup EXIT

# Launch containers
docker network create build
docker run -d --name=mariadb --network=build --env-file=build.env mariadb:10.4
docker run -d --name=redis --network=build redis:5-alpine
docker run -d --name=php --network=build --env-file=build.env $IMAGE:$TAG

# Run deptrac
docker exec -t php bin/deptrac.phar --no-progress

# Run tests without coverage
docker exec -t php run-tests.sh

if [[ ! -z "${CI}" ]]; then
    # Enable xdebug
    docker exec -t -u root php docker-php-ext-enable xdebug

    # Run tests with coverage
    docker exec -t -e COVERAGE_REPORT=1 -e COVERALLS_RUN_LOCALLY -e COVERALLS_REPO_TOKEN php run-tests.sh

    # Login to docker hub
    echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin

    # Push image to docker hub
    docker push $IMAGE:$TAG
fi

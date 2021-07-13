#!/usr/bin/env bash
set -e

if [[ -z "${GITHUB_REF}" ]]; then
  TAG="latest"
else
  TAG=$(sed 's#refs/heads/##' <<< "${GITHUB_REF}")
fi

cleanup() {
    echo 'Cleanup: Removing containers ...'
    TAG=$TAG docker-compose -f docker-compose.build.yml down -v
}

# Make sure we clean up running containers in case of error
trap cleanup EXIT

# Launch containers
TAG=$TAG docker-compose -f docker-compose.build.yml up -d --build

# Run deptrac
TAG=$TAG docker-compose -f docker-compose.build.yml exec -T php bin/deptrac.phar --no-progress

# Run tests without coverage
TAG=$TAG docker-compose -f docker-compose.build.yml exec -T php run-tests.sh

if [[ ! -z "${CI}" ]]; then
    # Enable xdebug
    TAG=$TAG docker-compose -f docker-compose.build.yml exec -T -u root php docker-php-ext-enable xdebug

    # Run tests with coverage
    TAG=$TAG docker-compose -f docker-compose.build.yml exec -T -e COVERAGE_REPORT=1 -e COVERALLS_RUN_LOCALLY -e COVERALLS_REPO_TOKEN php run-tests.sh

    # Login to docker hub
    echo $DOCKER_PASS | docker login -u $DOCKER_USER --password-stdin

    # Push image to docker hub
    docker push mklocke/liga-manager-api:$TAG
fi

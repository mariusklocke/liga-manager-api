#!/usr/bin/env bash

if [[ -z "${DOCKER_REPO}" ]]; then
  DOCKER_REPO="mklocke/liga-manager-api"
fi

if [[ -z "${TAG}" ]]; then
  TAG="latest"
fi

trap 'rc=$?' ERR

# Build image
docker build -f docker/php/Dockerfile -t $DOCKER_REPO:$TAG .

# Define environment
MYSQL_ENV_ARGS="-e MYSQL_ALLOW_EMPTY_PASSWORD=yes -e MYSQL_HOST=mariadb -e MYSQL_DATABASE=test -e MYSQL_USER=test -e MYSQL_PASSWORD=test"
EMAIL_ENV_ARGS="-e EMAIL_URL=null://localhost -e EMAIL_SENDER_ADDRESS=noreply@example.com -e EMAIL_SENDER_NAME=noreply"
APP_ENV_ARGS="$MYSQL_ENV_ARGS $EMAIL_ENV_ARGS -e LOG_LEVEL=warning -e REDIS_HOST=redis -e JWT_SECRET=a194be3811fc"

# Launch MariaDB and Redis containers
docker run -d --name mariadb ${MYSQL_ENV_ARGS} mariadb > /dev/null
docker run -d --name redis redis:4-alpine > /dev/null

# Wait until MariaDB and Redis are ready
docker run --link redis --rm dadarek/wait-for-dependencies redis:6379
docker run --link mariadb --rm dadarek/wait-for-dependencies mariadb:3306

# Run tests
docker run --link mariadb --link redis --rm ${APP_ENV_ARGS} \
    mklocke/liga-manager-api:${TAG} sh -c "bin/install.sh && phpunit --testdox"

if [[ $1 = "-c" ]]; then
    # Build image with xdebug
    docker build -f docker/php/Dockerfile -t $DOCKER_REPO:$TAG-xdebug --build-arg XDEBUG=1 .

    # Create temporary volume
    docker volume create tmp-vol

    # Run tests with coverage
    docker run --link mariadb --link redis --rm ${APP_ENV_ARGS} -v tmp-vol:/tmp \
        mklocke/liga-manager-api:${TAG}-xdebug sh -c "bin/install.sh && phpunit --coverage-clover /tmp/clover.xml"

    # Upload coverage data
    docker run --rm -v $PWD:/var/www/api -v tmp-vol:/tmp -e TRAVIS -e TRAVIS_JOB_ID \
        kielabokkie/coveralls-phpcov sh -c "cd /var/www/api && php-coveralls -v -x /tmp/clover.xml -o /tmp/coveralls.json"

    # Remove temporary volume
    docker volume rm tmp-vol
fi

# Cleanup
docker rm -f mariadb redis > /dev/null

exit ${rc}
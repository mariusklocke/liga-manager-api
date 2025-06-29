#!/usr/bin/env bash
set -ex

function cleanup()
{
    set +e
    docker compose logs php
	docker compose exec php cat php-errors.log
	docker compose down -v
}

function generate_secret() {
    tr -dc a-f0-9 </dev/urandom | head -c 32
}

export DB_PASSWORD=$(generate_secret)
export DB_ROOT_PASSWORD=$(generate_secret)
export JWT_SECRET=$(generate_secret)

trap cleanup EXIT

if [[ -n "${DOCKER_TOKEN}" ]]; then
    echo "${DOCKER_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
fi

# Install dev dependencies
docker run --rm -v $PWD:/app -u $(id -u):$(id -g) --userns host \
    composer install --ignore-platform-reqs --no-cache --no-progress

# Start containers
docker compose up --detach --quiet-pull

# Verify architecture contraints
docker compose exec php deptrac analyse --config-file config/deptrac.yaml --no-progress

# Validate OpenAPI spec
docker compose exec php php-openapi validate openapi.yml

# Run tests without coverage
docker compose exec php phpunit -c config/phpunit.xml --display-deprecations --display-warnings

# Enable xdebug
docker compose exec php sh -c "echo 'zend_extension=xdebug' >> /etc/php/php.ini"

# Run tests with coverage
docker compose exec php phpunit -c config/phpunit.xml --coverage-clover coverage.xml -d error_log=php-errors.log

# Extract coverage report
docker compose exec php cat coverage.xml > build/coverage.xml

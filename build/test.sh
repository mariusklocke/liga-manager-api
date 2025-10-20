#!/usr/bin/env bash
set -ex

function cleanup()
{
    docker compose down -v
}

function generate_secret() {
    head -c "$1" /dev/urandom | xxd -ps | tr -d '\n'
}

trap cleanup EXIT

if [[ -n "${DOCKER_TOKEN}" ]]; then
    echo "${DOCKER_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
fi

# Create fresh artifacts directory
rm -rf build/artifacts && mkdir -m 777 build/artifacts

# Install dev dependencies
docker run --rm -v "$PWD:/app" -u "$(id -u):$(id -g)" --userns host \
    composer install --ignore-platform-reqs --no-cache --no-progress

# Start containers
DB_PASSWORD=$(generate_secret 16) \
DB_ROOT_PASSWORD=$(generate_secret 16) \
JWT_SECRET=$(generate_secret 32) \
docker compose up --wait --quiet-pull

# Verify architecture contraints
docker compose exec php deptrac analyse --config-file config/deptrac.yaml --no-progress

# Validate OpenAPI spec
docker compose exec php php-openapi validate openapi.yml

# Run tests without coverage
docker compose exec php phpunit -c config/phpunit.xml --display-deprecations --display-warnings

# Enable xdebug
docker compose exec php sh -c "echo 'zend_extension=xdebug' >> /etc/php/php.ini"

# Run tests with coverage
docker compose exec -e LOG_PATH=/artifacts/app-xdebug.log php phpunit -c config/phpunit.xml --coverage-clover /artifacts/coverage.xml

#!/usr/bin/env bash
set -ex

function cleanup()
{
    docker compose logs php || true
	docker compose exec php cat php-errors.log || true
	docker compose down -v || true
	rm -rf "build/.secrets" || true
}

trap cleanup EXIT

if [[ -n "${DOCKER_TOKEN}" ]]; then
    echo "${DOCKER_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
fi

mkdir -p "build/.secrets"
openssl rand -hex 16 | tr -d '\n' > build/.secrets/db-password
openssl rand -hex 16 | tr -d '\n' > build/.secrets/db-root-password
openssl rand -hex 16 | tr -d '\n' > build/.secrets/jwt-secret
docker compose up --detach --quiet-pull
docker compose exec php composer install --no-cache --no-progress
docker compose exec php deptrac analyse --config-file config/deptrac.yaml --no-progress
docker compose exec php phpunit -c config/phpunit.xml --testdox --display-deprecations --display-warnings
docker compose exec php php-ext-configure enable xdebug
docker compose exec php phpunit -c config/phpunit.xml --testdox --coverage-clover coverage.xml -d error_log=php-errors.log
docker compose exec php cat coverage.xml > build/coverage.xml

export SHELL:=/bin/bash
export SHELLOPTS:=$(if $(SHELLOPTS),$(SHELLOPTS):)pipefail:errexit

export MARIADB_VERSION ?= 11.4
export POSTGRES_VERSION ?= 17
export REDIS_VERSION ?= 6
export TARGET_TYPE ?= fpm
export MARIADB_IMAGE = mariadb:${MARIADB_VERSION}
export POSTGRES_IMAGE = postgres:${POSTGRES_VERSION}-alpine
export REDIS_IMAGE = redis:${REDIS_VERSION}-alpine
export COMPOSE_FILE = build/compose.yml
export COMPOSE_PROJECT_NAME = liga-manager-api-build
export DOCKER_USERNAME = mklocke
export DB_DRIVER ?= pdo-mysql
export DB_HOSTNAME ?= mariadb

ifeq (${GITHUB_REF_TYPE}, tag)
	export TAG = ${GITHUB_REF_NAME}
	export APP_VERSION = ${GITHUB_REF_NAME}
else
 	export TAG = latest
 	export APP_VERSION = dev-latest
endif

ifneq (${TARGET_TYPE}, fpm)
	export TARGET_IMAGE = mklocke/liga-manager-api:${TAG}-${TARGET_TYPE}
else
	export TARGET_IMAGE = mklocke/liga-manager-api:${TAG}
endif

.PHONY: build test clean publish

build:
	docker build --build-arg APP_VERSION=${APP_VERSION} --file "docker/php/${TARGET_TYPE}/Dockerfile" --pull --tag "${TARGET_IMAGE}" .

test:
ifdef DOCKER_TOKEN
	echo "${DOCKER_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
endif
	mkdir -p "build/.secrets"
	openssl rand -hex 16 | tr -d '\n' > build/.secrets/db-password
	openssl rand -hex 16 | tr -d '\n' > build/.secrets/db-root-password
	openssl rand -hex 16 | tr -d '\n' > build/.secrets/jwt-secret
	docker compose up --detach --quiet-pull
	docker compose exec php composer install --no-cache --no-progress
	docker compose exec php deptrac analyse --config-file config/deptrac.yaml --no-progress
	docker compose exec php phpunit -c config/phpunit.xml --testdox --display-deprecations --display-warnings
	docker compose exec -u root php xdebug on
	docker compose exec php phpunit -c config/phpunit.xml --testdox --coverage-clover coverage.xml
ifdef CODECOV_TOKEN
	docker compose cp php:/var/www/api/coverage.xml coverage.xml
endif

clean:
	docker compose logs php || true
	docker compose down -v || true
	rm -rf "build/.secrets" || true

publish:
	echo "${DOCKER_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
	docker push --quiet "${TARGET_IMAGE}"

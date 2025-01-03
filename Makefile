export SHELL:=/bin/bash
export SHELLOPTS:=$(if $(SHELLOPTS),$(SHELLOPTS):)pipefail:errexit

export APP_RUNTIME ?= fpm
export APP_VERSION ?= latest
export MARIADB_VERSION ?= 11.4
export NGINX_VERSION ?= 1
export PHP_VERSION ?= 8.4
export POSTGRES_VERSION ?= 17
export REDIS_VERSION ?= 6
export COMPOSE_FILE = build/compose.yml
export COMPOSE_PROJECT_NAME = liga-manager-api-build
export DOCKER_USERNAME = mklocke
export DB_DRIVER ?= pdo-mysql
export DB_HOST ?= mariadb

ifeq (${APP_RUNTIME}, fpm)
	export APP_TAG = ${APP_VERSION}
else
	export APP_TAG = ${APP_VERSION}-${APP_RUNTIME}
endif

.PHONY: build test clean publish

build:
	docker build \
		--build-arg APP_VERSION=${APP_VERSION} \
		--build-arg PHP_VERSION=${PHP_VERSION} \
		--file "docker/php/${APP_RUNTIME}/Dockerfile" \
		--pull \
		--tag "mklocke/liga-manager-api:${APP_TAG}" \
		.

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
	docker compose exec php apk add --no-cache php$(subst .,,${PHP_VERSION})-xdebug
	docker compose exec php sh -c "echo 'zend_extension=xdebug.so' > /etc/php/conf.d/50_xdebug.ini"
	docker compose exec php phpunit -c config/phpunit.xml --testdox --coverage-clover tests/coverage.xml

clean:
	docker compose logs php || true
	docker compose down -v || true
	rm -rf "build/.secrets" || true

publish:
	echo "${DOCKER_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
	docker push --quiet "mklocke/liga-manager-api:${APP_TAG}"

export SHELL:=/bin/bash
export SHELLOPTS:=$(if $(SHELLOPTS),$(SHELLOPTS):)pipefail:errexit

export PHP_VERSION ?= 8.3
export MARIADB_VERSION ?= 10.11
export REDIS_VERSION ?= 6
export TARGET_TYPE ?= fpm
export MARIADB_IMAGE = mariadb:${MARIADB_VERSION}
export REDIS_IMAGE = redis:${REDIS_VERSION}-alpine
export COMPOSE_FILE = build/compose.yml
export COMPOSE_PROJECT_NAME = liga-manager-api-build
export DOCKER_USERNAME = mklocke

ifeq (${GITHUB_REF_TYPE}, tag)
	export TAG = ${GITHUB_REF_NAME}
else
 	export TAG = latest
endif

ifneq (${TARGET_TYPE}, fpm)
	export TARGET_IMAGE = mklocke/liga-manager-api:${TAG}-${TARGET_TYPE}
else
	export TARGET_IMAGE = mklocke/liga-manager-api:${TAG}
endif

.ONESHELL:
.PHONY: build test publish

build:
	docker compose build php

test:
	set -x
	function tearDown {
		docker compose down
	}
	trap tearDown EXIT
	docker compose up --detach --quiet-pull
	sleep 10
	docker compose exec php composer install --no-cache --no-progress
	docker compose exec php deptrac analyse --config-file config/deptrac.yaml --no-progress
	docker compose exec php phpunit -c config/phpunit.xml --display-deprecations
	docker compose exec php gdpr-dump config/gdpr-dump.yml > /dev/null
	if [[ -n "${COVERALLS_RUN_LOCALLY}" ]]; then
		docker compose exec -u root php docker-php-ext-enable xdebug
		docker compose exec php phpunit -c config/phpunit.xml --coverage-clover clover.xml --display-deprecations
		docker compose exec -u root php apk add git
		docker compose exec php git config --global --add safe.directory /var/www/api
		docker compose exec -e COVERALLS_RUN_LOCALLY -e COVERALLS_REPO_TOKEN php php-coveralls -v -x clover.xml -o coveralls.json
	fi

publish:
	echo "${DOCKER_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
	docker push --quiet "${TARGET_IMAGE}"

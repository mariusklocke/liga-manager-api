export SHELL:=/bin/bash
export SHELLOPTS:=$(if $(SHELLOPTS),$(SHELLOPTS):)pipefail:errexit

export APP_RUNTIME ?= fpm
export APP_VERSION ?= latest
export MARIADB_VERSION ?= 11.8
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

.PHONY: build test publish release tarball docs

build:
	build/build.sh

test:
	build/test.sh

publish:
	build/publish.sh

release:
	build/release.sh

tarball:
	build/tarball.sh

docs:
	build/docs.sh

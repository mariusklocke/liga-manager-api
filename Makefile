export SHELL:=/bin/bash
export SHELLOPTS:=$(if $(SHELLOPTS),$(SHELLOPTS):)pipefail:errexit

export APP_RUNTIME ?= fpm
export APP_VERSION ?= latest
export MARIADB_VERSION ?= 11.8
export PHP_VERSION ?= 8.5
export POSTGRES_VERSION ?= 17
export REDIS_VERSION ?= 8.2
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

define generate_secret
	head -c $(1) /dev/urandom | xxd -ps | tr -d '\n' > docker/.secrets/$(2)
endef

.PHONY: help build test publish release tarball docs secrets
default: help

help:
	@echo 'make build      Build docker image'
	@echo 'make test       Test docker image'
	@echo 'make publish    Publish docker image'
	@echo 'make release    Create a new release'
	@echo 'make tarball    Build tarball'
	@echo 'make docs       Build docs'
	@echo 'make secrets    Generate random secrets'

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

secrets:
	test -d docker/.secrets || mkdir docker/.secrets
	$(call generate_secret,16,db-password)
	$(call generate_secret,16,db-root-password)
	$(call generate_secret,32,jwt-secret)

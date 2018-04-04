#!/usr/bin/env bash

docker run -d --name mariadb --env-file .env mariadb > /dev/null
docker run --link mariadb dadarek/wait-for-dependencies mariadb:3306
docker run --link mariadb --env-file .env --rm mklocke/liga-manager-api bin/console.php orm:schema-tool:create
docker run --link mariadb --env-file .env --rm mklocke/liga-manager-api phpunit
docker stop mariadb > /dev/null
docker rm mariadb > /dev/null
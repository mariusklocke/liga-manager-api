#!/usr/bin/env bash

docker run -d --name mariadb --env-file .env mariadb
docker run --link mariadb dadarek/wait-for-dependencies mariadb:3306
docker run --link mariadb --env-file .env ligamanager/php bin/console.php orm:schema-tool:create
docker run --link mariadb --env-file .env ligamanager/php phpunit
docker stop mariadb
docker rm mariadb
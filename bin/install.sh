#!/bin/sh
set -ex

php vendor/bin/doctrine orm:schema-tool:drop --force
php vendor/bin/doctrine orm:schema-tool:create
php vendor/bin/doctrine orm:generate-proxies
php bin/console.php app:generate-jwt-secret
php bin/console.php app:load-fixtures

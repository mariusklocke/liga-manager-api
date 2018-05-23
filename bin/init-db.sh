#!/bin/sh
set -xe

php bin/console.php orm:schema-tool:drop --force
php bin/console.php orm:schema-tool:create
php bin/console.php app:load-fixtures

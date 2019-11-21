#!/bin/sh
set -ex

php vendor/bin/doctrine orm:schema-tool:drop --force
php vendor/bin/doctrine dbal:run-sql "DROP TABLE IF EXISTS doctrine_migration_versions;"
php vendor/bin/doctrine-migrations migrations:migrate -n

if [ "$ADMIN_EMAIL " != "" ] && [ "$ADMIN_PASSWORD" != "" ]; then
    php bin/console.php app:create-user --email=$ADMIN_EMAIL --password=$ADMIN_PASSWORD
fi

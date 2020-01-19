#!/bin/sh
set -e

init.sh
doctrine orm:schema-tool:drop --force
doctrine dbal:run-sql "DROP TABLE IF EXISTS doctrine_migration_versions;"
doctrine-migrations migrations:migrate -n

if [ "$ADMIN_EMAIL " != "" ] && [ "$ADMIN_PASSWORD" != "" ]; then
    lima app:create-user --email=$ADMIN_EMAIL --password=$ADMIN_PASSWORD
fi

#!/bin/sh
set -e

doctrine orm:schema-tool:drop --force
doctrine dbal:run-sql "DROP TABLE IF EXISTS doctrine_migration_versions;"
doctrine-migrations migrations:migrate -n

# Create default admin user
lima app:create-user --email=$ADMIN_EMAIL --password=$ADMIN_PASSWORD --role=admin --first-name=admin --last-name=admin

#!/bin/sh
set -e

doctrine orm:schema-tool:drop --force
doctrine dbal:run-sql "DROP TABLE IF EXISTS doctrine_migration_versions;"
doctrine-migrations migrations:migrate -n

#!/bin/sh
set -ex

php vendor/bin/doctrine orm:schema-tool:drop --force
php vendor/bin/doctrine orm:schema-tool:create
php vendor/bin/doctrine orm:generate-proxies

if [ "$ADMIN_EMAIL " != "" ] && [ "$ADMIN_PASSWORD" != "" ]; then
    php bin/console.php app:create-user --email=$ADMIN_EMAIL --password=$ADMIN_PASSWORD
fi

#!/bin/sh
set -ex

php vendor/bin/doctrine-migrations migrations:migrate -n

if [ "$ADMIN_EMAIL " != "" ] && [ "$ADMIN_PASSWORD" != "" ]; then
    php bin/console.php app:create-user --email=$ADMIN_EMAIL --password=$ADMIN_PASSWORD
fi

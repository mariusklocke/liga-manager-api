#!/bin/sh
set -ex

if ssh-keygen -t rsa -b 4096 -f ${JWT_SECRET_PATH}/secret.key -N ''; then
    rm ${JWT_SECRET_PATH}/secret.key.pub
    chmod 600 ${JWT_SECRET_PATH}/secret.key
fi

php vendor/bin/doctrine orm:schema-tool:drop --force
php vendor/bin/doctrine orm:schema-tool:create
php vendor/bin/doctrine orm:generate-proxies
php bin/console.php app:load-fixtures

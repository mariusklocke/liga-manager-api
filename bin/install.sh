#!/bin/sh
set -ex

if ssh-keygen -t rsa -b 4096 -f ${JWT_SECRET_PATH}/secret.key -N ''; then
    rm ${JWT_SECRET_PATH}/secret.key.pub
    chmod 600 ${JWT_SECRET_PATH}/secret.key
fi

php bin/console.php orm:schema-tool:drop --force
php bin/console.php orm:schema-tool:create
php bin/console.php orm:generate-proxies
php bin/console.php app:load-fixtures

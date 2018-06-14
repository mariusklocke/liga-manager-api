#!/bin/sh
set -e

if [ ! -d /etc/jwt ]; then
    mkdir /etc/jwt
fi
if ssh-keygen -t rsa -b 4096 -f /etc/jwt/secret.key -N ''; then
    rm /etc/jwt/secret.key.pub
    chmod 644 /etc/jwt/secret.key
fi

php bin/console.php orm:schema-tool:drop --force
php bin/console.php orm:schema-tool:create
php bin/console.php app:load-fixtures
php bin/console.php orm:generate-proxies

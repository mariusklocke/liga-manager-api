#!/bin/sh
set -e

init-db.sh

if [ -n "$COVERAGE" ]; then
    docker-php-ext-enable xdebug
    phpunit.phar --coverage-clover /tmp/clover.xml
    php-coveralls.phar -v -x /tmp/clover.xml -o /tmp/coveralls.json
else
    phpunit.phar --testdox
fi

#!/bin/sh
# Abort script execution when a command fails
set -e

# Make sure container is ready for action
init-container.sh

# Create default admin user
lima app:create-user --email=$ADMIN_EMAIL --password=$ADMIN_PASSWORD

# In case variable $COVERAGE has been set, run tests with coverage and send report to coveralls.io
if [ -n "$COVERAGE" ]; then
    docker-php-ext-enable xdebug
    phpunit.phar --coverage-clover /tmp/clover.xml
    php-coveralls.phar -v -x /tmp/clover.xml -o /tmp/coveralls.json
else
    phpunit.phar --testdox
fi

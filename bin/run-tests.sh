#!/bin/sh
# Abort script execution when a command fails
set -e

if [ ! -n "$ALLOW_TESTS" ]; then
  echo 'Running tests is not allowed in this environment. Please set ALLOW_TESTS=1'
  exit 1
fi

# Wait until container is ready
source wait-for.sh
wait_for 127.0.0.1 9000

# Make sure we have a clean database
init-db.sh

# Run tests regularly
phpunit.phar --testdox

# In case variable $TRAVIS has been set, run tests with coverage and send report to coveralls.io
if [ -n "$TRAVIS" ]; then
    # Make sure we have a clean database
    init-db.sh

    # Enable xdebug as coverage driver (requires root privileges)
    docker-php-ext-enable xdebug

    # Run tests with coverage
    phpunit.phar --coverage-clover /tmp/clover.xml

    # Upload coverage report to coveralls.io
    php-coveralls.phar -v -x /tmp/clover.xml -o /tmp/coveralls.json
fi

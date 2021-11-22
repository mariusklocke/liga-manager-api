#!/bin/sh
# Abort script execution when a command fails
set -e

if [ ! -n "$ALLOW_TESTS" ]; then
  echo 'Running tests is not allowed in this environment. Please set ALLOW_TESTS=1'
  exit 1
fi

if [ ! -z "$COVERAGE_REPORT" ]; then
    PHPUNIT_OPTIONS="--coverage-clover /tmp/clover.xml"
else
    PHPUNIT_OPTIONS="--testdox"
fi

# Wait until container is ready
source wait-for.sh
wait_for 127.0.0.1 9000

# Make sure we have a clean database
lima app:setup:db -n

# Run tests
phpunit.phar $PHPUNIT_OPTIONS

if [ ! -z "$COVERAGE_REPORT" ]; then
    # Upload coverage report to coveralls.io
    php-coveralls.phar -v -x /tmp/clover.xml -o /tmp/coveralls.json
fi

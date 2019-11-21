#!/bin/sh

mkdir -p tools
wget -O tools/composer.phar https://github.com/composer/composer/releases/download/1.9.1/composer.phar
wget -O tools/phpunit.phar https://phar.phpunit.de/phpunit-8.4.3.phar
wget -O tools/php-coveralls.phar https://github.com/php-coveralls/php-coveralls/releases/download/v2.2.0/php-coveralls.phar
chmod +x tools/*.phar

if [ -x "$(command -v php)" ]; then
  tools/composer.phar install --prefer-dist --no-dev --optimize-autoloader --ignore-platform-reqs
fi

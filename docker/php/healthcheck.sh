#!/bin/sh
# Inspired by: https://github.com/renatomefi/php-fpm-healthcheck/tree/master
set -eu

REQUEST_METHOD="GET" \
SCRIPT_NAME="/_status" \
SCRIPT_FILENAME="/_status" \
cgi-fcgi -bind -connect 127.0.0.1:9000

# Line feed
echo

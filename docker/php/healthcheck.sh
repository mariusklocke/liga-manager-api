#!/bin/sh
set -eu

REQUEST_METHOD="GET" \
REQUEST_URI="/api/health" \
SCRIPT_FILENAME="public/index.php" \
cgi-fcgi -bind -connect 127.0.0.1:9000

# Line feed
echo

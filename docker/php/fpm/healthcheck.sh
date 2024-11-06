#!/bin/sh

REQUEST_METHOD=GET \
REQUEST_URI=/api/health \
SCRIPT_NAME=index.php \
SCRIPT_FILENAME=${APP_HOME}/public/index.php \
cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1

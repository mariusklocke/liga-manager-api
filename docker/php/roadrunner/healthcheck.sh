#!/bin/sh

curl -sSf http://127.0.0.1:8080/api/health || exit 1

#!/usr/bin/env bash
set -eu

build_phar() {
    docker run --rm -v $(pwd):/app -w /app -u "$(id -u):$(id -g)" --userns host composer/composer:latest \
        composer run build-phar
}

build_phar

#!/usr/bin/env bash

TEMP_CONTAINER=$(docker create mklocke/liga-manager-api:${APP_TAG})
docker cp ${TEMP_CONTAINER}:/var/www/api/vendor ./vendor
docker rm ${TEMP_CONTAINER}

tar -czf build/liga-manager-api.tar.gz \
    bin \
    config \
    locales \
    public \
    src \
    vendor \
    composer.json \
    composer.lock \
    CHANGELOG.md \
    LICENSE \
    README.md

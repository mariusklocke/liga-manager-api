#!/usr/bin/env bash
set -eu

echo "${DOCKER_TOKEN}" | docker login -u "${DOCKER_USERNAME}" --password-stdin
docker push --quiet "mklocke/liga-manager-api:${APP_TAG}"
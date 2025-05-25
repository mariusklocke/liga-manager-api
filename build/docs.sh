#!/usr/bin/env bash
set -eu

rm -rf ./docs
TEMP_CONTAINER=$(docker create redocly/redoc)
docker cp ${TEMP_CONTAINER}:/usr/share/nginx/html ./docs
docker rm ${TEMP_CONTAINER}

cp openapi.yml ./docs/openapi.yaml
sed -i 's/%PAGE_TITLE%/Liga-Manager API docs/' ./docs/index.html
sed -i 's/%BASE_PATH%//' ./docs/index.html
sed -i 's/%PAGE_FAVICON%/favicon.png/' ./docs/index.html
sed -i 's/%SPEC_URL%/\/openapi.yaml/' ./docs/index.html
sed -i 's/%REDOC_OPTIONS%//' ./docs/index.html

#!/usr/bin/env bash
set -eu

rm -rf ./docs
TEMP_CONTAINER=$(docker create redocly/redoc)
docker cp "${TEMP_CONTAINER}:/usr/share/nginx/html" ./docs
docker rm "${TEMP_CONTAINER}"

cp openapi.yml ./docs/openapi.yml
sed -i 's/${PAGE_TITLE}/Liga-Manager API docs/' ./docs/index.tpl.html
sed -i 's/${PAGE_FAVICON}/favicon.png/' ./docs/index.tpl.html
sed -i 's/${SPEC_URL}/openapi.yml/' ./docs/index.tpl.html
sed -i 's/${REDOC_OPTIONS}//' ./docs/index.tpl.html
cp ./docs/index.tpl.html ./docs/index.html
rm ./docs/index.tpl.html

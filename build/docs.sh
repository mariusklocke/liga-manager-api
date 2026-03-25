#!/usr/bin/env bash
set -eu

rm -rf ./docs
TEMP_CONTAINER=$(docker create redocly/redoc)
docker cp "${TEMP_CONTAINER}:/usr/share/nginx/html" ./docs
docker rm "${TEMP_CONTAINER}"

cat > ./docs/index.html <<EOL
<!doctype html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Liga-Manager API docs</title>
    <link rel="icon" href="favicon.png" />
    <link rel="stylesheet" href="styles.css" />
    <link
      href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700"
      rel="stylesheet"
    />
  </head>

  <body>
    <redoc spec-url="openapi.yml" type-of-usage="docker"></redoc>
    <script type="module" src="redoc.standalone.js"></script>
  </body>
</html>
EOL
cp openapi.yml ./docs/openapi.yml
rm ./docs/index.tpl.html ./docs/index.prefix.tpl.html

#!/usr/bin/env bash
set -eu

build_docs() {
    rm -rf docs
    mkdir docs
    cp openapi.yml docs/openapi.yml
    cat > docs/index.html <<EOL
<!doctype html>
<html>
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Liga-Manager API docs</title>
    <link
      href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700"
      rel="stylesheet"
    />
  </head>
  <body>
    <redoc spec-url="openapi.yml"></redoc>
    <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
  </body>
</html>
EOL
}

build_docs

[![Build Status](https://travis-ci.org/mariusklocke/liga-manager-api.svg?branch=master)](https://travis-ci.org/mariusklocke/liga-manager-api)
[![Coverage Status](https://coveralls.io/repos/github/mariusklocke/liga-manager-api/badge.svg?branch=master)](https://coveralls.io/github/mariusklocke/liga-manager-api?branch=master)

## Requirements
A working installation of `docker` and `docker-compose`

## Get started
This application comes with an example configuration for running with `docker-compose`. To get started rename `.env.dist` to `.env` and `docker-compose.yml.dist` to `docker-compose.yml` and adjust both configuration files to your local needs.

Before you run the application for the first time, you need to build the docker images:
```bash
bash docker/build-images.sh
```

Now you are ready to start the containers
```bash
docker-compose up -d
```

If you run the `php` container for the first time, you need to run `bin/install.sh`
```bash
docker-compose exec php bin/install.sh
```

For more information on how to manage containers, please refer to the [docker-compose CLI reference](https://docs.docker.com/compose/reference/overview/#command-options-overview-and-help)

## Conventions

### Singular vs. Plural

* PHP classes: Singular
* REST resources: Plural (if resource can have multiple instances)
* DB tables: Plural

### CamelCase vs. snake_case

* PHP classes: UpperCamelCase
* PHP methods and properties: lowerCamelCase
* JSON fields: snake_case
* DB tables and columns: snake_case

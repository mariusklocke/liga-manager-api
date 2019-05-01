[![Build Status](https://travis-ci.org/mariusklocke/liga-manager-api.svg?branch=master)](https://travis-ci.org/mariusklocke/liga-manager-api)
[![Coverage Status](https://coveralls.io/repos/github/mariusklocke/liga-manager-api/badge.svg?branch=master)](https://coveralls.io/github/mariusklocke/liga-manager-api?branch=master)

## Requirements
A working installation of `docker` and `docker-compose`

## Get started
This application comes with an example configuration for running with `docker-compose`. To get started rename `.env.dist` to `.env` and `docker-compose.yml.dist` to `docker-compose.yml` and adjust both configuration files to your local needs.

The file `docker-compose.yml.dist` contains a default configuration suitable for local development. To build the images and run tests:
```bash
$ ./build.sh
```

Now you are ready to start the containers
```bash
$ docker-compose up -d
```

To initialize the database you need to run `bin/install.sh`
```bash
$ docker-compose exec php bin/install.sh
```

For more information on how to manage containers, please refer to the [docker-compose CLI reference](https://docs.docker.com/compose/reference/overview/#command-options-overview-and-help)

## JSON Web Token

This application uses JSON Web Token (JWT) for authentication. In order to create secure tokens, you have to generate an environment-specific secret.
Please run `docker-compose exec php bin/console.php app:generate-jwt-secret` and add the generated key to your `.env` file as described in the command output.

## OS users & file permissions

Configure `user: dev` in `docker-compose.yml` if you want to mount the project files into the container.

## Naming Conventions

### Singular vs. Plural

* PHP classes: Singular
* REST resources: Plural (if resource can have multiple instances)
* DB tables: Plural

### CamelCase vs. snake_case

* PHP classes: UpperCamelCase
* PHP methods and properties: lowerCamelCase
* JSON fields: snake_case
* DB tables and columns: snake_case

### Domain Events

* Class name must start with a context subject like "Match" or "Team"
* Class name must contain a verb in past tense

### Inspired by

* https://apihandyman.io/writing-openapi-swagger-specification-tutorial-part-3-simplifying-specification-file/
* https://fideloper.com/hexagonal-architecture

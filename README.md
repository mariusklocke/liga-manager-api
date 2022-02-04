![Build Status](https://github.com/mariusklocke/liga-manager-api/actions/workflows/docker-build.yml/badge.svg)
[![Coverage Status](https://coveralls.io/repos/github/mariusklocke/liga-manager-api/badge.svg?branch=master)](https://coveralls.io/github/mariusklocke/liga-manager-api?branch=master)

## Requirements
A working installation of `docker` and `docker-compose`

## Getting started
This application comes with an example configuration for running with `docker-compose`. To get started rename `.env.dist` to `.env` and `docker-compose.yml.dist` to `docker-compose.yml` and adjust both configuration files to your local needs.

The file `docker-compose.yml.dist` contains a default configuration suitable for local development. To build the images and run tests:
```bash
$ ./build.sh
```

Now you are ready to start the containers
```bash
$ docker-compose up -d
```

To setup your `.env` file follow the instructions given from
```bash
$ docker-compose exec php lima app:setup:env
```

For more information on how to manage containers, please refer to the [docker-compose CLI reference](https://docs.docker.com/compose/reference/overview/#command-options-overview-and-help)

## Useful commands

```bash
# Wipe database
$ docker-compose exec php lima app:db:wipe

# Run migrations
$ docker-compose exec php lima migrations:migrate

# Create a new user (any role)
$ docker-compose exec php lima app:create-user

# Create the default admin user
$ docker-compose exec php lima app:create-user --default

# Load demo data
$ docker-compose exec php loma app:db:demo-data
```

## Enable HTTPS

The default nginx config for development contains configuration for HTTPS support.
The only thing you need to do is to generate a certificate. You can use a self-signed certificate for simplicity, if you
can tolerate the warnings in the browser.
But there are already scripts helping you to generate a local CA certificate, which you can install in your browser or
operating system.

For generating a local root CA certificate, please refer to `docker/nginx/generate-root-cert.sh`. Please read the script
and verify the paths are valid for your system before running.

To generate a signed certificate for `lima.local`, please check out `docker/nginx/generate-site-cert.sh`.
Same rules for that script: Please have a look inside, before running it.

If you need to change paths, make your sure to reflect the changes in `docker-compose.yml` and your nginx configuration.

## OS users & file permissions

Configure `user: 1000:1000` in `docker-compose.yml` if you want to mount the project files into the container.

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

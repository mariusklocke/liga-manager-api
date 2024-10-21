![Build Status](https://github.com/mariusklocke/liga-manager-api/actions/workflows/docker-build.yml/badge.svg)
[![Coverage Status](https://codecov.io/gh/mariusklocke/liga-manager-api/graph/badge.svg?token=08EF0R5UTL)](https://codecov.io/gh/mariusklocke/liga-manager-api)
## Requirements
A working installation of `docker` and `docker compose`.

## Getting started
This application comes with an example configuration for running with `docker compose`. To get started rename `.env.dist` to `.env` and `docker-compose.yml.dist` to `docker-compose.yml` and adjust both configuration files to your local needs.

The file `docker-compose.yml.dist` contains a default configuration suitable for local development.

Build the image using:
```bash
$ make build
```

Now you are ready to start the containers
```bash
$ docker compose up -d
```

To set up your `.env` file follow the instructions given from
```bash
$ docker compose exec php lima app:env:setup
```

For more information on how to manage containers, please refer to the [docker-compose CLI reference](https://docs.docker.com/compose/reference/overview/#command-options-overview-and-help)

## Useful commands

```bash
# Wipe database
$ docker compose exec php lima app:db:wipe

# Run migrations
$ docker compose exec php lima migrations:migrate

# Create a new user (any role)
$ docker compose exec php lima app:user:create

# Create the default admin user
$ docker compose exec php lima app:user:create --default

# Load demo data
$ docker compose exec php lima app:db:demo-data

# Export database
$ docker compose exec php lima app:db:export database.xml

# Export database anonymized
$ docker compose exec php lima app:db:export database.xml --anonymize

# Import database
$ docker compose exec php lima app:db:import database.xml
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

## Maintenance Mode

To enable maintenance mode create a file named `.maintenance` in application root. Remove the file to disable it.
In maintenance mode every API request will be responded with HTTP Status `503 - Service unavailable`.
CLI commands will stay available though.

## Error codes

In case of error the client will be supplied with a human-readable message, a specific error code (uppercase string,
starting with "ERR"-) and an associated HTTP status code. A client should implement its own validation to ensure user
input does not cause errors in the API.

| Code                   | HTTP Status Code            | Description                                             |
|------------------------|-----------------------------|---------------------------------------------------------|
| ERR-AUTHENTICATION     | 401 - Unauthorized          | User could not be authorized                            |
| ERR-CONFLICT           | 409 - Conflict              | Request conflicts with the current state of an object   |
| ERR-INTERNAL           | 500 - Internal Server Error | Internal error, report to developer                     |
| ERR-INVALID-INPUT      | 400 - Bad Request           | Request contains invalid input values                   |
| ERR-MAINTENANCE-MODE   | 503 - Service Unavailable   | Service is temporarily not available due to maintenance |
| ERR-METHOD-NOT-ALLOWED | 405 - Method Not Allowed    | HTTP method is not allowed for this URL                 |
| ERR-NOT-FOUND          | 404 - Not Found             | Requested resource could not be found                   |
| ERR-PERMISSION         | 403 - Forbidden             | Request is not permitted to the current user            |
| ERR-RATE-LIMIT         | 429 - Too Many Requests     | Client has exceeded the rate limit                      |
| ERR-UNIQUENESS         | 400 - Bad Request           | A Value violates a uniqueness constraint                |

## Environment variables

The following table lists the environment variables used in the application. Dynamic values are populated automatically
by the application. Static values must be supplied from the outside (e.g. by using a `.env` file)

| Name                  | Mode     | Description                              |
|-----------------------|----------|------------------------------------------|
| ADMIN_EMAIL           | Static   | Email address for default admin user     |
| ADMIN_PASSWORD        | Static   | Password for default admin user          |
| APP_BASE_URL          | Static   | Public base URL for the application      |
| APP_HOME              | Dynamic  | Path to application home directory       |
| APP_LOGOS_PATH        | Dynamic  | Path to directory for logos              |
| APP_LOGOS_PUBLIC_PATH | Static   | Public URL path to logos                 |
| EMAIL_SENDER_ADDRESS  | Static   | Sender address for outbound emails       |
| EMAIL_SENDER_NAME     | Static   | Sender name for outbound emails          |
| EMAIL_URL             | Static   | URL to use for outbound emails (gateway) |
| JWT_SECRET            | Static   | Hex-encoded secret for JSON Web Tokens   |
| LOG_LEVEL             | Static   | Minimum level for log messages           |
| LOG_PATH              | Static   | Path to log file                         |
| MYSQL_DATABASE        | Static   | MySQL database name                      |
| MYSQL_HOST            | Static   | Hostname or IP address running MySQL     |
| MYSQL_PASSWORD        | Static   | Password for authenticating with MySQL   |
| MYSQL_USER            | Static   | Username for authenticating with MySQL   |
| RATE_LIMIT            | Static   | Defines an API rate limit                |
| REDIS_HOST            | Static   | Hostname or IP address running Redis     |

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

## Inspired by

* https://apihandyman.io/writing-openapi-swagger-specification-tutorial-part-3-simplifying-specification-file/
* https://fideloper.com/hexagonal-architecture

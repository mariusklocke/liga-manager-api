![Build Status](https://github.com/mariusklocke/liga-manager-api/actions/workflows/docker-build.yml/badge.svg)
[![Coverage Status](https://codecov.io/gh/mariusklocke/liga-manager-api/graph/badge.svg?token=08EF0R5UTL)](https://codecov.io/gh/mariusklocke/liga-manager-api)
## Requirements
A working installation of `docker` and `docker compose`.

## Getting started
This application comes with an example configuration for running with `docker compose`. To get started copy `.env.dist` to `.env` and `docker-compose.yml.dist` to `docker-compose.yml` and adjust both configuration files to your local needs.

The file `docker-compose.yml.dist` contains a default configuration suitable for local development.

Once you are ready, use following commands to get started:
```bash
# Create/start containers
$ docker compose up -d

# Start containers (must be created)
$ docker compose start

# Stop containers
$ docker compose stop

# Stop/delete containers
$ docker compose down

# Stop/delete containers and delete volumes
$ docker compose down -v
```

For more information on how to manage containers, please refer to the [docker compose CLI reference](https://docs.docker.com/reference/cli/docker/compose/).

## Useful commands

Each of the following commands can be run like `docker compose exec php lima app:config:validate` once the containers are running.

```
  app:config:validate               Validate the config (does not check backing services connection)
  app:db:browse                     Browse the database interactively
  app:db:demo-data                  Load demo data into current database
  app:db:export                     Export the database
  app:db:import                     Import a database
  app:db:migrate                    Migrate the database
  app:db:wipe                       Erase all data from the current database
  app:env:setup                     Setup environment config interactively
  app:health:check                  Performs health checks
  app:import:season                 Import season data from L98 files
  app:logo:cleanup                  Cleanup logos not referenced by a team
  app:logo:import                   Import a logo (source file will be deleted)
  app:mail:send                     Send a mail with HTML body
  app:user:create                   Create a user
  app:user:delete                   Delete a user
  app:user:list                     List users
```

## FAQ

### How to generate certificate for HTTPS?
At first you need to choose between: Using `localhost` or a custom DNS name like `lima.local`.

You can use a self-signed certificate for simplicity, if you can tolerate the warnings in the browser.
But there are already scripts helping you to generate a local CA certificate, which you can install in your browser or
operating system.

For generating a local root CA certificate, please refer to [docker/nginx/generate-root-cert.sh](). Please read the script
and verify the paths are valid for your system before running.

To generate a CA-signed certificate for `lima.local`, please check out [docker/nginx/generate-site-cert.sh]().
Same rules for that script: Please have a look inside, before running it.

### How to enable HTTPS?
* Generate a certificate
* Mount the certificate to the nginx container
* Expose port `443/tcp` for the nginx container
* Use either [docker/nginx/dev-roadrunner-ssl.conf]() or [docker/nginx/dev-fpm-ssl.conf]() as an nginx config

### How to enable HTTP2 or HTTP3?
* Enable HTTPS first
* HTTP2 is enabled by the `http2 on` instruction in nginx config
* HTTP3 is enabled by the `quic` and `add_header Alt-Svc` instructions in nginx config
* For HTTP3, make sure you expose port `443/udp` for the nginx container

## Users & Permissions

Since this application generates code when starting up (Doctrine proxy classes), it is advisable NOT to mount the project dir into the container.
The recommended development model is: Use a `build` config for the `php` service in `docker-compose.yml` and rebuild the container when changing sources.
This should be sufficiently fast due to Docker build layer caching. Rebuilding the container on changes can be automated by running `docker compose watch`. 

The docker containers built by this project come in two flavors: `PHP-FPM` and `Roadrunner`. Both following the same execution model: Container is initialized as `root` user and start a control process as `root` user. The control process forks worker processes running as `www-data` user. This means that the `entrypoint` script always should be run as `root`. It is NOT advisable to override the initializing user by using the `user` property in `docker-compose.yml`. Since the worker processes are dealing with public HTTP traffic, they are running as a non-privileged user anyway. In contrast to many other PHP-deployments the PHP code inside the container is owned by `root`. By using this approach the running application is not able to modify its own code.

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

| Name                  | Mode     | Description                                    |
|-----------------------|----------|------------------------------------------------|
| ADMIN_EMAIL           | Static   | Email address for default admin user           |
| ADMIN_PASSWORD        | Static   | Password for default admin user                |
| APP_BASE_URL          | Static   | Public base URL for the application            |
| APP_HOME              | Dynamic  | Path to application home directory             |
| APP_LOGOS_PATH        | Dynamic  | Path to directory for logos                    |
| APP_LOGOS_PUBLIC_PATH | Static   | Public URL path to logos                       |
| APP_VERSION           | Dynamic  | Application version                            |
| DB_PASSWORD_FILE      | Static   | Path to DB password file                       |
| DB_URL                | Static   | URL for database connection (MySQL/PostgreSQL) |
| EMAIL_SENDER_ADDRESS  | Static   | Sender address for outbound emails             |
| EMAIL_SENDER_NAME     | Static   | Sender name for outbound emails                |
| EMAIL_URL             | Static   | URL to use for outbound emails (gateway)       |
| JWT_SECRET            | Static   | Hex-encoded secret for JSON Web Tokens         |
| LOG_LEVEL             | Static   | Minimum level for log messages                 |
| LOG_PATH              | Static   | Path to log file                               |
| RATE_LIMIT            | Static   | Defines an API rate limit                      |
| REDIS_HOST            | Static   | Hostname or IP address running Redis           |

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

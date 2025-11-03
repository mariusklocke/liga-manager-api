# CHANGELOG

## 1.28.1 - 2025-11-03
* Bugfix: Fixed an issue with "team not ranked" after team replaced in season

## 1.28.0 - 2025-10-23
* Feature: Add mutation createMatch to GraphQL API
* Feature: Add mutation createMatchDayForSeason to GraphQL API
* Improvement: Integrate dependabot
* Improvement: Improve output in `app:api:query` command

## 1.27.0 - 2025-10-21
* Feature: Add insights endpoint (local only)
* Improvement: Update composer packages
* Improvement: Remove nginx from integration tests
* Improvement: Improve error logging

## 1.26.3 - 2025-10-07
* Improvement: Refactor API client building
* Improvement: Update Roadrunner to 2025.1.4
* Improvement: Update composer packages

## 1.26.2 - 2025-09-16
* Improvement: Use local timezone in API responses
* Improvement: Generate UUIDs without third-party code
* Improvement: Update composer packages
* Improvement: Update Roadrunner to 2025.1.3

## 1.26.1 - 2025-08-29
* Improvement: Update composer packages
* Improvement: Improve error handling and logging

## 1.26.0 - 2025-07-22
* Feature: Support GIF, JPG and PNG images as team logos
* Feature: Allow tournaments to be started and ended

## 1.25.4 - 2025-07-13
* Improvement: Update Roadrunner to 2025.1.2
* Improvement: Update composer packages
* Improvement: Don't terminate worker process on internal server error
* Improvement: Improve output on ListVersionsCommand
* Improvement: Remove composer from docker images
* Improvement: Reduce docker image layers

## 1.25.3 - 2025-06-24
* Improvement: Add OpenAPI spec
* Improvement: Provide API docs
* Improvement: Run tests against OpenAPI spec
* Improvement: Refactor CLI tests
* Improvement: Update composer packages

## 1.25.2 - 2025-05-04
* Improvement: Update composer packages
* Improvement: Update roadrunner to 2025.1.1

## 1.25.1 - 2025-03-25
* Improvement: Update composer packages
* Improvement: Use in-memory secrets in build

## 1.25.0 - 2025-03-10
* Feature: Add CLI command to send API requests
* Feature: Add CLI command to list versions
* Feature: Add CLI command to show config
* Feature: Add CLI command to inspect container
* Improvement: Refactor build scripts
* Improvement: Update default configs for development
* Improvement: Add `php-ext-configure` to quickly toggle PHP extensions
* Improvement: Refactor filesystem access classes
* Improvement: Refactor roadrunner worker code
* Improvement: Integrate roadrunner RPC interface for metrics
* Improvement: Refactor filter handling

## 1.24.0 - 2025-01-21
* Feature: Add user locale for localized emails
* Improvement: Send emails with additional plaintext content
* Improvement: Remove `doctrine/migrations`
* Improvement: Update composer packages
* Improvement: Exclude xdebug from docker images
* Improvement: Remove experimental WebAuthn feature
* Improvement: Enable Roadrunner metrics

## 1.23.1 - 2024-12-28
* Bugfix: Fix email logo not loading in Gmail

## 1.23.0 - 2024-12-27
* Feature: Add command for validating config
* Improvement: Add support for PostgreSQL database
* Improvement: Modernize email templates
* Improvement: Use `doctrine/orm` instead of `doctrine/migratios` for DB migrations
* Improvement: Improved support for `docker secrets`
* Improvement: Move doctrine proxies to `src` folder
* Improvement: Bump MariaDB version to 11.4 in CI actions

## 1.22.1 - 2024-12-09
* Improvement: Update docker images to PHP 8.4
* Improvement: Add proxy support to RateLimitMiddleware
* Improvement: Make healthchecks optional in docker
* Improvement: Make EmailHealthCheck a regular healthcheck
* Improvement: Replace [coveralls.io](https://coveralls.io) with [codecov.io](https://codecov.io)
* Improvement: Refactor config handling
* Improvement: Update composer packages

## 1.22.0 - 2024-10-17
* Feature: Add command for email health checks
* Feature: Add commands for uploading and cleaning up team logos
* Bugfix: Fix missing user/group config for PHP-FPM
* Bugfix: Fix entrypoint scripts in docker images
* Improvement: Run tests more similar to production
* Improvement: Update dependencies

## 1.21.0 - 2024-09-23
* Feature: Add commands for DB import/export (XML format)
* Bugfix: Terminate worker process after internal error
* Improvement: Refactor API controllers
* Improvement: Write version to composer.json
* Improvement: Return GraphQL schema on "GET /api/graphql"
* Improvement: Add "Content-Length" header to responses

## 1.20.2 - 2024-09-10
* Bugfix: Fix config issue for PHP-FPM docker image builds
* Improvement: Allow toggling maintenance mode without restart
* Improvement: Update dependencies

## 1.20.1 - 2024-09-02
* Bugfix: Fix timezone issue when scheduling all matches in a season
* Improvement: Speedup builds by installing pre-compiled PHP packages from Alpine repos
* Improvement: Replace hardcoded values in test by generated values

## 1.20.0 - 2024-08-28
* Feature: Allow matches to be moved to another match day
* Improvement: Update dependencies

## 1.19.0 - 2024-07-30
* Feature: Add metrics endpoint
* Feature: Allow JSON config
* Improvement: Update dependencies
* Improvement: Using `docker compose` during builds

## 1.18.1 - 2024-04-16
* Bugfix: Fix links in mails
* Improvement: Update dependencies

## 1.18.0 - 2024-03-25
* Feature: Ship Roadrunner as alternative runtime to PHP-FPM for docker images

## 1.17.0 - 2024-02-13
* Improvement: Update `doctrine/orm` to 3.0
* Improvement: Update `doctrine/dbal` to 4.0
* Improvement: Improve security in docker image
* Improvement: Minor refactorings

## 1.16.0 - 2024-02-04
* Feature: Add configurable rate limiting
* Improvement: Improve debug logging
* Improvement: Update dependencies

## 1.15.0 - 2024-01-23
* Improvement: Update docker image to PHP 8.3
* Improvement: Update docker image to composer 2.6.6
* Improvement: Update dependencies

## 1.14.0 - 2023-12-18
* Feature: Add team logo upload
* Improvement: Refactor config handling
* Improvement: Add health check for docker container
* Improvement: Update dependencies

## 1.13.1 - 2023-11-14
* Improvement: Refactor logging
* Improvement: Refactor installer
* Improvement: Do not ship dev tools in docker image
* Improvement: Update dependencies
* Improvement: Documentation of environment variables

## 1.13.0 - 2023-08-27
* Feature: Add CLI command to browse the database interactively
* Improvement: Refactor exception handling
* Improvement: Add error code in API response
* Improvement: Update docker image to PHP 8.2
* Improvement: Update dependencies

## 1.12.10 - 2023-03-29
* Improvement: Updated dependencies

## 1.12.9 - 2023-01-16
* Improvement: Updated composer libraries

## 1.12.8 - 2022-11-30
* Improvement: Add assertions for replacing team in season
* Improvement: Update composer libraries
* Improvement: Refactor retry logic into dedicated class
* Improvement: Do not override ENTRYPOINT in docker image

## 1.12.7 - 2022-10-12
* Improvement: Retry in case of failed connection to MariaDB or Redis

## 1.12.6 - 2022-09-21
* Bugfix: Fix issue with replacing team in season
* Improvement: Updated composer libraries
* Improvement: Updated PHAR tools

## 1.12.5 - 2022-07-26
* Bugfix: Fix issue with undefined constant in FPM context

## 1.12.4 - 2022-07-26
* Improvement: Replace `monolog/monolog` with custom logger
* Improvement: Updated composer libraries

## 1.12.3 - 2022-06-27
* Improvement: Updated PHAR tools
* Improvement: Updated composer libraries

## 1.12.2 - 2022-05-09
* Improvement: Updated PHAR tools
* Improvement: Updated composer libraries
* Refactoring: Remove unnecessary stuff from docker image
* Refactoring: Optimize build steps

## 1.12.1 - 2022-04-19
* Refactoring: Simplify query criteria building
* Refactoring: Make pitch location nullable in database

## 1.12.0 - 2022-03-02
* Improvement: Updated docker image to PHP 8.1
* Improvement: Updated PHAR tools
* Refactoring: Dropped support for PHP 7.3
* Refactoring: Added type definitions to class properties
* Bugfix: Fixed an empty condition bug in CLI command for listing users

## 1.11.6 - 2022-02-09
* Improvement: Added CLI commands for listing and deleting users
* Improvement: Updated dependencies
* Improvement: Added more tests

## 1.11.5 - 2022-01-31
* Improvement: Update `doctrine/dbal` to 3.3.0
* Improvement: Integrate `gdpr-dump`
* Refactoring: Decouple `EventDispatcher` from `HandlerResolver` in DI config

## 1.11.4 - 2022-01-24
* Improvement: Allow testing of non-default versions of PHP, MariaDB and Redis
* Improvement: Updated several composer dependencies
* Improvement: Updated PHAR Tools
* Improvement: Use type hints in all command constructors
* Improvement: Simplify mapping Exceptions to HTTP status codes

## 1.11.3 - 2022-01-14
* Improvement: Use `symfony/event-dispatcher` for handling of domain events

## 1.11.2 - 2022-01-12
* Improvement: Replace `swiftmailer/swiftmailer` with `symfony/mailer`

## 1.11.1 - 2022-01-09
* Bugfix: Fix missing sorting on matchDays

## 1.11.0 - 2022-01-09
* Feature: Added query "teamsByPattern"
* Improvement: Replaced mysqli with pdo_mysql for SELECT queries
* Improvement: Introduced generic filters, sortings and pagination
* Improvement: Increased test coverage

## 1.10.0 - 2021-11-30
* Feature: Added query "matchesByKickoff"
* Feature: Added maintenance mode
* Improvement: Replaced some shell script
* Improvement: Added tests for some CLI commands
* Improvement: Updated dependencies

## 1.9.0 - 2021-11-22
* Feature: Add command to schedule all matches for a match day

## 1.8.0 - 2021-07-31
* Feature: Add PHP 8.0 compatibility
* Feature: Ship Docker Image with PHP 8.0 by default
* Feature: Use Docker Buildkit

## 1.7.3 - 2021-07-18
* Bugfix: Fixed issue in build script

## 1.7.2 - 2021-07-18
* Improvement: Updated dependencies
* Improvement: Speedup build process with proper caching

## 1.7.1 - 2021-07-16
* Bugfix: Fixed an issue with tags in build script

## 1.7.0 - 2021-07-16
* Feature: Add ReplaceTeamInSeasonCommand
* Improvement: Improve docker build performance
* Improvement: Move from Travis CI to GitHub Actions

## 1.6.2 - 2020-07-28
* Bugfix: Fix permission issue

## 1.6.1 - 2020-07-25
* Bugfix: Fix permission issue in Dockerfile

## 1.6.0 - 2020-07-25
* Feature: Add second season half
* Feature: Add health checks
* Feature: Add plain PHP install script
* Feature: Add .editorconfig file
* Improvement: Use Redis 5 in docker-compose config
* Improvement: Add deptrac
* Improvement: Build script now uses docker-compose
* Bugfix: Fix bug in auth checking
* Multiple refactorings

## 1.5.3 - 2020-01-13
* Fix regression in 1.5.2

## 1.5.2 - 2020-01-13
* Refactor and improve code quality

## 1.5.1 - 2020-01-02
* Fix security issue when requesting passwort reset
* Fix issue with handling time zones
* Replace Pimple with PHP-DI

## 1.5.0 - 2019-12-16
Update Slim Framework to v4

## 1.4.0 - 2019-12-01
Upgrade to PHP 7.4

## 1.3.0 - 2019-11-21
* Added ScheduleAllMatchesForSeasonCommand
* Improved tests
* Improved error handling
* Minor refactorings

## 1.2.4 - 2019-11-03
Increase length for WebAuthn credential IDs to 255 bytes

## 1.2.3 - 2019-11-03
Fix logging

## 1.2.2 - 2019-11-02
Add support for more attestation statement formats

## 1.2.1 - 2019-11-02
Improve WebAuthn test client

## 1.2.0 - 2019-11-02
* Add experimental support for passwordless authentication via WebAuthn
* Add command for invalidating access tokens
* Add command for inviting users by email
* Add doctrine-migrations
* Improved error handling by using proper HTTP status codes in GraphQL API
* Enable Opcache

## 1.1.0 - 2019-06-15
Drop support for REST API and supplying JWT Secret from file

## 1.0.7 - 2019-05-01
Added support for SMTP over TLS


# CHANGELOG

## NEXT

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


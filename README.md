## Requirements
* PHP 7.1 or later
* mysqli extension
* pdo_mysql extension

## Install
```bash
php bin/installPharTools.php
php composer.phar install
npm install swagger-ui-dist
mv node_modules/swagger-ui-dist public/docs
php bin/console.php orm:schema-tool:create
php bin/console.php app:load-fixtures
```

## Run Application
```bash
php -S localhost:8080 -t public public/index.php
```

## Create Migration

```bash
php bin/console.php orm:schema-tool:update --dump-sql > data/migrations/update_$(date +%Y%m%d%H%M%S).sql
```
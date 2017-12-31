# Useful Commands

## Create Migration

```bash
php vendor/doctrine/orm/bin/doctrine.php orm:schema-tool:update --dump-sql > data/migrations/update_$(date +%Y%m%d%H%M%S).sql
```

## Install Swagger UI
```bash
npm install swagger-ui-dist
mv node_modules/swagger-ui-dist public/docs
```
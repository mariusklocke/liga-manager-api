FROM robertkitzing/liga-manager-ui:latest AS source
FROM nginx:1-alpine

COPY --from=source /usr/src/app/www /var/www/ui

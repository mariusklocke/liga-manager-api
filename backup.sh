#!/usr/bin/env bash
set -e

RETENTION_DAYS=90

case $1 in
  "create")
    docker compose exec -T mariadb sh -c "find /backup -mtime +$RETENTION_DAYS -type f -name 'lima-db*.sql.gz' -delete"
    TIMESTAMP=$(date +%Y-%m-%d-%H-%M-%S)
    if docker compose exec -T mariadb sh -c "mysqldump -u root -pdev --single-transaction wilde-liga-bremen | gzip > /backup/lima-db-$TIMESTAMP.sql.gz"; then
        SIZE=$(docker compose exec -T mariadb sh -c "stat --printf='%s' /backup/lima-db-$TIMESTAMP.sql.gz")
        if [[ $SIZE -gt 0 ]]; then
            echo "Backup $TIMESTAMP has been created. Bytes written: $SIZE"
        fi
    fi
    ;;
  "delete")
    TIMESTAMP=$2
    docker compose exec mariadb sh -c "rm /backup/lima-db-$TIMESTAMP.sql.gz"
    ;;
  "list")
    docker compose exec mariadb sh -c "find /backup -maxdepth 1 -name 'lima-db-*.sql.gz' -type f"
    ;;
  "restore")
    TIMESTAMP=$2
    if docker compose exec -T mariadb sh -c "zcat /backup/lima-db-$TIMESTAMP.sql.gz | mysql -u root -pdev wilde-liga-bremen"; then
        echo "Backup $TIMESTAMP has been restored."
    fi
    ;;
  *)
    echo "Usage:"
    echo "$0 create"
    echo "$0 delete <timestamp>"
    echo "$0 list"
    echo "$0 restore <timestamp>"
    ;;
esac

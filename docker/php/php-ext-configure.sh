#!/bin/sh
set -e

usage() {
  echo "Usage: $0 {enable|disable} EXTENSION"
  echo "Example: $0 enable xdebug"
}

case "$1" in
  enable)
    EXTENSION=$2
    if [ -z EXTENSION ]; then
      usage
      exit 1
    fi

    CONFIG_FILE=$(find /etc/php/conf.d -name "*$EXTENSION.ini" -type f | head -n 1)
    if [ ! -f $CONFIG_FILE ]; then
      echo "Failed to enable extension $EXTENSION: Config file not found"
      exit 1
    fi

    CONFIG_LINE="extension=$EXTENSION"
    if [ "$EXTENSION" = "opcache" ] || [ "$EXTENSION" = "xdebug" ]; then
      CONFIG_LINE="zend_extension=$EXTENSION"
    fi

    if grep -q "^;$CONFIG_LINE" "$CONFIG_FILE"; then
      sed -i "s/;$CONFIG_LINE/$CONFIG_LINE/" "$CONFIG_FILE"
      echo "Extension $EXTENSION has been enabled"
    else
      echo "Extension $EXTENSION already enabled"
    fi
    ;;

  disable)
    EXTENSION=$2
    if [ -z EXTENSION ]; then
      usage
      exit 1
    fi

    CONFIG_FILE=$(find /etc/php/conf.d -name "*$EXTENSION.ini" -type f | head -n 1)
    if [ ! -f $CONFIG_FILE ]; then
      echo "Failed to disable extension $EXTENSION: Config file not found"
      exit 1
    fi

    CONFIG_LINE="extension=$EXTENSION"
    if [ "$EXTENSION" = "opcache" ] || [ "$EXTENSION" = "xdebug" ]; then
      CONFIG_LINE="zend_extension=$EXTENSION"
    fi

    if grep -q "^$CONFIG_LINE" "$CONFIG_FILE"; then
      sed -i "s/$CONFIG_LINE/;$CONFIG_LINE/" "$CONFIG_FILE"
      echo "Extension $EXTENSION has been disabled"
    else
      echo "Extension $EXTENSION is not enabled"
    fi
    ;;
    
  *)
    usage
    exit 1
    ;;
esac

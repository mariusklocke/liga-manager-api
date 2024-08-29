#!/bin/sh
set -e

CONFIG_PATH="/etc/php/conf.d/50_xdebug.ini"

case "$1" in
  on)
    echo "zend_extension=xdebug.so" > "$CONFIG_PATH"
    ;;

  off)
    echo "" > "$CONFIG_PATH"
    ;;

  *)
    echo "Usage: $0 {on|off}"
    exit 1
    ;;
esac


#!/bin/sh

install () {
    TARGET_PATH="$1"
    SOURCE_URL="$2"
    CHECKSUM="$3"

    wget -O "$TARGET_PATH" "$SOURCE_URL"
    echo "$CHECKSUM $TARGET_PATH" > checksums
    sha256sum -c checksums
    rm checksums
    chmod +x "$TARGET_PATH"
}

COMPOSER_PATH="/usr/local/bin/composer"
COMPOSER_VERSION="2.7.7"
COMPOSER_URL="https://github.com/composer/composer/releases/download/$COMPOSER_VERSION/composer.phar"
COMPOSER_CHECKSUM="aab940cd53d285a54c50465820a2080fcb7182a4ba1e5f795abfb10414a4b4be"

set -e
install "$COMPOSER_PATH" "$COMPOSER_URL" "$COMPOSER_CHECKSUM"

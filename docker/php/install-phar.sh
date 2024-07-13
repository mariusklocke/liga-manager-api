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
COMPOSER_VERSION="2.7.6"
COMPOSER_URL="https://github.com/composer/composer/releases/download/$COMPOSER_VERSION/composer.phar"
COMPOSER_CHECKSUM="29dc9a19ef33535db061b31180b2a833a7cf8d2cf4145b33a2f83504877bba08"

GDPR_DUMP_PATH="/usr/local/bin/gdpr-dump"
GDPR_DUMP_VERSION="4.2.2"
GDPR_DUMP_URL="https://github.com/Smile-SA/gdpr-dump/releases/download/$GDPR_DUMP_VERSION/gdpr-dump.phar"
GDPR_DUMP_CHECKSUM="ee74e89cb48dc7565bd062d4a84b5ebe4be93ed1c395a090f332a17c3440f8d4"

set -e
install "$COMPOSER_PATH" "$COMPOSER_URL" "$COMPOSER_CHECKSUM"
install "$GDPR_DUMP_PATH" "$GDPR_DUMP_URL" "$GDPR_DUMP_CHECKSUM"

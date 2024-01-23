#!/bin/sh

TARGET_PATH="$1"
SOURCE_URL="$2"
CHECKSUM="$3"

set -e
wget -O "$TARGET_PATH" "$SOURCE_URL"
echo "$CHECKSUM $TARGET_PATH" > checksums
sha256sum -c checksums
rm checksums
chmod +x "$TARGET_PATH"

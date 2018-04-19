#!/bin/sh
set -xe

if [ ! -d /etc/jwt ]; then
    mkdir /etc/jwt
fi
ssh-keygen -t rsa -b 4096 -f /etc/jwt/secret.key -N ''
rm /etc/jwt/secret.key.pub
chmod 644 /etc/jwt/secret.key

#!/usr/bin/env bash

mkdir /etc/jwt
ssh-keygen -t rsa -b 4096 -f /etc/jwt/secret.key -N ''
rm /etc/jwt/secret.key.pub
chmod 644 /etc/jwt/secret.key

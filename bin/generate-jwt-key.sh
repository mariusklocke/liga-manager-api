#!/usr/bin/env bash

mkdir $1
ssh-keygen -t rsa -b 4096 -f $1/secret.key -N ''
rm $1/secret.key.pub
chmod 644 $1/secret.key

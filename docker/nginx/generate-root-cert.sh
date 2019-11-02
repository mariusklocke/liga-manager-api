#!/bin/sh
if ! [ $(id -u) = 0 ]; then
   echo "Missing privileges: Are you root?"
   exit 1
fi

ROOT_CA_CERT=/etc/ssl/certs/local-ca.crt
ROOT_CA_KEY=/etc/ssl/private/local-ca.key

openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
       -keyout ${ROOT_CA_KEY} \
       -out ${ROOT_CA_CERT}

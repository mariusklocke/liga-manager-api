#!/bin/sh
if ! [ $(id -u) = 0 ]; then
   echo "Missing privileges: Are you root?"
   exit 1
fi

ROOT_CA_CERT=/etc/ssl/certs/local-ca.crt
ROOT_CA_KEY=/etc/ssl/private/local-ca.key

openssl req -new -nodes -newkey rsa:2048 \
    -subj "/C=DE/O=localhost/CN=localhost" \
    -out /etc/ssl/certs/localhost.csr \
    -keyout /etc/ssl/private/localhost.key

openssl x509 -req -days 3650 -sha256 \
    -in /etc/ssl/certs/localhost.csr \
    -CA ${ROOT_CA_CERT} \
    -CAkey ${ROOT_CA_KEY} \
    -CAcreateserial \
    -out /etc/ssl/certs/localhost.crt \
    -extfile localhost.ext

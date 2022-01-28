#!/bin/sh
if ! [ $(id -u) = 0 ]; then
   echo "Missing privileges: Are you root?"
   exit 1
fi

ROOT_CA_CERT=/etc/ssl/certs/local-ca.crt
ROOT_CA_KEY=/etc/ssl/private/local-ca.key
SITE_CERT=/etc/ssl/certs/lima.local.crt
SITE_CSR=/etc/ssl/certs/lima.local.csr
SITE_KEY=/etc/ssl/private/lima.local.key
SITE_EXT=$(mktemp)

cat <<EOF >> "${SITE_EXT}"
authorityKeyIdentifier=keyid,issuer
basicConstraints=CA:FALSE
keyUsage = digitalSignature, nonRepudiation, keyEncipherment, dataEncipherment
subjectAltName = @alt_names

[alt_names]
DNS.1 = lima.local
EOF

openssl req -new -nodes -newkey rsa:2048 \
    -subj "/C=DE/O=lima.local/CN=lima.local" \
    -out ${SITE_CSR} \
    -keyout ${SITE_KEY}

openssl x509 -req -days 3650 -sha256 \
    -in ${SITE_CSR} \
    -CA ${ROOT_CA_CERT} \
    -CAkey ${ROOT_CA_KEY} \
    -CAcreateserial \
    -out ${SITE_CERT} \
    -extfile "${SITE_EXT}"

rm "${SITE_EXT}"

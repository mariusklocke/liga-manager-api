FROM nginx:alpine
RUN mkdir -p /etc/ssl/certs /etc/ssl/private \
    && apk add openssl \
    && openssl req -x509 -nodes -days 3650 -newkey rsa:2048 \
       -keyout /etc/ssl/private/selfsigned.key \
       -out /etc/ssl/certs/selfsigned.crt \
       -subj "/C=US/ST=CA/O=MyOrg, Inc./CN=example.com"
COPY default.conf /etc/nginx/conf.d/default.conf

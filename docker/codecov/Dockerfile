FROM alpine:latest
RUN apk add curl \
    && curl -s -o /usr/local/bin/codecov https://uploader.codecov.io/latest/alpine/codecov \
    && chmod +x /usr/local/bin/codecov

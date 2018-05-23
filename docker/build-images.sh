#!/bin/bash

docker build -f docker/php/Dockerfile -t mklocke/liga-manager-api:latest .
docker build -f docker/php/Dockerfile -t mklocke/liga-manager-api:latest-xdebug --build-arg ENABLE_XDEBUG=1 .
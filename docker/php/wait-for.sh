#!/bin/sh

START=$(date +%s)
SLEEP_LENGTH=2
TIMEOUT_LENGTH=60
echo "Waiting for $1 to listen on $2..."
while ! nc -z $1 $2; do
    if [ $(($(date +%s) - $START)) -gt $TIMEOUT_LENGTH ]; then
        echo "Service $1:$2 did not start within $TIMEOUT_LENGTH seconds. Aborting..."
        exit 1
    fi
    echo "sleeping"
    sleep $SLEEP_LENGTH
done

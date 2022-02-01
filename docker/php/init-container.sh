#!/bin/sh
set -e

lima app:maintenance --mode=on
doctrine-migrations migrations:migrate -n
lima app:maintenance --mode=off

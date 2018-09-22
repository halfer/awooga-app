#!/bin/bash

# The 'r' command inserts a whole file at the pattern
APP_NAME=web
sed \
    -e '/__BASE__/r Dockerfile.base' \
    Dockerfile.$APP_NAME \
    > /tmp/awooga.Dockerfile.$APP_NAME

docker build \
    --file /tmp/awooga.Dockerfile.$APP_NAME \
	--tag awooga-$APP_NAME \
	--target runtime \
	.

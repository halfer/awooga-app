#!/bin/bash

APP_NAME=cron
REGISTRY=registry.gitlab.com/halfercode/awooga-$APP_NAME

# The 'r' command inserts a whole file at the pattern
sed \
    -e '/__BASE__/r Dockerfile.base' \
    Dockerfile.$APP_NAME \
    > /tmp/awooga.Dockerfile.$APP_NAME

docker build \
    --file /tmp/awooga.Dockerfile.$APP_NAME \
	--tag $REGISTRY \
	--target runtime \
	.

#!/bin/bash

APP_NAME=cron
PROJECT=awooga-$APP_NAME
REGISTRY=registry.gitlab.com/halfercode/$PROJECT

# The 'r' command inserts a whole file at the pattern
sed \
    -e '/__BASE__/r Dockerfile.base' \
    Dockerfile.$APP_NAME \
    > /tmp/awooga.Dockerfile.$APP_NAME

docker build \
    --file /tmp/awooga.Dockerfile.$APP_NAME \
	--tag $PROJECT \
	--target runtime \
	.

echo
echo "Suggested:"
echo "  docker tag $PROJECT $REGISTRY:<version>"
echo "  docker push $REGISTRY:<version>"

#!/bin/bash

# The 'r' command inserts a whole file at the pattern
sed -e '/__BASE__/r Dockerfile.base' Dockerfile.cron

docker build \
    --file Dockerfile.cron \
	--tag awooga \
	--target runtime \
	.

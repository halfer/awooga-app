#!/bin/bash

# The 'r' command inserts a whole file at the pattern
sed -e '/__BASE__/r Dockerfile.base' Dockerfile.web

docker build \
    --file Dockerfile.web \
	--tag awooga \
	--target runtime \
	.

#!/bin/bash
#
# Creates a volume on a Docker host

VOLUME_NAME=awooga-volume

# Check if the volume exists already
docker volume inspect $VOLUME_NAME &> /dev/null
VOLUME_RESULT=$?

if [ $VOLUME_RESULT -eq 0 ]; then
    echo Error: volume '$VOLUME_RESULT' already exists
    exit 1
fi

# Create the volume
docker volume create \
    --driver local \
    --opt o=size=50m \
    --opt type=tmpfs \
    --opt device=tmpfs \
    $VOLUME_NAME

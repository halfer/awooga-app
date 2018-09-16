#!/bin/bash
#
# Creates a volume on a Docker host

# @todo Check if the volume exists already
#if volume exists
#    echo error
#    exit
#then

# Create the volume
docker volume create \
    --driver local \
    --opt o=size=50m \
    --opt type=tmpfs \
    --opt device=tmpfs \
    awooga-volume

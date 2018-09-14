#!/bin/bash
#
# Start script for local development

# Get the host IP address
export DOCKER_HOSTIP=`ifconfig docker0 | grep "inet addr" | cut -d ':' -f 2 | cut -d ' ' -f 1`
echo "Connecting to database on Docker host ${DOCKER_HOSTIP}"

docker run \
    --add-host=docker:${DOCKER_HOSTIP} \
    --env SLIM_MODE=local \
    --detach \
    --publish 8083:80 \
    --rm \
    awooga

# Go back to original dir
cd $STARTDIR

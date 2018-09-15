#!/bin/bash
#
# Start script for local development

# Save pwd and then change dir to the project root
STARTDIR=`pwd`
cd `dirname $0`/..

# Get the host IP address
export DOCKER_HOSTIP=`ifconfig docker0 | grep "inet addr" | cut -d ':' -f 2 | cut -d ' ' -f 1`
echo "Connecting to database on Docker host ${DOCKER_HOSTIP}"

docker run \
    --add-host=docker:${DOCKER_HOSTIP} \
    --env SLIM_MODE=local \
    --volume $PWD/filesystem:/var/www/localhost/htdocs/filesystem \
    --detach \
    --publish 8083:80 \
    --rm \
    awooga

# Go back to original dir
cd $STARTDIR

# Go back to original dir
cd $STARTDIR

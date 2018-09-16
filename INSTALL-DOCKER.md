Docker installation instructions
===

Getting started
---

To build a Docker image, run the shell script in the root:

    ./rebuild.sh

To run in Docker, first you'll need a volume:

    SLIM_MODE=production sudo console/create-volume.sh

To run in a Docker Swarm, use this create command:

    docker service create \
        --env SLIM_MODE=production \
        --mount source=awooga-volume,target=/var/www/localhost/htdocs/filesystem \
        awooga


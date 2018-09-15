#!/bin/sh
#
# Shell script to start system

# @todo Blow up if SLIM_MODE does not exist

# @todo Also blow up if docker network alias does not exist

# @todo Mount the file system

# Start Apache
/usr/sbin/httpd -DFOREGROUND

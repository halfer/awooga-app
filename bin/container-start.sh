#!/bin/sh
#
# Shell script to start system

# @todo Blow up if SLIM_MODE does not exist

# @todo Also blow up if docker network alias does not exist

# Ensure the file perms in the volume are correct (the 'web' and 'cron'
# containers both have an 'apache' user with a common UID)
chown -R apache:apache /var/www/localhost/htdocs/filesystem/mount

# Start Apache
/usr/sbin/httpd -DFOREGROUND

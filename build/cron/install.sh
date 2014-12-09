#!/bin/bash

# Save pwd and then change dir to the script location
STARTDIR=`pwd`
cd `dirname $0`/../..

# Recreate the awooga user, don't permit direct login
deluser awooga --quiet &>/dev/null
useradd awooga --no-create-home --shell=/bin/false

# Ensure log file exists and is writable
mkdir --parents /var/log/awooga
touch /var/log/awooga/cron.log
chown -R awooga /var/log/awooga

# @todo Add some log rotation for the above?

# Install the command as a system cron file
# Using semicolons here as a regex delimiter, since filenames have forward slashes
ROOT=`pwd`
sed -e "s;__ROOT__;${ROOT};g" < $ROOT/build/cron/template > /etc/cron.d/awooga

# Go back to original dir
cd $STARTDIR 

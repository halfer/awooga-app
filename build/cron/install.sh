#!/bin/bash

# Save pwd and then change dir to the script location
STARTDIR=`pwd`
cd `dirname $0`/../..

# Recreate the awooga user, don't permit direct login
deluser awooga --quiet &>/dev/null
useradd awooga --no-create-home --shell=/bin/false

# Install the command as a system cron file
# Using semicolons here as a regex delimiter, since filenames have forward slashes
ROOT=`pwd`
sed -e "s;__ROOT__;${ROOT};g" < $ROOT/build/cron/template > /etc/cron.d/awooga

# Go back to original dir
cd $STARTDIR 

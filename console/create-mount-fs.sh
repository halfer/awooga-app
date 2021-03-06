#!/bin/sh
#
# Converted for use in Alpine/BusyBox

######
#
# Script to create and mount a filing system into which contribution repos are pulled. The
# use of a filing system is to prevent large remote repos from accidentally/maliciously
# overwhelming the local machine by consuming all available disk space.
#
# @todo I've added a full path for `mount`, do I need to do this for `mkdir`, `chown`, `chmod` etc?
######

# Set up some constants
IMAGE=filesystem/image.img
MOUNT_POINT=filesystem/mount
ROOT_SEARCH_INDEX=$MOUNT_POINT/search-index
ROOT_REPOS=$MOUNT_POINT/repos
SIZE=20
USER=awooga

# Mount command is not found on Ubuntu at boot time, need to specify full path
MOUNTCMD=/bin/mount

# Bomb out if not run as root, exit as fail (if this is only ever run
# in a container, it could just be taken out)
EUID=`id -u`
if [ "$EUID" -ne 0 ]
then
    echo "Mount script must be run as root"
    exit 1
fi

# Check if user exists
id -u $USER &> /dev/null
result=$?

if [ $result -ne 0 ]; then
	# Create this user
    #
    # -H Don't create a home folder
    # -D Don't assign a password
    # -s Use this shell
	adduser -H -D -s /bin/false $USER
fi

# Save pwd and then change dir to the script location
STARTDIR=`pwd`
cd `dirname $0`/..

# Get project folder root
PROJECT_ROOT=`pwd`

# If the FS image does not exist, let's create it
if [ ! -f "$IMAGE" ]; then
    # Number of M to set aside for this filing system
    dd if=/dev/zero of=$IMAGE bs=1M count=$SIZE &> /dev/null

    # Format: the -F permits creation even though it's not a "block special device"
    mkfs.ext3 -F -q $IMAGE
fi

# Mount if the filing system is not already mounted
$MOUNTCMD | cut -d ' ' -f 3 | grep -q "^${PROJECT_ROOT}/${MOUNT_POINT}$"
if [ $? -ne 0 ]; then
    # -p Create all parent dirs as necessary
	mkdir -p $MOUNT_POINT
	$MOUNTCMD -t ext3 $IMAGE $MOUNT_POINT
fi

# Set appropriate user perms on mounted filing system
chown $USER $MOUNT_POINT

# The following is for the search index. I wonder if this would be better with its own
# private filing system?

# Set up repo folder (only need rwx access for Awooga user)
mkdir -p $ROOT_REPOS
chown -R $USER $ROOT_REPOS
chmod -R 700 $ROOT_REPOS

# Set up search index folder (needs write to Awooga and Apache users)
mkdir -p $ROOT_SEARCH_INDEX
chown -R $USER $ROOT_SEARCH_INDEX
chgrp -R www-data $ROOT_SEARCH_INDEX
chmod -R 770 $ROOT_SEARCH_INDEX

# Go back to original dir
cd $STARTDIR

# Exit OK
exit 0

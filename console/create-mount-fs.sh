#!/bin/bash

######
#
# Script to create and mount a filing system into which contribution repos are pulled. The
# use of a filing system is to prevent large remote repos from accidentally/maliciously
# overwhelming the local machine by consuming all available disk space.
#
######

# Set up some constants
IMAGE=filesystem/image.img
MOUNT_POINT=filesystem/mount
SIZE=20
USER=awooga

# Bomb out if not run as root, exit as fail
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
	useradd --no-create-home --shell /bin/false $USER
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
mount | cut -d ' ' -f 3 | grep -q "^${PROJECT_ROOT}/${MOUNT_POINT}$"
if [ $? -ne 0 ]; then
	mkdir --parents $MOUNT_POINT
	mount $IMAGE $MOUNT_POINT
fi

# Set appropriate user perms on mounted filing system
chown $USER $MOUNT_POINT

# Go back to original dir
cd $STARTDIR 

# Exit OK
exit 0

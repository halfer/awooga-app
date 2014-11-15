#!/bin/bash

# Set up some constants
IMAGE=filesystem/image.img
MOUNT_POINT=filesystem/mount
SIZE=20

# Bomb out if not run as root, exit as fail
if [ "$EUID" -ne 0 ]
then
    echo "Mount script must be run as root"
    exit 1
fi

# Save pwd and then change dir to the script location
STARTDIR=`pwd`
cd `dirname $0`/..

# Get project folder root
PROJECT_ROOT=`pwd`

# If the filing system is already mounted, exit OK
if mount | cut -d ' ' -f 3 | grep -q "^${PROJECT_ROOT}/${MOUNT_POINT}$" ; then
    echo "File system '${MOUNT_POINT}' already mounted, exiting"
    exit 0
fi

# If the FS image does not exist, let's create it
if [ ! -f "$IMAGE" ]; then
    # Number of M to set aside for this filing system
    dd if=/dev/zero of=$IMAGE bs=1M count=$SIZE &> /dev/null

    # Format: the -F permits creation even though it's not a "block special device"
    mkfs.ext3 -F -q $IMAGE
fi

# Create folder if required, mount
mkdir --parents $MOUNT_POINT
mount $IMAGE $MOUNT_POINT

# Go back to original dir
cd $STARTDIR 

# Exit OK
exit 0

#!/bin/bash

# Save pwd and then change dir to the script location
STARTDIR=`pwd`
cd `dirname $0`/../../..

# Start up built-in web server
php \
	-S 127.0.0.1:8095 \
	-t web \
	test/browser/scripts/router.php \
	2> /dev/null

# Go back to original dir
cd $STARTDIR 

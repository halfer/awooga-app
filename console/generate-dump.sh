# This script grabs a copy of the database for the development user and saves it
# in the fixtures data file. If we want to run tests on a specific set of data, run
# this and then adjust the browser tests to match.
#
# --no-create-info: no database structure, using the build SQL for that
# --single-transaction: prevents permission problems when using LOCK statements
# --ignore-table=awooga.issue: this is created in the build SQL

# Save pwd and then change dir to the script location
STARTDIR=`pwd`
cd `dirname $0`/..

mysqldump \
	-u awooga_user --password=password \
	--complete-insert \
	--no-create-info \
	--single-transaction \
	--ignore-table=awooga.issue \
	awooga \
	> test/browser/fixtures/data.sql

# Go back to original dir
cd $STARTDIR 

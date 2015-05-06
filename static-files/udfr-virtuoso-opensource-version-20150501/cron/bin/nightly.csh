#! /bin/csh -f

set base = /udfr/apps/virtuoso-opensource-version
set week = `date '+%Y%W'`
set target = backup/$week

set prefix = "virt-inc_dump_#"	# from admin guide
set size = 500 			# num of 8k pages/file

# this should already be created by the weekly cron which performs a full backup
if ( ! -e $target ) mkdir -p $target

set tmp = /tmp/virtuoso-daily.$$
echo "backup_online( '$prefix', $size, 0, vector('$target'));" >>  $tmp
# $base/bin/isql 1111 dba Br0_Z6Jp < $tmp
$base/bin/isql 1111 dba dba < $tmp

date
cat $tmp
exit 0

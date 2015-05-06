#! /bin/csh -f

set base = /udfr/apps/virtuoso-opensource-version
set week = `date '+%Y%W'`
set target = backup/$week

set prefix = "virt-inc_dump_#"	# from admin guide
set size = 500 			# num of 8k pages/file

cd $base
if ( ! -e $target ) mkdir -p $target

set tmp = /tmp/virtuoso-weekly.$$
echo "backup_context_clear();" >  $tmp
echo "checkpoint;" >> $tmp
echo "backup_online( '$prefix', $size, 0, vector('$target'));" >>  $tmp
# $base/bin/isql 1111 dba Br0_Z6Jp < $tmp
$base/bin/isql 1111 dba dba < $tmp

date
cat $tmp
exit 0

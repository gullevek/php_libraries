#!/bin/bash

# quick hack for import

#echo "EXIT";
#exit;

db='<db name>';
host='<db host>';
user='<db user>';
schemas="public dev";

file_prefix="trg";
trigger_prefix="trg";
index_prefix="idx";

for file in `cat ORDER`;
do
	if [ -f $file ];
	then
		# write them into a var, so we can re order them in the other way
		new_order=$file" "$new_order;
	fi;
done;

for file in $new_order;
do
	sqltype=`echo $file | egrep "table/"`;
	trgtype=`echo $file | egrep "trigger/"`;
	idxtype=`echo $file | egrep "index/"`;
	fcttype=`echo $file | egrep "function/"`;
	datatype=`echo $file | egrep "data/"`;
	# remove all around to get table name
	t_file=`echo $file | sed -e 's/^.*\///g' | sed -e 's/.sql$//g'`;
	for prefix in $file_prefix;
	do
		prefix=$prefix"_";
		t_file=`echo $t_file | sed -e "s/\$prefix//g"`;
	done;
	# copy the trigger template to the target

	for path in $schema;
	do
		if [ $sqltype ];
		then
			echo "SQL "$path"."$t_file;
			echo "DROP TABLE "$path"."$t_file" CASCADE;" | psql -U $user -h $host $db
		fi;
		if [ $trgtype ];
		then
			trigger=$trigger_prefix"_"$t_file;
			echo "TRG $trigger TBL "$path".$t_file";
			echo "DROP TRIGGER "$path".$trigger ON "$t_file" CASCADE;" | psql -U $user -h $host $db
		fi;
		if [ $fcttype ];
		then
			echo "FCT "$path"."$t_file;
			echo "DROP FUNCTION "$path"."$t_file"();" | psql -U $user -h $host $db
		fi;
		if [ $idxtype ];
		then
			index=$index_prefix"_"$t_file;
	#		echo "IDX "$t_file;
	#		echo "DROP INDEX $index ON $t_file;" | psql -U $user -h $host $db
		fi;
		if [ $datatype ];
		then
			echo "DATA "$t_file;
	#		echo "DROP FUNCTION "$t_file"();" | psql -U $user -h $host $db
		fi;

	#	psql -U cms_user -h 192.168.12.14 -f $file CMSv2
	done;
done;

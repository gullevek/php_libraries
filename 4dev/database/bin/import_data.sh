#!/bin/bash

# quick hack for import

#echo "EXIT";
#exit;

db='gullevek';
host='db.tokyo.tequila.jp';
user='gullevek';
#schema="publicv";

for file in `cat ORDER`;
do
	if [ -f $file ];
	then
#		for path in $schema;
#		do
#			echo "WORK ON "$schema"."$file;
			psql -U $user -h $host -f $file $db 1>> output 2>> error
#		done;
	fi;
done;

#!/bin/bash

# creates the default on update trigger for the inherited generic tables (date/name)

orig_file="../tmpl/trigger.tmpl"
trigger_path="trigger/";
sql_path="table/";
file_prefix="";
trigger_prefix="trg";

function_params="";
function_name="set_generic";

# remove slash from the name
#sql_path_prep=`echo $sql_path | sed -e "s/\///g"`;

# goes for each file and strips headers and endings, and creates trigger name
for name in $sql_path*;
do
	echo "Wokring on $name";
	# strip ending
#	t_name=`echo $name | sed -e 's/.sql$//g' | sed -e "s/^$sql_path_prep//g" | sed -e 's/\///g'`;
	t_name=`echo $name | sed -e 's/^.*\///g' | sed -e 's/.sql$//g'`;
	# clean all beginnings
	for prefix in $file_prefix;
	do
		prefix=$prefix"_";
		t_name=`echo $t_name | sed -e "s/\$prefix//g"`;
	done;

# those tables don't need a trigger
# edit_generic
# generic

	# copy the trigger template to the target
	trg_filename=$trigger_path$trigger_prefix"_"$t_name".sql";
	cp $orig_file $trg_filename;
	# replace all the data in the new file
	trigger_name=$trigger_prefix"_"$t_name;
	sed -i -e "s/##TRIGGERNAME##/$trigger_name/g" $trg_filename;
	sed -i -e "s/##TABLENAME##/$t_name/g" $trg_filename;
	sed -i -e "s/##FUNCTIONNAME##/$function_name/g" $trg_filename;
	sed -i -e "s/##PARAMETERS##/$function_params/g" $trg_filename;
	# finished
done;

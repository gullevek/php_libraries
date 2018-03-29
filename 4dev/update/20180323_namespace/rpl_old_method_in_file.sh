#!/bin/bash

cat tmp.comp | while read i;
do
	found=$(echo "${i}" | grep "function ");
	if [ -n "${found}" ]; then
		fk=$(echo "${i}" | cut -d " " -f 3 | cut -d "(" -f 1);
	fi;
	found=$(echo "${i}" | grep "\$this->");
	if [ -n "${found}" ]; then
		# no to debug
		found=$(echo "${i}" | grep "debug(");
		if [ -z "${found}" ]; then
			fk_n=$(echo "${i}" | cut -d "(" -f 1);
			echo "rpl '\$this->${fk}' '${fk_n}' CoreLibs/DB/IO.inc";
		fi;
	fi;
done;

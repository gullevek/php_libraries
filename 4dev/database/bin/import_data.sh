#!/bin/bash

# quick hack for import

#echo "EXIT";
#exit;

# if flagged 'y' then it will ask after each import to continue
development='n';
# do not import anything, just order flow output
test='n';
# write to file do not write to DB directly
write='y';
# database connection info
db='<db name>';
host='<db host>';
user='<db user>';
schemas='public';
export PGPASSWORD='';
# log files
error_file='log/error';
output_file='log/output';
data_file='database_create_data.sql';

if [ "$write" = 'y' ]; then
	rm -f "${data_file}";
fi;
if [ ! -f ORDER ]; then
	echo "Could not find ORDER file";
	exit;
fi;

if [ "$test" != "n" ]; then
	echo "TESTING MODE, NO DATA WILL BE IMPORTED";
fi;
if [ "$development" = "y" ]; then
	echo "STEP BY STEP IMPORT MODE ACTIVATED";
fi;

while read file <&3; do
	if [ "$file" = "FINISHED" ]; then
		echo "Database data is flagged as FINISHED in ORDER file";
		exit;
	fi;
	if [ -f "$file" ]; then
		for path in "$schemas"; do
			echo "[+] WORK ON '${file}' @ '${path}'";
			# skip all on test
			if [ "$test" = 'n' ]; then
				# write to file
				if [ "$write" = 'y' ]; then
					echo "-- START: ${file}" >> ${data_file};
					cat "${file}" >> ${data_file};
					echo "-- END: ${file}" >> ${data_file};
				else
					# write to DB
					echo "=== START [$file] ===>" >> ${error_file};
					psql -U ${user} -h ${host} -f "${file}" ${db} 1>> ${output_file} 2>> ${error_file}
					echo "=== END   [$file] ===>" >> ${error_file};
					# next wait for dev
					if [ "$development" = "y" ]; then
						echo "Press 'y' to move to next. Press 'r' to reload last file. ^c to abort";
					fi;
					# loop run for reload on failed
					while [ "$development" = "y" ] && [ "$input" != "y" ]; do
						read -ep "Continue (y|r|^c): " input;
						if [ "$input" = "r" ]; then
							echo "Reload File '${file}' ...";
							if [ "$test" = 'n' ]; then
								echo "=== START RELOAD [$file] ===>" >> ${error_file};
								psql -U ${user} -h ${host} -f "${file}" ${db} 1>> ${output_file} 2>> ${error_file}
								echo "=== END RELOAD   [$file] ===>" >> ${error_file};
							fi;
						fi;
					done;
					input='';
				fi;
			fi;
		done;
	elif [[ ${file::1} != "#" ]]; then
		echo "[!] COULD NOT FIND FILE: '${file}'";
	fi;
done 3<ORDER;

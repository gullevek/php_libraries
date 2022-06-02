#!/usr/bin/env bash

# note: there is currently no port selection, standard 5432 port is assumed
# note: we use the default in path postgresql commands and connect to whatever default DB is set

# PARAMETER 1: database data file to load
# PARAMETER 2: db user WHO MUST BE ABLE TO CREATE A DATABASE
# PARAMETER 3: db name
# PARAMETER 4: db host

load_sql="${1}";
# abort with 1 if we cannot find the file
if [ ! -f "${load_sql}" ]; then
	echo 1;
	exit 1;
fi;
db_user="${2}";
db_name="${3}";
db_host="${4}";
# empty db name or db user -> exit with 2
if [ -z "${db_user}" ] || [ -z "${db_name}" ] || [ -z "${db_host}" ]; then
	echo 2;
	exit 2;
fi;
# drop database, on error exit with 3
dropdb -U ${db_user} -h ${db_host} ${db_name} 2>&1;
if [ $? -ne 0 ]; then
	echo 3;
	exit 3;
fi;
# create database, on error exit with 4
createdb -U ${db_user} -O ${db_user} -h ${db_host} -E utf8 ${db_name} 2>&1;
if [ $? -ne 0 ]; then
	echo 4;
	exit 4;
fi;
# load data (redirect ALL error to null), on error exit with 5
psql -U ${db_user} -h ${db_host} -f ${load_sql} ${db_name} 2>&1 1>/dev/null 2>/dev/null;
if [ $? -ne 0 ]; then
	echo 5;
	exit 5;
fi;
echo 0;
exit 0;

# __END__

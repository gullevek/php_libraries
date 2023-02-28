#!/usr/bin/env bash

# if we don't have one base file we are in the wrong folder
if [ ! -f "locale/en_US/LC_MESSAGES/admin.mo" ]; then
	echo "Locale file is missing, wrong base folder?"
	echo "Should be: 4dev/tests/includes/"
	exit;
fi;

for file in $(ls -1 locale/*.po); do
	echo $file;
	file=$(basename $file .po);
	locale=$(echo "${file}" | cut -d "-" -f 1);
	domain=$(echo "${file}" | cut -d "-" -f 2);
	msgfmt -o locale/${locale}/LC_MESSAGES/${domain}.mo locale/${locale}-${domain}.po;
done;

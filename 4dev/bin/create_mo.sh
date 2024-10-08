#!/usr/bin/env bash

BASE_FOLDER=$(dirname "$(readlink -f "$0")")"/";
# Assume script is in 4dev/bin
base_folder="${BASE_FOLDER}../../www/";

# locale gettext po to mo translator master
for file in "${base_folder}"../4dev/locale/*.po; do
	[[ -e "$file" ]] || break
	file=$(basename "$file" .po);
	locale=$(echo "${file}" | cut -d "-" -f 1);
	domain=$(echo "${file}" | cut -d "-" -f 2);
	echo "- Translate language file '${file}' for locale '${locale}' and domain '${domain}':";
	if [ ! -d "${base_folder}/includes/locale/${locale}/LC_MESSAGES/" ]; then
		mkdir -p "${base_folder}/includes/locale/${locale}/LC_MESSAGES/";
	fi;
	msgfmt -o "${base_folder}/includes/locale/${locale}/LC_MESSAGES/${domain}.mo" "${base_folder}../4dev/locale/${locale}-${domain}.po";
done;

# __END__

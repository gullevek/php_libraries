#!/bin/bash

# read source mo files and writes target js files in object form

# check for ARG 1 is "mv"
# then move the files directly and don't do manual check (don't create temp files)
FILE_MOVE=0;
if [ "${1}" = "mv" ]; then
	echo "*** Direct write ***";
	FILE_MOVE=1;
fi;

target='';
BASE_FOLDER=$(dirname $(readlink -f $0))"/";
# Assume script is in 4dev/bin
base_folder="${BASE_FOLDER}../../www/";
po_folder='../4dev/locale/'
mo_folder='includes/locale/';
target_folder='';
template_file_stump='##SUFFIX##translate-##LANGUAGE##.TMP.js';
# for output file names
source_list=(iw);
language_list=(en ja);
# set target names
if [ "${target}" == '' ]; then
	echo "*** Non smarty ***";
	TEXTDOMAINDIR=${base_folder}${mo_folder}.
	# default is admin
	TEXTDOMAIN=admin;
fi;
js_folder="${TEXTDOMAIN}/layout/javascript/";

error=0;
# this checks if the TEXTDOMAIN target actually exists
if [ ! -d "${base_folder}${js_folder}" ]; then
	echo "Cannot find target javascript folder ${base_folder}${js_folder}";
	error=1;
else
	target_folder="${base_folder}${js_folder}";
fi;

if [ ${error} -eq 1 ]; then
	exit;
fi;

# locale gettext po to mo translator master
for file in $(ls -1 ${base_folder}../4dev/locale/*.po); do
	file=$(basename $file .po);
	echo "Translate language ${file}";
	locale=$(echo "${file}" | cut -d "-" -f 1);
	domain=$(echo "${file}" | cut -d "-" -f 2);
	if [ ! -d "${base_folder}/includes/locale/${locale}/LC_MESSAGES/" ]; then
		mkdir -p "${base_folder}/includes/locale/${locale}/LC_MESSAGES/";
	fi;
	msgfmt -o ${base_folder}/includes/locale/${locale}/LC_MESSAGES/${domain}.mo ${base_folder}${po_folder}${locale}-${domain}.po;
done;

rx_msgid_empty="^msgid \"\"";
rx_msgid="^msgid \"";
rx_msgstr="^msgstr \""

# quick copy string at the end
quick_copy='';

for language in ${language_list[*]}; do
	# I don't know which one must be set, but I think at least LANGUAGE
	case ${language} in
		ja)
			LANG=ja_JP.UTF-8;
			ENCODING=UTF-8;
			LANGUAGE=ja;
			;;
		en)
			# was en_JP.UTF-8
			LANG=en_US.UTF-8;
			ENCODING=UTF-8;
			LANGUAGE=en;
			;;
	esac;
	# write only one for language and then symlink files
	template_file=$(echo ${template_file_stump} | sed -e "s/##SUFFIX##//" | sed -e "s/##LANGUAGE##/${LANG}/");
	original_file=$(echo ${template_file} | sed -e 's/\.TMP//g');
	if [ "${FILE_MOVE}" -eq 0 ]; then
		file=${target_folder}${template_file};
	else
		file=${target_folder}${original_file};
	fi;
	echo "===> Write translation file ${file}";
	echo ". = normal, : = escape, x = skip";
	# init line [aka don't touch this file]
	echo "// AUTO FILL, changes will be overwritten" > $file;
	echo "// source: ${suffix}, language: ${language}" >> $file;
	echo "// Translation strings in the format" >> $file;
	echo "// \"Original\":\"Translated\""$'\n' >> $file;
	echo "var i18n = {" >> $file;
	# translations stuff
	# read the po file
	pos=0; # do we add a , for the next line
	cat "${base_folder}${po_folder}${language}-${TEXTDOMAIN}.po" |
	while read str; do
		# echo "S: ${str}";
		# skip empty
		if [[ "${str}" =~ ${rx_msgid_empty} ]]; then
			# skip on empty
			echo -n "x";
		# msgid is left, msgstr is right
		elif [[ "${str}" =~ ${rx_msgid} ]]; then
			echo -n ".";
			# open left side
			# TODO: how to handle multi line strings: or don't use them
			# extract from between ""
			str_source=$(echo "${str}" | sed -e "s/^msgid \"//" | sed -e "s/\"$//");
			# close right side, if not last add ,
			if [ "${pos}" -eq 1 ]; then
				echo -n "," >> $file;
			fi;
			# all " inside string need to be escaped
			str_source=$(echo "${str_source}" | sed -e 's/"/\\"/g');
			# fix with proper layout
			echo -n "\"$str_source\":\"$(TEXTDOMAINDIR=${TEXTDOMAINDIR} LANGUAGE=${language} LANG=${LANG} gettext ${TEXTDOMAIN} "${str_source}")\"" >> $file;
			pos=1;
		elif [[ "${str}" =~ ${rx_msgstr} ]]; then
			# open right side (ignore)
			echo -n "";
		else
			# general ignore (anything between or comments)
			echo -n "";
		fi;
	done;

	echo "" >> $file;
	echo "};" >> $file;
	echo " [DONE]";

	# on no move
	if [ "${FILE_MOVE}" -eq 0 ]; then
		echo "===> Confirm all changes in ${file} and then move data to original";
		echo "";
		quick_copy=${quick_copy}"mv ${template_file} ${original_file}"$'\n';
	fi;

	# symlink to master file
	for suffix in ${source_list[*]}; do
		# symlink with full lang name
		symlink_file[0]=$(echo ${template_file_stump} | sed -e "s/##SUFFIX##/${suffix}_/" | sed -e "s/##LANGUAGE##/${LANG}/" | sed -e 's/\.TMP//g');
		# create second one with lang (no country) + encoding
		symlink_file[1]=$(echo ${template_file_stump} | sed -e "s/##SUFFIX##/${suffix}_/" | sed -e "s/##LANGUAGE##/${LANGUAGE}\.${ENCODING}/" | sed -e 's/\.TMP//g');
		for template_file in ${symlink_file[@]}; do
		# if this is not symlink, create them
			if [ ! -h "${template_file}" ]; then
				echo "Create symlink: ${template_file}";
				# symlik to original
				cd "${target_folder}";
				ln -sf "${original_file}" "${template_file}";
				cd - >/dev/null;
			fi;
		done;
	done;
done;

if [ "${FILE_MOVE}" -eq 0 ]; then
	echo "";
	echo "-- IN FOLDER: ${target_folder}";
	echo "-- START: copy lines below to copy created over original --";
	echo "${quick_copy}";
	echo "-- END ----------------------------------------------------";
fi;

# __END__

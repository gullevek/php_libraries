#!/bin/env bash

base="/storage/var/www/html/developers/clemens/core_data/php_libraries/trunk/";
# -c phpunit.xml
# --testdox
# call with "t" to give verbose testdox output
# SUPPORTED: https://www.php.net/supported-versions.php
# call with php version number to force a certain php version

opt_testdox="";
if [ "${1}" = "t" ] || [ "${2}" = "t" ]; then
	opt_testdox="--testdox";
fi;
php_bin="";
if [ ! -z "${1}" ]; then
	case "${1}" in
		# "7.3") php_bin="/usr/bin/php7.3 "; ;;
		# "7.4") php_bin="/usr/bin/php7.4 "; ;;
		# "8.0") php_bin="/usr/bin/php8.0 "; ;;
		"8.1") php_bin="/usr/bin/php8.1 "; ;;
		"8.2") php_bin="/usr/bin/php8.2 "; ;;
		*) echo "Not support PHP: ${1}"; exit; ;;
	esac;
fi;
if [ ! -z "${2}" ] && [ -z "${php_bin}" ]; then
	case "${2}" in
		# "7.3") php_bin="/usr/bin/php7.3 "; ;;
		# "7.4") php_bin="/usr/bin/php7.4 "; ;;
		# "8.0") php_bin="/usr/bin/php8.0 "; ;;
		"8.1") php_bin="/usr/bin/php8.1 "; ;;
		"8.2") php_bin="/usr/bin/php8.2 "; ;;
		*) echo "Not support PHP: ${1}"; exit; ;;
	esac;
fi;

phpunit_call="${php_bin}${base}www/vendor/bin/phpunit ${opt_testdox} -c ${base}phpunit.xml ${base}4dev/tests/";

${phpunit_call};

if [ ! -z "${php_bin}" ]; then
	echo "CALLED WITH PHP: ${php_bin}"$(${php_bin} --version);
else
	echo "Default PHP used: "$(php --version);
fi;

# __END__

#!/bin/env bash

base="/storage/var/www/html/developers/clemens/core_data/php_libraries/trunk/";
# -c phpunit.xml
# --testdox
# call with "-tt" to give verbose testdox output
# SUPPORTED: https://www.php.net/supported-versions.php
# call with -p <php version number> to force a certain php version

opt_testdox="";
php_bin="";
while [ -n "${1-}" ]; do
	case "${1}" in
		-t | --testdox)
			opt_testdox="--testdox";
			;;
		-p | --php)
			php_bin="/usr/bin/php${2-}";
			shift
			;;
		# invalid option
		-?*)
			error "[!] Unknown option: '$1'."
			;;
	esac
	shift;
done;

if [ ! -f "${php_bin}" ]; then
	echo "Set php ${php_bin} does not exist";
	exit;
fi;
php_bin="${php_bin} ";

# Note 4dev/tests/bootstrap.php has to be set as bootstrap file in phpunit.xml
phpunit_call="${php_bin}${base}vendor/bin/phpunit ${opt_testdox} -c ${base}phpunit.xml ${base}4dev/tests/";

${phpunit_call};

if [ -n "${php_bin}" ]; then
	echo "CALLED WITH PHP: ${php_bin}$(${php_bin} --version)";
else
	echo "Default PHP used: $(php --version)";
fi;

# __END__

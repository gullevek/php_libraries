base="/storage/var/www/html/developers/clemens/core_data/php_libraries/trunk/";
# -c phpunit.xml
# --testdox
# call with "t" to give verbose testdox output
# call with 7.3, 7.4, 8.0, 8.1 to force a certain php version

opt_testdox="";
if [ "${1}" = "t" ] || [ "${2}" = "t" ]; then
	opt_testdox="--testdox";
fi;
php_bin="";
case "${1}" in
	"7.3") php_bin="/usr/bin/php7.3 "; ;;
	"7.4") php_bin="/usr/bin/php7.4 "; ;;
	"8.0") php_bin="/usr/bin/php8.0 "; ;;
	"8.1") php_bin="/usr/bin/php8.1 "; ;;
esac;
if [ -z "${php_bin}" ]; then
	case "${2}" in
		"7.3") php_bin="/usr/bin/php7.3 "; ;;
		"7.4") php_bin="/usr/bin/php7.4 "; ;;
		"8.0") php_bin="/usr/bin/php8.0 "; ;;
		"8.1") php_bin="/usr/bin/php8.1 "; ;;
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

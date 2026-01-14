#!/bin/env bash

function error() {
	if [ -t 1 ]; then echo "[MAK] ERROR: $*" >&2; fi; exit 0;
}

usage() {
	cat <<EOF
Usage: $(basename "${BASH_SOURCE[0]}") [-h] [-h | --help] [-p | --php VERSION] [-c | --composer]

Runs phan static analyzer.

If -p is not set, the default intalled PHP is used.

Available options:

-h, --help        Print this help and exit
-p, --php VERSION Chose PHP version in the form of "N.N", if not found will exit
-c, --composer    Use composer version and not the default phives bundle
EOF
	exit
}

BASE_PATH=$(pwd)"/";
PHP_BIN_PATH=$(which php);
if [ -z "${PHP_BIN_PATH}" ]; then
	echo "Cannot find php binary";
	exit;
fi;
DEFAULT_PHP_VERSION=$(${PHP_BIN_PATH} -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;");
if [ -z "${DEFAULT_PHP_VERSION}" ]; then
	echo "Cannot set default PHP version";
	exit;
fi;
php_version="";
no_php_version=0;
use_composer=0;
while [ -n "${1-}" ]; do
	case "${1}" in
		-p | --php)
			php_version="${2-}";
			shift
			;;
		-c | --composer)
			use_composer=1;
			shift
			;;
		-h | --help)
			usage
			;;
		# invalid option
		-?*)
			error "[!] Unknown option: '$1'."
			;;
	esac
	shift;
done;
if [ -z "${php_version}" ]; then
	php_version="${DEFAULT_PHP_VERSION}";
	no_php_version=1;
fi;
php_bin="${PHP_BIN_PATH}${php_version}";
echo "Use PHP Version: ${php_version}";
if [ "${use_composer}" -eq 1 ]; then
	echo "Use composer installed phan";
else
	echo "Use phan installed via phives";
fi;

if [ ! -f "${php_bin}" ]; then
	echo "Set php ${php_bin} does not exist";
	exit;
fi;

BASE_PATH=$(pwd)"/";
cd "$BASE_PATH" || exit;
PHPSTAN_CALL=(
	"${php_bin}"
);
if [ "${use_composer}" -eq 1 ]; then
	PHPSTAN_CALL+=("${BASE_PATH}vendor/bin/phpstan");
else
	PHPSTAN_CALL+=("${BASE_PATH}tools/phpstan");
fi;
"${PHPSTAN_CALL[@]}";
if [ "${no_php_version}" -eq 0 ]; then
	echo "*** CALLED WITH PHP ${php_bin} ***";
	${php_bin} --version;
else
	echo "Default PHP used: $(php --version)";
fi;
cd ~ || exit;

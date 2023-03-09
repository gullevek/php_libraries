<?php // phpcs:ignore PSR1.Files.SideEffects

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2003/06/10
* SHORT DESCRIPTION:
* configuration file
* HISTORY:
*********************************************************************/

declare(strict_types=1);

/************* PATHS *********************/
// [DEPRECATED] directory seperator
define('DS', DIRECTORY_SEPARATOR);
// ** NEW/BETTER DIR DECLARATIONS **
// path to original file (if symlink)
define('DIR', __DIR__ . DIRECTORY_SEPARATOR);
// base dir root folder level
define('BASE', str_replace('/configs', '', __DIR__) . DIRECTORY_SEPARATOR);

// ** OLD DIR DECLARATIONS **
// path to document root of file called
define('ROOT', getcwd() . DIRECTORY_SEPARATOR);
// libs path
define('LIB', 'lib' . DIRECTORY_SEPARATOR);
define('LIBS', 'lib' . DIRECTORY_SEPARATOR);
// configs folder
define('CONFIGS', 'configs' . DIRECTORY_SEPARATOR);
// includes (strings, arrays for static, etc)
define('INCLUDES', 'includes' . DIRECTORY_SEPARATOR);
// data folder (mostly in includes, or root for internal data)
define('DATA', 'data' . DIRECTORY_SEPARATOR);
// layout base path
define('LAYOUT', 'layout' . DIRECTORY_SEPARATOR);
// pic-root (compatible to CMS)
define('PICTURES', 'images' . DIRECTORY_SEPARATOR);
// images
define('IMAGES', 'images' . DIRECTORY_SEPARATOR);
// icons (below the images/ folder)
define('ICONS', 'icons' . DIRECTORY_SEPARATOR);
// media (accessable from outside)
define('MEDIA', 'media' . DIRECTORY_SEPARATOR);
// uploads (anything to keep or data)
define('UPLOADS', 'uploads' . DIRECTORY_SEPARATOR);
// files (binaries) (below media or data)
define('BINARIES', 'binaries' . DIRECTORY_SEPARATOR);
// files (videos) (below media or data)
define('VIDEOS', 'videos' . DIRECTORY_SEPARATOR);
// files (documents) (below media or data)
define('DOCUMENTS', 'documents' . DIRECTORY_SEPARATOR);
// files (pdfs) (below media or data)
define('PDFS', 'documents' . DIRECTORY_SEPARATOR);
// files (general) (below media or data)
define('FILES', 'files' . DIRECTORY_SEPARATOR);
// CSV
define('CSV', 'csv' . DIRECTORY_SEPARATOR);
// css
define('CSS', 'css' . DIRECTORY_SEPARATOR);
// font (web)
define('FONT', 'font' . DIRECTORY_SEPARATOR);
// js
define('JS', 'javascript' . DIRECTORY_SEPARATOR);
// table arrays
define('TABLE_ARRAYS', 'table_arrays' . DIRECTORY_SEPARATOR);
// smarty libs path
define('SMARTY', 'Smarty' . DIRECTORY_SEPARATOR);
// po locale file
define('LOCALE', 'locale' . DIRECTORY_SEPARATOR);
// cache path
define('CACHE', 'cache' . DIRECTORY_SEPARATOR);
// temp path
define('TMP', 'tmp' . DIRECTORY_SEPARATOR);
// log files
define('LOG', 'log' . DIRECTORY_SEPARATOR);
// compiled template folder
define('TEMPLATES_C', 'templates_c' . DIRECTORY_SEPARATOR);
// template base
define('TEMPLATES', 'templates' . DIRECTORY_SEPARATOR);

/************* HASH / ACL DEFAULT / ERROR SETTINGS / SMARTY *************/
// default hash type
define('DEFAULT_HASH', 'sha256');
// default acl level
define('DEFAULT_ACL_LEVEL', 80);
// SSL host name
// define('SSL_HOST', $_ENV['SSL_HOST'] ?? '');
// error page strictness, Default is 3
// 1: only show error page as the last mesure if really no mid & aid can be loaded and found at all
// 2: if template not found, do not search, show error template
// 3: if default template is not found, show error template, do not fall back to default tree
// 4: very strict, even on normal fixable errors through error
// define('ERROR_STRICT', 3);
// allow page caching in general, set to 'false' if you do debugging or development!
// define('ALLOW_SMARTY_CACHE', false);
// cache life time, in second', default here is 2 days (172800s)
// -1 is never expire cache
// define('SMARTY_CACHE_LIFETIME', -1);

/************* LOGOUT ********************/
// logout target
define('LOGOUT_TARGET', '');

/************* AJAX / ACCESS *************/
// ajax request type
define('AJAX_REQUEST_TYPE', 'POST');
// what AJAX type to use
define('USE_PROTOTYPE', false);
define('USE_SCRIPTACULOUS', false);
define('USE_JQUERY', true);

/************* LAYOUT WIDTHS *************/
define('PAGE_WIDTH', '100%');
define('CONTENT_WIDTH', '100%');
// the default template name
define('MASTER_TEMPLATE_NAME', 'main_body.tpl');

/************* OVERALL CONTROL NAMES *************/
// BELOW has HAS to be changed
// base name for all session and log names
// only alphanumeric characters, strip all others
define('BASE_NAME', preg_replace('/[^A-Za-z0-9]/', '', $_ENV['BASE_NAME'] ?? ''));

/************* SESSION NAMES *************/
// server name HASH
define('SERVER_NAME_HASH', hash('crc32b', $_SERVER['HTTP_HOST']));
define('SERVER_PATH_HASH', hash('crc32b', BASE));
// backend
define('EDIT_SESSION_NAME', BASE_NAME . 'Admin' . SERVER_NAME_HASH . SERVER_PATH_HASH);
// frontend
define('SESSION_NAME', BASE_NAME . SERVER_NAME_HASH . SERVER_PATH_HASH);

/************* CACHE/COMPILE IDS *************/
define('CACHE_ID', 'CACHE_' . BASE_NAME . '_' . SERVER_NAME_HASH);
define('COMPILE_ID', 'COMPILE_' . BASE_NAME . '_' . SERVER_NAME_HASH);

/************* LANGUAGE / ENCODING *******/
// default lang + encoding
define('DEFAULT_LOCALE', 'en_US.UTF-8');
// default web page encoding setting
define('DEFAULT_ENCODING', 'UTF-8');

/************* LOGGING *******************/
// below two can be defined here, but they should be
// defined in either the header file or the file itself
// as $LOG_FILE_ID which takes presence over LOG_FILE_ID
// see Basic class constructor
define('LOG_FILE_ID', BASE_NAME);

/************* QUEUE TABLE *************/
// if we have a dev/live system
// set_live is a per page/per item
// live_queue is a global queue system
// define('QUEUE', 'live_queue');

/************* DB PATHS (PostgreSQL) *****************/
// schema names, can also be defined per <DB INFO>
define('PUBLIC_SCHEMA', 'public');
define('DEV_SCHEMA', 'public');
define('TEST_SCHEMA', 'public');
define('LIVE_SCHEMA', 'public');
define('GLOBAL_DB_SCHEMA', '');
define('LOGIN_DB_SCHEMA', '');

/************* CORE HOST SETTINGS *****************/
if (file_exists(BASE . CONFIGS . 'config.host.php')) {
	require BASE . CONFIGS . 'config.host.php';
}
if (!isset($SITE_CONFIG)) {
	$SITE_CONFIG = [];
}
/************* DB ACCESS *****************/
if (file_exists(BASE . CONFIGS . 'config.db.php')) {
	require BASE . CONFIGS . 'config.db.php';
}
if (!isset($DB_CONFIG)) {
	$DB_CONFIG = [];
}
/************* OTHER PATHS *****************/
if (file_exists(BASE . CONFIGS . 'config.path.php')) {
	require BASE . CONFIGS . 'config.path.php';
}

/************* MASTER INIT *****************/
// live frontend pages
// ** missing live domains **
// get the name without the port
list($HOST_NAME) = array_pad(explode(':', $_SERVER['HTTP_HOST'], 2), 2, null);
// set HOST name
define('HOST_NAME', $HOST_NAME);
// BAIL ON MISSING MASTER SITE CONFIG
if (!isset($SITE_CONFIG[HOST_NAME]['location'])) {
	echo 'Missing SITE_CONFIG entry for: "' . HOST_NAME . '". Contact Administrator';
	exit;
}
// BAIL ON MISSING DB CONFIG:
// we have either no db selction for this host but have db config entries
// or we have a db selection but no db config as array or empty
// or we have a selection but no matching db config entry
if (
	(!isset($SITE_CONFIG[HOST_NAME]['db_host']) && count($DB_CONFIG)) ||
	(isset($SITE_CONFIG[HOST_NAME]['db_host']) &&
		// missing DB CONFIG
		((is_array($DB_CONFIG) && !count($DB_CONFIG)) ||
		!is_array($DB_CONFIG) ||
		// has DB CONFIG but no match
		empty($DB_CONFIG[$SITE_CONFIG[HOST_NAME]['db_host']]))
	)
) {
	echo 'No matching DB config found for: "' . HOST_NAME . '". Contact Administrator';
	exit;
}
// set SSL on
$is_secure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	$is_secure = true;
} elseif (
	!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ||
	!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'
) {
	$is_secure = true;
}
if ($is_secure) {
	define('HOST_SSL', true);
	define('HOST_PROTOCOL', 'https://');
} else {
	define('HOST_SSL', false);
	define('HOST_PROTOCOL', 'http://');
}
// define the db config set name, the db config and the db schema
define('DB_CONFIG_NAME', $SITE_CONFIG[HOST_NAME]['db_host'] ?? '');
define('DB_CONFIG', $DB_CONFIG[DB_CONFIG_NAME] ?? []);
// because we can't change constant, but we want to for db debug flag
$GLOBALS['DB_CONFIG_SET'] = DB_CONFIG;
// define('DB_CONFIG_TARGET', SITE_CONFIG[$HOST_NAME]['db_host_target']);
// define('DB_CONFIG_OTHER', SITE_CONFIG[$HOST_NAME]['db_host_other']);
// override for login and global schemas
// where the edit* tables are
// define('LOGIN_DB_SCHEMA', PUBLIC_SCHEMA);
// where global tables are that are used by all schemas (eg queue tables for online, etc)
// define('GLOBAL_DB_SCHEMA', PUBLIC_SCHEMA);
// debug settings, site lang, etc
define('TARGET', $SITE_CONFIG[HOST_NAME]['location'] ?? 'test');
define('DEBUG', $SITE_CONFIG[HOST_NAME]['debug_flag'] ?? false);
define('SITE_LOCALE', $SITE_CONFIG[HOST_NAME]['site_locale'] ?? DEFAULT_LOCALE);
define('SITE_DOMAIN', str_replace(DIRECTORY_SEPARATOR, '', CONTENT_PATH));
define('SITE_ENCODING', $SITE_CONFIG[HOST_NAME]['site_encoding'] ?? DEFAULT_ENCODING);
define('LOGIN_ENABLED', $SITE_CONFIG[HOST_NAME]['login_enabled'] ?? false);
define('AUTH', $SITE_CONFIG[HOST_NAME]['auth'] ?? false);
// paths
// define('CSV_PATH', $PATHS[TARGET]['csv_path'] ?? '');
// define('EXPORT_SCRIPT', $PATHS[TARGET]['perl_bin'] ?? '');
// define('REDIRECT_URL', $PATHS[TARGET]['redirect_url'] ?? '');

// show all errors if debug_all & show_error_handling are enabled
define('SHOW_ALL_ERRORS', true);

/************* GENERAL PAGE TITLE ********/
define('G_TITLE', $_ENV['G_TITLE'] ?? '');

/************ STYLE SHEETS / JS **********/
define('ADMIN_STYLESHEET', 'edit.css');
define('ADMIN_JAVASCRIPT', 'edit.js');
define('STYLESHEET', $_ENV['STYLESHEET'] ?? 'frontend.css');
define('JAVASCRIPT', $_ENV['JAVASCRIPT'] ?? 'frontend.js');

// anything optional
/************* INTERNAL ******************/
// any other global definitons in the config.other.php
if (file_exists(BASE . CONFIGS . 'config.other.php')) {
	require BASE . CONFIGS . 'config.other.php';
}

/************* DEBUG *******************/
// turn off debug if debug flag is OFF
if (defined('DEBUG') && DEBUG == false) {
	$ECHO_ALL = false;
	$DEBUG_ALL = false;
	$PRINT_ALL = false;
	$DB_DEBUG = false;
	$ENABLE_ERROR_HANDLING = false;
	$DEBUG_ALL_OVERRIDE = false;
} else {
	$ECHO_ALL = false;
	$DEBUG_ALL = true;
	$PRINT_ALL = true;
	$DB_DEBUG = true;
	$ENABLE_ERROR_HANDLING = false;
	$DEBUG_ALL_OVERRIDE = false;
}

// __END__

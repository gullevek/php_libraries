<?php declare(strict_types=1);
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2003/06/10
* SHORT DESCRIPTION:
* configuration file
* HISTORY:
*********************************************************************/

/************* PATHS *********************/
// directory seperator
DEFINE('DS', DIRECTORY_SEPARATOR);
// ** NEW/BETTER DIR DECLARATIONS **
// path to original file (if symlink)
DEFINE('DIR', __DIR__.DS);
// base dir root folder level
DEFINE('BASE', str_replace('/configs', '', __DIR__).DS);

// ** OLD DIR DECLARATIONS **
// path to document root of file called
DEFINE('ROOT', getcwd().DS);
// libs path
DEFINE('LIB', 'lib'.DS);
DEFINE('LIBS', 'lib'.DS);
// configs folder
DEFINE('CONFIGS', 'configs'.DS);
// includes (strings, arrays for static, etc)
DEFINE('INCLUDES', 'includes'.DS);
// data folder (mostly in includes)
DEFINE('DATA', 'data'.DS);
// layout base path
DEFINE('LAYOUT', 'layout'.DS);
// pic-root (compatible to CMS)
DEFINE('PICTURES', 'images'.DS);
// images
DEFINE('IMAGES', 'images'.DS);
// icons (below the images/ folder)
DEFINE('ICONS', 'icons'.DS);
// media
DEFINE('MEDIA', 'media'.DS);
// flash-root (below media)
DEFINE('FLASH', 'flash'.DS);
// uploads (anything to keep)
DEFINE('UPLOADS', 'uploads'.DS);
// files (binaries) (below media)
DEFINE('BINARIES', 'binaries'.DS);
// files (videos) (below media)
DEFINE('VIDEOS', 'videos'.DS);
// files (documents) (below media)
DEFINE('DOCUMENTS', 'documents'.DS);
// files (pdfs) (below media)
DEFINE('PDFS', 'documents'.DS);
// CSV
DEFINE('CSV', 'csv'.DS);
// css
DEFINE('CSS', 'css'.DS);
// js
DEFINE('JS', 'javascript'.DS);
// table arrays
DEFINE('TABLE_ARRAYS', 'table_arrays'.DS);
// smarty libs path
DEFINE('SMARTY', 'Smarty'.DS);
// po langs
DEFINE('LANG', 'lang'.DS);
// cache path
DEFINE('CACHE', 'cache'.DS);
// temp path
DEFINE('TMP', 'tmp'.DS);
// log files
DEFINE('LOG', 'log'.DS);
// compiled template folder
DEFINE('TEMPLATES_C', 'templates_c'.DS);
// template base
DEFINE('TEMPLATES', 'templates'.DS);

/************* HASH / ACL DEFAULT / ERROR SETTINGS / SMARTY *************/
// default hash type
DEFINE('DEFAULT_HASH', 'sha256');
// default acl level
DEFINE('DEFAULT_ACL_LEVEL', 80);
// SSL host name
// DEFINE('SSL_HOST', 'ssl.host.name');
// error page strictness, Default is 3
// 1: only show error page as the last mesure if really no mid & aid can be loaded and found at all
// 2: if template not found, do not search, show error template
// 3: if default template is not found, show error template, do not fall back to default tree
// 4: very strict, even on normal fixable errors through error
// DEFINE('ERROR_STRICT', 3);
// allow page caching in general, set to 'FALSE' if you do debugging or development!
// DEFINE('ALLOW_SMARTY_CACHE', FALSE);
// cache life time, in second', default here is 2 days (172800s)
// -1 is never expire cache
// DEFINE('SMARTY_CACHE_LIFETIME', -1);

/************* LOGOUT ********************/
// logout target
DEFINE('LOGOUT_TARGET', '');
// password change allowed
DEFINE('PASSWORD_CHANGE', false);
DEFINE('PASSWORD_FORGOT', false);
// min/max password length
DEFINE('PASSWORD_MIN_LENGTH', 8);
DEFINE('PASSWORD_MAX_LENGTH', 255);

/************* AJAX / ACCESS *************/
// ajax request type
DEFINE('AJAX_REQUEST_TYPE', 'POST');
// what AJAX type to use
DEFINE('USE_PROTOTYPE', false);
DEFINE('USE_SCRIPTACULOUS', false);
DEFINE('USE_JQUERY', true);

/************* LAYOUT WIDTHS *************/
DEFINE('PAGE_WIDTH', 800);
// the default template name
DEFINE('MASTER_TEMPLATE_NAME', 'main_body.tpl');

/************* OVERALL CONTROL NAMES *************/
// BELOW has HAS to be changed
// base name for all session and log names
DEFINE('BASE_NAME', 'CoreLibs');

/************* SESSION NAMES *************/
// server name HASH
DEFINE('SERVER_NAME_HASH', hash('crc32b', $_SERVER['HTTP_HOST']));
DEFINE('SERVER_PATH_HASH', hash('crc32b', BASE));
// backend
DEFINE('EDIT_SESSION_NAME', BASE_NAME.'Admin'.SERVER_NAME_HASH.SERVER_PATH_HASH);
// frontend
DEFINE('SESSION_NAME', BASE_NAME.SERVER_NAME_HASH.SERVER_PATH_HASH);
// SET_SESSION_NAME should be set in the header if a special session name is needed
DEFINE('SET_SESSION_NAME', SESSION_NAME);

/************* CACHE/COMPILE IDS *************/
DEFINE('CACHE_ID', 'CACHE_'.BASE_NAME.'_'.SERVER_NAME_HASH);
DEFINE('COMPILE_ID', 'COMPILE_'.BASE_NAME.'_'.SERVER_NAME_HASH);

/************* LANGUAGE / ENCODING *******/
DEFINE('DEFAULT_LANG', 'en_utf8');
// default web page encoding setting
DEFINE('DEFAULT_ENCODING', 'UTF-8');

/************* LOGGING *******************/
// below two can be defined here, but they should be
// defined in either the header file or the file itself
// as $LOG_FILE_ID which takes presence over LOG_FILE_ID
// see Basic class constructor
DEFINE('LOG_FILE_ID', BASE_NAME);

/************* CLASS ERRORS *******************/
// 0 = default all OFF
// 1 = throw notice on unset class var
// 2 = no notice on unset class var, but do not set undefined class var
// 3 = throw error and do not set class var
define('CLASS_VARIABLE_ERROR_MODE', 3);

/************* QUEUE TABLE *************/
// if we have a dev/live system
// set_live is a per page/per item
// live_queue is a global queue system
// DEFINE('QUEUE', 'live_queue');

/************* DB PATHS (PostgreSQL) *****************/
// schema names, can also be defined per <DB INFO>
DEFINE('PUBLIC_SCHEMA', 'public');
DEFINE('DEV_SCHEMA', 'public');
DEFINE('TEST_SCHEMA', 'public');
DEFINE('LIVE_SCHEMA', 'public');

/************* CORE HOST SETTINGS *****************/
if (file_exists(BASE.CONFIGS.'config.host.php')) {
	require BASE.CONFIGS.'config.host.php';
}
if (!isset($SITE_CONFIG)) {
	$SITE_CONFIG = array();
}
/************* DB ACCESS *****************/
if (file_exists(BASE.CONFIGS.'config.db.php')) {
	require BASE.CONFIGS.'config.db.php';
}
if (!isset($DB_CONFIG)) {
	$DB_CONFIG = array();
}
/************* OTHER PATHS *****************/
if (file_exists(BASE.CONFIGS.'config.path.php')) {
	require BASE.CONFIGS.'config.path.php';
}

/************* MASTER INIT *****************/
// live frontend pages
// ** missing live domains **
// get the name without the port
list($HOST_NAME) = array_pad(explode(':', $_SERVER['HTTP_HOST'], 2), 2, null);
// set HOST name
DEFINE('HOST_NAME', $HOST_NAME);
// BAIL ON MISSING MASTER SITE CONFIG
if (!isset($SITE_CONFIG[HOST_NAME]['location'])) {
	echo 'Missing SITE_CONFIG entry for: "'.HOST_NAME.'". Contact Administrator';
	exit;
}
// BAIL ON MISSING DB CONFIG:
// we have either no db selction for this host but have db config entries
// or we have a db selection but no db config as array or empty
// or we have a selection but no matching db config entry
if ((!isset($SITE_CONFIG[HOST_NAME]['db_host']) && count($DB_CONFIG)) ||
	(isset($SITE_CONFIG[HOST_NAME]['db_host']) &&
		// missing DB CONFIG
		((is_array($DB_CONFIG) && !count($DB_CONFIG)) ||
		!is_array($DB_CONFIG) ||
		// has DB CONFIG but no match
		(is_array($DB_CONFIG) && count($DB_CONFIG) && !isset($DB_CONFIG[$SITE_CONFIG[HOST_NAME]['db_host']])))
	)
) {
	echo 'No matching DB config found for: "'.HOST_NAME.'". Contact Administrator';
	exit;
}
// set SSL on
if ((array_key_exists('HTTPS', $_SERVER) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
	$_SERVER['SERVER_PORT'] == 443) {
	DEFINE('HOST_SSL', true);
	DEFINE('HOST_PROTOCOL', 'https://');
} else {
	DEFINE('HOST_SSL', false);
	DEFINE('HOST_PROTOCOL', 'http://');
}
// define the db config set name, the db config and the db schema
DEFINE('DB_CONFIG_NAME', $SITE_CONFIG[HOST_NAME]['db_host']);
DEFINE('DB_CONFIG', isset($DB_CONFIG[DB_CONFIG_NAME]) ? $DB_CONFIG[DB_CONFIG_NAME] : array());
// DEFINE('DB_CONFIG_TARGET', SITE_CONFIG[$HOST_NAME]['db_host_target']);
// DEFINE('DB_CONFIG_OTHER', SITE_CONFIG[$HOST_NAME]['db_host_other']);
// override for login and global schemas
// DEFINE('LOGIN_DB_SCHEMA', PUBLIC_SCHEMA); // where the edit* tables are
// DEFINE('GLOBAL_DB_SCHEMA', PUBLIC_SCHEMA); // where global tables are that are used by all schemas (eg queue tables for online, etc)
// debug settings, site lang, etc
DEFINE('TARGET', $SITE_CONFIG[HOST_NAME]['location']);
DEFINE('DEBUG', $SITE_CONFIG[HOST_NAME]['debug_flag']);
DEFINE('SITE_LANG', $SITE_CONFIG[HOST_NAME]['site_lang']);
DEFINE('LOGIN_ENABLED', $SITE_CONFIG[HOST_NAME]['login_enabled']);
// paths
// DEFINE('CSV_PATH', $PATHS[TARGET]['csv_path']);
// DEFINE('EXPORT_SCRIPT', $PATHS[TARGET]['perl_bin']);
// DEFINE('REDIRECT_URL', $PATHS[TARGET]['redirect_url']);

// show all errors if debug_all & show_error_handling are enabled
DEFINE('SHOW_ALL_ERRORS', true);

/************* GENERAL PAGE TITLE ********/
DEFINE('G_TITLE', '<OVERALL FALLBACK PAGE TITLE>');

/************ STYLE SHEETS / JS **********/
DEFINE('ADMIN_STYLESHEET', 'edit.css');
DEFINE('ADMIN_JAVASCRIPT', 'edit.js');
DEFINE('STYLESHEET', 'frontend.css');
DEFINE('JAVASCRIPT', 'frontend.js');

// anything optional
/************* INTERNAL ******************/
// any other global definitons in the config.other.php
if (file_exists(BASE.CONFIGS.'config.other.php')) {
	require BASE.CONFIGS.'config.other.php';
}

/************* CONVERT *******************/
// this only needed if the external thumbnail create is used
$paths = array(
	'/bin',
	'/usr/bin',
	'/usr/local/bin'
);
// find convert
foreach ($paths as $path) {
	if (file_exists($path.DS.'convert') && is_file($path.DS.'convert')) {
		// image magick convert location
		DEFINE('CONVERT', $path.DS.'convert');
	}
}
unset($paths);

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

/************* AUTO LOADER *******************/
// read auto loader
require BASE.LIB.'autoloader.php';

// __END__

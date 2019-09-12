<?php
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
// default levels for certain actions
/*	DEFINE('DEFAULT_ACL_READ', 20);
DEFINE('DEFAULT_ACL_CONFIRM', 35);
DEFINE('DEFAULT_ACL_MOD', 40);
DEFINE('DEFAULT_ACL_WRITE', 60);
DEFINE('DEFAULT_ACL_SEND', 70);
DEFINE('DEFAULT_ACL_DEL', 80);
DEFINE('DEFAULT_ACL_ADMIN', 100); */
// SSL host name
//	DEFINE('SSL_HOST', 'ssl.host.name');
// error page strictness, Default is 3
// 1: only show error page as the last mesure if really no mid & aid can be loaded and found at all
// 2: if template not found, do not search, show error template
// 3: if default template is not found, show error template, do not fall back to default tree
// 4: very strict, even on normal fixable errors through error
//	DEFINE('ERROR_STRICT', 3);
// allow page caching in general, set to 'FALSE' if you do debugging or development!
//	DEFINE('ALLOW_SMARTY_CACHE', FALSE);
// cache life time, in second', default here is 2 days (172800s)
// -1 is never expire cache
//	DEFINE('SMARTY_CACHE_LIFETIME', -1);

/************* LOGOUT ********************/
// logout target
DEFINE('LOGOUT_TARGET', '');
// password change allowed
DEFINE('PASSWORD_CHANGE', false);
// min/max password length
DEFINE('PASSWORD_MIN_LENGTH', 8);
DEFINE('PASSWORD_MAX_LENGTH', 255);

/************* AJAX / ACCESS *************/
// ajax request type
DEFINE('AJAX_REQUEST_TYPE', 'POST');
// what AJAX type to use
DEFINE('USE_PROTOTYPE', true);
DEFINE('USE_SCRIPTACULOUS', false);
DEFINE('USE_JQUERY', false);

/************* LAYOUT WIDTHS *************/
DEFINE('PAGE_WIDTH', 800);
// the default template name
DEFINE('MASTER_TEMPLATE_NAME', 'main_body.tpl');

/************* SESSION NAMES *************/
// server name HASH
DEFINE('SERVER_NAME_HASH', hash('crc32b', $_SERVER['HTTP_HOST']));
// backend
DEFINE('EDIT_SESSION_NAME', 'ADMIN_SESSION_NAME'.SERVER_NAME_HASH);
// frontend
DEFINE('SESSION_NAME', 'SESSION_NAME'.SERVER_NAME_HASH);
// SET_SESSION_NAME should be set in the header if a special session name is needed
// DEFINE('SET_SESSION_NAME', SESSION_NAME);

/************* CACHE/COMPILE IDS *************/
DEFINE('CACHE_ID', 'CACHE_'.SERVER_NAME_HASH);
DEFINE('COMPILE_ID', 'COMPILE_'.SERVER_NAME_HASH);

/************* LANGUAGE / ENCODING *******/
DEFINE('DEFAULT_LANG', 'en_utf8');
// default web page encoding setting
DEFINE('DEFAULT_ENCODING', 'UTF-8');

// below two can be defined here, but they should be
// defined in either the header file or the file itself
/************* LOGGING *******************/
// DEFINE('LOG_FILE_ID', '');

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
//	DEFINE('QUEUE', 'live_queue');

/************* DB PATHS (PostgreSQL) *****************/
// schema names, can also be defined per <DB INFO>
DEFINE('PUBLIC_SCHEMA', 'public');
DEFINE('DEV_SCHEMA', 'public');
DEFINE('TEST_SCHEMA', 'public');
DEFINE('LIVE_SCHEMA', 'public');

/************* DB ACCESS *****************/
if (file_exists(BASE.CONFIGS.'config.db.inc')) {
	require BASE.CONFIGS.'config.db.inc';
}
/************* CORE HOST SETTINGS *****************/
if (file_exists(BASE.CONFIGS.'config.host.inc')) {
	require BASE.CONFIGS.'config.host.inc';
}
/************* OTHER PATHS *****************/
if (file_exists(BASE.CONFIGS.'config.path.inc')) {
	require BASE.CONFIGS.'config.path.inc';
}

// set the USE_DATABASE var, if there is nothing set, we assume TRUE
$USE_DATABASE = defined('USE_DATABASE') ? USE_DATABASE : true;

// live frontend pages
// ** missing live domains **
// get the name without the port
list($HOST_NAME) = array_pad(explode(':', $_SERVER['HTTP_HOST'], 2), 2, null);
if (!isset($DB_HOST[$HOST_NAME]) && $USE_DATABASE) {
	echo 'No matching DB config found. Contact Admin<br>';
	exit -1;
}
// set HOST name
DEFINE('HOST_NAME', $HOST_NAME);
// set SSL on
if ((array_key_exists('HTTPS', $_SERVER) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
	$_SERVER['SERVER_PORT'] == 443) {
	DEFINE('HOST_SSL', true);
	DEFINE('HOST_PROTOCOL', 'https://');
} else {
	DEFINE('HOST_SSL', false);
	DEFINE('HOST_PROTOCOL', 'http://');
}
// define the static names
DEFINE('LOGIN_DB', $DB_HOST[$HOST_NAME]);
DEFINE('MAIN_DB', $DB_HOST[$HOST_NAME]);
DEFINE('DB_SCHEMA', $DB_PATH[$HOST_NAME]);
// DEFINE('TARGET_DB', $DB_TARGET_HOST[$HOST_NAME]);
// DEFINE('URL_REDIRECT_DB', $DB_URL_REDIRECT_HOST[$HOST_NAME]);
// next three if top is not set
// DEFINE('TEST_SCHEMA', $DB_CONFIG[MAIN_DB]['db_schema']);
// DEFINE('DEV_SCHEMA', $DB_CONFIG[MAIN_DB]['db_schema']);
// DEFINE('PUBLIC_SCHEMA', $DB_CONFIG[TARGET_DB]['db_schema']);
DEFINE('LOGIN_DB_SCHEMA', PUBLIC_SCHEMA); // where the edit* tables are
DEFINE('GLOBAL_DB_SCHEMA', PUBLIC_SCHEMA); // where global tables are that are used by all schemas (eg queue tables for online, etc)
DEFINE('TARGET', $LOCATION[$HOST_NAME]);
// DEFINE('CSV_PATH', $PATHS[TARGET]['csv_path']);
// DEFINE('EXPORT_SCRIPT', $PATHS[TARGET]['perl_bin']);
// DEFINE('REDIRECT_URL', $PATHS[TARGET]['redirect_url']);
DEFINE('DEBUG', $DEBUG_FLAG[$HOST_NAME]);
DEFINE('SITE_LANG', $SITE_LANG[$HOST_NAME]);

DEFINE('SHOW_ALL_ERRORS', true); // show all errors if debug_all & show_error_handling are enabled

/************* GENERAL PAGE TITLE ********/
DEFINE('G_TITLE', '<OVERALL FALLBACK PAGE TITLE>');

/************ STYLE SHEETS / JS **********/
DEFINE('ADMIN_STYLESHEET', 'edit.css');
DEFINE('ADMIN_JAVASCRIPT', 'edit.js');
DEFINE('STYLESHEET', 'frontend.css');
DEFINE('JAVASCRIPT', 'frontend.js');

// anything optional
/************* INTERNAL ******************/
// any other global definitons here
//	DEFINE('SOME_ID', <SOME VALUE>);

/************* CONVERT *******************/
$paths = array (
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

// turn off debug if debug flag is OFF
if (defined('DEBUG') && DEBUG == false) {
	$ECHO_ALL = 0;
	$DEBUG_ALL = 0;
	$PRINT_ALL = 0;
	$DB_DEBUG = 0;
	$ENABLE_ERROR_HANDLING = 0;
} else {
	$ECHO_ALL = 0;
	$DEBUG_ALL = 1;
	$PRINT_ALL = 1;
	$DB_DEBUG = 1;
	$ENABLE_ERROR_HANDLING = 0;
}

// read auto loader
require BASE.LIB.'autoloader.php';

// __END__

<?php
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2003/06/10
* SHORT DESCRIPTION:
*   configuration file
* HISTORY:
*********************************************************************/

/************* SESSION NAMES *************/
// backend
DEFINE('EDIT_SESSION_NAME', "<ADMIN SESSION NAME>");
// frontend
DEFINE('SESSION_NAME', "<SESSION NAME>");
// set the session name
define('SET_SESSION_NAME', EDIT_SESSION_NAME);

/************* LANGUAGE / ENCODING *******/
DEFINE('DEFAULT_LANG', "en_utf8");
// default web page encoding setting
DEFINE('DEFAULT_ENCODING', "UTF-8");

/************* PATHS *********************/
// directory seperator
DEFINE('DS', DIRECTORY_SEPARATOR);
// ** NEW/BETTER DIR DECLARATIONS **
// path to original file (if symlink)
DEFINE('DIR', __DIR__.DS);
// base dir root folder level
DEFINE('BASE', str_replace('configs', '', __DIR__));

// ** OLD DIR DECLARATIONS **
// path to document root of file called
DEFINE('ROOT', getcwd().DS);
// libs path
DEFINE('LIB', 'lib'.DS);
DEFINE('LIBS', 'lib'.DS);
// includes (strings, arrays for static, etc)
DEFINE('INCLUDES', 'includes'.DS);
// layout base path
DEFINE('LAYOUT', 'layout'.DS);
// pic-root (compatible to CMS)
DEFINE('PICTURES', "images".DS);
// images
DEFINE('IMAGES', "images".DS);
// icons (below the images/ folder)
DEFINE('ICONS', 'icons'.DS);
// media
DEFINE('MEDIA', "media".DS);
// flash-root (below media)
DEFINE('FLASH', "flash".DS);
// uploads (anything to keep)
DEFINE('UPLOADS', "uploads".DS);
// files (binaries) (below media)
DEFINE('BINARIES', "binaries".DS);
// files (videos) (below media)
DEFINE('VIDEOS', "videos".DS);
// files (documents) (below media)
DEFINE('DOCUMENTS', "documents".DS);
// files (pdfs) (below media)
DEFINE('PDFS', "documents".DS);
// CSV
DEFINE('CSV', 'csv'.DS);
// css
DEFINE('CSS', "css".DS);
// js
DEFINE('JS', "javascript".DS);
// table arrays
DEFINE('TABLE_ARRAYS', "table_arrays".DS);
// smarty libs path
DEFINE('SMARTY', "Smarty".DS);
// po langs
DEFINE('LANG', "lang".DS);
// cache path
DEFINE('CACHE', "cache".DS);
// temp path
DEFINE('TMP', "tmp".DS);
// log files
DEFINE('LOG', 'log'.DS);
// compiled template folder
DEFINE('TEMPLATES_C', 'templates_c'.DS);
// template base
DEFINE('TEMPLATES', "templates".DS);
// frontend template dir', only for admin
DEFINE('TEMPLATES_FRONTEND', "templates_frontend".DS);
// default template
DEFINE('DEFAULT_TEMPLATE', "default".DS);
// default template file
DEFINE('DEFAULT_TEMPLATE_FILE', 'default.tpl');

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
//	DEFINE('SSL_HOST', "ssl.host.name");
// error page strictness, Default is 3
// 1: only show error page as the last mesure if really no mid & aid can be loaded and found at all
// 2: if template not found, do not search, show error template
// 3: if default template is not found, show error template, do not fall back to default tree
// 4: very strict, even on normal fixable errors through error
//	DEFINE('ERROR_STRICT', 3);
// allow page caching in general, set to "FALSE" if you do debugging or development!
//	DEFINE('ALLOW_SMARTY_CACHE', FALSE);
// cache life time, in seconds, default here is 2 days (172800s)
// -1 is never expire cache
//	DEFINE('SMARTY_CACHE_LIFETIME', -1);

/************* LOGOUT ********************/
// logout target
DEFINE('LOGOUT_TARGET', '');
// password change allowed
DEFINE('PASSWORD_CHANGE', false);

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

// non constant part
/************* DB ACCESS *****************/
// please be VERY carefull only to change the right side
$DB_CONFIG = array (
	"<DB ID>" => array (
		"db_name" => "<DB NAME>",
		"db_user" => "<DB USER>",
		"db_pass" => "<DB PASSWORD>",
		"db_host" => "<DB HOST>",
		"db_port" => "5432",
		"db_schema" => "<DB SCHEMA>", // if not set, uses public
		"db_type" => "pgsql",
		"db_encoding" => '',
		"db_ssl" => 'disable' // allow, disable, require, prefer
	)
);

// File and Folder paths
// ID is TARGET (first array element)
//	$PATHS["test"]["csv_path"] = "";
//	$PATHS["test"]["perl_bin"] = "";
//	$PATHS["test"]["redirect_url"] = "";

// each host has a different db_host
// development host
$DB_HOST['<HOST NAME>'] = "<DB ID>";
// target host (live)
//	$DB_TARGET_HOST['<HOST NAME>'] = "<DB ID>";
// url redirect database
//	$DB_URL_REDIRECT_HOST['<HOST NAME>'] = "<DB ID>";
// location flagging
// test/dev/live
$LOCATION['<HOST NAME>'] = '<test|live|remote|etc>';
// show DEBUG override
// true/false
$DEBUG_FLAG['<HOST NAME>'] = true;
// set postgresql paths (schemas)
$DB_PATH['<HOST NAME>'] = PUBLIC_SCHEMA;

// set the USE_DATABASE var, if there is nothing set, we assume TRUE
$USE_DATABASE = defined('USE_DATABASE') ? USE_DATABASE : true;

// live frontend pages
// ** missing live domains **
// get the name without the port
list($HOST_NAME, $PORT) = array_pad(explode(":", $_SERVER['HTTP_HOST'], 2), 2, null);
if (!$DB_HOST[$HOST_NAME] && $USE_DATABASE) {
	echo "No matching DB config found. Contact Admin<br>";
	exit -1;
}

// define the static names
DEFINE('LOGIN_DB', $DB_HOST[$HOST_NAME]);
DEFINE('MAIN_DB', $DB_HOST[$HOST_NAME]);
DEFINE('DB_SCHEMA', $DB_PATH[$HOST_NAME]);
//	DEFINE('TARGET_DB', $DB_TARGET_HOST[$HOST_NAME]);
//	DEFINE('URL_REDIRECT_DB', $DB_URL_REDIRECT_HOST[$HOST_NAME]);
// next three if top is not set
//	DEFINE('DEV_SCHEMA', $DB_CONFIG[MAIN_DB]['db_schema']);
//	DEFINE('TEST_SCHEMA', $DB_CONFIG[MAIN_DB]['db_schema']);
//	DEFINE('PUBLIC_SCHEMA', $DB_CONFIG[TARGET_DB]['db_schema']);
DEFINE('LOGIN_DB_SCHEMA', 'public'); // where the edit* tables are
DEFINE('GLOBAL_DB_SCHEMA', 'public'); // where global tables are that are used by all schemas (eg queue tables for online, etc)
DEFINE('TARGET', $LOCATION[$HOST_NAME]);
//	DEFINE('CSV_PATH', $PATHS[TARGET]['csv_path']);
//	DEFINE('EXPORT_SCRIPT', $PATHS[TARGET]['perl_bin']);
//	DEFINE('REDIRECT_URL', $PATHS[TARGET]['redirect_url']);
DEFINE('DEBUG', $DEBUG_FLAG[$HOST_NAME]);
DEFINE('SHOW_ALL_ERRORS', false); // show all errors if debug_all & show_error_handling are enabled

/************* GENERAL PAGE TITLE ********/
$G_TITLE = '<OVERALL PAGE TITLE>';

/************ STYLE SHEETS / JS **********/
$EDIT_STYLESHEET = "edit.css";
$EDIT_JAVASCRIPT = "edit.js";

$STYLESHEET = "frontend.css";
$JAVASCRIPT = "frontend.js";

/************* CONVERT *******************/
$paths = array (
	'/bin',
	'/usr/bin',
	'/usr/local/bin'
);
// find convert
foreach ($paths as $path) {
	if (file_exists($path."/convert") && is_file($path."/convert")) {
		// image magick convert location
		DEFINE('CONVERT', $path."/convert");
	}
}

// turn off debug if debug flag is OFF
if (DEBUG == false) {
	$ECHO_ALL = 0;
	$DEBUG_ALL = 0;
	$PRINT_ALL = 0;
	$DB_DEBUG = 0;
	$ENABLE_ERROR_HANDLING = 0;
}

// any other global definitons here
//	DEFINE('SOME_ID', <SOME VALUE>);

// read auto loader
require BASE.LIB.'autoloader.php';

# __END__

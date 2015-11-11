<?
	/********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2003/06/10
	* SHORT DESCRIPTION:
	*   configuration file
	* HISTORY:
	*********************************************************************/

	/************* SESSION NAMES *************/
	// backend
	DEFINE('EDIT_SESSION_NAME', "ADMIN_SESSION_NAME");
	// frontend
	DEFINE('SESSION_NAME', "SESSION_NAME");
	
	/************* LANGUAGE / ENCODING *******/
	DEFINE('DEFAULT_LANG', "en_utf8");
	// default web page encoding setting
	DEFINE('DEFAULT_ENCODING', "UTF-8");
		
	/************* PATHS *********************/
	// path to document root
	DEFINE('ROOT', getcwd()."/");
	// libs path
	DEFINE('LIBS', "libs/");
	// includes (strings, arrays for stati, etc)
	DEFINE('INCLUDES', "includes/");
	// layout base path
	DEFINE('LAYOUT', 'layout/');
	// pic-root (compatible to CMS)
	DEFINE('PICTURES', "images/");
	// images
	DEFINE('IMAGES', "images/");
	// icons (below the images/ folder)
	DEFINE('ICONS', 'icons/');
	// media
	DEFINE('MEDIA', "media/");
	// flash-root (below media)
	DEFINE('FLASH', "flash/");
	// uploads (anything to keep)
	DEFINE('UPLOADS', "uploads/");
	// files (binaries) (below media)
	DEFINE('BINARIES', "binaries/");
	// files (videos) (below media)
	DEFINE('VIDEOS', "videos/");
	// files (documents) (below media)
	DEFINE('DOCUMENTS', "documents/");
	// files (pdfs) (below media)
	DEFINE('PDFS', "documents/");
	// css 
	DEFINE('CSS', "css/");
	// js
	DEFINE('JS', "javascript/");
	// table arrays
	DEFINE('TABLE_ARRAYS', "table_arrays/");
	// smarty libs path
	DEFINE('SMARTY', "Smarty/");
	// po langs
	DEFINE('LANG', "lang/");
	// cache path
	DEFINE('CACHE', "cache/");
	// temp path
	DEFINE('TMP', "tmp/");
	// log files
	DEFINE('LOG', 'log/');
	// template base
	DEFINE('TEMPLATES', "templates/");
	// frontend template dir', only for admin
	DEFINE('TEMPLATES_FRONTEND', "templates_frontend/");
	// default template
	DEFINE('DEFAULT_TEMPLATE', "default/");
	// default template file
	DEFINE('DEFAULT_TEMPLATE_FILE', 'default.tpl');

	/************* HASH / ACL DEFAULT / ERROR SETTINGS / SMARTY *************/
	// default hash type
	DEFINE('DEFAULT_HASH', 'ripemd160');
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
//	DEFINE('SSL_HOST', "www4.adidas.co.jp");
	// error page strictness, Default is 3
	// 1: only show error page as the last mesure if really no mid & aid can be loaded and found at all
	// 2: if template not found, do not search, show error template
	// 3: if default template is not found, show error template, do not fall back to default tree
	// 4: very strict, even on normal fixable errors through error
//	DEFINE('ERROR_STRICT', 3);
	// allow page caching in general, set to "FALSE" if you do debugging or development!
//	DEFINE('ALLOW_SMARTY_CACHE', FALSE);
	// cache life time, in second', default here is 2 days (172800s)
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

	// non constant part
	/************* DB ACCESS *****************/
	// please be VERY carefull only to change the right side
	$DB_CONFIG = array(
		"test" => array (
			"db_name" => "gullevek",
			"db_user" => "gullevek",
			"db_pass" => "gullevek",
			"db_host" => "db.tokyo.tequila.jp",
			"db_port" => "5432",
			"db_schema" => "public",
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
	$DB_HOST['soba'] = "test";
	$DB_HOST['soba.tokyo.tequila.jp'] = "test";
	// target host (live)
//	$DB_TARGET_HOST['soba'] = "<DB ID>";
	// url redirect database
//	$DB_URL_REDIRECT_HOST['soba'] = "<DB ID>";
	// location flagging
	// test/dev/live
	$LOCATION['soba'] = 'test';
	$LOCATION['soba.tokyo.tequila.jp'] = 'test';
	// show DEBUG override
	// true/false
	$DEBUG_FLAG['soba'] = true;
	$DEBUG_FLAG['soba.tokyo.tequila.jp'] = true;
	// schema names, can also be defined per <DB INFO>
	DEFINE('PUBLIC_SCHEMA', 'public');
	DEFINE('DEV_SCHEMA', 'public');
	DEFINE('TEST_SCHEMA', 'public');
    // and set to domain
    $DB_PATH['soba'] = PUBLIC_SCHEMA;
    $DB_PATH['soba.tokyo.tequila.jp'] = PUBLIC_SCHEMA;

	// set the USE_DATABASE var, if there is nothing set, we assume TRUE
	$USE_DATABASE = defined('USE_DATABASE') ? USE_DATABASE : true;

	// live frontend pages
	// ** missing live domains **
	// get the name without the port
	list($HOST_NAME) = explode(":", $_SERVER['HTTP_HOST']);
	if (!$DB_HOST[$HOST_NAME] && $USE_DATABASE)
	{
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
	DEFINE('GLOBAL_DB_SCHEMA', 'public'); // where global tables are that are used by all schemas (eg queue tables for online', etc)
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
	foreach ($paths as $path)
	{
		if (file_exists($path."/convert") && is_file($path."/convert"))
		{
			// image magick convert location
			DEFINE('CONVERT', $path."/convert");
		}
	}

	// turn off debug if debug flag is OFF
	if (DEBUG == false)
	{
		$ECHO_ALL = 0;
		$DEBUG_ALL = 0;
		$PRINT_ALL = 0;
		$DB_DEBUG = 0;
		$ENABLE_ERROR_HANDLING = 0;
	}
	else
	{
		$ECHO_ALL = 1;
		$DEBUG_ALL = 1;
		$PRINT_ALL = 1;
		$DB_DEBUG = 1;
		$ENABLE_ERROR_HANDLING = 1;
	}

	// any other global definitons here
//	DEFINE('SOME_ID', <SOME VALUE>);
	
?>

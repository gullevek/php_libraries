<?php declare(strict_types=1);
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2008/08/01
* SHORT DESCRIPTION:
* URL redirect header
* HISTORY:
*********************************************************************/

//------------------------------ variable init start
// for dev test we set full error reporting; writes everything, except E_ERROR into logs/php_error-<day>.log
if ($DEBUG_ALL && $ENABLE_ERROR_HANDLING) {
	include BASE.LIBS."Error.Handling.php";
}
// predefine vars
$LANG = '';
$messages = array();
// import all POST vars
extract($_POST, EXTR_SKIP);
//------------------------------ variable init end

//------------------------------ library include start
// set output to quiet for load of classes & session settings
ob_start();
// set the session name
$SET_SESSION_NAME = EDIT_SESSION_NAME;
//------------------------------ library include end

//------------------------------ basic variable settings start
if (!isset($AJAX_PAGE)) {
	$AJAX_PAGE = false;
}
if (!isset($ZIP_STREAM)) {
	$ZIP_STREAM = false;
}
// set encoding
if (!isset($ENCODING)) {
	$ENCODING = DEFAULT_ENCODING;
}
// set the default lang, if not given
if (session_id() && isset($_SESSION['DEFAULT_LANG']) && $_SESSION['DEFAULT_LANG']) {
	$LANG = $_SESSION['DEFAULT_LANG'];
} else {
	$LANG = defined('SITE_LANG') ? SITE_LANG : DEFAULT_LANG;
}
// end the stop of the output flow, but only if we didn't request a csv file download
if (isset($_POST['action']) && $_POST['action'] != 'download_csv' && !$AJAX_PAGE) {
	header("Content-type: text/html; charset=".$ENCODING);
}
if ($AJAX_PAGE && !$ZIP_STREAM) {
	header("Content-Type: application/json; charset=UTF-8");
}
//------------------------------ basic variable settings start

//------------------------------ class init start
// login & page access check
$login = new CoreLibs\ACL\Login(DB_CONFIG, $LANG);
// post login lang check
if ($_SESSION['DEFAULT_LANG']) {
	$LANG = $_SESSION['DEFAULT_LANG'];
}
// create smarty object
$smarty = new CoreLibs\Template\SmartyExtend($LANG);
// create new DB class
$cms = new CoreLibs\Admin\Backend(DB_CONFIG, $LANG);
// the menu show flag (what menu to show)
$cms->menu_show_flag = 'main';
// db nfo
$cms->dbInfo();
// set acl
$cms->setACL($login->acl);
// flush
ob_end_flush();
//------------------------------ class init end

//------------------------------ logging start
// log backend data
// data part creation
$data = array(
	'_SESSION' => $_SESSION,
	'_GET' => $_GET,
	'_POST' => $_POST,
	'_FILES' => $_FILES
);
// log action
// no log if login
if (!$login->login) {
	$cms->adbEditLog('Submit', $data, 'BINARY');
}
//------------------------------ logging end

// automatic hide for DEBUG messages on live server
// can be overridden when setting DEBUG_ALL_OVERRIDE on top of the script (for emergency debugging of one page only)
if ((TARGET == 'live' || TARGET == 'remote') && !$DEBUG_ALL_OVERRIDE) {
	$login->debug_output_all = false;
	$login->echo_output_all = false;
	$login->print_output_all = false;
	$cms->debug_output_all = false;
	$cms->echo_output_all = false;
	$cms->print_output_all = false;
}
$smarty->DATA['JS_DEBUG'] = DEBUG;

// __END__

<?php

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2008/08/01
* SHORT DESCRIPTION:
* URL redirect header
* HISTORY:
*********************************************************************/

declare(strict_types=1);

//------------------------------ variable init start
// for dev test we set full error reporting; writes everything, except E_ERROR into logs/php_error-<day>.log
if (!empty($DEBUG_ALL) && !empty($ENABLE_ERROR_HANDLING)) {
	include BASE . LIBS . "Error.Handling.php";
}
//------------------------------ variable init end

//------------------------------ library include start
// set output to quiet for load of classes & session settings
ob_start();
// set the session name
$SET_SESSION_NAME = EDIT_SESSION_NAME;
$LOG_FILE_ID = BASE_NAME . 'Admin';
//------------------------------ library include end

//------------------------------ basic variable settings start
if (!isset($AJAX_PAGE)) {
	$AJAX_PAGE = false;
}
if (!isset($ZIP_STREAM)) {
	$ZIP_STREAM = false;
}
// set encoding
if (!isset($ENCODING) || !$ENCODING) {
	$ENCODING = DEFAULT_ENCODING;
}
// end the stop of the output flow, but only if we didn't request a csv file download
if (isset($_POST['action']) && $_POST['action'] != 'download_csv' && !$AJAX_PAGE) {
	header("Content-type: text/html; charset=" . $ENCODING);
}
if ($AJAX_PAGE && !$ZIP_STREAM) {
	header("Content-Type: application/json; charset=UTF-8");
}
// start session
CoreLibs\Create\Session::startSession();
//------------------------------ basic variable settings start

//------------------------------ class init start
// create logger
$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => LOG_FILE_ID,
	'print_file_date' => true,
	'debug_all' => $DEBUG_ALL ?? false,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? false,
]);
// automatic hide for DEBUG messages on live server
// can be overridden when setting DEBUG_ALL_OVERRIDE on top of the script
// (for emergency debugging of one page only)
if (
	(TARGET == 'live' || TARGET == 'remote') &&
	DEBUG === true &&
	!empty($DEBUG_ALL_OVERRIDE)
) {
	foreach (['debug', 'echo', 'print'] as $target) {
		$log->setLogLevelAll($target, false);
	}
}
// db config with logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
// login & page access check
$login = new CoreLibs\ACL\Login($db, $log);
// create smarty object
$smarty = new CoreLibs\Template\SmartyExtend();
// create new Backend class with db and loger attached
$cms = new CoreLibs\Admin\Backend($db, $log);
// the menu show flag (what menu to show)
$cms->menu_show_flag = 'main';
// db info
$cms->db->dbInfo();
// set acl
$cms->setACL($login->acl);
// flush (can we move that to header block above)
ob_end_flush();
//------------------------------ class init end

//------------------------------ logging start
// log backend data
// data part creation
$data = [
	'_SESSION' => $_SESSION,
	'_GET' => $_GET,
	'_POST' => $_POST,
	'_FILES' => $_FILES
];
// log action
// no log if login
if (!$login->login) {
	$cms->adbEditLog('Submit', $data, 'BINARY');
}
//------------------------------ logging end

// pass on DEBUG flag to JS via smarty variable
$smarty->DATA['JS_DEBUG'] = DEBUG;

// __END__

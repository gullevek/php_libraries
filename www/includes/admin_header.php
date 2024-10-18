<?php

/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED:
* SHORT DESCRIPTION:
*
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
//------------------------------ library include end

//------------------------------ basic variable settings start
// set the session name
$SET_SESSION_NAME = EDIT_SESSION_NAME;
$LOG_FILE_ID = BASE_NAME . 'Admin';
// ajax page flag
if (!isset($AJAX_PAGE)) {
	$AJAX_PAGE = false;
}
// zip download flag
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
//------------------------------ basic variable settings start

//------------------------------ class init start
// start session
$session = new \CoreLibs\Create\Session($SET_SESSION_NAME);
// create logger
$log = new \CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	// set log level based on host setting
	'log_level' => \CoreLibs\Logging\Logging::processLogLevel(DEBUG_LEVEL),
	'log_per_date' => true,
]);
// allow override of debug log level settings
if (!empty($DEBUG_ALL_OVERRIDE)) {
	$log->setLoggingLevel((string)$DEBUG_ALL_OVERRIDE);
}
// db config with logger
$db = new \CoreLibs\DB\IO(DB_CONFIG, $log);
// login & page access check
$login = new \CoreLibs\ACL\Login(
	$db,
	$log,
	$session,
	[
		'auto_login' => true,
		'default_acl_level' => DEFAULT_ACL_LEVEL,
		'logout_target' => '',
		'site_locale' => SITE_LOCALE,
		'site_domain' => SITE_DOMAIN,
		'site_encoding' => SITE_ENCODING,
		'locale_path' => BASE . INCLUDES . LOCALE,
	]
);
// lang, path, domain
// pre auto detect language after login
$locale = $login->loginGetLocale();
// set lang and pass to smarty/backend
$l10n = new \CoreLibs\Language\L10n(
	$locale['locale'],
	$locale['domain'],
	$locale['path'],
	$locale['encoding'],
);

// create smarty object
$smarty = new \CoreLibs\Template\SmartyExtend($l10n, CACHE_ID, COMPILE_ID);
// create new Backend class with db and loger attached
$cms = new \CoreLibs\Admin\Backend($db, $log, $session, $l10n, DEFAULT_ACL_LEVEL);
// the menu show flag (what menu to show)
$cms->menu_show_flag = 'main';
// db info
$cms->db->dbInfo();
// set acl
$cms->setACL($login->loginGetAcl());
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
if (!$login->loginActionRun()) {
	$cms->adbEditLog('Submit', $data, 'BINARY');
}
//------------------------------ logging end

// pass on DEBUG flag to JS via smarty variable
$smarty->DATA['JS_DEBUG'] = $log->loggingLevelIsDebug();

// __END__

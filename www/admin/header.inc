<?
	/********************************************************************
	* AUTHOR: Clemens Schwaighofer
	* CREATED: 2008/08/01
	* SHORT DESCRIPTION:
	* URL redirect header
	* HISTORY:
	*********************************************************************/

	//------------------------------ variable init start
	// for dev test we set full error reporting; writes everything, except E_ERROR into logs/php_error-<day>.log
	if ($DEBUG_ALL && $ENABLE_ERROR_HANDLING)
		include("libs/Error.Handling.inc");
	// predefine vars
	$lang = '';
	$messages = array ();
	// import all POST vars
	extract($_POST, EXTR_SKIP);
	//------------------------------ variable init end

	//------------------------------ library include start
	// set output to quiet for load of classes & session settings
	ob_start();
	require("config.inc");
	// set the session name
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
	// login class
	require(LIBS."Class.Login.inc");
	// DB connection & work time class
	require(LIBS.'Class.Admin.Backend.inc');
	// Smarty: and the small extend for l10n calls
	require(LIBS.'Class.Smarty.Extend.inc');
	//------------------------------ library include end

	//------------------------------ basic variable settings start
	// set encoding 
	if (!isset($encoding))
		$encoding = DEFAULT_ENCODING;
	// set the default lang, if not given
	if (session_id() && $_SESSION['DEFAULT_LANG'])
		$lang = $_SESSION['DEFAULT_LANG'];
	elseif (!$lang)
		$lang = DEFAULT_LANG;
	// end the stop of the output flow, but only if we didn't request a csv file download
	if ($_POST['action'] != 'download_csv')
	{
		header("Content-type: text/html; charset=".$encoding);
		ob_end_flush();
	}
	//------------------------------ basic variable settings start

	//------------------------------ class init start
	// login & page access check
	$login = new login($DB_CONFIG[LOGIN_DB], $lang);
	// post login lang check
	if ($_SESSION['DEFAULT_LANG'])
		$lang = $_SESSION['DEFAULT_LANG'];
	// create smarty object
	$smarty = new SmartyML($lang);
	// create new DB class
	$cms = new AdminBackend($DB_CONFIG[MAIN_DB], $lang);
	// set daily rotation
	$cms->file_name_ext = '_'.date('Y-m-d').'.log';
	// set search path to the default DB schema
	$cms->db_exec("SET search_path TO ".DB_SCHEMA);
	// the menu show flag (what menu to show)
	$cms->menu_show_flag = 'main';
	// db nfo
	$cms->db_info();
	//------------------------------ class init end

	//------------------------------ logging start
	// log backend data
	// data part creation
	$data = array (
		'_SESSION' => $_SESSION,
		'_GET' => $_GET,
		'_POST' => $_POST,
		'_FILES' => $_FILES
	);
	// log action
	// no log if login
	if (!$login->login)
		$cms->adbEditLog('Submit', $data, 'BINARY');
	//------------------------------ logging end

	//------------------------------ page rights start
	// flag if to show the edit access id drop down list
	// check if we have more than one EA ID
	$cms->DATA['show_ea_extra'] = $login->acl['acl']['show_ea_extra'];
	//------------------------------ page rights ned

	// automatic hide for DEBUG messages on live server
	if (TARGET == 'live' || TARGET == 'remote')
	{
		$login->debug_output_all = 0;
		$cms->debug_output_all = 1;
		$cms->echo_output_all = 0;
		$cms->print_output_all = 1;
	}
	$cms->DATA['JS_DEBUG'] = DEBUG;
?>

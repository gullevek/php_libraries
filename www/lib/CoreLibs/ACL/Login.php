<?php

/*********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2000/06/01
* VERSION: 5.0.0
* RELEASED LICENSE: GNU GPL 3
* SHORT DESCRIPTON:
*   ~ 2003/03/03: change the whole include file into one class
*   advantages are a) can include before actuall call, can control it
*   easer (login db, etc), etc etc etc
*
*   a login lib that should stand out of all others
*   will be a class one day
*
*   descrption of session_vars
*     DEBUG_ALL - set to one, prints out error_msg var at end of php execution
*     DB_DEBUG - prints out database debugs (query, etc)
*     GROUP_LEVEL - the level he can access (numeric)
*     USER_NAME - login name from user
*     LANG - lang to show edit interface (not yet used)
*     DEFAULT_CHARSET - in connection with LANG (not yet used)
*     PAGES - array of hashes
*       edit_page_id - ID from the edit_pages table
*       filename - name of the file
*       page_name - name in menu
*       menu - appears in menu
*       popup - is a popup
*       popup_x - if popup -> width
*       popup_y - if popup -> height
*       online - page is online (user can access)
*       query_string - string to paste for popup (will change)
*
* HISTORY:
* 2010/12/21 (cs) merge back password change interface
* 2010/12/17 (cs) change that password can be blowfish encrypted,
*                 auto detects if other encryption is used (md5, std des)
*                 and tries to use them
* 2007/05/29 (cs) BUG with assign query and visible sub arrays to pages
* 2005/09/21 (cs) if error -> unset the session vars
* 2005/07/04 (cs) add a function to write into the edit log file
* 2005/07/01 (cs) start adepting login class to new edit interface layout
* 2005/03/31 (cs) fixed the class call with all debug vars
* 2004/11/17 (cs) unused var cleanup
* 2004/11/16 (cs) rewrite login so it uses a template and not just plain html.
*                 prepare it, so it will be able to use external stuff later
*                 (some interface has to be designed for that
* 2004/11/16 (cs) removed the mobile html part from login
* 2004/09/30 (cs) layout fix
*   2003-11-11: if user has debug 1 unset memlimit, because there can be serious
*               problems with the query logging
*   2003-06-12: added flag to PAGES array
*               changed the get vars from GLOBALS to _POST
*               changed the session registration. no more GLOBAL vars are registered
*               only _SESSION["..."]
*   2003-06-09: added mobile phone login possibility
*   2003-03-04: droped ADMIN and added GROUP_LEVEL
*   2003-03-03: started to change the include file function collection
*               to become a class
*   2003-02-28: various advances and changes, but far from perfect
*               decided to change it into a class for easier handling
*               add also possibility to change what will stored in the
*               login session ?
*   2000-06-01: created basic idea and functions
*********************************************************************/

declare(strict_types=1);

namespace CoreLibs\ACL;

use CoreLibs\Check\Password;
use CoreLibs\Create\Session;

class Login extends \CoreLibs\DB\IO
{
	/** @var string */
	private $euid; // the user id var
	// is set to one if login okay, or EUID is set and user is okay to access this page
	/** @var bool */
	private $permission_okay = false;
	/** @var string */
	public $login; // pressed login
	/** @var string */
	private $action; // master action command
	/** @var string */
	private $username; // login name
	/** @var string */
	private $password; // login password
	/** @var string */
	private $logout; // logout button
	// login error code, can be matched to the array login_error_msg, which holds the string
	/** @var int */
	private $login_error = 0;
	/** @var bool */
	private $password_change = false; // if this is set to true, the user can change passwords
	/** @var bool */
	private $password_change_ok = false; // password change was successful
	// can we reset password and mail to user with new password set screen
	/** @var bool */
	private $password_forgot = false;
	/** @var bool */
	// private $password_forgot_ok = false; // password forgot mail send ok
	/** @var string */
	private $change_password;
	/** @var string */
	private $pw_username;
	/** @var string */
	private $pw_old_password;
	/** @var string */
	private $pw_new_password;
	/** @var string */
	private $pw_new_password_confirm;
	/** @var array<string> */
	private $pw_change_deny_users = []; // array of users for which the password change is forbidden
	/** @var string */
	private $logout_target;
	/** @var int */
	private $max_login_error_count = -1;
	/** @var array<string> */
	private $lock_deny_users = [];
	/** @var string */
	private $page_name;

	// if we have password change we need to define some rules
	/** @var int */
	private $password_min_length = PASSWORD_MIN_LENGTH;
	// max length is fixed as 255 (for input type max), if set highter, it will be set back to 255
	/** @var int */
	private $password_max_length = PASSWORD_MAX_LENGTH;
	// can have several regexes, if nothing set, all is ok
	/** @var array<string> */
	private $password_valid_chars = [
		// '^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,}$',
		// '^(?.*(\pL)u)(?=.*(\pN)u)(?=.*([^\pL\pN])u).{8,}',
	];

	// all possible login error conditions
	/** @var array<mixed> */
	private $login_error_msg = [];
	// this is an array holding all strings & templates passed from the outside (translation)
	/** @var array<mixed> */
	private $login_template = [
		'strings' => [],
		'password_change' => '',
		'template' => ''
	];

	// acl vars
	/** @var array<mixed> */
	public $acl = [];
	/** @var array<mixed> */
	public $default_acl_list = [];
	// login html, if we are on an ajax page
	/** @var string|null */
	private $login_html = '';
	/** @var bool */
	private $login_is_ajax_page = false;

	// language
	/** @var \CoreLibs\Language\L10n */
	public $l;

	/**
	 * constructor, does ALL, opens db, works through connection checks, closes itself
	 * @param array<mixed> $db_config db config array
	 * @param \CoreLibs\Debug\Logging|null $log Logging class, if null, auto set
	 * @param \CoreLibs\Language\L10n|null $l10n l10n language class, if null, auto set
	 */
	public function __construct(
		array $db_config,
		?\CoreLibs\Debug\Logging $log = null,
		?\CoreLibs\Language\L10n $l10n = null
	) {
		// create db connection and init base class
		parent::__construct($db_config, $log ?? new \CoreLibs\Debug\Logging());
		// log login data for this class only
		$this->log->setLogPer('class', true);
		// set internal page name
		$this->page_name = \CoreLibs\Get\System::getPageName();
		// set db special errors
		if ($this->db_init_error === true) {
			echo 'Could not connect to DB<br>';
			// if I can't connect to the DB to auth exit hard. No access allowed
			exit;
		}

		// initial the session if there is no session running already
		// check if session exists and could be created
		// TODO: move session creation and check to outside?
		if (Session::startSession() === false) {
			echo '<b>Session not started or could not be started!</b><br>'
				. 'Use \'\CoreLibs\Create\Session::startSession();\'.<br>'
				. 'For less problems with other session, you can set a '
				. 'session name with \'\CoreLibs\Create\Session::startSession(\'name\');\'.<br>';
			exit;
		}

		// pre-check that password min/max lengths are inbetween 1 and 255;
		if ($this->password_max_length > 255) {
			echo '<b>Settings problem</b> PMaL<br>';
			exit;
		}
		if ($this->password_min_length < 1) {
			echo '<b>Settings problem</b> PMiL<br>';
			exit;
		}

		// set global is ajax page for if we show the data directly,
		// or need to pass it back
		// to the continue AJAX class for output back to the user
		$this->login_is_ajax_page = isset($GLOBALS['AJAX_PAGE']) && $GLOBALS['AJAX_PAGE'] ? true : false;
		// set the default lang
		$lang = 'en_utf8';
		if (Session::getSessionId() !== false && !empty($_SESSION['DEFAULT_LANG'])) {
			$lang = $_SESSION['DEFAULT_LANG'];
		} else {
			$lang = defined('SITE_LANG') ? SITE_LANG : DEFAULT_LANG;
		}
		$this->l = $l10n ?? new \CoreLibs\Language\L10n($lang);

		// if we have a search path we need to set it, to use the correct DB to login
		// check what schema to use. if there is a login schema use this, else check
		// if there is a schema set in the config, or fall back to DB_SCHEMA
		// if this exists, if this also does not exists use public schema
		/** @phpstan-ignore-next-line */
		if (defined('LOGIN_DB_SCHEMA') && !empty(LOGIN_DB_SCHEMA)) {
			$SCHEMA = LOGIN_DB_SCHEMA;
		} elseif (isset($db_config['db_schema']) && $db_config['db_schema']) {
			$SCHEMA = $db_config['db_schema'];
		} elseif (defined('PUBLIC_SCHEMA')) {
			$SCHEMA = PUBLIC_SCHEMA;
		} else {
			$SCHEMA = 'public';
		}
		// echo "<h1>*****SCHEMA******</h1>: $SCHEMA<br>";
		// set schema if schema differs to schema set in db conneciton
		if ($this->dbGetSchema() && $this->dbGetSchema() != $SCHEMA) {
			$this->dbExec("SET search_path TO " . $SCHEMA);
		}
		// if there is none, there is none, saves me POST/GET check
		$this->euid = array_key_exists('EUID', $_SESSION) ? $_SESSION['EUID'] : 0;
		// get login vars, are so, can't be changed
		// prepare
		// pass on vars to Object vars
		$this->login = $_POST['login_login'] ?? '';
		$this->username = $_POST['login_username'] ?? '';
		$this->password = $_POST['login_password'] ?? '';
		$this->logout = $_POST['login_logout'] ?? '';
		// password change vars
		$this->change_password = $_POST['change_password'] ?? '';
		$this->pw_username = $_POST['pw_username'] ?? '';
		$this->pw_old_password = $_POST['pw_old_password'] ?? '';
		$this->pw_new_password = $_POST['pw_new_password'] ?? '';
		$this->pw_new_password_confirm = $_POST['pw_new_password_confirm'] ?? '';
		// logout target (from config)
		$this->logout_target = LOGOUT_TARGET;
		// disallow user list for password change
		$this->pw_change_deny_users = ['admin'];
		// set flag if password change is okay
		if (defined('PASSWORD_CHANGE')) {
			$this->password_change = PASSWORD_CHANGE;
		}
		// NOTE: forgot password flow with email
		if (defined('PASSWORD_FORGOT')) {
			$this->password_forgot = PASSWORD_FORGOT;
		}
		// max login counts before error reporting
		$this->max_login_error_count = 10;
		// users that never get locked, even if they are set strict
		$this->lock_deny_users = ['admin'];

		// init default ACL list array
		$_SESSION['DEFAULT_ACL_LIST'] = [];
		// read the current edit_access_right list into an array
		$q = "SELECT level, type, name FROM edit_access_right WHERE level >= 0 ORDER BY level";
		while (is_array($res = $this->dbReturn($q))) {
			// level to description format (numeric)
			$this->default_acl_list[$res['level']] = [
				'type' => $res['type'],
				'name' => $res['name']
			];
		}
		// write that into the session
		$_SESSION['DEFAULT_ACL_LIST'] = $this->default_acl_list;

		// if username & password & !$euid start login
		$this->loginLoginUser();
		// checks if $euid given check if user is okay for that side
		$this->loginCheckPermissions();
		// logsout user
		$this->loginLogoutUser();
		// if the password change flag is okay, run the password change method
		if ($this->password_change) {
			$this->loginPasswordChange();
		}
		// password forgot
		if ($this->password_forgot) {
			$this->loginPasswordForgot();
		}
		// if !$euid || permission not okay, print login screan
		$this->login_html = $this->loginPrintLogin();
		// closing all connections, depending on error status, exit
		if (!$this->loginCloseClass()) {
			// if variable AJAX flag is not set, show output, else pass through for ajax work
			if ($this->login_is_ajax_page !== true) {
				// the login screen if we hav no login permission & login screen html data
				if ($this->login_html !== null) {
					echo $this->login_html;
				}
				// do not go anywhere, quit processing here
				// do something with possible debug data?
				if (TARGET == 'live' || TARGET == 'remote')	{
					// login
					$this->log->setLogLevelAll('debug', DEBUG ? true : false);
					$this->log->setLogLevelAll('echo', false);
					$this->log->setLogLevelAll('print', DEBUG ? true : false);
				}
				$status_msg = $this->log->printErrorMsg();
				// if ($this->echo_output_all) {
				if ($this->log->getLogLevelAll('echo')) {
					echo $status_msg;
				}
				// exit so we don't process anything further, at all
				exit;
			} else {
				// if we are on an ajax page reset any POST/GET array data to avoid
				// any accidentical processing going on
				$_POST = [];
				$_GET = [];
				// set the action to login so we can trigger special login html return
				$_POST['action'] = 'login';
				$_POST['login_html'] = $this->login_html;
				// NOTE: this part needs to be catched by the frontend AJAX
				// and some function needs to then set something like this
				// document.getElementsByTagName('html')[0].innerHTML  = data.content.login_html;
			}
		}
		// set acls for this user/group and this page
		$this->loginSetAcl();
	}

	/**
	 * deconstructory, called with the last function to close DB connection
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * checks if password is valid, sets internal error login variable
	 * @param  string $hash     password hash
	 * @param  string $password submitted password
	 * @return bool             true or false on password ok or not
	 */
	private function loginPasswordCheck(string $hash, string $password = ''): bool
	{
		// check with what kind of prefix the password begins:
		// $2a$ or $2y$: BLOWFISCH
		// $1$: MD5
		// $ and one alphanumeric letter, 13 chars long, but nor $ at the end: STD_DESC
		// if no $ => normal password
		// NOW, if we have a password encoded, but not the correct encoder available, throw special error
		$password_ok = false;
		if (!$password) {
			$password = $this->password;
		}
		// first, errors on missing encryption
		if (
			(preg_match("/^\\$2(a|y)\\$/", $hash) && CRYPT_BLOWFISH != 1) ||
			(preg_match("/^\\$1\\$/", $hash) && CRYPT_MD5 != 1) ||
			(preg_match("/^\\$[0-9A-Za-z.]{12}$/", $hash) && CRYPT_STD_DES != 1)
		) {
			// this means password cannot be decrypted because of missing crypt methods
			$this->login_error = 9999;
			$password_ok = false;
		} elseif (
			preg_match("/^\\$2y\\$/", $hash) &&
			!Password::passwordVerify($password, $hash)
		) {
			// this is the new password hash methid, is only $2y$
			// all others are not valid anymore
			$this->login_error = 1013;
			$password_ok = false;
		} elseif (
			!preg_match("/^\\$2(a|y)\\$/", $hash) &&
			!preg_match("/^\\$1\\$/", $hash) &&
			!preg_match("/^\\$[0-9A-Za-z.]{12}$/", $hash) &&
			$hash != $password
		) {
			// check old plain password, case sensitive
			$this->login_error = 1012;
			$password_ok = false;
		} else {
			// all ok
			$password_ok = true;
		}
		return $password_ok;
	}

	/**
	 * if user pressed login button this script is called, but only if there is no preview euid set]
	 * @return void has not return
	 */
	private function loginLoginUser(): void
	{
		// have to get the global stuff here for setting it later
		if (!$this->euid && $this->login) {
			if (!($this->password && $this->username)) {
				$this->login_error = 102;
			} else {
				// we have to get the themes in here too
				$q = "SELECT eu.edit_user_id, eu.username, eu.password, "
					. "eu.edit_group_id, "
					. "eg.name AS edit_group_name, admin, "
					. "eu.login_error_count, eu.login_error_date_last, "
					. "eu.login_error_date_first, eu.strict, eu.locked, "
					. "eu.debug, eu.db_debug, "
					. "eareu.level AS user_level, eareu.type AS user_type, "
					. "eareg.level AS group_level, eareg.type AS group_type, "
					. "eu.enabled, el.short_name AS lang_short, el.iso_name AS lang_iso, "
					. "first.header_color AS first_header_color, "
					. "second.header_color AS second_header_color, second.template "
					. "FROM edit_user eu "
					. "LEFT JOIN edit_scheme second ON "
					. "(second.edit_scheme_id = eu.edit_scheme_id AND second.enabled = 1), "
					. "edit_language el, edit_group eg, "
					. "edit_access_right eareu, "
					. "edit_access_right eareg, "
					. "edit_scheme first "
					. "WHERE first.edit_scheme_id = eg.edit_scheme_id "
					. "AND eu.edit_group_id = eg.edit_group_id "
					. "AND eu.edit_language_id = el.edit_language_id AND "
					. "eu.edit_access_right_id = eareu.edit_access_right_id AND "
					. "eg.edit_access_right_id = eareg.edit_access_right_id AND "
					// password match is done in script, against old plain or new blowfish encypted
					. "(LOWER(username) = '" . $this->dbEscapeString(strtolower($this->username)) . "') ";
				$res = $this->dbReturn($q);
				if (!is_array($res)) {
					$this->login_error = 1009;
					$this->permission_okay = false;
				} elseif (!$this->cursor_ext[md5($q)]['num_rows']) {
					// username is wrong, but we throw for wrong username and wrong password the same error
					$this->login_error = 1010;
				} else {
					// if login errors is half of max errors and the last login error
					// was less than 10s ago, forbid any new login try

					// check flow
					// - user is enabled
					// - user is not locked
					// - password is readable
					// - encrypted password matches
					// - plain password matches

					if (!$res['enabled']) {
						// user is enabled
						$this->login_error = 104;
					} elseif ($res['locked']) {
						// user is locked, either set or auto set
						$this->login_error = 105;
					} elseif (!$this->loginPasswordCheck($res['password'])) {
						// none to be set, set in login password check
					} else {
						// check if the current password is an invalid hash and do a rehash and set password
						// $this->debug('LOGIN', 'Hash: '.$res['password'].' -> VERIFY: '
						//	.($Password::passwordVerify($this->password, $res['password']) ? 'OK' : 'FAIL')
						//	.' => HASH: '.(Password::passwordRehashCheck($res['password']) ? 'NEW NEEDED' : 'OK'));
						if (Password::passwordRehashCheck($res['password'])) {
							// update password hash to new one now
							$q = "UPDATE edit_user "
								. "SET password = '" . $this->dbEscapeString(Password::passwordSet($this->password))
								. "' WHERE edit_user_id = " . $res['edit_user_id'];
							$this->dbExec($q);
						}
						// normal user processing
						// set class var and session var
						$_SESSION['EUID'] = $this->euid = $res['edit_user_id'];
						// check if user is okay
						$this->loginCheckPermissions();
						if ($this->login_error == 0) {
							// now set all session vars and read page permissions
							$_SESSION['DEBUG_ALL'] = $this->dbBoolean($res['debug']);
							$_SESSION['DB_DEBUG'] = $this->dbBoolean($res['db_debug']);
							// general info for user logged in
							$_SESSION['USER_NAME'] = $res['username'];
							$_SESSION['ADMIN'] = $res['admin'];
							$_SESSION['GROUP_NAME'] = $res['edit_group_name'];
							$_SESSION['USER_ACL_LEVEL'] = $res['user_level'];
							$_SESSION['USER_ACL_TYPE'] = $res['user_type'];
							$_SESSION['GROUP_ACL_LEVEL'] = $res['group_level'];
							$_SESSION['GROUP_ACL_TYPE'] = $res['group_type'];
							// deprecated TEMPLATE setting
							$_SESSION['TEMPLATE'] = $res['template'] ? $res['template'] : '';
							$_SESSION['HEADER_COLOR'] = $res['second_header_color'] ?
								$res['second_header_color'] :
								$res['first_header_color'];
							$_SESSION['LANG'] = $res['lang_short'];
							$_SESSION['DEFAULT_CHARSET'] = $res['lang_iso'];
							$_SESSION['DEFAULT_LANG'] = $res['lang_short'] . '_'
								. strtolower(str_replace('-', '', $res['lang_iso']));
							// reset any login error count for this user
							if ($res['login_error_count'] > 0) {
								$q = "UPDATE edit_user "
									. "SET login_error_count = 0, login_error_date_last = NULL, "
									. "login_error_date_first = NULL "
									. "WHERE edit_user_id = " . $res['edit_user_id'];
								$this->dbExec($q);
							}
							$edit_page_ids = [];
							$pages = [];
							$pages_acl = [];
							// set pages access
							$q = "SELECT ep.edit_page_id, ep.cuid, epca.cuid AS content_alias_uid, "
								. "ep.hostname, ep.filename, ep.name AS edit_page_name, "
								. "ep.order_number AS edit_page_order, ep.menu, "
								. "ep.popup, ep.popup_x, ep.popup_y, ep.online, ear.level, ear.type "
								. "FROM edit_page ep "
								. "LEFT JOIN edit_page epca ON (epca.edit_page_id = ep.content_alias_edit_page_id)"
								. ", edit_page_access epa, edit_access_right ear "
								. "WHERE ep.edit_page_id = epa.edit_page_id "
								. "AND ear.edit_access_right_id = epa.edit_access_right_id "
								. "AND epa.enabled = 1 AND epa.edit_group_id = " . $res["edit_group_id"] . " "
								. "ORDER BY ep.order_number";
							while ($res = $this->dbReturn($q)) {
								if (!is_array($res)) {
									break;
								}
								// page id array for sub data readout
								$edit_page_ids[$res['edit_page_id']] = $res['cuid'];
								// create the array for pages
								$pages[$res['cuid']] = [
									'edit_page_id' => $res['edit_page_id'],
									'cuid' => $res['cuid'],
									// for reference of content data on a differen page
									'content_alias_uid' => $res['content_alias_uid'],
									'hostname' => $res['hostname'],
									'filename' => $res['filename'],
									'page_name' => $res['edit_page_name'],
									'order' => $res['edit_page_order'],
									'menu' => $res['menu'],
									'popup' => $res['popup'],
									'popup_x' => $res['popup_x'],
									'popup_y' => $res['popup_y'],
									'online' => $res['online'],
									'acl_level' => $res['level'],
									'acl_type' => $res['type'],
									'query' => [],
									'visible' => []
								];
								// make reference filename -> level
								$pages_acl[$res['filename']] = $res['level'];
							} // for each page
							// get the visible groups for all pages and write them to the pages
							$q = "SELECT epvg.edit_page_id, name, flag "
								. "FROM edit_visible_group evp, edit_page_visible_group epvg "
								. "WHERE evp.edit_visible_group_id = epvg.edit_visible_group_id "
								. "AND epvg.edit_page_id IN (" . join(', ', array_keys($edit_page_ids)) . ") "
								. "ORDER BY epvg.edit_page_id";
							while (is_array($res = $this->dbReturn($q))) {
								$pages[$edit_page_ids[$res['edit_page_id']]]['visible'][$res['name']] = $res['flag'];
							}
							// get the same for the query strings
							$q = "SELECT eqs.edit_page_id, name, value, dynamic FROM edit_query_string eqs "
								. "WHERE enabled = 1 AND edit_page_id "
								. "IN (" . join(', ', array_keys($edit_page_ids)) . ") "
								. "ORDER BY eqs.edit_page_id";
							while (is_array($res = $this->dbReturn($q))) {
								$pages[$edit_page_ids[$res['edit_page_id']]]['query'][] = [
									'name' => $res['name'],
									'value' => $res['value'],
									'dynamic' => $res['dynamic']
								];
							}
							// get the page content and add them to the page
							$q = "SELECT epc.edit_page_id, epc.name, epc.uid, epc.order_number, "
								. "epc.online, ear.level, ear.type "
								. "FROM edit_page_content epc, edit_access_right ear "
								. "WHERE epc.edit_access_right_id = ear.edit_access_right_id AND "
								. "epc.edit_page_id IN (" . join(', ', array_keys($edit_page_ids)) . ") "
								. "ORDER BY epc.order_number";
							while (is_array($res = $this->dbReturn($q))) {
								$pages[$edit_page_ids[$res['edit_page_id']]]['content'][$res['uid']] = [
									'name' => $res['name'],
									'uid' => $res['uid'],
									'online' => $res['online'],
									'order' => $res['order_number'],
									// access name and level
									'acl_type' => $res['type'],
									'acl_level' => $res['level']
								];
							}
							// write back the pages data to the output array
							$_SESSION['PAGES'] = $pages;
							$_SESSION['PAGES_ACL_LEVEL'] = $pages_acl;
							// load the edit_access user rights
							$q = "SELECT ea.edit_access_id, level, type, ea.name, ea.color, ea.uid, edit_default "
								. "FROM edit_access_user eau, edit_access_right ear, edit_access ea "
								. "WHERE eau.edit_access_id = ea.edit_access_id "
								. "AND eau.edit_access_right_id = ear.edit_access_right_id "
								. "AND eau.enabled = 1 AND edit_user_id = " . $this->euid . " "
								. "ORDER BY ea.name";
							$unit_access = [];
							$eauid = [];
							$unit_acl = [];
							while (is_array($res = $this->dbReturn($q))) {
								// read edit access data fields and drop them into the unit access array
								$q_sub = "SELECT name, value "
									. "FROM edit_access_data "
									. "WHERE enabled = 1 AND edit_access_id = " . $res['edit_access_id'];
								$ea_data = [];
								while (is_array($res_sub = $this->dbReturn($q_sub))) {
									$ea_data[$res_sub['name']] = $res_sub['value'];
								}
								// build master unit array
								$unit_access[$res['edit_access_id']] = [
									'id' => $res['edit_access_id'],
									'acl_level' => $res['level'],
									'acl_type' => $res['type'],
									'name' => $res['name'],
									'uid' => $res['uid'],
									'color' => $res['color'],
									'default' => $res['edit_default'],
									'data' => $ea_data
								];
								// set the default unit
								if ($res['edit_default']) {
									$_SESSION['UNIT_DEFAULT'] = $res['edit_access_id'];
								}
								// sub arrays for simple access
								array_push($eauid, $res['edit_access_id']);
								$unit_acl[$res['edit_access_id']] = $res['level'];
							}
							$_SESSION['UNIT'] = $unit_access;
							$_SESSION['UNIT_ACL_LEVEL'] = $unit_acl;
							$_SESSION['EAID'] = $eauid;
						} // user has permission to THIS page
					} // user was not enabled or other login error
					if ($this->login_error && is_array($res)) {
						$login_error_date_first = '';
						if ($res['login_error_count'] == 0) {
							$login_error_date_first = ", login_error_date_first = NOW()";
						}
						// update login error count for this user
						$q = "UPDATE edit_user "
							. "SET login_error_count = login_error_count + 1, "
							. "login_error_date_last = NOW() " . $login_error_date_first . " "
							. "WHERE edit_user_id = " . $res['edit_user_id'];
						$this->dbExec($q);
						// totally lock the user if error max is reached
						if (
							$this->max_login_error_count != -1 &&
							$res['login_error_count'] + 1 > $this->max_login_error_count
						) {
							// do some alert reporting in case this error is too big
							// if strict is set, lock this user
							// this needs manual unlocking by an admin user
							if ($res['strict'] && !in_array($this->username, $this->lock_deny_users)) {
								$q = "UPDATE edit_user SET locked = 1 WHERE edit_user_id = " . $res['edit_user_id'];
							}
						}
					}
				} // user was not found
			} // if not username AND password where given
			// if there was an login error, show login screen
			if ($this->login_error) {
				// reset the perm var, to confirm logout
				$this->permission_okay = false;
			}
		} // if he pressed login at least and is not yet loggined in
	}

	/**
	 * for every page the user access this script checks if he is allowed to do so
	 * @return bool permission okay as true/false
	 */
	public function loginCheckPermissions(): bool
	{
		if ($this->euid && $this->login_error != 103) {
			$q = "SELECT filename "
				. "FROM edit_page ep, edit_page_access epa, edit_group eg, edit_user eu "
				. "WHERE ep.edit_page_id = epa.edit_page_id "
				. "AND eg.edit_group_id = epa.edit_group_id "
				. "AND eg.edit_group_id = eu.edit_group_id "
				. "AND eu.edit_user_id = " . $this->euid . " "
				. "AND filename = '" . $this->page_name . "' "
				. "AND eg.enabled = 1 AND epa.enabled = 1";
			$res = $this->dbReturnRow($q);
			if (!is_array($res)) {
				$this->login_error = 109;
				$this->permission_okay = false;
				return $this->permission_okay;
			}
			if (isset($res['filename']) && $res['filename'] == $this->page_name) {
				$this->permission_okay = true;
			} else {
				$this->login_error = 103;
				$this->permission_okay = false;
			}
		}
		// if called from public, so we can check if the permissions are ok
		return $this->permission_okay;
	}

	/**
	 * if a user pressed on logout, destroyes session and unsets all global vars
	 * @return void has no return
	 */
	public function loginLogoutUser(): void
	{
		if ($this->logout || $this->login_error) {
			// unregister and destroy session vars
			foreach (
				// TODO move this into some global array for easier update
				[
					'ADMIN',
					'BASE_ACL_LEVEL',
					'DB_DEBUG',
					'DEBUG_ALL',
					'DEFAULT_ACL_LIST',
					'DEFAULT_CHARSET',
					'DEFAULT_LANG',
					'EAID',
					'EUID',
					'GROUP_ACL_LEVEL',
					'GROUP_ACL_TYPE',
					'GROUP_NAME',
					'HEADER_COLOR',
					'LANG',
					'PAGES_ACL_LEVEL',
					'PAGES',
					'TEMPLATE',
					'UNIT_ACL_LEVEL',
					'UNIT_DEFAULT',
					'UNIT',
					'USER_ACL_LEVEL',
					'USER_ACL_TYPE',
					'USER_NAME',
				] as $session_var
			) {
				unset($_SESSION[$session_var]);
			}
			// final unset all
			session_unset();
			// final destroy session
			session_destroy();
			// then prints the login screen again
			$this->permission_okay = false;
		}
	}

	/**
	 * sets all the basic ACLs
	 * init set the basic acl the user has, based on the following rules
	 * - init set from config DEFAULT ACL
	 * - if page ACL is set, it overrides the default ACL
	 * - if group ACL is set, it overrides the page ACL
	 * - if user ACL is set, it overrides the group ACL
	 * set the page ACL
	 * - default ACL set
	 * - set group ACL if not default overrides default ACL
	 * - set page ACL if not default overrides group ACL
	 * set edit access ACL and set default edit access group
	 * - if an account ACL is set, set this parallel, account ACL overrides user ACL if it applies
	 * - if edit access ACL level is set, use this, else use page
	 * set all base ACL levels as a list keyword -> ACL number
	 * @return void has no return
	 */
	private function loginSetAcl(): void
	{
		// only set acl if we have permission okay
		if ($this->permission_okay) {
			// username (login), group name
			$this->acl['user_name'] = $_SESSION['USER_NAME'];
			$this->acl['group_name'] = $_SESSION['GROUP_NAME'];
			// we start with the default acl
			$this->acl['base'] = DEFAULT_ACL_LEVEL;

			// set admin flag and base to 100
			if ($_SESSION['ADMIN']) {
				$this->acl['admin'] = 1;
				$this->acl['base'] = 100;
			} else {
				$this->acl['admin'] = 0;
				// now go throw the flow and set the correct ACL
				// user > page > group
				// group ACL 0
				if ($_SESSION['GROUP_ACL_LEVEL'] != -1) {
					$this->acl['base'] = $_SESSION['GROUP_ACL_LEVEL'];
				}
				// page ACL 1
				if ($_SESSION['PAGES_ACL_LEVEL'][$this->page_name] != -1) {
					$this->acl['base'] = $_SESSION['PAGES_ACL_LEVEL'][$this->page_name];
				}
				// user ACL 2
				if ($_SESSION['USER_ACL_LEVEL'] != -1) {
					$this->acl['base'] = $_SESSION['USER_ACL_LEVEL'];
				}
			}
			$_SESSION['BASE_ACL_LEVEL'] = $this->acl['base'];

			// set the current page acl
			// start with default acl
			// set group if not -1, overrides default
			// set page if not -1, overrides group set
			$this->acl['page'] = DEFAULT_ACL_LEVEL;
			if ($_SESSION['GROUP_ACL_LEVEL'] != -1) {
				$this->acl['page'] = $_SESSION['GROUP_ACL_LEVEL'];
			}
			if (
				isset($_SESSION['PAGES_ACL_LEVEL'][$this->page_name]) &&
				$_SESSION['PAGES_ACL_LEVEL'][$this->page_name] != -1
			) {
				$this->acl['page'] = $_SESSION['PAGES_ACL_LEVEL'][$this->page_name];
			}

			// PER ACCOUNT (UNIT/edit access)->
			foreach ($_SESSION['UNIT'] as $ea_id => $unit) {
				// if admin flag is set, all units are set to 100
				if ($this->acl['admin']) {
					$this->acl['unit'][$ea_id] = $this->acl['base'];
				} else {
					if ($unit['acl_level'] != -1) {
						$this->acl['unit'][$ea_id] = $unit['acl_level'];
					} else {
						$this->acl['unit'][$ea_id] = $this->acl['base'];
					}
				}
				// detail name/level set
				$this->acl['unit_detail'][$ea_id] = [
					'name' => $unit['name'],
					'uid' => $unit['uid'],
					'level' => $this->default_acl_list[$this->acl['unit'][$ea_id]]['name'],
					'default' => $unit['default'],
					'data' => $unit['data']
				];
				// set default
				if ($unit['default']) {
					$this->acl['unit_id'] = $unit['id'];
					$this->acl['unit_name'] = $unit['name'];
					$this->acl['unit_uid'] = $unit['uid'];
				}
			}
			// flag if to show extra edit access drop downs (because user has multiple groups assigned)
			if (count($_SESSION['UNIT']) > 1) {
				$this->acl['show_ea_extra'] = true;
			} else {
				$this->acl['show_ea_extra'] = false;
			}
			// set the default edit access
			$this->acl['default_edit_access'] = $_SESSION['UNIT_DEFAULT'];
			// integrate the type acl list, but only for the keyword -> level
			foreach ($this->default_acl_list as $level => $data) {
				$this->acl['min'][$data['type']] = $level;
			}
			// set the full acl list too
			$this->acl['acl_list'] = $_SESSION['DEFAULT_ACL_LIST'];
			// debug
			// $this->debug('ACL', $this->print_ar($this->acl));
		}
	}

	/**
	 * checks if this edit access id is valid
	 * @param  int|null $edit_access_id access id pk to check
	 * @return bool                     true/false: if the edit access is not
	 *                                  in the valid list: false
	 */
	public function loginCheckEditAccess($edit_access_id): bool
	{
		if ($edit_access_id === null) {
			return false;
		}
		if (array_key_exists($edit_access_id, $this->acl['unit'])) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * checks if the password is in a valid format
	 * @param  string $password the new password
	 * @return bool             true or false if valid password or not
	 */
	private function loginPasswordChangeValidPassword($password): bool
	{
		$is_valid_password = true;
		// check for valid in regex arrays in list
		if (is_array($this->password_valid_chars)) {
			foreach ($this->password_valid_chars as $password_valid_chars) {
				if (!preg_match("/$password_valid_chars/", $password)) {
					$is_valid_password = false;
				}
			}
		}
		// check for min length
		if (strlen($password) < $this->password_min_length || strlen($password) > $this->password_max_length) {
			$is_valid_password = false;
		}
		return $is_valid_password;
	}

	/**
	 * dummy declare for password forget
	 * @return void has no return
	 */
	private function loginPasswordForgot(): void
	{
		// will do some password recovert, eg send email
	}

	/**
	 * sets the minium length and checks on valid
	 * @param  int  $length set the minimum length
	 * @return bool         true/false on success
	 */
	public function loginSetPasswordMinLength(int $length): bool
	{
		// check that numeric, positive numeric, not longer than max input string lenght
		// and not short than min password length
		if (is_numeric($length) && $length >= PASSWORD_MIN_LENGTH && $length <= $this->password_max_length) {
			$this->password_min_length = $length;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * changes a user password
	 * @return void has no return
	 */
	private function loginPasswordChange(): void
	{
		if ($this->change_password) {
			$event = 'Password Change';
			$data = '';
			// check that given username is NOT in the deny list, else silent skip (with error log)
			if (!in_array($this->pw_username, $this->pw_change_deny_users)) {
				// init the edit user id variable
				$edit_user_id = '';
				// cehck if either username or old password is not set
				if (!$this->pw_username || !$this->pw_old_password) {
					$this->login_error = 200;
					$data = 'Missing username or old password.';
				}
				// check user exist, if not -> error
				if (!$this->login_error) {
					$q = "SELECT edit_user_id "
						. "FROM edit_user "
						. "WHERE enabled = 1 "
						. "AND username = '" . $this->dbEscapeString($this->pw_username) . "'";
					$res = $this->dbReturnRow($q);
					if (
						!is_array($res) ||
						(is_array($res) && empty($res['edit_user_id']))
					) {
						// username wrong
						$this->login_error = 201;
						$data = 'User could not be found';
					}
				}
				// check old passwords match -> error
				if (!$this->login_error) {
					$q = "SELECT edit_user_id, password "
						. "FROM edit_user "
						. "WHERE enabled = 1 "
						. "AND username = '" . $this->dbEscapeString($this->pw_username) . "'";
					$edit_user_id = '';
					$res = $this->dbReturnRow($q);
					if (is_array($res)) {
						$edit_user_id = $res['edit_user_id'];
					}
					if (
						!is_array($res) ||
						(is_array($res) &&
						(empty($res['edit_user_id']) ||
						!$this->loginPasswordCheck($res['old_password_hash'], $this->pw_old_password)))
					) {
						// old password wrong
						$this->login_error = 202;
						$data = 'The old password does not match';
					}
				}
				// check if new passwords were filled out -> error
				if (!$this->login_error) {
					if (!$this->pw_new_password || !$this->pw_new_password_confirm) {
						$this->login_error = 203;
						$data = 'Missing new password or new password confirm.';
					}
				}
				// check new passwords both match -> error
				if (!$this->login_error) {
					if ($this->pw_new_password != $this->pw_new_password_confirm) {
						$this->login_error = 204;
						$data = 'The new passwords do not match';
					}
				}
				// password shall match to something in minimum length or form
				if (!$this->login_error) {
					if (!$this->loginPasswordChangeValidPassword($this->pw_new_password)) {
						$this->login_error = 205;
						$data = 'The new password string is not valid';
					}
				}
				// no error change this users password
				if (!$this->login_error && $edit_user_id) {
					// update the user (edit_user_id) with the new password
					$q = "UPDATE edit_user "
						. "SET password = "
						. "'" . $this->dbEscapeString(Password::passwordSet($this->pw_new_password)) . "' "
						. "WHERE edit_user_id = " . $edit_user_id;
					$this->dbExec($q);
					$data = 'Password change for user "' . $this->pw_username . '"';
					$this->password_change_ok = true;
				}
			} else {
				// illegal user error
				$this->login_error = 220;
				$data = 'Illegal user for password change: ' . $this->pw_username;
			}
			// log this password change attempt
			$this->writeLog($event, $data, $this->login_error, $this->pw_username);
		} // button pressed
	}

	/**
	 * prints out login html part if no permission (error) is set
	 * @return string|null html data for login page, or null for nothing
	 */
	private function loginPrintLogin()
	{
		$html_string = null;
		if (!$this->permission_okay) {
			// set the templates now
			$this->loginSetTemplates();
			// if there is a global logout target ...
			if (file_exists($this->logout_target) && $this->logout_target) {
				$LOGOUT_TARGET = $this->logout_target;
			} else {
				$LOGOUT_TARGET = "";
			}

			$html_string = (string)$this->login_template['template'];

			// if password change is okay
			if ($this->password_change) {
				$html_string_password_change = $this->login_template['password_change'];

				// pre change the data in the PASSWORD_CHANGE_DIV first
				foreach ($this->login_template['strings'] as $string => $data) {
					if ($data) {
						$html_string_password_change = str_replace(
							'{' . $string . '}',
							$data,
							$html_string_password_change
						);
					}
				}
				// print error messagae
				if ($this->login_error) {
					$html_string_password_change = str_replace(
						'{ERROR_MSG}',
						$this->login_error_msg[$this->login_error] . '<br>',
						$html_string_password_change
					);
				} else {
					$html_string_password_change = str_replace(
						'{ERROR_MSG}',
						'<br>',
						$html_string_password_change
					);
				}
				// if pw change action, show the float again
				if ($this->change_password && !$this->password_change_ok) {
					$html_string_password_change = str_replace(
						'{PASSWORD_CHANGE_SHOW}',
						'<script language="JavaScript">'
							. 'ShowHideDiv(\'pw_change_div\');</script>',
						$html_string_password_change
					);
				} else {
					$html_string_password_change = str_replace(
						'{PASSWORD_CHANGE_SHOW}',
						'',
						$html_string_password_change
					);
				}
				$this->login_template['strings']['PASSWORD_CHANGE_DIV'] = $html_string_password_change;
			}

			// put in the logout redirect string
			if ($this->logout && $LOGOUT_TARGET) {
				$html_string = str_replace(
					'{LOGOUT_TARGET}',
					'<meta http-equiv="refresh" content="0; URL=' . $LOGOUT_TARGET . '">',
					$html_string
				);
			} else {
				$html_string = str_replace('{LOGOUT_TARGET}', '', $html_string);
			}

			// print error messagae
			if ($this->login_error) {
				$html_string = str_replace(
					'{ERROR_MSG}',
					$this->login_error_msg[$this->login_error] . '<br>',
					$html_string
				);
			} elseif ($this->password_change_ok && $this->password_change) {
				$html_string = str_replace(
					'{ERROR_MSG}',
					$this->login_error_msg[300] . '<br>',
					$html_string
				);
			} else {
				$html_string = str_replace('{ERROR_MSG}', '<br>', $html_string);
			}

			// create the replace array context
			foreach ($this->login_template['strings'] as $string => $data) {
				$html_string = str_replace('{' . $string . '}', $data, $html_string);
			}
		} // if permission is 0 then print out login
		// return the created HTML here or null for nothing
		return $html_string;
	}

	/**
	 * last function called, writes log and prints out error msg and
	 * exists script if permission 0
	 * @return bool true on permission ok, false on permission wrong
	 */
	private function loginCloseClass(): bool
	{
		// write to LOG table ...
		if ($this->login_error || $this->login || $this->logout) {
			$username = '';
			// $password = '';
			// set event
			if ($this->login) {
				$event = 'Login';
			} elseif ($this->logout) {
				$event = 'Logout';
			} else {
				$event = 'No Permission';
			}
			// prepare for log
			if ($this->euid) {
				// get user from user table
				$q = "SELECT username FROM edit_user WHERE edit_user_id = " . $this->euid;
				$username = '';
				if (is_array($res = $this->dbReturnRow($q))) {
					$username = $res['username'];
				}
			} // if euid is set, get username (or try)
			$this->writeLog($event, '', $this->login_error, $username);
		} // write log under certain settings
		// now close DB connection
		// $this->error_msg = $this->_login();
		if (!$this->permission_okay) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * checks if there are external templates, if not uses internal fallback ones
	 * @return void has no return
	 */
	private function loginSetTemplates(): void
	{
		$strings = [
			'HTML_TITLE' => $this->l->__('LOGIN'),
			'TITLE' => $this->l->__('LOGIN'),
			'USERNAME' => $this->l->__('Username'),
			'PASSWORD' => $this->l->__('Password'),
			'LOGIN' => $this->l->__('Login'),
			'ERROR_MSG' => '',
			'LOGOUT_TARGET' => '',
			'PASSWORD_CHANGE_BUTTON_VALUE' => $this->l->__('Change Password')
		];

		$error_msgs = [
			// actually obsolete
			'100' => $this->l->__('Fatal Error: <b>[EUID] came in as GET/POST!</b>'),
			// query errors
			'1009' => $this->l->__('Fatal Error: <b>Login query reading failed<b>'),
			// user not found
			'1010' => $this->l->__('Fatal Error: <b>Login Failed - Wrong Username or Password</b>'),
			// blowfish password wrong
			'1011' => $this->l->__('Fatal Error: <b>Login Failed - Wrong Username or Password</b>'),
			// fallback md5 password wrong
			'1012' => $this->l->__('Fatal Error: <b>Login Failed - Wrong Username or Password</b>'),
			// new password_hash wrong
			'1013' => $this->l->__('Fatal Error: <b>Login Failed - Wrong Username or Password</b>'),
			'102' => $this->l->__('Fatal Error: <b>Login Failed - Please enter username and password</b>'),
			'103' => $this->l->__('Fatal Error: <b>You do not have the rights to access this Page</b>'),
			'104' => $this->l->__('Fatal Error: <b>Login Failed - User not enabled</b>'),
			'105' => $this->l->__('Fatal Error: <b>Login Failed - User is locked</b>'),
			'109' => $this->l->__('Fatal Error: <b>Check permission query reading failed</b>'),
			// actually this is an illegal user, but I mask it
			'220' => $this->l->__('Fatal Error: <b>Password change - The user could not be found</b>'),
			'200' => $this->l->__('Fatal Error: <b>Password change - Please enter username and old password</b>'),
			'201' => $this->l->__('Fatal Error: <b>Password change - The user could not be found</b>'),
			'202' => $this->l->__('Fatal Error: <b>Password change - The old password is not correct</b>'),
			'203' => $this->l->__('Fatal Error: <b>Password change - Please fill out both new password fields</b>'),
			'204' => $this->l->__('Fatal Error: <b>Password change - The new passwords do not match</b>'),
			// we should also not here WHAT is valid
			'205' => $this->l->__('Fatal Error: <b>Password change - The new password is not in a valid format</b>'),
			// for OK password change
			'300' => $this->l->__('Success: <b>Password change successful</b>'),
			// this is bad bad error
			'9999' => $this->l->__('Fatal Error: <b>necessary crypt engine could not be found</b>. '
				. 'Login is impossible'),
		];

		// if password change is okay
		if ($this->password_change) {
			$strings = array_merge($strings, [
				'TITLE_PASSWORD_CHANGE' => 'Change Password for User',
				'OLD_PASSWORD' => $this->l->__('Old Password'),
				'NEW_PASSWORD' => $this->l->__('New Password'),
				'NEW_PASSWORD_CONFIRM' => $this->l->__('New Password confirm'),
				'CLOSE' => $this->l->__('Close'),
				'JS_SHOW_HIDE' => "function ShowHideDiv(id) { "
						. "element = document.getElementById(id); "
						. "if (element.className == 'visible' || !element.className) element.className = 'hidden'; "
						. "else element.className = 'visible'; }",
				'PASSWORD_CHANGE_BUTTON' => '<input type="button" name="pw_change" value="'
					. $strings['PASSWORD_CHANGE_BUTTON_VALUE']
					. '" OnClick="ShowHideDiv(\'pw_change_div\');">'
			]);
			// NOTE: for the HTML block I ignore line lengths
			// phpcs:disable
			$this->login_template['password_change'] = <<<EOM
<div id="pw_change_div" class="hidden" style="position: absolute; top: 30px; left: 50px; width: 400px; height: 220px; background-color: white; border: 1px solid black; padding: 25px;">
<table>
<tr><td class="norm" align="center" colspan="2"><h3>{TITLE_PASSWORD_CHANGE}</h3></td></tr>
<tr><td class="norm" colspan="2">{ERROR_MSG}</td></tr>
<tr><td class="norm" align="right">{USERNAME}</td><td><input type="text" name="pw_username" value=""></td></tr>
<tr><td class="norm" align="right">{OLD_PASSWORD}</td>
<td><input type="password" name="pw_old_password" value=""></td></tr>
<tr><td class="norm" align="right">{NEW_PASSWORD}</td>
<td><input type="password" name="pw_new_password" value=""></td></tr>
<tr><td class="norm" align="right">{NEW_PASSWORD_CONFIRM}</td>
<td><input type="password" name="pw_new_password_confirm" value=""></td></tr>
<tr><td></td>
<td><input type="submit" name="change_password" value="{PASSWORD_CHANGE_BUTTON_VALUE}">
<input type="button" name="pw_change" value="{CLOSE}" OnClick="ShowHideDiv('pw_change_div');"></td></tr>
</table>
</div>
{PASSWORD_CHANGE_SHOW}
EOM;
			// phpcs:enable
		}
		if ($this->password_forgot) {
		}
		if (!$this->password_change && !$this->password_forgot) {
			$strings = array_merge($strings, [
				'JS_SHOW_HIDE' => '',
				'PASSWORD_CHANGE_BUTTON' => '',
				'PASSWORD_CHANGE_DIV' => ''
			]);
		}

		// first check if all strings are set from outside, if not, set with default ones
		foreach ($strings as $string => $data) {
			if (!array_key_exists($string, $this->login_template['strings'])) {
				$this->login_template['strings'][$string] = $data;
			}
		}

		// error msgs the same
		foreach ($error_msgs as $code => $data) {
			if (!array_key_exists($code, $this->login_error_msg)) {
				$this->login_error_msg[$code] = $data;
			}
		}

		// now check templates
		if (!$this->login_template['template']) {
			$this->login_template['template'] = <<<EOM
<html>
<head>
<title>{HTML_TITLE}</title>
<style type="text/css">
.norm { font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 10px; line-height: 15px; color: #000000}
h3 { font-size: 18px; }
.visible { visibility: visible; }
.hidden { visibility: hidden; display: none; }
</style>
<script language="JavaScript">
<!--
{JS_SHOW_HIDE}
//-->
</script>
{LOGOUT_TARGET}
</head>

<body bgcolor="#FFFFFF">
<br>
<br>
<br>
<form method="post">
<table width="500" border="0" cellpadding="2" cellspacing="1">
<tr>
<td class="norm" align="right">
	<h3>{TITLE}</h3>
</td>
<td>&nbsp;</td>
</tr>
<tr>
<td class="norm" colspan="2" align="center">
	{ERROR_MSG}
</td>
</tr>
<tr>
<td align="right" class="norm">{USERNAME}</td>
<td><input type="text" name="login_username"></td>
</tr>
<tr>
<td align="right" class="norm">{PASSWORD}</td>
<td><input type="password" name="login_password"></td>
</tr>
<tr>
<td align="right"></td>
<td>
	<input type="submit" name="login_login" value="{LOGIN}">
	{PASSWORD_CHANGE_BUTTON}
</td>
</tr>
<tr>
<td align="right">
	<br><br>
</td>
<td>&nbsp;</td>
</tr>
</table>
{PASSWORD_CHANGE_DIV}
</form>
</body>
</html>
EOM;
		}
	}

	/**
	 * writes detailed data into the edit user log table (keep log what user does)
	 * @param  string     $event    string of what has been done
	 * @param  string     $data     data information (id, etc)
	 * @param  string|int $error    error id (mostly an int)
	 * @param  string     $username login user username
	 * @return void                 has no return
	 */
	private function writeLog(string $event, string $data, $error = '', string $username = ''): void
	{
		if ($this->login) {
				$this->action = 'Login';
		} elseif ($this->logout) {
				$this->action = 'Logout';
		} else {
			$this->action = '';
		}
		$_data_binary = [
				'_SESSION' => $_SESSION,
				'_GET' => $_GET,
				'_POST' => $_POST,
				'_FILES' => $_FILES,
				'error' => $this->login_error
		];
		$data_binary = $this->dbEscapeBytea((string)bzcompress(serialize($_data_binary)));
		// SQL querie for log entry
		$q = "INSERT INTO edit_log "
			. "(username, password, euid, event_date, event, error, data, data_binary, page, "
			. "ip, user_agent, referer, script_name, query_string, server_name, http_host, "
			. "http_accept, http_accept_charset, http_accept_encoding, session_id, "
			. "action, action_id, action_yes, action_flag, action_menu, action_loaded, "
			. "action_value, action_error) "
			. "VALUES ('" . $this->dbEscapeString($username) . "', 'PASSWORD', "
			. ($this->euid ? $this->euid : 'NULL') . ", "
			. "NOW(), '" . $this->dbEscapeString($event) . "', "
			. "'" . $this->dbEscapeString((string)$error) . "', "
			. "'" . $this->dbEscapeString($data) . "', '" . $data_binary . "', "
			. "'" . $this->page_name . "', ";
		foreach (
			[
				'REMOTE_ADDR', 'HTTP_USER_AGENT', 'HTTP_REFERER', 'SCRIPT_FILENAME',
				'QUERY_STRING', 'SERVER_NAME', 'HTTP_HOST', 'HTTP_ACCEPT',
				'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING'
			] as $server_code
		) {
			if (array_key_exists($server_code, $_SERVER)) {
				$q .= "'" . $this->dbEscapeString($_SERVER[$server_code]) . "', ";
			} else {
				$q .= "NULL, ";
			}
		}
		$q .= "'" . Session::getSessionId() . "', ";
		$q .= "'" . $this->dbEscapeString($this->action) . "', ";
		$q .= "'" . $this->dbEscapeString($this->username) . "', ";
		$q .= "NULL, ";
		$q .= "'" . $this->dbEscapeString((string)$this->login_error) . "', ";
		$q .= "NULL, NULL, ";
		$q .= "'" . $this->dbEscapeString((string)$this->permission_okay) . "', ";
		$q .= "NULL)";
		$this->dbExec($q, 'NULL');
	}

	/**
	 * checks that the given edit access id is valid for this user
	 * @param  int|null $edit_access_id edit access id to check
	 * @return int|null                 same edit access id if ok
	 *                                  or the default edit access id if given one is not valid
	 */
	public function loginCheckEditAccessId(?int $edit_access_id): ?int
	{
		if (
			$edit_access_id !== null &&
			isset($_SESSION['UNIT']) &&
			is_array($_SESSION['UNIT']) &&
			!array_key_exists($edit_access_id, $_SESSION['UNIT'])
		) {
			return (int)$_SESSION['UNIT_DEFAULT'];
		} else {
			return $edit_access_id;
		}
	}

	/**
	 * retunrn a set entry from the UNIT session for an edit access_id
	 * if not found return false
	 * @param  int        $edit_access_id edit access id
	 * @param  string|int $data_key       key value to search for
	 * @return bool|string                false for not found or string for found data
	 */
	public function loginSetEditAccessData(int $edit_access_id, $data_key)
	{
		if (!isset($_SESSION['UNIT'][$edit_access_id]['data'][$data_key])) {
			return false;
		} else {
			return $_SESSION['UNIT'][$edit_access_id]['data'][$data_key];
		}
	}
	// close class
}

// __END__

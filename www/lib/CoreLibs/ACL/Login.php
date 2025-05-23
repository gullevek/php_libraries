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
* TODO: Update session var info
*     [DEPRECATED] DEBUG_ALL - set to one, prints out error_msg var at end of php execution
*     [DEPRECATED] DB_DEBUG - prints out database debugs (query, etc)
*     [REMOVED] LOGIN_GROUP_LEVEL - the level he can access (numeric)
*     LOGIN_USER_NAME - login name from user
*     [DEPRECATED] LANG - lang to show edit interface (not yet used)
*     DEFAULT_CHARSET - in connection with LANG (not yet used)
*     LOGIN_PAGES - array of hashes
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

use CoreLibs\Security\Password;
use CoreLibs\Create\Uids;
use CoreLibs\Convert\Json;

class Login
{
	/** @var ?int the user id var*/
	private ?int $edit_user_id;
	/** @var ?string the user cuid (note will be super seeded with uuid v4 later) */
	private ?string $edit_user_cuid;
	/** @var ?string UUIDv4, will superseed the eucuid and replace euid as login id */
	private ?string $edit_user_cuuid;
	/** @var string _GET/_POST loginUserId parameter for non password login */
	private string $login_user_id = '';
	/** @var string source, either _GET or _POST or empty */
	private string $login_user_id_source = '';
	/** @var bool set to true if illegal characters where found in the login user id string */
	private bool $login_user_id_unclear = false;
	// is set to one if login okay, or EUCUUID is set and user is okay to access this page
	/** @var bool */
	private bool $permission_okay = false;
	/** @var string pressed login */
	private string $login = '';
	/** @var string master action command */
	private string $action;
	/** @var string login name */
	private string $username;
	/** @var string login password */
	private string $password;
	/** @var string logout button */
	private string $logout;
	/** @var bool if this is set to true, the user can change passwords */
	private bool $password_change = false;
	/** @var bool password change was successful */
	private bool $password_change_ok = false;
	// can we reset password and mail to user with new password set screen
	/** @var bool */
	private bool $password_forgot = false;
	/** @var bool password forgot mail send ok */
	// private $password_forgot_ok = false;
	/** @var string */
	private string $change_password;
	/** @var string */
	private string $pw_username;
	/** @var string */
	private string $pw_old_password;
	/** @var string */
	private string $pw_new_password;
	/** @var string */
	private string $pw_new_password_confirm;
	/** @var array<string> array of users for which the password change is forbidden */
	private array $pw_change_deny_users = [];
	/** @var string */
	private string $logout_target = '';
	/** @var int */
	private int $max_login_error_count = -1;
	/** @var array<string> */
	private array $lock_deny_users = [];
	/** @var string */
	private string $page_name = '';

	/** @var int if we have password change we need to define some rules */
	private int $password_min_length = 9;
	/** @var int an true maxium min, can never be set below this */
	private int $password_min_length_max = 9;
	// max length is fixed as 255 (for input type max), if set highter
	// it will be set back to 255
	/** @var int */
	private int $password_max_length = 255;

	/** @var int minum password length */
	public const PASSWORD_MIN_LENGTH = 9;
	/** @var int maxium password lenght */
	public const PASSWORD_MAX_LENGTH = 255;
	/** @var string special characters for regex */
	public const PASSWORD_SPECIAL_RANGE = '@$!%*?&';
	/** @var string regex for lower case alphabet */
	public const PASSWORD_LOWER = '(?=.*[a-z])';
	/** @var string regex for upper case alphabet */
	public const PASSWORD_UPPER = '(?=.*[A-Z])';
	/** @var string regex for numbers */
	public const PASSWORD_NUMBER = '(?=.*\d)';
	/** @var string regex for special chanagers */
	public const PASSWORD_SPECIAL = "(?=.*[" . self::PASSWORD_SPECIAL_RANGE . "])";
	/** @var string regex for fixed allowed characters password regex */
	public const PASSWORD_REGEX = "/^"
		. self::PASSWORD_LOWER
		. self::PASSWORD_UPPER
		. self::PASSWORD_NUMBER
		. self::PASSWORD_SPECIAL
		. "[A-Za-z\d" . self::PASSWORD_SPECIAL_RANGE . "]"
		. "{" . self::PASSWORD_MIN_LENGTH . "," . self::PASSWORD_MAX_LENGTH . "}"
		. "$/";

	/** @var array<string> can have several regexes, if nothing set, all is ok */
	private array $password_valid_chars = [
		// '^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%]{8,}$',
		// '^(?.*(\pL)u)(?=.*(\pN)u)(?=.*([^\pL\pN])u).{8,}',
	];

	// login error code, can be matched to the array login_error_msg,
	// which holds the string
	/** @var int */
	private int $login_error = 0;
	/** @var array<mixed> all possible login error conditions */
	private array $login_error_msg = [];
	// this is an array holding all strings & templates passed
	// rom the outside (translation)
	/** @var array<mixed> */
	private array $login_template = [
		'strings' => [],
		'password_change' => '',
		'password_forgot' => '',
		'template' => ''
	];

	// acl vars
	/** @var array<mixed> */
	private array $acl = [];
	/** @var array<mixed> */
	private array $default_acl_list = [];
	/** @var array<string,int> Reverse list to lookup level from type */
	private array $default_acl_list_type = [];
	/** @var int default ACL level to be based on if nothing set */
	private int $default_acl_level = 0;
	// login html, if we are on an ajax page
	/** @var string|null */
	private ?string $login_html = '';
	/** @var bool */
	private bool $login_is_ajax_page = false;

	// logging
	/** @var array<string> list of allowed types for edit log write */
	private const WRITE_TYPES = ['BINARY', 'BZIP2', 'LZIP', 'STRING', 'SERIAL', 'JSON'];
	/** @var array<string> list of available write types for log */
	private array $write_types_available = [];

	// settings
	/** @var array<string,mixed> options */
	private array $options = [];
	/** @var array<string,string> locale options: locale, domain, encoding (opt), path */
	private array $locale = [
		'locale' => '',
		'domain' => '',
		'encoding' => '',
		'path' => '',
	];

	/** @var int resync interval time in minutes */
	private const DEFAULT_AUTH_RESYNC_INTERVAL = 5 * 60;
	/** @var int the session max garbage collection life time */
	// private const DEFAULT_SESSION_GC_MAXLIFETIME = ;
	private int $default_session_gc_maxlifetime;
	/** @var int in how many minutes an auth resync is done */
	private int $auth_resync_interval;
	/** @var bool set the enhanced header security */
	private bool $header_enhance_security = false;

	/** @var \CoreLibs\Logging\Logging logger */
	public \CoreLibs\Logging\Logging $log;
	/** @var \CoreLibs\DB\IO database */
	public \CoreLibs\DB\IO $db;
	/** @var \CoreLibs\Language\L10n language */
	public \CoreLibs\Language\L10n $l;
	/** @var \CoreLibs\Create\Session session class */
	public \CoreLibs\Create\Session $session;

	/**
	 * constructor, does ALL, opens db, works through connection checks,
	 * finishes itself
	 *
	 * @param \CoreLibs\DB\IO           $db      Database connection class
	 * @param \CoreLibs\Logging\Logging $log     Logging class
	 * @param \CoreLibs\Create\Session  $session Session interface class
	 * @param array<string,mixed>       $options Login ACL settings
	 */
	public function __construct(
		\CoreLibs\DB\IO $db,
		\CoreLibs\Logging\Logging $log,
		\CoreLibs\Create\Session $session,
		array $options = [],
	) {
		// attach db class
		$this->db = $db;
		// attach logger
		$this->log = $log;
		// attach session class
		$this->session = $session;

		$this->default_session_gc_maxlifetime = (int)ini_get("session.gc_maxlifetime");

		// set and check options
		if (false === $this->loginSetOptions($options)) {
			// on failure, exit
			echo "<b>Could not set options</b>";
			$this->loginTerminate('Could not set options', 3000);
		}
		// init error array
		$this->loginInitErrorMessages();
		// acess right list
		$this->loginLoadAccessRightList();
		// log allowed write flags
		$this->loginSetEditLogWriteTypeAvailable();

		// this will be deprecated
		if ($this->options['auto_login'] === true) {
			$this->loginMainCall();
		}
	}

	// *************************************************************************
	// **** MARK: PROTECTED INTERNAL
	// *************************************************************************

	/**
	 * Wrapper for exit calls
	 *
	 * @param  string $message [='']
	 * @param  int    $code [=0]
	 * @return void
	 */
	protected function loginTerminate(string $message = '', int $code = 0): void
	{
		// all below 1000 are info end, all above 1000 are critical -> should throw exception?
		if ($code < 1000) {
			$this->log->info($message, ['code' => $code]);
		} else {
			$this->log->critical($message, ['code' => $code]);
		}
		// TODO throw error and not exit
		exit($code);
	}

	/**
	 * return current page name
	 *
	 * @return string Current page name
	 */
	protected function loginReadPageName(): string
	{
		// set internal page name as is
		return \CoreLibs\Get\System::getPageName();
	}

	/**
	 * print out login HTML via echo
	 *
	 * @return void
	 */
	protected function loginPrintLogin(): void
	{
		echo $this->loginGetLoginHTML();
	}

	// *************************************************************************
	// **** MARK: PRIVATE INTERNAL
	// *************************************************************************

	/**
	 * Set options
	 * Current allowed:
	 * target <string>: site target
	 * debug <bool>
	 * auto_login <bool>: self start login process
	 * db_schema <string>
	 * password_min_length <int>
	 * default_acl_level <int>
	 * logout_target <string>: should default be '' or target path to where
	 * can_change <bool>: can change password (NOT IMPLEMENTED)
	 * forget_flow <boo>: reset password on forget (NOT IMPLEMENTED)
	 * locale_path <string>: absolue path to the locale folder
	 * site_locale <string>: what locale to load
	 * site_domain <string>: what domain (locale file name) to use
	 *
	 * @param  array<string,mixed> $options Options array from class load
	 * @return bool                         True on ok, False on failure
	 */
	private function loginSetOptions(array $options): bool
	{
		// target and debug flag
		if (
			empty($options['target'])
		) {
			$options['target'] = 'test';
		}
		if (
			empty($options['debug']) ||
			!is_bool($options['debug'])
		) {
			$options['debug'] = false;
		}

		// AUTO LOGIN
		if (
			!isset($options['auto_login']) ||
			!is_bool($options['auto_login'])
		) {
			// if set to true will run login call during class construction
			$options['auto_login'] = false;
		}

		// DB SCHEMA
		if (
			empty($options['db_schema']) ||
			// TODO more strict check
			is_string($options['db_schema'])
		) {
			// get scham from db, else fallback to public
			if (!empty($this->db->dbGetSchema(true))) {
				$options['db_schema'] = $this->db->dbGetSchema(true);
			} else {
				$options['db_schema'] = 'public';
			}
		}
		if ($this->db->dbGetSchema() != $options['db_schema']) {
			$this->db->dbSetSchema($options['db_schema']);
		}

		// MIN PASSWORD LENGTH
		// can only be in length of current defined min/max
		if (
			!empty($options['password_min_lenght']) &&
			!is_numeric($options['password_min_length']) &&
			$options['password_min_length'] >= self::PASSWORD_MIN_LENGTH &&
			$options['password_min_length'] <= self::PASSWORD_MAX_LENGTH
		) {
			if (
				false === $this->loginSetPasswordMinLength(
					(int)$options['password_min_length']
				)
			) {
				$options['password_min_length'] = self::PASSWORD_MIN_LENGTH;
			}
		}

		// DEFAULT ACL LEVEL
		if (
			!isset($options['default_acl_level']) ||
			!is_numeric($options['default_acl_level']) ||
			$options['default_acl_level'] < 0 || $options['default_acl_level'] > 100
		) {
			$options['default_acl_level'] = 0;
			if (defined('DEFAULT_ACL_LEVEL')) {
				trigger_error(
					'loginMainCall: DEFAULT_ACL_LEVEL should not be used',
					E_USER_DEPRECATED
				);
				$options['default_acl_level'] = DEFAULT_ACL_LEVEL;
			}
		}
		$this->default_acl_level = (int)$options['default_acl_level'];

		// LOGOUT TARGET
		if (!isset($options['logout_target'])) {
			// defaults to ''
			$options['logout_target'] = '';
			$this->logout_target = $options['logout_target'];
		}

		// *** PASSWORD SETTINGS
		// User can change password
		if (
			!isset($options['can_change']) ||
			!is_bool($options['can_change'])
		) {
			$options['can_change'] = false;
		}
		$this->password_change = $options['can_change'];
		// User can trigger a forgot password flow
		if (
			!isset($options['forgot_flow']) ||
			!is_bool($options['forgot_flow'])
		) {
			$options['forgot_flow'] = false;
		}
		$this->password_forgot = $options['forgot_flow'];

		// sync _SESSION acl settings
		if (
			!isset($options['auth_resync_interval']) ||
			!is_numeric($options['auth_resync_interval']) ||
			$options['auth_resync_interval'] < 0 ||
			$options['auth_resync_interval'] > $this->default_session_gc_maxlifetime
		) {
			// default 5 minutues
			$options['auth_resync_interval'] = self::DEFAULT_AUTH_RESYNC_INTERVAL;
		} else {
			$options['auth_resync_interval'] = (int)$options['auth_resync_interval'];
		}
		$this->auth_resync_interval = $options['auth_resync_interval'];

		// *** LANGUAGE
		// LANG: LOCALE PATH
		if (empty($options['locale_path'])) {
			// trigger deprecation error
			trigger_error(
				'loginSetOptions: misssing locale_path entry is deprecated',
				E_USER_DEPRECATED
			);
			// set path
			$options['locale_path'] = BASE . INCLUDES . LOCALE;
		}
		// LANG: LOCALE
		if (empty($options['site_locale'])) {
			trigger_error(
				'loginMainCall: SITE_LOCALE should not be used',
				E_USER_DEPRECATED
			);
			$options['site_locale'] = defined('SITE_LOCALE') && !empty(SITE_LOCALE) ?
				SITE_LOCALE : 'en.UTF-8';
		}
		// LANG: DOMAIN
		if (empty($options['site_domain'])) {
			// we need to get domain set from outside
			$options['site_domain'] = 'admin';
			if (
				defined('SITE_DOMAIN')
			) {
				// trigger deprecation error
				trigger_error(
					'loginSetOptions: misssing site_domain entry is deprecated (SITE_DOMAIN)',
					E_USER_DEPRECATED
				);
				// set domain
				$options['site_domain'] = SITE_DOMAIN;
			} elseif (
				defined('CONTENT_PATH')
			) {
				// trigger deprecation error
				trigger_error(
					'loginSetOptions: misssing site_domain entry is deprecated (CONTENT_PATH)',
					E_USER_DEPRECATED
				);
				$options['set_domain'] = str_replace(DIRECTORY_SEPARATOR, '', CONTENT_PATH);
			}
		}
		// LANG: ENCODING
		if (empty($options['site_encoding'])) {
			trigger_error(
				'loginMainCall: SITE_ENCODING should not be used',
				E_USER_DEPRECATED
			);
			$options['site_encoding'] = defined('SITE_ENCODING') && !empty(SITE_ENCODING) ?
				SITE_ENCODING : 'UTF-8';
		}
		// set enhancded security flag
		if (
			empty($options['enhanced_security']) ||
			!is_bool($options['enhanced_security'])
		) {
			$options['enhanced_security'] = true;
		}
		$this->header_enhance_security = $options['enhanced_security'];

		// write array to options
		$this->options = $options;
		return true;
	}

	/**
	 * sets the login error message array
	 *
	 * @return void
	 */
	private function loginInitErrorMessages()
	{
		// string key, msg: string, flag: e (error), o (ok)
		$this->login_error_msg = [
			'0' => [
				'msg' => 'No error',
				'flag' => 'o'
			],
			// actually obsolete
			'100' => [
				'msg' => '[EUCUUID] set from GET/POST!',
				'flag' => 'e',
			],
			// query errors
			'1009' => [
				'msg' => 'Login query reading failed',
				'flag' => 'e',
			],
			// user not found
			'1010' => [
				'msg' => 'Login Failed - Wrong Username or Password',
				'flag' => 'e'
			],
			// general login error
			'1011' => [
				'msg' => 'Login Failed - General authentication error',
				'flag' => 'e'
			],
			// fallback md5 password wrong
			'1012' => [
				'msg' => 'Login Failed - Wrong Username or Password',
				'flag' => 'e'
			],
			// new password_hash wrong
			'1013' => [
				'msg' => 'Login Failed - Wrong Username or Password',
				'flag' => 'e'
			],
			'1101' => [
				'msg' => 'Login Failed - Login User ID must be validated',
				'flag' => 'e'
			],
			'1102' => [
				'msg' => 'Login Failed - Login User ID is outside valid date range',
				'flag' => 'e'
			],
			'102' => [
				'msg' => 'Login Failed - Please enter username and password',
				'flag' => 'e'
			],
			'103' => [
				'msg' => 'You do not have the rights to access this Page',
				'flag' => 'e'
			],
			'104' => [
				'msg' => 'Login Failed - User not enabled',
				'flag' => 'e'
			],
			'105' => [
				'msg' => 'Login Failed - User is locked',
				'flag' => 'e'
			],
			'106' => [
				'msg' => 'Login Failed - User is deleted',
				'flag' => 'e'
			],
			'107' => [
				'msg' => 'Login Failed - User in locked via date period',
				'flag' => 'e'
			],
			'108' => [
				'msg' => 'Login Failed - User is locked via Login User ID',
				'flag' => 'e'
			],
			'109' => [
				'msg' => 'Check permission query reading failed',
				'flag' => 'e'
			],
			'110' =>  [
				'msg' => 'Forced logout',
				'flag' => '',
			],
			// actually this is an illegal user, but I mask it
			'220' => [
				'msg' => 'Password change - The user could not be found',
				'flag' => 'e'
			],
			'200' => [
				'msg' => 'Password change - Please enter username and old password',
				'flag' => 'e'
			],
			'201' => [
				'msg' => 'Password change - The user could not be found',
				'flag' => 'e'
			],
			'202' => [
				'msg' => 'Password change - The old password is not correct',
				'flag' => 'e'
			],
			'203' => [
				'msg' => 'Password change - Please fill out both new password fields',
				'flag' => 'e'
			],
			'204' => [
				'msg' => 'Password change - The new passwords do not match',
				'flag' => 'e'
			],
			// we should also not here WHAT is valid
			'205' => [
				'msg' => 'Password change - The new password is not in a valid format',
				'flag' => 'e'
			],
			// for OK password change
			'300' => [
				'msg' => 'Password change successful',
				'flag' => 'o'
			],
			// this is bad bad error
			'9999' => [
				'msg' => 'Necessary crypt engine could not be found. Login is impossible',
				'flag' => 'e'
			],
		];
	}

	/**
	 * loads the access right list from the database
	 *
	 * @return void
	 */
	private function loginLoadAccessRightList(): void
	{
		// read the current edit_access_right list into an array
		$q = <<<SQL
		SELECT
			level, type, name
		FROM
			edit_access_right
		WHERE
			level >= 0
		ORDER BY
			level
		SQL;
		while (is_array($res = $this->db->dbReturn($q))) {
			// level to description format (numeric)
			$this->default_acl_list[$res['level']] = [
				'type' => $res['type'],
				'name' => $res['name']
			];
			$this->default_acl_list_type[(string)$res['type']] = (int)$res['level'];
		}
		// write that into the session
		$this->session->setMany([
			'LOGIN_DEFAULT_ACL_LIST' => $this->default_acl_list,
			'LOGIN_DEFAULT_ACL_LIST_TYPE' => $this->default_acl_list_type,
		]);
	}

	/**
	 * Improves the application's security over HTTP(S) by setting specific headers
	 *
	 * @return void
	 */
	protected function loginEnhanceHttpSecurity(): void
	{
		// skip if not wanted
		if (!$this->header_enhance_security) {
			return;
		}
		// remove exposure of PHP version (at least where possible)
		header_remove('X-Powered-By');
		// if the user is signed in
		if ($this->permission_okay) {
			// prevent clickjacking
			header('X-Frame-Options: sameorigin');
			// prevent content sniffing (MIME sniffing)
			header('X-Content-Type-Options: nosniff');

			// disable caching of potentially sensitive data
			header('Cache-Control: no-store, no-cache, must-revalidate', true);
			header('Expires: Thu, 19 Nov 1981 00:00:00 GMT', true);
			header('Pragma: no-cache', true);
		}
	}

	// MARK: validation checks

	/**
	 * Checks for all flags and sets error codes for each
	 * In order:
	 * delete > enable > lock > period lock > login user id lock
	 *
	 * @param  int  $deleted              User deleted check
	 * @param  int  $enabled              User not enabled check
	 * @param  int  $locked               Locked because of too many invalid passwords
	 * @param  int  $locked_period        Locked because of time period set
	 * @param  int  $login_user_id_locked Locked from using Login User Id
	 * @param  int  $force_logout         Force logout counter, if higher than session, permission is false
	 * @return bool
	 */
	private function loginValidationCheck(
		int $deleted,
		int $enabled,
		int $locked,
		int $locked_period,
		int $login_user_id_locked,
		int $force_logout
	): bool {
		$validation = false;
		if ($deleted) {
			// user is deleted
			$this->login_error = 106;
		} elseif (!$enabled) {
			// user is not  enabled
			$this->login_error = 104;
		} elseif ($locked) {
			// user is locked, either set or auto set
			$this->login_error = 105;
		} elseif ($locked_period) {
			// locked date trigger
			$this->login_error = 107;
		} elseif ($login_user_id_locked) {
			// user is locked, either set or auto set
			$this->login_error = 108;
		} elseif ($force_logout > $this->session->get('LOGIN_FORCE_LOGOUT')) {
			$this->login_error = 110;
		} else {
			$validation = true;
		}
		return $validation;
	}

	/**
	 * checks if password is valid, sets internal error login variable
	 *
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
			// below is all deprecated. all the ones below will always be true
			// all the crypt standards are always set
			// FIXME: remove this error code
			/** @phpstan-ignore-next-line Why? */
			(preg_match("/^\\$2(a|y)\\$/", $hash) && CRYPT_BLOWFISH != 1) ||
			/** @phpstan-ignore-next-line Why? */
			(preg_match("/^\\$1\\$/", $hash) && CRYPT_MD5 != 1) ||
			/** @phpstan-ignore-next-line Why? */
			(preg_match("/^\\$[0-9A-Za-z.]{12}$/", $hash) && CRYPT_STD_DES != 1)
		) {
			// this means password cannot be decrypted because of missing crypt methods
			$this->login_error = 9999;
		} elseif (
			preg_match("/^\\$2y\\$/", $hash) &&
			!Password::passwordVerify($password, $hash)
		) {
			// this is the new password hash method, is only $2y$
			// all others are not valid anymore
			$this->login_error = 1013;
		} elseif (
			!preg_match("/^\\$2(a|y)\\$/", $hash) &&
			!preg_match("/^\\$1\\$/", $hash) &&
			!preg_match("/^\\$[0-9A-Za-z.]{12}$/", $hash) &&
			$hash != $password
		) {
			// check old plain password, case sensitive
			$this->login_error = 1012;
		} else {
			// all ok
			$password_ok = true;
		}
		return $password_ok;
	}

	/**
	 * Check if Login User ID is allowed to login
	 *
	 * @param  int  $login_user_id_valid_date
	 * @param  int  $login_user_id_revalidate
	 * @return bool
	 */
	private function loginLoginUserIdCheck(
		int $login_user_id_valid_date,
		int $login_user_id_revalidate
	): bool {
		$login_id_ok = false;
		if ($login_user_id_revalidate) {
			$this->login_error = 1101;
		} elseif (!$login_user_id_valid_date) {
			$this->login_error = 1102;
		} else {
			$login_id_ok = true;
		}
		return $login_id_ok;
	}

	/**
	 * write error data for login errors
	 *
	 * @param  array<string,mixed> $res
	 * @return void
	 */
	private function loginWriteLoginError(array $res)
	{
		if (!$this->login_error) {
			return;
		}
		$login_error_date_first = '';
		if ($res['login_error_count'] == 0) {
			$login_error_date_first = ", login_error_date_first = NOW()";
		}
		// update login error count for this user
		$q = <<<SQL
		UPDATE edit_user
		SET
			login_error_count = login_error_count + 1,
			login_error_date_last = NOW()
			{LOGIN_ERROR_SQL}
		WHERE edit_user_id = $1
		SQL;
		$this->db->dbExecParams(
			str_replace('{LOGIN_ERROR_SQL}', $login_error_date_first, $q),
			[$res['edit_user_id']]
		);
		// totally lock the user if error max is reached
		if (
			$this->max_login_error_count != -1 &&
			$res['login_error_count'] + 1 > $this->max_login_error_count
		) {
			// do some alert reporting in case this error is too big
			// if strict is set, lock this user
			// this needs manual unlocking by an admin user
			if ($res['strict'] && !in_array($this->username, $this->lock_deny_users)) {
				$q = <<<SQL
				UPDATE edit_user
				SET locked = 1
				WHERE edit_user_id = $1
				SQL;
				// [$res['edit_user_id']]
			}
		}
	}

	/**
	 * set the core edit_user table id/cuid/cuuid
	 *
	 * @param  array<string,mixed> $res
	 * @return void
	 */
	private function loginSetEditUserUidData(array $res)
	{
		// normal user processing
		// set class var and session var
		$this->edit_user_id = (int)$res['edit_user_id'];
		$this->edit_user_cuid = (string)$res['cuid'];
		$this->edit_user_cuuid = (string)$res['cuuid'];
		$this->session->setMany([
			'LOGIN_EUID' => $this->edit_user_id,
			'LOGIN_EUCUID' => $this->edit_user_cuid,
			'LOGIN_EUCUUID' => $this->edit_user_cuuid,
		]);
	}

	/**
	 * check for re-loading of ACL data after a period of time
	 * or if any of the core session vars is not set
	 *
	 * @return void
	 */
	private function loginAuthResync()
	{
		if (!$this->session->get('LOGIN_LAST_AUTH_RESYNC')) {
			$this->session->set('LOGIN_LAST_AUTH_RESYNC', 0);
		}
		// reauth on missing session vars and timed out re-sync interval
		$mandatory_session_vars = [
			'LOGIN_USER_NAME', 'LOGIN_GROUP_NAME', 'LOGIN_EUCUID', 'LOGIN_EUCUUID',
			'LOGIN_USER_ADDITIONAL_ACL', 'LOGIN_GROUP_ADDITIONAL_ACL',
			'LOGIN_ADMIN', 'LOGIN_GROUP_ACL_LEVEL',
			'LOGIN_PAGES', 'LOGIN_PAGES_LOOKUP', 'LOGIN_PAGES_ACL_LEVEL',
			'LOGIN_USER_ACL_LEVEL',
			'LOGIN_UNIT', 'LOGIN_UNIT_DEFAULT_EACUID'
		];
		$force_reauth = false;
		foreach ($mandatory_session_vars as $_session_var) {
			if (!isset($_SESSION[$_session_var])) {
				$force_reauth = true;
				break;
			}
		}
		if (
			$this->session->get('LOGIN_LAST_AUTH_RESYNC') + $this->auth_resync_interval <= time() &&
			$force_reauth == false
		) {
			return;
		}
		if (($res = $this->loginLoadUserData($this->edit_user_cuuid)) === false) {
			return;
		}
		// set the session vars
		$this->loginSetSession($res);
	}

	// MARK: MAIN LOGIN ACTION

	/**
	 * if user pressed login button this script is called,
	 * but only if there is no preview euid set
	 *
	 * @return void has not return
	 */
	private function loginLoginUser(): void
	{
		// if pressed login at least and is not yet loggined in
		if ($this->edit_user_cuuid || (!$this->login && !$this->login_user_id)) {
			// run reload user data based on re-auth timeout, but only if we got a set cuuid
			if ($this->edit_user_cuuid) {
				$this->loginAuthResync();
			}
			return;
		}
		// if not username AND password where given
		// OR no login_user_id
		if (!($this->username && $this->password) && !$this->login_user_id) {
			$this->login_error = 102;
			$this->permission_okay = false;
			return;
		}
		// load user data, abort on error
		if (($res = $this->loginLoadUserData()) === false) {
			return;
		}
		// if login errors is half of max errors and the last login error
		// was less than 10s ago, forbid any new login try

		// check flow
		// - user is enabled
		// - user is not locked
		// - password is readable
		// - encrypted password matches
		// - plain password matches
		if (
			!$this->loginValidationCheck(
				(int)$res['deleted'],
				(int)$res['enabled'],
				(int)$res['locked'],
				(int)$res['locked_period'],
				(int)$res['login_user_id_locked'],
				(int)$res['force_logout']
			)
		) {
			// error set in method (104, 105, 106, 107, 108)
		} elseif (
			empty($this->username) &&
			!empty($this->login_user_id) &&
			!$this->loginLoginUserIdCheck(
				(int)$res['login_user_id_valid_date'],
				(int)$res['login_user_id_revalidate']
			)
		) {
			// check done in loginLoginIdCheck method
			// aborts on must revalidate and not valid (date range)
		} elseif (
			!empty($this->username) &&
			!$this->loginPasswordCheck($res['password'])
		) {
			// none to be set, set in login password check
			// this is not valid password input error here
			// all error codes are set in loginPasswordCheck method
			// also valid if login_user_id is ok
		} else {
			// check if the current password is an invalid hash and do a rehash and set password
			// $this->debug('LOGIN', 'Hash: '.$res['password'].' -> VERIFY: '
			//	.($Password::passwordVerify($this->password, $res['password']) ? 'OK' : 'FAIL')
			//	.' => HASH: '.(Password::passwordRehashCheck($res['password']) ? 'NEW NEEDED' : 'OK'));
			if (Password::passwordRehashCheck($res['password'])) {
				// update password hash to new one now
				$q = <<<SQL
				UPDATE edit_user
				SET password = $1
				WHERE edit_user_id = $2
				SQL;
				$this->db->dbExecParams($q, [
					Password::passwordSet($this->password),
					$res['edit_user_id']
				]);
			}
			// normal user processing
			// set class var and session var
			$this->loginSetEditUserUidData($res);
			// set the last login time stamp for normal login only (not for reauthenticate)
			$this->db->dbExecParams(<<<SQL
			UPDATE edit_user SET
				last_login = NOW()
			WHERE
				edit_user_id = $1
			SQL, [$this->edit_user_id]);
			// set the session vars
			$this->loginSetSession($res);
		} // user was not enabled or other login error
		// check for login error and write to the user
		$this->loginWriteLoginError($res);
		// if there was an login error, show login screen
		if ($this->login_error) {
			// reset the perm var, to confirm logout
			$this->permission_okay = false;
		}
	}

	/**
	 * load user data and all connect4ed settings
	 *
	 * @param ?string $edit_user_cuuid for re-auth
	 * @return array<string,mixed>|false
	 */
	private function loginLoadUserData(?string $edit_user_cuuid = null): array|false
	{
		$q = <<<SQL
		SELECT
			eu.edit_user_id, eu.cuid, eu.cuuid, eu.username, eu.password, eu.email,
			eu.edit_group_id,
			eg.name AS edit_group_name, eu.admin,
			-- additinal acl lists
			eu.additional_acl AS user_additional_acl,
			eg.additional_acl AS group_additional_acl,
			-- force logoutp counter
			eu.force_logout,
			-- login error + locked
			eu.login_error_count, eu.login_error_date_last,
			eu.login_error_date_first, eu.strict, eu.locked,
			-- date based lock
			CASE WHEN (
				(
					eu.lock_until IS NULL
					OR (eu.lock_until IS NOT NULL AND NOW() >= eu.lock_until)
				)
				AND (
					eu.lock_after IS NULL
					OR (eu.lock_after IS NOT NULL AND NOW() <= eu.lock_after)
				)
			) THEN 0::INT ELSE 1::INT END locked_period,
			-- enabled
			eu.enabled, eu.deleted,
			-- for checks only
			eu.login_user_id,
			-- login id validation
			CASE WHEN (
				(
					eu.login_user_id_valid_from IS NULL
					OR (eu.login_user_id_valid_from IS NOT NULL AND NOW() >= eu.login_user_id_valid_from)
				)
				AND (
					eu.login_user_id_valid_until IS NULL
					OR (eu.login_user_id_valid_until IS NOT NULL AND NOW() <= eu.login_user_id_valid_until)
				)
			) THEN 1::INT ELSE 0::INT END AS login_user_id_valid_date,
			-- check if user must login
			CASE WHEN
				eu.login_user_id_revalidate_after IS NOT NULL
				AND eu.login_user_id_revalidate_after > '0 days'::INTERVAL
				AND (eu.login_user_id_last_revalidate + eu.login_user_id_revalidate_after)::DATE
				<= NOW()::DATE
			THEN 1::INT ELSE 0::INT END AS login_user_id_revalidate,
			eu.login_user_id_locked,
			-- language
			el.short_name AS locale, el.iso_name AS encoding,
			-- levels
			eareu.level AS user_level, eareu.type AS user_type,
			eareg.level AS group_level, eareg.type AS group_type,
			-- colors
			first.header_color AS first_header_color,
			second.header_color AS second_header_color, second.template
			FROM edit_user eu
			LEFT JOIN edit_scheme second ON
			(second.edit_scheme_id = eu.edit_scheme_id AND second.enabled = 1),
			edit_language el, edit_group eg,
			edit_access_right eareu,
			edit_access_right eareg,
			edit_scheme first
			WHERE first.edit_scheme_id = eg.edit_scheme_id
			AND eu.edit_group_id = eg.edit_group_id
			AND eu.edit_language_id = el.edit_language_id
			AND eu.edit_access_right_id = eareu.edit_access_right_id
			AND eg.edit_access_right_id = eareg.edit_access_right_id
			AND {SEARCH_QUERY}
		SQL;
		$params = [];
		$replace_string = '';
		// if login is OK and we have edit_user_cuuid as parameter, then this is internal re-auth
		// else login_user_id OR password must be given
		if (!empty($edit_user_cuuid)) {
			$replace_string = 'eu.cuuid = $1';
			$params = [$this->edit_user_cuuid];
		} elseif (!empty($this->login_user_id) && empty($this->username)) {
			// check with login id if set and NO username
			$replace_string = 'eu.login_user_id = $1';
			$params = [$this->login_user_id];
		} else {
			// password match is done in script, against old plain or new blowfish encypted
			$replace_string = 'LOWER(username) = $1';
			$params = [strtolower($this->username)];
		}
		$q = str_replace(
			'{SEARCH_QUERY}',
			$replace_string,
			$q
		);
		// reset any query data that might exist
		$this->db->dbCacheReset($q, $params, show_warning:false);
		// never cache return data
		$res = $this->db->dbReturnParams($q, $params, $this->db::NO_CACHE);
		// query was not run successful
		if (!empty($this->db->dbGetLastError())) {
			$this->login_error = 1009;
			$this->permission_okay = false;
			return false;
		} elseif (!is_array($res)) {
			// username is wrong, but we throw for wrong username
			// and wrong password the same error
			// unless with have edit user cuuid set then we run an general ACL error
			if (empty($edit_user_cuuid)) {
				$this->login_error = 1010;
			} else {
				$this->login_error = 1011;
			}
			$this->permission_okay = false;
			return false;
		}
		return $res;
	}

	// MARK: login set all session variables

	/**
	 * set all the _SESSION variables
	 *
	 * @param  array<string,mixed> $res user data loaded query result
	 * @return void
	 */
	private function loginSetSession(array $res): void
	{
		// user has permission to THIS page
		if ($this->login_error != 0) {
			return;
		}
		// set the dit group id
		$edit_group_id = $res["edit_group_id"];
		$edit_user_id = (int)$res['edit_user_id'];
		// update last revalidate flag
		if (
			!empty($res['login_user_id']) &&
			!empty($this->username) && !empty($this->password)
		) {
			$q = <<<SQL
			UPDATE edit_user
			SET login_user_id_last_revalidate = NOW()
			WHERE edit_user_id = $1
			SQL;
			$this->db->dbExecParams($q, [$edit_user_id]);
		}
		$locale = $res['locale'] ?? 'en';
		$encoding = $res['encoding'] ?? 'UTF-8';
		$this->session->setMany([
			// now set all session vars and read page permissions
			// DEBUG flag is deprecated
			// 'DEBUG_ALL' => $this->db->dbBoolean($res['debug']),
			// 'DB_DEBUG' => $this->db->dbBoolean($res['db_debug']),
			// login timestamp
			'LOGIN_LAST_AUTH_RESYNC' => time(),
			// current forced logout counter
			'LOGIN_FORCE_LOGOUT' => $res['force_logout'],
			// general info for user logged in
			'LOGIN_USER_NAME' => $res['username'],
			'LOGIN_EMAIL' => $res['email'],
			'LOGIN_ADMIN' => $res['admin'],
			'LOGIN_GROUP_NAME' => $res['edit_group_name'],
			'LOGIN_USER_ACL_LEVEL' => $res['user_level'],
			'LOGIN_USER_ACL_TYPE' => $res['user_type'],
			'LOGIN_USER_ADDITIONAL_ACL' => Json::jsonConvertToArray($res['user_additional_acl']),
			'LOGIN_GROUP_ACL_LEVEL' => $res['group_level'],
			'LOGIN_GROUP_ACL_TYPE' => $res['group_type'],
			'LOGIN_GROUP_ADDITIONAL_ACL' => Json::jsonConvertToArray($res['group_additional_acl']),
			// deprecated TEMPLATE setting
			// 'TEMPLATE' => $res['template'] ? $res['template'] : '',
			'LOGIN_HEADER_COLOR' => !empty($res['second_header_color']) ?
				$res['second_header_color'] :
				$res['first_header_color'],
			// LANGUAGE/LOCALE/ENCODING:
			// 'LOGIN_LANG' => $locale,
			'DEFAULT_CHARSET' => $encoding,
			'DEFAULT_LOCALE' => $locale . '.' . strtoupper($encoding),
			'DEFAULT_LANG' => $locale . '_' . strtolower(str_replace('-', '', $encoding))
		]);
		// missing # before, this is for legacy data, will be deprecated
		if (
			!empty($this->session->get('LOGIN_HEADER_COLOR')) &&
			preg_match("/^[\dA-Fa-f]{6,8}$/", $this->session->get('LOGIN_HEADER_COLOR'))
		) {
			$this->session->set('LOGIN_HEADER_COLOR', '#' . $this->session->get('LOGIN_HEADER_COLOR'));
		}
		// TODO: make sure that header color is valid:
		// # + 6 hex
		// # + 8 hex (alpha)
		// rgb(), rgba(), hsl(), hsla()
		// rgb: nnn.n for each
		// hsl: nnn.n for first, nnn.n% for 2nd, 3rd
		// Check\Colors::validateColor()
		// reset any login error count for this user
		if ($res['login_error_count'] > 0) {
			$q = <<<SQL
			UPDATE edit_user
			SET
				login_error_count = 0, login_error_date_last = NULL,
				login_error_date_first = NULL
			WHERE edit_user_id = $1
			SQL;
			$this->db->dbExecParams($q, [$edit_user_id]);
		}
		$edit_page_ids = [];
		$pages = [];
		$pages_lookup = [];
		$pages_acl = [];
		// set pages access
		$q = <<<SQL
		SELECT
			ep.edit_page_id, ep.cuid, ep.cuuid, epca.cuid AS content_alias_uid,
			ep.hostname, ep.filename, ep.name AS edit_page_name,
			ep.order_number AS edit_page_order, ep.menu,
			ep.popup, ep.popup_x, ep.popup_y, ep.online, ear.level, ear.type
		FROM edit_page ep
		LEFT JOIN edit_page epca ON (
			epca.edit_page_id = ep.content_alias_edit_page_id
		),
		edit_page_access epa, edit_access_right ear
		WHERE
			ep.edit_page_id = epa.edit_page_id
			AND ear.edit_access_right_id = epa.edit_access_right_id
			AND epa.enabled = 1 AND epa.edit_group_id = $1
		ORDER BY ep.order_number
		SQL;
		while (is_array($res = $this->db->dbReturnParams($q, [$edit_group_id]))) {
			// page id array for sub data readout
			$edit_page_ids[$res['edit_page_id']] = $res['cuid'];
			// create the array for pages
			$pages[$res['cuid']] = [
				'edit_page_id' => $res['edit_page_id'],
				'cuid' => $res['cuid'],
				'cuuid' => $res['cuuid'],
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
			$pages_lookup[$res['filename']] = $res['cuid'];
			// make reference filename -> level
			$pages_acl[$res['filename']] = $res['level'];
		} // for each page
		// edit page id params
		$params = ['{' . join(',', array_keys($edit_page_ids)) . '}'];
		// get the visible groups for all pages and write them to the pages
		$q = <<<SQL
		SELECT epvg.edit_page_id, name, flag
		FROM edit_visible_group evp, edit_page_visible_group epvg
		WHERE
			evp.edit_visible_group_id = epvg.edit_visible_group_id
			AND epvg.edit_page_id = ANY($1)
		ORDER BY epvg.edit_page_id
		SQL;
		while (is_array($res = $this->db->dbReturnParams($q, $params))) {
			$pages[$edit_page_ids[$res['edit_page_id']]]['visible'][$res['name']] = $res['flag'];
		}
		// get the same for the query strings
		$q = <<<SQL
		SELECT eqs.edit_page_id, name, value, dynamic
		FROM edit_query_string eqs
		WHERE
			enabled = 1
			AND edit_page_id = ANY($1)
		ORDER BY eqs.edit_page_id
		SQL;
		while (is_array($res = $this->db->dbReturnParams($q, $params))) {
			$pages[$edit_page_ids[$res['edit_page_id']]]['query'][] = [
				'name' => $res['name'],
				'value' => $res['value'],
				'dynamic' => $res['dynamic']
			];
		}
		// get the page content and add them to the page
		$q = <<<SQL
		SELECT
			epc.edit_page_id, epc.name, epc.uid, epc.cuid, epc.cuuid, epc.order_number,
			epc.online, ear.level, ear.type
		FROM edit_page_content epc, edit_access_right ear
		WHERE
			epc.edit_access_right_id = ear.edit_access_right_id
			AND epc.edit_page_id = ANY($1)
		ORDER BY epc.order_number
		SQL;
		while (is_array($res = $this->db->dbReturnParams($q, $params))) {
			$pages[$edit_page_ids[$res['edit_page_id']]]['content'][$res['uid']] = [
				'name' => $res['name'],
				'uid' => $res['uid'],
				'cuid' => $res['cuid'],
				'cuuid' => $res['cuuid'],
				'online' => $res['online'],
				'order' => $res['order_number'],
				// access name and level
				'acl_type' => $res['type'],
				'acl_level' => $res['level']
			];
		}
		// write back the pages data to the output array
		$this->session->setMany([
			'LOGIN_PAGES' => $pages,
			'LOGIN_PAGES_LOOKUP' => $pages_lookup,
			'LOGIN_PAGES_ACL_LEVEL' => $pages_acl,
		]);
		// load the edit_access user rights
		$q = <<<SQL
		SELECT
			ea.edit_access_id, ea.cuid, ea.cuuid, level, type, ea.name,
			ea.color, ea.uid, edit_default, ea.additional_acl
		FROM edit_access_user eau, edit_access_right ear, edit_access ea
		WHERE
			eau.edit_access_id = ea.edit_access_id
			AND eau.edit_access_right_id = ear.edit_access_right_id
			AND eau.enabled = 1 AND edit_user_id = $1
		ORDER BY ea.name
		SQL;
		$unit_access_cuid = [];
		// legacy
		$unit_access_eaid = [];
		$unit_cuid_lookup = [];
		$eaid = [];
		$eacuid = [];
		$unit_acl = [];
		$unit_uid_lookup = [];
		while (is_array($res = $this->db->dbReturnParams($q, [$edit_user_id]))) {
			// read edit access data fields and drop them into the unit access array
			$q_sub = <<<SQL
			SELECT name, value
			FROM edit_access_data
			WHERE enabled = 1 AND edit_access_id = $1
			SQL;
			$ea_data = [];
			while (is_array($res_sub = $this->db->dbReturnParams($q_sub, [$res['edit_access_id']]))) {
				$ea_data[$res_sub['name']] = $res_sub['value'];
			}
			// build master unit array
			$unit_access_cuid[$res['cuid']] = [
				'id' => (int)$res['edit_access_id'], // DEPRECATED
				'cuuid' => $res['cuuid'],
				'acl_level' => $res['level'],
				'acl_type' => $res['type'],
				'name' => $res['name'],
				'uid' => $res['uid'],
				'color' => $res['color'],
				'default' => $res['edit_default'],
				'additional_acl' => Json::jsonConvertToArray($res['additional_acl']),
				'data' => $ea_data
			];
			// LEGACY LOOKUP
			$unit_access_eaid[$res['edit_access_id']] = [
				'cuid' => $res['cuid'],
			];
			// set the default unit
			$this->session->setMany([
				'LOGIN_UNIT_DEFAULT_EAID' => null,
				'LOGIN_UNIT_DEFAULT_EACUID' => null,
			]);
			if ($res['edit_default']) {
				$this->session->set('LOGIN_UNIT_DEFAULT_EAID', (int)$res['edit_access_id']); // DEPRECATED
				$this->session->set('LOGIN_UNIT_DEFAULT_EACUID', (int)$res['cuid']);
			}
			$unit_uid_lookup[$res['uid']] = $res['edit_access_id']; // DEPRECATED
			$unit_cuid_lookup[$res['uid']] = $res['cuid'];
			// sub arrays for simple access
			array_push($eaid, $res['edit_access_id']);
			array_push($eacuid, $res['cuid']);
			$unit_acl[$res['cuid']] = $res['level'];
		}
		$this->session->setMany([
			'LOGIN_UNIT_UID' => $unit_uid_lookup, // DEPRECATED
			'LOGIN_UNIT_CUID' => $unit_cuid_lookup,
			'LOGIN_UNIT' => $unit_access_cuid,
			'LOGIN_UNIT_LEGACY' => $unit_access_eaid, // DEPRECATED
			'LOGIN_UNIT_ACL_LEVEL' => $unit_acl,
			'LOGIN_EAID' => $eaid, // DEPRECATED
			'LOGIN_EACUID' => $eacuid,
		]);
	}

	// MARK: login set ACL

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
	 *
	 * @return void has no return
	 */
	private function loginSetAcl(): void
	{
		// only set acl if we have permission okay
		if (!$this->permission_okay) {
			return;
		}
		// username (login), group name
		$this->acl['user_name'] = $_SESSION['LOGIN_USER_NAME'];
		$this->acl['group_name'] = $_SESSION['LOGIN_GROUP_NAME'];
		// DEPRECATED
		$this->acl['euid'] = $_SESSION['LOGIN_EUID'];
		// edit user cuid
		$this->acl['eucuid'] = $_SESSION['LOGIN_EUCUID'];
		$this->acl['eucuuid'] = $_SESSION['LOGIN_EUCUUID'];
		// set additional acl
		$this->acl['additional_acl'] = [
			'user' => $_SESSION['LOGIN_USER_ADDITIONAL_ACL'],
			'group' => $_SESSION['LOGIN_GROUP_ADDITIONAL_ACL'],
		];
		// we start with the default acl
		$this->acl['base'] = $this->default_acl_level;

		// set admin flag and base to 100
		if (!empty($_SESSION['LOGIN_ADMIN'])) {
			$this->acl['admin'] = 1;
			$this->acl['base'] = 100;
		} else {
			$this->acl['admin'] = 0;
			// now go throw the flow and set the correct ACL
			// user > page > group
			// group ACL 0
			if ($_SESSION['LOGIN_GROUP_ACL_LEVEL'] != -1) {
				$this->acl['base'] = (int)$_SESSION['LOGIN_GROUP_ACL_LEVEL'];
			}
			// page ACL 1
			if (
				isset($_SESSION['LOGIN_PAGES_ACL_LEVEL'][$this->page_name]) &&
				$_SESSION['LOGIN_PAGES_ACL_LEVEL'][$this->page_name] != -1
			) {
				$this->acl['base'] = (int)$_SESSION['LOGIN_PAGES_ACL_LEVEL'][$this->page_name];
			}
			// user ACL 2
			if ($_SESSION['LOGIN_USER_ACL_LEVEL'] != -1) {
				$this->acl['base'] = (int)$_SESSION['LOGIN_USER_ACL_LEVEL'];
			}
		}
		$this->session->set('LOGIN_BASE_ACL_LEVEL', $this->acl['base']);

		// set the current page acl
		// start with base acl
		// set group if not -1, overrides default
		// set page if not -1, overrides group set
		$this->acl['page'] = $this->acl['base'];
		if ($_SESSION['LOGIN_GROUP_ACL_LEVEL'] != -1) {
			$this->acl['page'] = $_SESSION['LOGIN_GROUP_ACL_LEVEL'];
		}
		if (
			isset($_SESSION['LOGIN_PAGES_ACL_LEVEL'][$this->page_name]) &&
			$_SESSION['LOGIN_PAGES_ACL_LEVEL'][$this->page_name] != -1
		) {
			$this->acl['page'] = $_SESSION['LOGIN_PAGES_ACL_LEVEL'][$this->page_name];
		}
		$this->acl['pages_detail'] = $_SESSION['LOGIN_PAGES'];
		$this->acl['pages_lookup_cuid'] = $_SESSION['LOGIN_PAGES_LOOKUP'];

		$this->acl['unit_cuid'] = null;
		$this->acl['unit_name'] = null;
		$this->acl['unit_uid'] = null;
		$this->acl['unit'] = [];
		$this->acl['unit_legacy'] = [];
		$this->acl['unit_detail'] = [];

		// PER ACCOUNT (UNIT/edit access)->
		foreach ($_SESSION['LOGIN_UNIT'] as $ea_cuid => $unit) {
			// if admin flag is set, all units are set to 100
			if (!empty($this->acl['admin'])) {
				$this->acl['unit'][$ea_cuid] = $this->acl['base'];
			} else {
				if ($unit['acl_level'] != -1) {
					$this->acl['unit'][$ea_cuid] = $unit['acl_level'];
				} else {
					$this->acl['unit'][$ea_cuid] = $this->acl['base'];
				}
			}
			// legacy
			$this->acl['unit_legacy'][$unit['id']] = $this->acl['unit'][$ea_cuid];
			// detail name/level set
			$this->acl['unit_detail'][$ea_cuid] = [
				'id' =>  $unit['id'],
				'name' => $unit['name'],
				'uid' => $unit['uid'],
				'cuuid' => $unit['cuuid'],
				'level' => $this->default_acl_list[$this->acl['unit'][$ea_cuid]]['name'] ?? -1,
				'level_number' => $this->acl['unit'][$ea_cuid],
				'default' => $unit['default'],
				'data' => $unit['data'],
				'additional_acl' => $unit['additional_acl']
			];
			// set default
			if (!empty($unit['default'])) {
				$this->acl['unit_cuid'] = $ea_cuid;
				$this->acl['unit_name'] = $unit['name'];
				$this->acl['unit_uid'] = $unit['uid'];
			}
		}
		// flag if to show extra edit access drop downs (because user has multiple groups assigned)
		if (count($_SESSION['LOGIN_UNIT']) > 1) {
			$this->acl['show_ea_extra'] = true;
		} else {
			$this->acl['show_ea_extra'] = false;
		}
		// set the default edit access
		$this->acl['default_edit_access'] = $_SESSION['LOGIN_UNIT_DEFAULT_EACUID'];
		// integrate the type acl list, but only for the keyword -> level
		$this->acl['min'] = $this->default_acl_list_type;
		// set the full acl list too (lookup level number and get level data)
		$this->acl['acl_list'] = $this->default_acl_list;
		// debug
		// $this->debug('ACL', $this->print_ar($this->acl));
	}

	// MARK: login set locale

	/**
	 * set locale
	 * if invalid, set to empty string
	 *
	 * @return void
	 */
	private function loginSetLocale(): void
	{
		// ** LANGUAGE SET AFTER LOGIN **
		// set the locale
		if (
			!empty($_SESSION['DEFAULT_LOCALE']) &&
			preg_match("/^[-A-Za-z0-9_.@]+$/", $_SESSION['DEFAULT_LOCALE'])
		) {
			$locale = $_SESSION['DEFAULT_LOCALE'];
		} elseif (
			!preg_match("/^[-A-Za-z0-9_.@]+$/", $this->options['site_locale'])
		) {
			$locale = $this->options['site_locale'];
		} else {
			$locale = '';
		}
		// set the charset
		preg_match('/(?:\\.(?P<charset>[-A-Za-z0-9_]+))/', $locale, $matches);
		$locale_encoding = $matches['charset'] ?? '';
		if (!empty($locale_encoding)) {
			$encoding = strtoupper($locale_encoding);
		} elseif (
			!empty($_SESSION['DEFAULT_CHARSET']) &&
			preg_match("/^[-A-Za-z0-9_]+$/", $_SESSION['DEFAULT_CHARSET'])
		) {
			$encoding = $_SESSION['DEFAULT_CHARSET'];
		} elseif (
			!preg_match("/^[-A-Za-z0-9_]+$/", $this->options['site_encoding'])
		) {
			$encoding = $this->options['site_encoding'];
		} else {
			$encoding = '';
		}
		// check domain
		$domain = $this->options['site_domain'];
		if (
			!preg_match("/^\w+$/", $this->options['site_domain'])
		) {
			$domain = '';
		}
		$path = $this->options['locale_path'];
		if (!is_dir($path)) {
			$path = '';
		}
		// domain and path are a must set from class options
		$this->locale = [
			'locale' => $locale,
			'domain' => $domain,
			'encoding' => $encoding,
			'path' => $path,
		];
	}

	// MARK: password handling

	/**
	 * checks if the password is in a valid format
	 *
	 * @param  string $password the new password
	 * @return bool             true or false if valid password or not
	 */
	private function loginPasswordChangeValidPassword(string $password): bool
	{
		$is_valid_password = true;
		// check for valid in regex arrays in list
		foreach ($this->password_valid_chars as $password_valid_chars) {
			if (!preg_match("/$password_valid_chars/", $password)) {
				$is_valid_password = false;
			}
		}
		// check for min length
		if (
			strlen($password) < $this->password_min_length ||
			strlen($password) > $this->password_max_length
		) {
			$is_valid_password = false;
		}
		return $is_valid_password;
	}

	/**
	 * dummy declare for password forget
	 *
	 * @return void has no return
	 */
	private function loginPasswordForgot(): void
	{
		// will do some password recovert, eg send email
	}

	/**
	 * changes a user password
	 *
	 * @return void has no return
	 */
	private function loginPasswordChange(): void
	{
		// only continue if password change button pressed
		if (!$this->change_password) {
			return;
		}
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
					. "AND username = '" . $this->db->dbEscapeString($this->pw_username) . "'";
				$res = $this->db->dbReturnRow($q);
				if (
					!is_array($res) ||
					empty($res['edit_user_id'])
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
					. "AND username = '" . $this->db->dbEscapeString($this->pw_username) . "'";
				$edit_user_id = '';
				$res = $this->db->dbReturnRow($q);
				if (is_array($res)) {
					$edit_user_id = $res['edit_user_id'];
				}
				if (
					!is_array($res) ||
					empty($res['edit_user_id']) ||
					!$this->loginPasswordCheck(
						$res['old_password_hash'],
						$this->pw_old_password
					)
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
					. "'" . $this->db->dbEscapeString(Password::passwordSet($this->pw_new_password)) . "' "
					. "WHERE edit_user_id = " . $edit_user_id;
				$this->db->dbExec($q);
				$data = 'Password change for user "' . $this->pw_username . '"';
				$this->password_change_ok = true;
			}
		} else {
			// illegal user error
			$this->login_error = 220;
			$data = 'Illegal user for password change: ' . $this->pw_username;
		}
		// log this password change attempt
		$this->writeEditLog($event, $data, $this->login_error, $this->pw_username);
	}

	// MARK: set HTML login page

	/**
	 * creates the login html part if no permission (error) is set
	 * this does not print anything yet
	 *
	 * @return string|null html data for login page, or null for nothing
	 */
	private function loginCreateLoginHTML(): ?string
	{
		$html_string = null;
		// if permission is ok, return null
		if ($this->permission_okay) {
			return $html_string;
		}
		// set the templates now
		$this->loginSetTemplates();
		// if there is a global logout target ...
		if (file_exists($this->logout_target)) {
			$LOGOUT_TARGET = $this->logout_target;
		} else {
			$LOGOUT_TARGET = '';
		}

		$html_string = (string)$this->login_template['template'];

		$locales = $this->l->parseLocale($this->l->getLocale());
		$this->login_template['strings']['LANGUAGE'] = $locales['lang'] ?? 'en';

		// if password change is okay
		// TODO: this should be a "forgot" password
		// -> input email
		// -> send to user with link + token
		// -> validate token -> show change password
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
					['{ERROR_VISIBLE}', '{ERROR_MSG}'],
					['login-visible', $this->loginGetErrorMsg($this->login_error)],
					$html_string_password_change
				);
			} else {
				$html_string_password_change = str_replace(
					['{ERROR_VISIBLE}', '{ERROR_MSG}'],
					['login-hidden', ''],
					$html_string_password_change
				);
			}
			// if pw change action, show the float again
			if ($this->change_password && !$this->password_change_ok) {
				$html_string_password_change = str_replace(
					'{PASSWORD_CHANGE_SHOW}',
					<<<HTML
					<script language="JavaScript">
					ShowHideDiv('login_pw_change_div');
					</script>
					HTML,
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
				['{ERROR_VISIBLE}', '{ERROR_MSG}'],
				['login-visible', $this->loginGetErrorMsg($this->login_error)],
				$html_string
			);
		} elseif ($this->password_change_ok && $this->password_change) {
			$html_string = str_replace(
				['{ERROR_VISIBLE}', '{ERROR_MSG}'],
				['login-visible', $this->loginGetErrorMsg(300)],
				$html_string
			);
		} else {
			$html_string = str_replace(
				['{ERROR_VISIBLE}', '{ERROR_MSG}'],
				['login-hidden', ''],
				$html_string
			);
		}

		// create the replace array context
		foreach ($this->login_template['strings'] as $string => $data) {
			$html_string = str_replace('{' . $string . '}', $data, $html_string);
		}
		// return the created HTML here
		return $html_string;
	}

	// MARK: logout call

	/**
	 * last function called, writes log and prints out error msg and
	 * exists script if permission 0
	 *
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
			if ($this->edit_user_cuuid) {
				// get user from user table
				$q = <<<SQL
				SELECT username
				FROM edit_user
				WHERE cuuid = $1
				SQL;
				$username = '';
				if (is_array($res = $this->db->dbReturnRowParams($q, [$this->edit_user_cuuid]))) {
					$username = $res['username'];
				}
			} // if euid is set, get username (or try)
			$this->writeEditLog($event, '', $this->login_error, $username);
		} // write log under certain settings
		// now close DB connection
		// $this->error_msg = $this->_login();
		if (!$this->permission_okay) {
			return false;
		} else {
			return true;
		}
	}

	// MARK: set template for login page

	/**
	 * checks if there are external templates, if not uses internal fallback ones
	 *
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

		// if password change is okay
		if ($this->password_change) {
			$strings = array_merge($strings, [
				'TITLE_PASSWORD_CHANGE' => 'Change Password for User',
				'OLD_PASSWORD' => $this->l->__('Old Password'),
				'NEW_PASSWORD' => $this->l->__('New Password'),
				'NEW_PASSWORD_CONFIRM' => $this->l->__('New Password confirm'),
				'CLOSE' => $this->l->__('Close'),
				'JS_SHOW_HIDE' => <<<JAVASCRIPT
				function ShowHideDiv(id) {
					element = document.getElementById(id);
					let visible = !!(
						element.offsetWidth ||
						element.offsetHeight ||
						element.getClientRects().length ||
						window.getComputedStyle(element).visibility == "hidden"
					);
					if (visiblee) {
						element.className = 'login-hidden';
					} else {
						element.className = 'login-visible';
					}
				}
				JAVASCRIPT,
				'PASSWORD_CHANGE_BUTTON' => str_replace(
					'{PASSWORD_CHANGE_BUTTON_VALUE}',
					$strings['PASSWORD_CHANGE_BUTTON_VALUE'],
					// phpcs:disable Generic.Files.LineLength
					<<<HTML
				<input type="button" name="pw_change" value="{PASSWORD_CHANGE_BUTTON_VALUE}" OnClick="ShowHideDiv('login_pw_change_div'); return false;">
				HTML
					// phpcs:enable Generic.Files.LineLength
				),
			]);
			// phpcs:disable Generic.Files.LineLength
			$this->login_template['password_change'] = <<<HTML
<div id="loginPasswordChangeBox" class="login-password-change login-hidden">
	<div id="loginTitle" class="login-title">
		{TITLE_PASSWORD_CHANGE}
	</div>
	<div id="loginPasswordChangeError" class="login-error {ERROR_VISIBLE}">
		{ERROR_MSG}
	</div>
	<div id="loginPasswordChange" class="login-data">
		<div class="login-data-row">
			<div class="login-data-left">
				{USERNAME}
			</div>
			<div class="login-data-right">
				<input type="text" name="login_username" class="login-input-text">
			</div>
		</div>
		<div class="login-data-row">
			<div class="login-data-left">
			{OLD_PASSWORD}
			</div>
			<div class="login-data-right">
				<input type="password" name="login_pw_old_password" class="login-input-text">
			</div>
		</div>
		<div class="login-data-row">
			<div class="login-data-left">
				{NEW_PASSWORD}
			</div>
			<div class="login-data-right">
				<input type="password" name="login_pw_new_password" class="login-input-text">
			</div>
		</div>
		<div class="login-data-row">
			<div class="login-data-left">
				{NEW_PASSWORD_CONFIRM}
			</div>
			<div class="login-data-right">
				<input type="password" name="login_pw_new_password_confirm" class="login-input-text">
			</div>
		</div>
		<div class="login-data-row login-button-row">
			<div class="login-data-left">
				&nbsp;
			</div>
			<div class="login-data-right">
				<button type="submit" name="login_change_password" class="login-button" value="{PASSWORD_CHANGE_BUTTON_VALUE}">{PASSWORD_CHANGE_BUTTON_VALUE}</button>
			</div>
			<div class="login-data-button">
				<button type="button" name="login_pw_change" class="login-button" value="{CLOSE}" OnClick="ShowHideDiv('loginPasswordChangeBox'); return false;">{CLOSE}</button>
			</div>
		</div>
	</div>
</div>
{PASSWORD_CHANGE_SHOW}
HTML;
			// phpcs:enable Generic.Files.LineLength
		}
		if ($this->password_forgot) {
			// TODO: create a password forget request flow
		}
		if (!$this->password_change && !$this->password_forgot) {
			$strings = array_merge($strings, [
				'JS_SHOW_HIDE' => '',
				'PASSWORD_CHANGE_BUTTON' => '',
				'PASSWORD_CHANGE_DIV' => ''
			]);
		}

		// first check if all strings are set from outside,
		// if not, set with default ones
		foreach ($strings as $string => $data) {
			if (!array_key_exists($string, $this->login_template['strings'])) {
				$this->login_template['strings'][$string] = $data;
			}
		}

		// now check templates
		if (!$this->login_template['template']) {
			// phpcs:disable Generic.Files.LineLength
			$this->login_template['template'] = <<<HTML
<!DOCTYPE html>
<html lang="{LANGUAGE}">
<head>
	<meta charset="utf-8">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0">
	<title>{HTML_TITLE}</title>
	<style type="text/css">
body {
	margin: 0;
	padding: 0;
	background-color: #ffffff;
	width: 100%;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	box-sizing: border-box;
}
.login-box {
	margin: 50px 0;
	width: 100%;
}
.login-title {
	font-size: 2em;
	padding: 1% 0 1% 10%;
	background-color: hsl(0, 0%, 90%);
}
.login-error {
	margin: 2% 5%;
}
.login-data {
	margin: 2% 5% 5% 5%;
}
.login-data-row {
	display: flex;
	justify-content: flex-start;
	padding: 5px 0;
}
.login-data-row .login-data-left {
	width: 10%;
	font-size: 0.8em;
	text-align: right;
	padding-right: 5px;
	margin: auto 0;
}
.login-data-row .login-data-right {
	width: 20%;
}
.login-data-row .login-data-button {
	width: 70%;
	text-align: right;
}
input.login-input-text {
	font-size: 1.3em;
}
button.login-button {
	font-size: 1.3em;
}
.login-visible {
	visibility: visible;
}
.login-hidden {
	visibility: hidden;
	display: none;
}
.login-password-change {
	position: absolute;
	top: 10%;
	left: 10%;
	width: 80%;
	background-color: white;
	border: 1px solid black;
}
@media only screen and ( max-width: 749px ) {
	.login-box {
		margin: 5% 0;
	}
	.login-data {
		margin: 5%;
	}
	.login-error {
		margin: 10% 5%;
	}
	.login-data-row {
		display: block;
	}
	.login-data-row .login-data-left,
	.login-data-row .login-data-right,
	.login-data-row .login-data-button {
		width: auto;
	}
	.login-data-row .login-data-left {
		text-align: left;
		margin-bottom: 2%;
	}
	.login-data-row .login-data-button {
		text-align: left;
		margin-top: 5%;
	}
	.login-password-change {
		top: 5%;
		left: 5%;
		width: 90%;
	}
}
</style>
	<script language="JavaScript">
{JS_SHOW_HIDE}
	</script>
{LOGOUT_TARGET}
</head>

<body bgcolor="#FFFFFF">
<form method="post">
<div id="loginBox" class="login-box">
	<div id="loginTitle" class="login-title">
		{TITLE}
	</div>
	<div id="loginError" class="login-error {ERROR_VISIBLE}">
		{ERROR_MSG}
	</div>
	<div id="loginData" class="login-data">
		<div class="login-data-row">
			<div class="login-data-left">
				{USERNAME}
			</div>
			<div class="login-data-right">
				<input type="text" name="login_username" class="login-input-text">
			</div>
		</div>
		<div class="login-data-row">
			<div class="login-data-left">
				{PASSWORD}
			</div>
			<div class="login-data-right">
				<input type="password" name="login_password" class="login-input-text">
			</div>
		</div>
		<div class="login-data-row login-button-row">
			<div class="login-data-left">
				&nbsp;
			</div>
			<div class="login-data-right">
				<button type="submit" name="login_login" class="login-button" value="{LOGIN}">{LOGIN}</button>
				{PASSWORD_CHANGE_BUTTON}
			</div>
		</div>
	</div>
	{PASSWORD_CHANGE_DIV}
</div>
</form>
</body>
</html>
HTML;
			// phpcs:enable Generic.Files.LineLength
		}
	}

	// MARK: LOGGING

	/**
	 * writes detailed data into the edit user log table (keep log what user does)
	 *
	 * @param  string     $event    string of what has been done
	 * @param  string     $data     data information (id, etc)
	 * @param  string|int $error    error id (mostly an int)
	 * @param  string     $username login user username
	 * @return void                 has no return
	 */
	private function writeEditLog(
		string $event,
		string $data,
		string|int $error = '',
		string $username = ''
	): void {
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
				'error' => $this->login_error,
				'data' => $data,
		];
		$_action_set = [
			'action' => $this->action,
			'action_id' => $this->username,
			'action_flag' => (string)$this->login_error,
			'action_value' => (string)$this->permission_okay,
		];

		$this->writeLog($event, $_data_binary, $_action_set, $error, $username);
	}

	/**
	 * writes all action vars plus other info into edit_log table
	 * this is for public class
	 *
	 * phpcs:disable Generic.Files.LineLength
	 * @param  string              $event [default='']        any kind of event description,
	 * @param  string|array<mixed> $data [default='']         any kind of data related to that event
	 * @param  array{action?:?string,action_id?:null|string|int,action_sub_id?:null|string|int,action_yes?:null|string|int|bool,action_flag?:?string,action_menu?:?string,action_loaded?:?string,action_value?:?string,action_type?:?string,action_error?:?string} $action_set [default=[]] action set names
	 * @param  string|int          $error    error id (mostly an int)
	 * @param  string              $write_type [default=JSON] write type can be
	 *                                                        JSON, STRING/SERIEAL, BINARY/BZIP or ZLIB
	 * @param  string|null         $db_schema [default=null]  override target schema
	 * @return void
	 * phpcs:enable Generic.Files.LineLength
	 */
	public function writeLog(
		string $event = '',
		string|array $data = '',
		array $action_set = [],
		string|int $error = '',
		string $username = '',
		string $write_type = 'JSON',
		?string $db_schema = null
	): void {
		$data_binary = '';
		$data_write = '';

		// check if write type is valid, if not fallback to JSON
		if (!in_array(strtoupper($write_type), $this->write_types_available)) {
			$this->log->warning('Write type not in allowed array, fallback to JSON', context:[
				"write_type" => $write_type,
				"write_list" => $this->write_types_available,
			]);
			$write_type = 'JSON';
		}
		switch ($write_type) {
			case 'BINARY':
			case 'BZIP':
				$data_binary = $this->db->dbEscapeBytea((string)bzcompress(serialize($data)));
				$data_write = Json::jsonConvertArrayTo([
					'type' => 'BZIP',
					'message' => 'see bzip compressed data_binary field'
				]);
				break;
			case 'ZLIB':
				$data_binary = $this->db->dbEscapeBytea((string)gzcompress(serialize($data)));
				$data_write = Json::jsonConvertArrayTo([
					'type' => 'ZLIB',
					'message' => 'see zlib compressed data_binary field'
				]);
				break;
			case 'STRING':
			case 'SERIAL':
				$data_binary = $this->db->dbEscapeBytea(Json::jsonConvertArrayTo([
					'type' => 'SERIAL',
					'message' => 'see serial string data field'
				]));
				$data_write = serialize($data);
				break;
			case 'JSON':
				$data_binary = $this->db->dbEscapeBytea(Json::jsonConvertArrayTo([
					'type' => 'JSON',
					'message' => 'see json string data field'
				]));
				// must be converted to array
				if (!is_array($data)) {
					$data = ["data" => $data];
				}
				$data_write = Json::jsonConvertArrayTo($data);
				break;
			default:
				$this->log->alert('Invalid type for data compression was set', context:[
					"write_type" => $write_type
				]);
				break;
		}

		/** @var string $DB_SCHEMA check schema */
		$DB_SCHEMA = 'public';
		if ($db_schema !== null) {
			$DB_SCHEMA = $db_schema;
		} elseif (!empty($this->db->dbGetSchema())) {
			$DB_SCHEMA = $this->db->dbGetSchema();
		}
		$q = <<<SQL
		INSERT INTO {DB_SCHEMA}.edit_log (
			username, euid, eucuid, eucuuid, event_date, event, error, data, data_binary, page,
			ip, ip_address, user_agent, referer, script_name, query_string, request_scheme, server_name,
			http_host, http_data, session_id,
			action_data
		) VALUES (
			-- ROW 1
			$1, $2, $3, $4, NOW(), $5, $6, $7, $8, $9,
			-- ROW 2
			$10, $11, $12, $13, $14, $15, $16, $17,
			-- ROW 3
			$18, $19, $20,
			-- ROW 4
			$21
		)
		SQL;
		$this->db->dbExecParams(
			str_replace(
				['{DB_SCHEMA}'],
				[$DB_SCHEMA],
				$q
			),
			[
				// row 1
				empty($username) ? $this->session->get('LOGIN_USER_NAME') ?? '' : $username,
				is_numeric($this->session->get('LOGIN_EUID')) ?
					$this->session->get('LOGIN_EUID') : null,
				is_string($this->session->get('LOGIN_EUCUID')) ?
					$this->session->get('LOGIN_EUCUID') : null,
				!empty($this->session->get('LOGIN_EUCUUID')) &&
					Uids::validateUuuidv4($this->session->get('LOGIN_EUCUUID')) ?
					$this->session->get('LOGIN_EUCUUID') : null,
				(string)$event,
				(string)$error,
				$data_write,
				$data_binary,
				(string)$this->page_name,
				// row 2
				$_SERVER["REMOTE_ADDR"] ?? null,
				Json::jsonConvertArrayTo([
					'REMOTE_ADDR' => $_SERVER["REMOTE_ADDR"] ?? null,
					'HTTP_X_FORWARDED_FOR' => !empty($_SERVER['HTTP_X_FORWARDED_FOR']) ?
						explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])
						: [],
					'CLIENT_IP' => !empty($_SERVER['CLIENT_IP']) ?
						explode(',', $_SERVER['CLIENT_IP'])
						: [],
				]),
				$_SERVER['HTTP_USER_AGENT'] ?? null,
				$_SERVER['HTTP_REFERER'] ?? null,
				$_SERVER['SCRIPT_FILENAME'] ?? null,
				$_SERVER['QUERY_STRING'] ?? null,
				$_SERVER['REQUEST_SCHEME'] ?? null,
				$_SERVER['SERVER_NAME'] ?? null,
				// row 3
				$_SERVER['HTTP_HOST'] ?? null,
				Json::jsonConvertArrayTo([
					'HTTP_ACCEPT' => $_SERVER['HTTP_ACCEPT'] ?? null,
					'HTTP_ACCEPT_CHARSET' => $_SERVER['HTTP_ACCEPT_CHARSET'] ?? null,
					'HTTP_ACCEPT_LANGUAGE' => $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null,
					'HTTP_ACCEPT_ENCODING' => $_SERVER['HTTP_ACCEPT_ENCODING'] ?? null,
				]),
				$this->session->getSessionId() !== '' ?
					$this->session->getSessionId() : null,
				// row 4
				// action data as JSONB
				Json::jsonConvertArrayTo([
					'action' => $action_set['action'] ?? null,
					'action_id' => $action_set['action_id'] ?? null,
					'action_sub_id' => $action_set['action_sub_id'] ?? null,
					'action_yes' => $action_set['action_yes'] ?? null,
					'action_flag' => $action_set['action_flag'] ?? null,
					'action_menu' => $action_set['action_menu'] ?? null,
					'action_loaded' => $action_set['action_loaded'] ?? null,
					'action_value' => $action_set['action_value'] ?? null,
					'action_type' => $action_set['action_type'] ?? null,
					'action_error' => $action_set['action_error'] ?? null,
				])
			],
			'NULL'
		);
	}

	/**
	 * set the write types that are allowed
	 *
	 * @return void
	 */
	private function loginSetEditLogWriteTypeAvailable()
	{
		// check what edit log data write types are allowed
		$this->write_types_available = self::WRITE_TYPES;
		if (!function_exists('bzcompress')) {
			$this->write_types_available = array_diff($this->write_types_available, ['BINARY', 'BZIP']);
		}
		if (!function_exists('gzcompress')) {
			$this->write_types_available = array_diff($this->write_types_available, ['LZIP']);
		}
	}

	// *************************************************************************
	// **** PUBLIC INTERNAL
	// *************************************************************************

	// MARK: MASTER PUBLIC LOGIN CALL

	/**
	 * Main call that needs to be run to actaully check for login
	 * If this is not called, no login checks are done, unless the class
	 * is initialzied with the legacy call parameter.
	 * If ajax_page is true or AJAX_PAGE global var is true then the internal
	 * ajax flag will be set and no echo or exit will be done.
	 *
	 * @param  bool $ajax_page [false] Set to true to never print out anythng
	 * @return void
	 */
	public function loginMainCall(bool $ajax_page = false): void
	{
		// start with no error
		$this->login_error = 0;
		// set db special errors
		if (!$this->db->dbGetConnectionStatus()) {
			$this->login_error = 1;
			echo 'Could not connect to DB<br>';
			// if I can't connect to the DB to auth exit hard. No access allowed
			$this->loginTerminate('Could not connect to DB', 1000);
		}
		// initial the session if there is no session running already
		// check if session exists and could be created
		if ($this->session->checkActiveSession() === false) {
			$this->login_error = 2;
			echo '<b>No active session found</b>';
			$this->loginTerminate('No active session found', 2000);
		}
		// set internal page name
		$this->page_name = $this->loginReadPageName();

		// set global is ajax page for if we show the data directly,
		// or need to pass it back
		// to the continue AJAX class for output back to the user
		$this->login_is_ajax_page = false;
		if ($ajax_page === true || !empty($GLOBALS['AJAX_PAGE'])) {
			$this->login_is_ajax_page = true;
		}

		// attach outside uid for login post > get > empty
		$this->login_user_id = $_POST['loginUserId'] ?? $_GET['loginUserId'] ?? '';
		// cleanup only alphanumeric
		if (!empty($this->login_user_id)) {
			// set post/get only if actually set
			if (isset($_POST['loginUserId'])) {
				$this->login_user_id_source = 'POST';
			} elseif (isset($_GET['loginUserId'])) {
				$this->login_user_id_source = 'GET';
			}
			// clean login user id
			$login_user_id_changed = 0;
			$this->login_user_id = preg_replace(
				"/[^A-Za-z0-9]/",
				'',
				$this->login_user_id,
				-1,
				$login_user_id_changed
			) ?? '';
			// flag unclean input data
			if ($login_user_id_changed > 0) {
				$this->login_user_id_unclear = true;
				// error for invalid user id?
				$this->log->error('LOGIN USER ID: Invalid characters: '
					. $login_user_id_changed . ' in loginUserId: '
					. $this->login_user_id . ' (' . $this->login_user_id_source . ')');
			}
		}
		// if there is none, there is none, saves me POST/GET check
		$this->edit_user_cuuid = (string)($this->session->get('LOGIN_EUCUUID') ?? '');
		// get login vars, are so, can't be changed
		// prepare
		// pass on vars to Object vars
		$this->login = $_POST['login_login'] ?? '';
		$this->username = $_POST['login_username'] ?? '';
		$this->password = $_POST['login_password'] ?? '';
		$this->logout = $_POST['login_logout'] ?? '';
		// password change vars
		$this->change_password = $_POST['login_change_password'] ?? '';
		$this->pw_username = $_POST['login_pw_username'] ?? '';
		$this->pw_old_password = $_POST['login_pw_old_password'] ?? '';
		$this->pw_new_password = $_POST['login_pw_new_password'] ?? '';
		$this->pw_new_password_confirm = $_POST['login_pw_new_password_confirm'] ?? '';
		// disallow user list for password change
		$this->pw_change_deny_users = ['admin'];
		// max login counts before error reporting
		$this->max_login_error_count = 10;
		// users that never get locked, even if they are set strict
		$this->lock_deny_users = ['admin'];

		// if username & password & !$euid start login
		$this->loginLoginUser();
		// checks if $euid given check if user is okay for that site
		$this->loginCheckPermissions();
		// logout user
		$this->loginLogoutUser();
		// set headers for enhanced security
		$this->loginEnhanceHttpSecurity();
		// ** LANGUAGE SET AFTER LOGIN **
		$this->loginSetLocale();
		// load translator
		$this->l = new \CoreLibs\Language\L10n(
			$this->locale['locale'],
			$this->locale['domain'],
			$this->locale['path']
		);
		// if the password change flag is okay, run the password change method
		if ($this->password_change) {
			$this->loginPasswordChange();
		}
		// password forgot
		if ($this->password_forgot) {
			$this->loginPasswordForgot();
		}
		// if !$euid || permission not okay, print login screan
		$this->login_html = $this->loginCreateLoginHTML();
		// closing all connections, depending on error status, exit
		if (!$this->loginCloseClass()) {
			// if variable AJAX flag is not set, show output
			// else pass through for ajax work
			if ($this->login_is_ajax_page === false) {
				// the login screen if we hav no login permission and
				// login screen html data
				if ($this->login_html !== null) {
					// echo $this->login_html;
					$this->loginPrintLogin();
				}
				// exit so we don't process anything further, at all
				$this->loginTerminate('Exit after non ajax page load', 100);
			} else {
				// if we are on an ajax page reset any POST/GET array data to avoid
				// any accidentical processing going on
				$_POST = [];
				$_GET = [];
				// set the action to login so we can trigger special login html return
				$_POST['action'] = 'login';
				$_POST['login_exit'] = 100;
				$_POST['login_error'] = $this->loginGetLastErrorCode();
				$_POST['login_error_text'] = $this->loginGetErrorMsg(
					$this->loginGetLastErrorCode(),
					true
				);
				$_POST['login_html'] = $this->login_html;
				// NOTE: this part needs to be catched by the frontend AJAX
				// and some function needs to then set something like this
				// document.getElementsByTagName('html')[0].innerHTML  = data.content.login_html;
			}
		}
		// set acls for this user/group and this page
		$this->loginSetAcl();
	}

	// MARK: setters/getters

	/**
	 * Returns current set login_html content
	 *
	 * @return string login page html content, created, empty string if none
	 */
	public function loginGetLoginHTML(): string
	{
		return $this->login_html ?? '';
	}

	/**
	 * return the current set page name or empty string for nothing set
	 *
	 * @return string current page name set
	 */
	public function loginGetPageName(): string
	{
		return $this->page_name;
	}

	/**
	 * Returns the current flag if this call is for an ajax type apge
	 *
	 * @return bool True for yes, False for normal HTML return
	 */
	public function loginGetAjaxFlag(): bool
	{
		return $this->login_is_ajax_page;
	}

	/**
	 * Returns current set loginUserId or empty if unset
	 *
	 * @return string loginUserId or empty string for not set
	 */
	public function loginGetLoginUserId(): string
	{
		return $this->login_user_id;
	}

	/**
	 * Returns GET/POST for where the loginUserId was set
	 *
	 * @return string GET or POST or empty string for not set
	 */
	public function loginGetLoginUserIdSource(): string
	{
		return $this->login_user_id_source;
	}

	/**
	 * Returns unclear login user id state. If true then illegal characters
	 * where present in the loginUserId parameter
	 *
	 * @return bool False for clear, True if illegal characters found
	 */
	public function loginGetLoginUserIdUnclean(): bool
	{
		return $this->login_user_id_unclear;
	}

	/**
	 * Return locale settings with
	 * locale
	 * domain
	 * encoding
	 * path
	 *
	 * empty string if not set
	 *
	 * @return array<string,string> Locale settings
	 */
	public function loginGetLocale(): array
	{
		return $this->locale;
	}

	/**
	 * return header color or null for not set
	 *
	 * @return string|null Header color in RGB hex with leading sharp
	 */
	public function loginGetHeaderColor(): ?string
	{
		return $this->session->get('LOGIN_HEADER_COLOR');
	}

	/**
	 * Return the current loaded list of pages the user can access
	 *
	 * @return array<mixed>
	 */
	public function loginGetPages(): array
	{

		return $this->session->get('LOGIN_PAGES');
	}

	/**
	 * Return the current loaded list of pages the user can access
	 *
	 * @return array<mixed>
	 */
	public function loginGetPageLookupList(): array
	{
		return $this->session->get('LOGIN_PAGES_LOOKUP');
	}

	/**
	 * Check access to a file in the pages list
	 *
	 * @param  string $filename File name to check
	 * @return bool             True if page in list and anything other than None access, False if failed
	 */
	public function loginPageAccessAllowed(string $filename): bool
	{
		return (
			$this->session->get('LOGIN_PAGES')[
				$this->session->get('LOGIN_PAGES_LOOKUP')[$filename] ?? ''
			] ?? 0
		) != 0 ? true : false;
	}

	// MARK: logged in uid(pk)/eucuid/eucuuid

	/**
	 * Get the current set EUID (edit user id)
	 *
	 * @return string EUID as string
	 */
	public function loginGetEuid(): string
	{
		return (string)$this->edit_user_id;
	}

	/**
	 * Get the current set EUCUID (edit user cuid)
	 *
	 * @return string EUCUID as string
	 */
	public function loginGetEuCuid(): string
	{
		return (string)$this->edit_user_cuid;
	}

	/**
	 * Get the current set EUCUUID (edit user cuuid)
	 *
	 * @return string EUCUUID as string
	 * @deprecated Wrong name, use ->loginGetEuCuuid
	 */
	public function loginGetEcuuid(): string
	{
		return (string)$this->edit_user_cuuid;
	}

	/**
	 * Get the current set EUCUUID (edit user cuuid)
	 *
	 * @return string EUCUUID as string
	 */
	public function loginGetEuCuuid(): string
	{
		return (string)$this->edit_user_cuuid;
	}

	// MARK: get error messages

	/**
	 * returns the last set error code
	 *
	 * @return int Last set error code, 0 for no error
	 */
	public function loginGetLastErrorCode(): int
	{
		return $this->login_error;
	}

	/**
	 * return set error message
	 * if nothing found for given code, return general error message
	 *
	 * @param  int    $code The error code for which we want the error string
	 * @param  bool   $text If set to true, do not use HTML code
	 * @return string       Error string
	 */
	public function loginGetErrorMsg(int $code, bool $text = false): string
	{
		$string = '';
		if (
			!empty($this->login_error_msg[(string)$code]['msg']) &&
			!empty($this->login_error_msg[(string)$code]['flag'])
		) {
			$error_str_prefix = '';
			switch ($this->login_error_msg[(string)$code]['flag']) {
				case 'e':
					$error_str_prefix = ($text ? '' : '<span style="color: red;">')
						. $this->l->__('Fatal Error:')
						. ($text ? '' : '</span>');
					break;
				case 'o':
					$error_str_prefix = $this->l->__('Success:');
					break;
			}
			$string = $error_str_prefix . ' '
				. ($text ? '' : '<b>')
				. $this->login_error_msg[(string)$code]['msg']
				. ($text ? '' : '</b>');
		} elseif (!empty($code)) {
			$string = $this->l->__('LOGIN: undefined error message');
		}
		return $string;
	}

	// MARK: password checks

	/**
	 * Sets the minium length and checks on valid.
	 * Current max length is 255 characters
	 *
	 * @param  int  $length set the minimum length
	 * @return bool         true/false on success
	 */
	public function loginSetPasswordMinLength(int $length): bool
	{
		// check that numeric, positive numeric, not longer than max input string lenght
		// and not short than min password length
		if (
			$length >= $this->password_min_length_max &&
			$length <= $this->password_max_length &&
			$length <= self::PASSWORD_MAX_LENGTH
		) {
			$this->password_min_length = $length;
			return true;
		}
		return false;
	}

	/**
	 * return password min/max length values as selected
	 * min: return current minimum lenght
	 * max: return current set maximum length
	 * min_length: get the fixed minimum password length
	 *
	 * @param  string $select Can be min/max or min_length
	 * @return int
	 */
	public function loginGetPasswordLenght(string $select): int
	{
		$value = 0;
		switch (strtolower($select)) {
			case 'min':
			case 'lower':
				$value = $this->password_min_length;
				break;
			case 'max':
			case 'upper':
				$value = $this->password_max_length;
				break;
			case 'minimum_length':
			case 'min_length':
			case 'length':
				$value = $this->password_min_length_max;
				break;
		}
		return $value;
	}

	// MARK: max login count

	/**
	 * Set the maximum login errors a user can have before getting locked
	 * if the user has the strict lock setting turned on
	 *
	 * @param  int  $times Value can be -1 (no locking) or greater than 0
	 * @return bool        True on sueccess set, or false on error
	 */
	public function loginSetMaxLoginErrorCount(int $times): bool
	{
		if ($times == -1 || $times > 0) {
			$this->max_login_error_count = $times;
			return true;
		}
		return false;
	}

	/**
	 * Get the current maximum login error count
	 *
	 * @return int Current set max login error count, Can be -1 or greater than 0
	 */
	public function loginGetMaxLoginErrorCount(): int
	{
		return $this->max_login_error_count;
	}

	// MARK: LGOUT USER

	/**
	 * if a user pressed on logout, destroyes session and unsets all global vars
	 *
	 * @return void has no return
	 */
	public function loginLogoutUser(): void
	{
		// must be either logout or error
		if (!$this->logout && !$this->login_error) {
			return;
		}
		// unset session vars set/used in this login
		$this->session->sessionDestroy();
		// unset euid
		$this->edit_user_id = null;
		$this->edit_user_cuid = null;
		$this->edit_user_cuuid = null;
		// then prints the login screen again
		$this->permission_okay = false;
	}

	// MARK: logged in user permssion check

	/**
	 * for every page the user access this script checks if he is allowed to do so
	 *
	 * @return bool permission okay as true/false
	 */
	public function loginCheckPermissions(): bool
	{
		// start with not allowed
		$this->permission_okay = false;
		// bail for no euid (no login)
		if (empty($this->edit_user_cuuid)) {
			return $this->permission_okay;
		}
		// euid must match eucuid and eucuuid
		// bail for previous wrong page match, eg if method is called twice
		if ($this->login_error == 103) {
			return $this->permission_okay;
		}
		$q = <<<SQL
		SELECT
			ep.filename, eu.edit_user_id, eu.cuid, eu.cuuid, eu.force_logout,
			-- base lock flags
			eu.deleted, eu.enabled, eu.locked,
			-- date based lock
			CASE WHEN (
				(
					eu.lock_until IS NULL
					OR (eu.lock_until IS NOT NULL AND NOW() >= eu.lock_until)
				)
				AND (
					eu.lock_after IS NULL
					OR (eu.lock_after IS NOT NULL AND NOW() <= eu.lock_after)
				)
			) THEN 0::INT ELSE 1::INT END locked_period,
			-- login id validation
			login_user_id,
			CASE WHEN (
				(
					eu.login_user_id_valid_from IS NULL
					OR (eu.login_user_id_valid_from IS NOT NULL AND NOW() >= eu.login_user_id_valid_from)
				)
				AND (
					eu.login_user_id_valid_until IS NULL
					OR (eu.login_user_id_valid_until IS NOT NULL AND NOW() <= eu.login_user_id_valid_until)
				)
			) THEN 1::INT ELSE 0::INT END AS login_user_id_valid_date,
			-- check if user must login
			CASE WHEN
				eu.login_user_id_revalidate_after IS NOT NULL
				AND eu.login_user_id_revalidate_after > '0 days'::INTERVAL
				AND eu.login_user_id_last_revalidate + eu.login_user_id_revalidate_after <= NOW()::DATE
			THEN 1::INT ELSE 0::INT END AS login_user_id_revalidate,
			eu.login_user_id_locked
		--
		FROM
			edit_page ep, edit_page_access epa, edit_group eg, edit_user eu
		WHERE
			ep.edit_page_id = epa.edit_page_id
			AND eg.edit_group_id = epa.edit_group_id
			AND eg.edit_group_id = eu.edit_group_id
			AND eg.enabled = 1 AND epa.enabled = 1
			AND eu.cuuid = $1
			AND ep.filename = $2
		SQL;
		$res = $this->db->dbReturnRowParams($q, [$this->edit_user_cuuid, $this->page_name]);
		if (!is_array($res)) {
			$this->login_error = 109;
			return $this->permission_okay;
		}
		if (
			!$this->loginValidationCheck(
				(int)$res['deleted'],
				(int)$res['enabled'],
				(int)$res['locked'],
				(int)$res['locked_period'],
				(int)$res['login_user_id_locked'],
				(int)$res['force_logout']
			)
		) {
			// errors set in method
			return $this->permission_okay;
		}
		// if login user id parameter and no username, check period here
		if (
			empty($this->username) &&
			!empty($this->login_user_id) &&
			!$this->loginLoginUserIdCheck(
				(int)$res['login_user_id_valid_date'],
				(int)$res['login_user_id_revalidate']
			)
		) {
			// errors set in method
			return $this->permission_okay;
		}
		if (isset($res['filename']) && $res['filename'] == $this->page_name) {
			$this->permission_okay = true;
		} else {
			$this->login_error = 103;
		}
		// set all the internal vars
		$this->loginSetEditUserUidData($res);
		// if called from public, so we can check if the permissions are ok
		return $this->permission_okay;
	}

	/**
	 * Return current permission status;
	 *
	 * @return bool True for permission ok, False for not
	 */
	public function loginGetPermissionOkay(): bool
	{
		return $this->permission_okay;
	}

	// MARK: ACL acess check

	/**
	 * Check if source (page, base) is matching to the given min access string
	 * min access string must be valid access level string (eg read, mod, write)
	 * This does not take in account admin flag set
	 *
	 * @param  string $source     a valid base level string eg base, page
	 * @param  string $min_access a valid min level string, eg read, mod, siteadmin
	 * @return bool               True for valid access, False for invalid
	 */
	public function loginCheckAccess(string $source, string $min_access): bool
	{
		if (!in_array($source, ['page', 'base'])) {
			$source = 'base';
		}
		if (
			empty($this->acl['min'][$min_access]) ||
			empty($this->acl[$source])
		) {
			return false;
		}
		// phan claims $this->acl['min'] can be null, but above should skip
		/** @phan-suppress-next-line PhanTypeArraySuspiciousNullable */
		if ($this->acl[$source] >= $this->acl['min'][$min_access]) {
			return true;
		}
		return false;
	}

	/**
	 * check if min accesss string (eg, read, mod, etc) is matchable
	 * EQUAL to BASE set right
	 *
	 * @param  string $min_access
	 * @return bool
	 */
	public function loginCheckAccessBase(string $min_access): bool
	{
		return $this->loginCheckAccess('base', $min_access);
	}

	/**
	 * check if min accesss string (eg, read, mod, etc) is matchable
	 * EQUAL to PAGE set right
	 *
	 * @param  string $min_access
	 * @return bool
	 */
	public function loginCheckAccessPage(string $min_access): bool
	{
		return $this->loginCheckAccess('page', $min_access);
	}

	/**
	 * Return ACL array as is
	 *
	 * @return array<mixed>
	 */
	public function loginGetAcl(): array
	{
		return $this->acl;
	}

	/**
	 * return full default acl list or a list entry if level is set and found
	 * for getting level from list type
	 * $login->loginGetAclList('list')['level'] ?? 0
	 *
	 * @param  int|null $level  Level to get or null/empty for full list
	 * @return array<string,mixed> Full default ACL level list or level entry if found
	 */
	public function loginGetAclList(?int $level = null): array
	{
		// if no level given, return full list
		if (empty($level)) {
			return $this->default_acl_list;
		}
		// if level given and exist return this array block (name/level)
		if (!empty($this->default_acl_list[$level])) {
			return $this->default_acl_list[$level];
		} else {
			// else return empty array
			return [];
		}
	}

	/**
	 * return level number in int from acl list depending on level
	 * if not found return false
	 *
	 * @param  string   $type Type name to look in the acl list
	 * @return int|bool       Either int level or false for not found
	 */
	public function loginGetAclListFromType(string $type): int|bool
	{
		if (!isset($this->default_acl_list_type[$type])) {
			return false;
		}
		return (int)$this->default_acl_list_type[$type];
	}

	// MARK: edit access helpers

	/**
	 * checks if this edit access id is valid
	 *
	 * @param  int|null $edit_access_id access id pk to check
	 * @return bool                     true/false: if the edit access is not
	 *                                  in the valid list: false
	 * @deprecated Please switch to using edit access cuid check with ->loginCheckEditAccessCuid()
	 */
	public function loginCheckEditAccess(?int $edit_access_id): bool
	{
		if ($edit_access_id === null) {
			return false;
		}
		if (array_key_exists($edit_access_id, $this->acl['unit_legacy'])) {
			return true;
		}
		return false;
	}

	/**
	 * check if this edit access cuid is valid
	 *
	 * @param  string|null $cuid
	 * @return bool
	 */
	public function loginCheckEditAccessCuid(?string $cuid): bool
	{
		if ($cuid === null) {
			return false;
		}
		if (array_key_exists($cuid, $this->acl['unit'])) {
			return true;
		}
		return false;
	}

	/**
	 * checks that the given edit access id is valid for this user
	 * return null if nothing set, or the edit access id
	 *
	 * @param  string|null $cuid edit access cuid to check
	 * @return string|null       same edit access cuid if ok
	 *                           or the default edit access id
	 *                           if given one is not valid
	 */
	public function loginCheckEditAccessValidCuid(?string $cuid): ?string
	{
		if (
			$cuid !== null &&
			is_array($this->session->get('LOGIN_UNIT')) &&
			!array_key_exists($cuid, $this->session->get('LOGIN_UNIT'))
		) {
			$cuid = null;
			if (!empty($this->session->get('LOGIN_UNIT_DEFAULT_EACUID'))) {
				$cuid = $this->session->get('LOGIN_UNIT_DEFAULT_EACUID');
			}
		}
		return $cuid;
	}

	/**
	 * checks that the given edit access id is valid for this user
	 * return null if nothing set, or the edit access id
	 *
	 * @param  int|null $edit_access_id edit access id to check
	 * @return int|null                 same edit access id if ok
	 *                                  or the default edit access id
	 *                                  if given one is not valid
	 * @#deprecated Please switch to using edit access cuid check with ->loginCheckEditAccessValidCuid()
	 */
	public function loginCheckEditAccessId(?int $edit_access_id): ?int
	{
		if (
			$edit_access_id !== null &&
			is_array($this->session->get('LOGIN_UNIT_LEGACY')) &&
			!array_key_exists($edit_access_id, $this->session->get('LOGIN_UNIT_LEGACY'))
		) {
			$edit_access_id = null;
			if (!empty($this->session->get('LOGIN_UNIT_DEFAULT_EAID'))) {
				$edit_access_id = (int)$this->session->get('LOGIN_UNIT_DEFAULT_EAID');
			}
		}
		return $edit_access_id;
	}

	/**
	 * return a set entry from the UNIT session for an edit access cuid
	 * if not found return false
	 *
	 * @param  string     $cuid     edit access cuid
	 * @param  string|int $data_key key value to search for
	 * @return false|string         false for not found or string for found data
	 */
	public function loginGetEditAccessData(
		string $cuid,
		string|int $data_key
	): false|string {
		if (!isset($_SESSION['LOGIN_UNIT'][$cuid]['data'][$data_key])) {
			return false;
		}
		return $_SESSION['LOGIN_UNIT'][$cuid]['data'][$data_key];
	}

	/**
	 * Return edit access primary key id from edit access uid
	 * false on not found
	 *
	 * @param  string   $uid Edit Access UID to look for
	 * @return int|false     Either primary key in int or false in bool for not found
	 * @deprecated use loginGetEditAccessCuidFromUid
	 */
	public function loginGetEditAccessIdFromUid(string $uid): int|false
	{
		if (!isset($_SESSION['LOGIN_UNIT_UID'][$uid])) {
			return false;
		}
		return (int)$_SESSION['LOGIN_UNIT_UID'][$uid];
	}

	/**
	 * Get the edit access UID from the edit access CUID
	 *
	 * @param  string   $uid
	 * @return int|false
	 */
	public function loginGetEditAccessCuidFromUid(string $uid): int|false
	{
		if (!isset($_SESSION['LOGIN_UNIT_CUID'][$uid])) {
			return false;
		}
		return (int)$_SESSION['LOGIN_UNIT_CUID'][$uid];
	}

	/**
	 * Legacy lookup for edit access id to cuid
	 *
	 * @param  int          $id edit access id PK
	 * @return string|false     edit access cuid or false if not found
	 */
	public function loginGetEditAccessCuidFromId(int $id): string|false
	{
		if (!isset($_SESSION['LOGIN_UNIT_LEGACY'][$id])) {
			return false;
		}
		return (string)$_SESSION['LOGIN_UNIT_LEGACY'][$id]['cuid'];
	}

	/**
	 * This is a Legacy lookup from the edit access id to cuid for further lookups in the normal list
	 *
	 * @param  string    $cuid edit access cuid
	 * @return int|false       false on not found or edit access id PK
	 */
	public function loginGetEditAccessIdFromCuid(string $cuid): int|false
	{
		if (!isset($_SESSION['LOGIN_UNIT'][$cuid])) {
			return false;
		}
		return $_SESSION['LOGIN_UNIT'][$cuid]['id'];
	}

	/**
	 * Check if admin flag is set
	 *
	 * @return bool True if admin flag set
	 */
	public function loginIsAdmin(): bool
	{
		if (!empty($this->acl['admin'])) {
			return true;
		}
		return false;
	}

	// MARK: various basic login id checks

	/**
	 * Returns true if login button was pressed
	 *
	 * @return bool If login action was run, return true
	 */
	public function loginActionRun(): bool
	{
		return empty($this->login) ? false : true;
	}
}

// __END__

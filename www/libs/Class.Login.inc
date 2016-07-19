<?
	/*********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2000/06/01
	* VERSION: 4.0.0
	* RELEASED LICENSE: BSD style (use it, u don't have to make YOUR source public)
	*                   but let me know if u made changes, and please don't redistribute it
	*                   with your name on it ...
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
	* 2010/12/17 (cs) change that password can be blowfish encrypted, auto detects if other encryption is used (md5, std des) and tries to use them
	* 2007/05/29 (cs) BUG with assign query and visible sub arrays to pages
	* 2005/09/21 (cs) if error -> unset the session vars
	* 2005/07/04 (cs) add a function to write into the edit log file
	* 2005/07/01 (cs) start adepting login class to new edit interface layout
	* 2005/03/31 (cs) fixed the class call with all debug vars
	* 2004/11/17 (cs) unused var cleanup
	* 2004/11/16 (cs) rewrite login so it uses a template and not just plain html. prepare it, so it will be able to use external stuff later (some interface has to be designed for that
	* 2004/11/16 (cs) removed the mobile html part from login	* 2004/09/30 (cs) layout fix
	*   2003-11-11: if user has debug 1 unset memlimit, because there can be serious problems with the query logging
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

	// try to include file from LIBS path, or from normal path
	_spl_autoload('Class.DB.IO.inc');

	class login extends db_io
	{
		private $euid; // the user id var
		private $permission_okay = 0; // is set to one if login okay, or EUID is set and user is okay to access this page
		public $login; // pressed login
		private $username; // login name
		private $password; // login password
		private $logout; // logout button
		private $login_error; // login error code, can be matched to the array login_error_msg, which holds the string
		private $password_change = false; // if this is set to true, the user can change passwords
		private $pw_change_deny_users = array (); // array of users for which the password change is forbidden

		// all possible login error conditions
		private $login_error_msg = array ();
		// this is an array holding all strings & templates passed from the outside (translation)
		private $login_template = array ('strings' => array (), 'password_change' => '', 'template' => '');

		// acl vars
		public $acl = array ();
		public $default_acl_list = array ();

		// METHOD: login
		// PARAMS: db_config -> array for logging in to DB where edit_users tables are
		//        db_debug -> sets debug output for db_io (can be overruled with DB_DEBUG)
		// RETURN: none
		// DESC  : cunstroctuor, does ALL, opens db, works through connection checks, closes itself
		public function __construct($db_config, $lang = 'en_utf8', $debug = 0, $db_debug = 0, $echo = 1, $print = 0)
		{
			// create db connection and init base class
			parent::__construct($db_config, $debug, $db_debug, $echo, $print);

			// no session could be found at all
			if (!session_id())
			{
				echo "<b>Session not started!</b><br>Use 'session_start();'.<br>For less problems with other session, you can set a session name with 'session_name(\"name\");'.<br>";
				exit;
			}

			// set log file name
			$this->file_name_ext = '_login_'.date('Y-m-d').'.log';

			// get the language sub class & init it
			_spl_autoload('Class.l10n.inc');
			$this->l = new l10n($lang);

			// if we have a search path we need to set it, to use the correct DB to login
			// check what schema to use. if there is a login schema use this, else check if there is a schema set in the config, or fall back to DB_SCHEMA if this exists, if this also does not exists use public schema
			$SCHEMA = defined('LOGIN_DB_SCHEMA') ? LOGIN_DB_SCHEMA : ($db_config['db_schema'] ? $db_config['db_schema'] : (defined('DB_SCHEMA') ? DB_SCHEMA : 'public'));
			$this->db_exec("SET search_path TO ".$SCHEMA);
			$this->euid = array_key_exists('EUID', $_SESSION) ? $_SESSION['EUID'] : 0; // if there is none, there is none, saves me POST/GET check
			// get login vars, are so, can't be changed
			// prepare
			if (!isset($_POST['login_login']))
				$_POST['login_login'] = '';
			if (!isset($_POST['login_username']))
				$_POST['login_username'] = '';
			if (!isset($_POST['login_password']))
				$_POST['login_password'] = '';
			if (!isset($_POST['login_logout']))
				$_POST['login_logout'] = '';
			if (!isset($_POST['change_password']))
				$_POST['change_password'] = '';
			if (!isset($_POST['pw_username']))
				$_POST['pw_username'] = '';
			if (!isset($_POST['pw_old_password']))
				$_POST['pw_old_password'] = '';
			if (!isset($_POST['pw_new_password']))
				$_POST['pw_new_password'] = '';
			if (!isset($_POST['pw_new_password_confirm']))
				$_POST['pw_new_password_confirm'] = '';
			// pass on vars to Object vars
			$this->login = $_POST["login_login"];
			$this->username = $_POST["login_username"];
			$this->password = $_POST["login_password"];
			$this->logout = $_POST["login_logout"];
			// password change vars
			$this->change_password = $_POST["change_password"];
			$this->pw_username = $_POST['pw_username'];
			$this->pw_old_password = $_POST['pw_old_password'];
			$this->pw_new_password = $_POST['pw_new_password'];
			$this->pw_new_password_confirm = $_POST['pw_new_password_confirm'];
			// logout target (from config)
			$this->logout_target = LOGOUT_TARGET;
			// disallow user list for password change
			$this->pw_change_deny_users = array ('admin');
			// set flag if password change is okay
			if (defined('PASSWORD_CHANGE'))
				$this->password_change = PASSWORD_CHANGE;
			// max login counts before error reporting
			$this->max_login_error_count = 10;
			// users that never get locked, even if they are set strict
			$this->lock_deny_users = array ('admin');

			// internal
			$this->class_info["login"] = array(
				"class_name" => "Login",
				"class_version" => "4.0.0",
				"class_created" => "2000-06-01",
				"class_author" => "cs/gullevek/at"
			);

			// init default ACL list array
			$_SESSION['DEFAULT_ACL_LIST'] = array ();
			// read the current edit_access_right list into an array
			$q = "SELECT level, type, name FROM edit_access_right WHERE level >= 0 ORDER BY level";
			while ($res = $this->db_return($q))
			{
				// level to description format (numeric)
				$this->default_acl_list[$res['level']] = array (
					'type' => $res['type'],
					'name' => $res['name']
				);
			}
			// write that into the session
			$_SESSION['DEFAULT_ACL_LIST'] = $this->default_acl_list;

			// if username & password & !$euid start login
			$this->login_login_user(); 
			// checks if $euid given check if user is okay for that side
			$this->login_check_permissions();
			// logsout user
			$this->login_logout_user();
			// if the password change flag is okay, run the password change method
			if ($this->password_change)
				$this->login_password_change();
			// if !$euid || permission not okay, print login screan
			echo $this->login_print_login();
			// closing all connections, depending on error status, exit
			if (!$this->login_close_class())
			{
				// do not go anywhere, quit processing here
				// do something with possible debug data?
				if (TARGET == 'live' || TARGET == 'remote')
				{
					// login
					$this->debug_output_all = DEBUG ? 1 : 0;
					$this->echo_output_all = 0;
					$this->print_output_all = DEBUG ? 1 : 0;
				}
				$status_msg = $this->print_error_msg();
				if ($this->echo_output_all)
					echo $status_msg;
				exit;
			}
			// set acls for this user/group and this page
			$this->login_set_acl();
		}

		// METHOD: _login
		// PARAMS: none
		// RETURN: none
		// DESC  : deconstructory, called with the last function to close DB connection
		public function __destruct()
		{
			parent::__destruct();
		}
 
		// METHOD: login_login_user
		// PARAMS: none
		// RETURN: none
		// DESC  : if user pressed login button this script is called, but only if there is no preview euid set
		private function login_login_user()
		{
			// have to get the global stuff here for setting it later
			if (!$this->euid && $this->login)
			{
				if (!($this->password && $this->username))
				{
					$this->login_error = 102;
				}
				else
				{
					// we have to get the themes in here too
					$q = "SELECT eu.edit_user_id, username, password, eu.edit_group_id, eg.name AS edit_group_name, admin, eu.login_error_count, eu.login_error_date_last, eu.login_error_date_first, eu.strict, eu.locked, ";
					$q .= "debug, db_debug, ";
					$q .= "eareu.level AS user_level, eareu.type AS user_type, ";
					$q .= "eareg.level AS group_level, eareg.type AS group_type, ";
					$q .= "eu.enabled, el.short_name AS lang_short, el.iso_name AS lang_iso, first.header_color AS first_header_color, second.header_color AS second_header_color, second.template ";
					$q .= "FROM edit_user eu ";
					$q .= "LEFT JOIN edit_scheme second ON (second.edit_scheme_id = eu.edit_scheme_id AND second.enabled = 1), ";
					$q .= "edit_language el, edit_group eg, ";
					$q .= "edit_access_right eareu, ";
					$q .= "edit_access_right eareg, ";
					$q .= "edit_scheme first ";
					$q .= "WHERE first.edit_scheme_id = eg.edit_scheme_id AND eu.edit_group_id = eg.edit_group_id AND eu.edit_language_id = el.edit_language_id AND ";
					$q .= "eu.edit_access_right_id = eareu.edit_access_right_id AND ";
					$q .= "eg.edit_access_right_id = eareg.edit_access_right_id AND ";
					// password match is done in script, against old plain or new blowfish encypted
					$q .= "(LOWER(username) = '".strtolower($this->username)."') ";
					$res = $this->db_return($q);
					// username is wrong, but we throw for wrong username and wrong password the same error
					if (!$this->cursor_ext[md5($q)]["num_rows"])
					{
						$this->login_error = 1010;
					}
					else
					{
						// if login errors is half of max errors and the last login error was less than 10s ago, forbid any new login try

						// check with what kind of prefix the password begins:
						// $2a$ or $2y$: BLOWFISCH
						// $1$: MD5
						// $ and one alphanumeric letter, 13 chars long, but nor $ at the end: STD_DESC
						// if no $ => normal password
						// NOW, if we have a password encoded, but not the correct encoder available, throw special error

						// check flow
						// - user is enabled
						// - user is not locked
						// - password is readable
						// - encrypted password matches
						// - plain password matches

						// user is enabled
						if (!$res["enabled"])
						{
							$this->login_error = 104;
						}
						// user is locked, either set or auto set
						elseif ($res['locked'])
						{
							$this->login_error = 105;
						}
						elseif ((preg_match("/^\\$2(a|y)\\$/", $res['password']) && CRYPT_BLOWFISH != 1) || (preg_match("/^\\$1\\$/", $res['password']) && CRYPT_MD5 != 1) || (preg_match("/^\\$[0-9A-Za-z.]{12}$/", $res['password']) && CRYPT_STD_DES != 1))
						{
							$this->login_error = 9999; // this means password cannot be decrypted because of missing crypt methods
						}
						// check passwword as crypted, $2a$ or $2y$ is blowfish start, $1$ is MD5 start, $\w{12} is standard DES
						elseif ((preg_match("/^\\$2(a|y)\\$/", $res['password']) || preg_match("/^\\$1\\$/", $res['password']) || preg_match("/^\\$[0-9A-Za-z.]{12}$/", $res['password'])) && !$this->verifyCryptString($this->password, $res['password']))
						{
							$this->login_error = 1011;
						}
						// check old plain password, non case sensitive
						elseif (!preg_match("/^\\$2(a|y)\\$/", $res['password']) && !preg_match("/^\\$1\\$/", $res['password']) && !preg_match("/^\\$[0-9A-Za-z.]{12}$/", $res['password']) && $res['password'] != $this->password)
						{
							$this->login_error = 1012;
						}
						// nromal user processing
						else
						{
							// set class var and session var
							$_SESSION["EUID"] = $this->euid = $res["edit_user_id"];
							// check if user is okay
							$this->login_check_permissions();
							if (!$this->login_error)
							{
								// now set all session vars and read page permissions
								$GLOBALS["DEBUG_ALL"] = $_SESSION["DEBUG_ALL"] = $res["debug"];
								$GLOBALS["DB_DEBUG"] = $_SESSION["DB_DEBUG"] = $res["db_debug"];
								$_SESSION["USER_NAME"] = $res["username"];
								$_SESSION["ADMIN"] = $res["admin"];
								$_SESSION["GROUP_NAME"] = $res["edit_group_name"];
								$_SESSION["USER_ACL_LEVEL"] = $res["user_level"];
								$_SESSION["USER_ACL_TYPE"] = $res["user_type"];
								$_SESSION["GROUP_ACL_LEVEL"] = $res["group_level"];
								$_SESSION["GROUP_ACL_TYPE"] = $res["group_type"];
								$_SESSION["TEMPLATE"] = ($res["template"]) ? $res["template"] : DEFAULT_TEMPLATE;
								$_SESSION["HEADER_COLOR"] = ($res["second_header_color"]) ? $res["second_header_color"] : $res["first_header_color"];
								$_SESSION["LANG"] = $res["lang_short"];
								$_SESSION["DEFAULT_CHARSET"] = $res["lang_iso"];
								$_SESSION["DEFAULT_LANG"] = $res["lang_short"].'_'.strtolower(str_replace('-', '', $res["lang_iso"]));
								// reset any login error count for this user
								if ($res['login_error_count'] > 0)
								{
									$q = "UPDATE edit_user SET login_error_count = 0, login_error_date_last = NULL, login_error_date_first = NULL WHERE edit_user_id = ".$res['edit_user_id'];
									$this->db_exec($q);
								}
								$pages = array();
								$edit_page_ids = array();
								// set pages access
								$q = "SELECT ep.edit_page_id, filename, ep.name AS edit_page_name, ep.order_number AS edit_page_order, menu, popup, popup_x, popup_y, online, ear.level, ear.type ";
								$q .= "FROM edit_page ep, edit_page_access epa, edit_access_right ear ";
								$q .= "WHERE ep.edit_page_id = epa.edit_page_id AND ear.edit_access_right_id = epa.edit_access_right_id ";
								$q .= "AND epa.enabled = 1 AND epa.edit_group_id = ".$res["edit_group_id"]." ";
								$q .= "ORDER BY ep.order_number";
								while ($res = $this->db_return($q))
								{
									// page id array for sub data readout
									$edit_page_ids[] = $res['edit_page_id'];
									// create the array for pages
									array_push($pages, array (
										"edit_page_id" => $res["edit_page_id"],
										"filename" => $res["filename"],
										"page_name" => $res["edit_page_name"],
										"order" => $res['edit_page_order'],
										"menu" => $res["menu"],
										"popup" => $res["popup"],
										"popup_x" => $res["popup_x"],
										"popup_y" => $res["popup_y"],
										"online" => $res["online"],
										"acl_level" => $res["level"],
										"acl_type" => $res["type"],
										"query" => array (),
										"visible" => array ()
									));
									// make reference filename -> level
									$pages_acl[$res["filename"]] = $res["level"];

								} // for each page
								// get the visible groups for all pages and write them to the pages
								$_edit_page_id = 0;
								$q = "SELECT epvg.edit_page_id, name, flag FROM edit_visible_group evp, edit_page_visible_group epvg WHERE evp.edit_visible_group_id = epvg.edit_visible_group_id AND epvg.edit_page_id IN (".join(', ', $edit_page_ids).") ORDER BY epvg.edit_page_id";
								while ($res = $this->db_return($q))
								{
									if ($res['edit_page_id'] != $_edit_page_id)
									{
										// search the pos in the array push
										$pos = $this->array_search_recursive($res['edit_page_id'], $pages, 'edit_page_id');
										$_edit_page_id = $res['edit_page_id'];
									}
									$pages[$pos[0]]['visible'][$res['name']] = $res['flag'];
								}
								// get the same for the query strings
								$_edit_page_id = 0;
								$q = "SELECT eqs.edit_page_id, name, value, dynamic FROM edit_query_string eqs WHERE enabled = 1 AND edit_page_id IN (".join(', ', $edit_page_ids).") ORDER BY eqs.edit_page_id";
								while ($res = $this->db_return($q))
								{
									if ($res['edit_page_id'] != $_edit_page_id)
									{
										// search the pos in the array push
										$pos = $this->array_search_recursive($res['edit_page_id'], $pages, 'edit_page_id');
										$_edit_page_id = $res['edit_page_id'];
									}
									$pages[$pos[0]]['query'][] = array (
										"name" => $res['name'],
										"value" => $res['value'],
										"dynamic" => $res['dynamic']
									);
								}

								$_SESSION["PAGES"] = $pages;
								$_SESSION["PAGES_ACL_LEVEL"] = $pages_acl;
								// load the edit_access user rights
								$q = "SELECT ea.edit_access_id, level, type, ea.name, ea.color, ea.uid, edit_default ";
								$q .= "FROM edit_access_user eau, edit_access_right ear, edit_access ea ";
								$q .= "WHERE eau.edit_access_id = ea.edit_access_id AND eau.edit_access_right_id = ear.edit_access_right_id AND eau.enabled = 1 AND edit_user_id = ".$this->euid." ";
								$q .= "ORDER BY ea.name";
								$unit_access = array();
								$eauid = array();
								$unit_acl = array();
								while ($res = $this->db_return($q))
								{
									// read edit access data fields and drop them into the unit access array
									$q_sub ="SELECT name, value FROM edit_access_data WHERE edit_access_id = ".$res['edit_access_id'];
									$ea_data = array ();
									while ($res_sub = $this->db_return($q_sub))
									{
										$ea_data[$res_sub['name']] = $res['value'];
									}
									// build master unit array
									$unit_access[$res['edit_access_id']] = array (
										"id" => $res['edit_access_id'],
										"acl_level" => $res["level"],
										"acl_type" => $res["type"],
										"name" => $res["name"],
										"uid" => $res['uid'],
										"color" => $res["color"],
										"default" => $res["edit_default"],
										'data' => $ea_data
									);
									// set the default unit
									if ($res['edit_default'])
										$_SESSION["UNIT_DEFAULT"] = $res['edit_access_id'];
									// sub arrays for simple access
									array_push($eauid, $res['edit_access_id']);
									$unit_acl[$res['edit_access_id']] = $res['level'];
								}
								$_SESSION["UNIT"] = $unit_access;
								$_SESSION["UNIT_ACL_LEVEL"] = $unit_acl;
								$_SESSION['EAID'] = $eauid;
							} // user has permission to THIS page
						} // user was not enabled or other login error
						if ($this->login_error)
						{
							if ($res['login_error_count'] == 0)
								$login_error_date_first = ', login_error_date_first = NOW()';
							// update login error count for this user
							$q = "UPDATE edit_user SET login_error_count = login_error_count + 1, login_error_date_last = NOW() $login_error_date_first WHERE edit_user_id = ".$res['edit_user_id'];
							$this->db_exec($q);
							// totally lock the user if error max is reached
							if ($res['login_error_count'] + 1 > $this->max_login_error_count)
							{
								// do some alert reporting in case this error is too big
								// if strict is set, lock this user
								// this needs manual unlocking by an admin user
								if ($res['strict'] && !in_array($this->username, $this->lock_deny_users))
								{
									$q = "UPDATE edit_user SET locked = 1 WHERE edit_user_id = ".$res['edit_user_id'];
								}
							}
						}
					} // user was not found
				} // if not username AND password where given
				// if there was an login error, show login screen
				if ($this->login_error)
				{
					// reset the perm var, to confirm logout
					$this->permission_okay = 0;
				}
			} // if he pressed login at least and is not yet loggined in
		}

		// METHOD: login_check_permission
		// PARAMS: none
		// RETUNR none
		// DESC  : for every page the user access this script checks if he is allowed to do so
		public function login_check_permissions()
		{
			if ($this->euid && $this->login_error != 103)
			{
				$q = "SELECT filename ";
				$q .= "FROM edit_page ep, edit_page_access epa, edit_group eg, edit_user eu ";
				$q .= "WHERE ep.edit_page_id = epa.edit_page_id AND eg.edit_group_id = epa.edit_group_id AND eg.edit_group_id = eu.edit_group_id ";
				$q .= "AND eu.edit_user_id = ".$this->euid." AND filename = '".$this->page_name."' AND eg.enabled = 1 AND epa.enabled = 1";
				$res = $this->db_return_row($q);
				// unset mem limit if debug is set to 1
//				if (($GLOBALS["DEBUG_ALL"] || $GLOBALS["DB_DEBUG"] || $_SESSION["DEBUG_ALL"] || $_SESSION["DB_DEBUG"]) && ini_get('memory_limit') != -1)
//					ini_set('memory_limit', -1);
				if ($res["filename"] == $this->page_name)
				{
					$this->permission_okay = 1;
				}
				else
				{
					$this->login_error = 103;
					$this->permission_okay = 0;
				}
			}
			// if called from public, so we can check if the permissions are ok
			return $this->permission_okay;
		}

		// METHOD: login_logout_user
		// PARAMS: none
		// RETURN: none
		// DESC  : if a user pressed on logout, destroyes session and unsets all global vars
		public function login_logout_user()
		{
			if ($this->logout || $this->login_error)
			{
				// unregister and destroy session vars
				unset($_SESSION["EUID"]);
				unset($_SESSION["GROUP_LEVEL"]);
				unset($_SESSION["PAGES"]);
				unset($_SESSION["USER_NAME"]);
				unset($_SESSION["UNIT"]);
				unset($_SESSION["DEBUG_ALL"]);
				unset($_SESSION["DB_DEBUG"]);
				unset($GLOBALS["DEBUG_ALL"]);
				unset($GLOBALS["DB_DEBUG"]);
				unset($_SESSION["LANG"]);
				unset($_SESSION["DEFAULT_CHARSET"]);
				unset($_SESSION["DEFAULT_LANG"]);
				unset($_SESSION["GROUP_NAME"]);
				unset($_SESSION["HEADER_COLOR"]);
				session_destroy();
				// he prints the login screen again
				$this->permission_okay = 0;
			}
		}

		// METHOD: login_set_acl
		// PARAMS: none
		// RETURN: none
		// DESC  : sets all the basic ACLs
		//         init set the basic acl the user has, based on the following rules
		//         * init set from config DEFAULT ACL
		//         * if page ACL is set, it overrides the default ACL
		//         * if group ACL is set, it overrides the page ACL
		//         * if user ACL is set, it overrides the group ACL
		//         set the page ACL
		//         * default ACL set
		//         * set group ACL if not default overrides default ACL
		//         * set page ACL if not default overrides group ACL
		//         set edit access ACL and set default edit access group
		//         * if an account ACL is set, set this parallel, account ACL overrides user ACL if it applies
		//         * if edit access ACL level is set, use this, else use page
		//         set all base ACL levels as a list keyword -> ACL number
		public function login_set_acl()
		{
			// we start with the default acl
			$this->acl['base'] = DEFAULT_ACL_LEVEL;

			// set admin flag and base to 100
			if ($_SESSION['ADMIN'])
			{
				$this->acl['admin'] = 1;
				$this->acl['base'] = 100;
			}
			else
			{
				// now go throw the flow and set the correct ACL
				// user > page > group
				// group ACL 0
				if ($_SESSION['GROUP_ACL_LEVEL'] != -1)
				{
					$this->acl['base'] = $_SESSION['GROUP_ACL_LEVEL'];
				}
				// page ACL 1
				if ($_SESSION['PAGES_ACL_LEVEL'][$this->page_name] != -1)
				{
					$this->acl['base'] = $_SESSION['PAGES_ACL_LEVEL'][$this->page_name];
				}
				// user ACL 2
				if ($_SESSION['USER_ACL_LEVEL'] != -1)
				{
					$this->acl['base'] = $_SESSION['USER_ACL_LEVEL'];
				}
			}

			// set the current page acl
			// start with default acl
			// set group if not -1, overrides default
			// set page if not -1, overrides group set
			$this->acl['page'] = DEFAULT_ACL_LEVEL;
			if ($_SESSION['GROUP_ACL_LEVEL'] != -1)
			{
				$this->acl['page'] = $_SESSION['GROUP_ACL_LEVEL'];
			}
			if ($_SESSION['PAGES_ACL_LEVEL'][$this->page_name] != -1)
			{
				$this->acl['page'] = $_SESSION['PAGES_ACL_LEVEL'][$this->page_name];
			}

			// PER ACCOUNT (UNIT/edit access)->
			foreach ($_SESSION['UNIT'] as $ea_id => $unit)
			{
				// if admin flag is set, all units are set to 100
				if ($this->acl['admin'])
				{
					$this->acl['unit'][$ea_id] = $this->acl['base'];
				}
				else
				{
					if ($unit['acl_level'] != -1)
						$this->acl['unit'][$ea_id] = $unit['acl_level'];
					else
						$this->acl['unit'][$ea_id] = $this->acl['base'];
				}
				// detail name/level set
				$this->acl['unit_detail'][$ea_id] = array (
					'name' => $unit['name'],
					'uid' => $unit['uid'],
					'level' => $this->default_acl_list[$this->acl['unit'][$ea_id]]['name'],
					'default' => $unit['default'],
					'data' => $unit['data']
				);
				// set default
				if ($unit['default'])
				{
					$this->acl['unit_id'] = $unit['id'];
					$this->acl['unit_name'] = $unit['name'];
					$this->acl['unit_uid'] = $unit['uid'];
				}
			}
			// flag if to show extra edit access drop downs (because user has multiple groups assigned)
			if (count($_SESSION['UNIT']) > 1)
				$this->acl['show_ea_extra'] = 1;
			else
				$this->acl['show_ea_extra'] = 0;
			// set the default edit access
			$this->acl['default_edit_access'] = $_SESSION['UNIT_DEFAULT'];
			// integrate the type acl list, but only for the keyword -> level
			foreach ($this->default_acl_list as $level => $data)
			{
				$this->acl['min'][$data['type']] = $level;
			}
			// set the full acl list too
			$this->acl['acl_list'] = $_SESSION['DEFAULT_ACL_LIST'];
			// debug
//			$this->debug('ACL', $this->print_ar($this->acl));
		}

		// METHOD: login_check_edit_access
		// PARAMS: edit_access_id to check
		// RETURN: true/false: if the edit access is not in the valid list: false
		// DESC  : checks if this edit access id is valid
		public function login_check_edit_access($edit_access_id)
		{
			if (array_key_exists($edit_access_id, $this->acl['info']['edit_access']))
				return true;
			else
				return false;
		}

		// METHOD: login_password_change
		// PARAMS: none
		// RETURN: none
		// DESC  : changes a user password
		private function login_password_change()
		{
			if ($this->change_password)
			{
				$event = 'Password Change';
				// check that given username is NOT in the deny list, else silent skip (with error log)
				if (!in_array($this->pw_username, $this->pw_change_deny_users))
				{
					if (!$this->pw_username || !$this->pw_password)
					{
						$this->login_error = 200;
						$data = 'Missing username or old password.';
					}
					// check user exist, if not -> error
					if (!$this->login_error)
					{
						$q = "SELECT edit_user_id FROM edit_user WHERE enabled = 1 AND username = '".$this->db_escape_string($this->pw_username)."'";
						list ($edit_user_id) = $this->db_return_row($q);
						if (!$edit_user_id)
						{
							// username wrong
							$this->login_error = 201;
							$data = 'User could not be found';
						}
					}
					// check old passwords match -> error
					if (!$this->login_error)
					{
						$q = "SELECT edit_user_id FROM edit_user WHERE enabled = 1 AND username = '".$this->db_escape_string($this->pw_username)."' AND password = '".$this->db_escape_string($this->pw_old_password)."'";
						list ($edit_user_id) = $this->db_return_row($q);
						if (!$edit_user_id)
						{
							// old password wrong
							$this->login_error = 202;
							$data = 'The old password does not match';
						}
					}
					// check if new passwords were filled out -> error
					if (!$this->login_error)
					{
						if (!$this->pw_new_password || !$this->pw_new_password_confirm)
						{
							$this->login_error = 203;
							$data = 'Missing new password or new password confirm.';
						}
					}
					// check new passwords both match -> error
					if (!$this->login_error)
					{
						if ($this->pw_new_password != $this->pw_new_password_confirm)
						{
							$this->login_error = 204;
							$data = 'The new passwords do not match: '.$this->pw_new_password.' == '.$this->pw_new_password_confirm;
						}
					}
					// no error change this users password
					if (!$this->login_error)
					{
						// update the user (edit_user_id) with the new password
						$q = "UPDATE edit_user SET password = '".$this->db_escape_string($this->cryptString($this->pw_new_password))."' WHERE edit_user_id = ".$edit_user_id;
						$this->db_exec($q);
						$data = 'Password change for user "'.$this->pw_username.'" from "'.$this->pw_old_password.'" to "'.$this->pw_new_password.'"';
					}
				}
				else
				{
					// illegal user error
					$this->login_error = '220';
					$data = 'Illegal user for password change: '.$this->pw_username;
				}
				// log this password change attempt
				$this->write_log($event, $data, $this->login_error, $pw_username, $pw_old_password);
			} // button pressed
		}

		// METHOD: login_print_login
		// PARAMS: none
		// RETURN: none
		// DESC  : prints out login html part if no permission (error) is set
		private function login_print_login()
		{
			if (!$this->permission_okay)
			{
				// set the templates now
				$this->login_set_templates();
				// if there is a global logout target ...
				if (file_exists($this->logout_target) && $this->logout_target)
				{
					$LOGOUT_TARGET = $this->logout_target;
				} else
				{
					$LOGOUT_TARGET = "";
				}

				$html_string = $this->login_template['template'];

				// if password change is okay
				if ($this->password_change)
				{
					$html_string_password_change = $this->login_template['password_change'];

					// pre change the data in the PASSWORD_CHANGE_DIV first
					foreach ($this->login_template['strings'] as $string => $data)
					{
						if ($data)
							$html_string_password_change = str_replace("{".$string."}", $data, $html_string_password_change);
					}
					$this->login_template['strings']['PASSWORD_CHANGE_DIV'] = $html_string_password_change;
				}

				// put in the logout redirect string
				if ($this->logout && $LOGOUT_TARGET)
					$html_string = str_replace("{LOGOUT_TARGET}", '<meta http-equiv="refresh" content="0; URL='.$LOGOUT_TARGET.'">', $html_string);
				else
					$html_string = str_replace("{LOGOUT_TARGET}", '', $html_string);

				// print error messagae
				if ($this->login_error)
					$html_string = str_replace("{ERROR_MSG}", $this->login_error_msg[$this->login_error]."<br>", $html_string);
				else
					$html_string = str_replace("{ERROR_MSG}", "<br>", $html_string);

				// create the replace array context
				foreach ($this->login_template['strings'] as $string => $data)
				{
					$html_string = str_replace("{".$string."}", $data, $html_string);
				}

				// return the created HTML here
				return $html_string;
			} // if permission is 0 then print out login
		}

		// METHOD: login_close_class
		// PARAMS: none
		// RETURN: none
		// DESC  : last function called, writes log and prints out error msg and exists script if permission 0
		private function login_close_class()
		{
			// write to LOG table ...
			if ($this->login_error || $this->login || $this->logout)
			{
				$username = '';
				$password = '';
				// set event
				if ($this->login)
					$event = "Login";
				else if ($this->logout)
					$event = "Logout";
				else
					$event = "No Permission";
				// prepare for log
				if ($this->euid)
				{
					// get user from user table
					$q = "SELECT username, password FROM edit_user WHERE edit_user_id = ".$this->euid;
					list($username, $password) = $this->db_return_row($q);
				} // if euid is set, get username (or try)
				$this->write_log($event, '', $this->login_error, $username, $password);
			} // write log under certain settings
			// now close DB connection
//			$this->error_msg = $this->_login();
			if (!$this->permission_okay)
			{
				return false;
			}
			else
			{
				return true;
			} 
		}

		// METHOD: login_set_templates
		// PARAMS: 
		// RETURN: none
		// DESC  : checks if there are external templates, if not uses internal fallback ones
		private function login_set_templates()
		{
			$strings = array (
				'HTML_TITLE' => $this->l->__("LOGIN"),
				'TITLE' => $this->l->__("LOGIN"),
				'USERNAME' => $this->l->__("Username"),
				'PASSWORD' => $this->l->__("Password"),
				'LOGIN' => $this->l->__("Login"),
				'ERROR_MSG' => '',
				'LOGOUT_TARGET' => '',
				'PASSWORD_CHANGE_BUTTON_VALUE' => $this->l->__('Change Password')
			);

			$error_msgs = array (
				"100" => $this->l->__("Fatal Error: <b>[EUID] came in as GET/POST!</b>"), // actually obsolete
				"1010" => $this->l->__("Fatal Error: <b>Login Failed - Wrong Username or Password</b>"), // user not found
				"1011" => $this->l->__("Fatal Error: <b>Login Failed - Wrong Username or Password</b>"), // blowfish password wrong
				"1012" => $this->l->__("Fatal Error: <b>Login Failed - Wrong Username or Password</b>"), // fallback md5 password wrong
				"102" => $this->l->__("Fatal Error: <b>Login Failed - Please enter username and password</b>"),
				"103" => $this->l->__("Fatal Error: <b>You do not have the rights to access this Page</b>"),
				"104" => $this->l->__("Fatal Error: <b>Login Failed - User not enabled</b>"),
				"105" => $this->l->__("Fatal Error: <b>Login Failed - User is locked</b>"),
				"220" => $this->l->__("Fatal Error: <b>Password change - The user could not be found</b>"), // actually this is an illegal user, but I mask it
				'200' => $this->l->__("Fatal Error: <b>Password change - Please enter username and old password</b>"),
				"201" => $this->l->__("Fatal Error: <b>Password change - The user could not be found</b>"),
				"202" => $this->l->__("Fatal Error: <b>Password change - The old password is not correct</b>"),
				"203" => $this->l->__("Fatal Error: <b>Password change - Please fill out both new password fields</b>"),
				"204" => $this->l->__("Fatal Error: <b>Password change - The new passwords do not match</b>"),
				"9999" => $this->l->__("Fatal Error: <b>necessary crypt engine could not be found</b>. Login is impossible") // this is bad bad error
			);

			// if password change is okay
			if ($this->password_change)
			{
				$strings = array_merge($strings, array (
					'TITLE_PASSWORD_CHANGE' => 'Change Password for User',
					'OLD_PASSWORD' => $this->l->__("Old Password"),
					'NEW_PASSWORD' => $this->l->__("New Password"),
					'NEW_PASSWORD_CONFIRM' => $this->l->__("New Password confirm"),
					'CLOSE' => $this->l->__('Close'),
					'JS_SHOW_HIDE' => "function ShowHideDiv(id) { element = document.getElementById(id); if (element.className == 'visible' || !element.className) element.className = 'hidden'; else element.className = 'visible'; }",
					'PASSWORD_CHANGE_BUTTON' => '<input type="button" name="pw_change" value="'.$strings['PASSWORD_CHANGE_BUTTON_VALUE'].'" OnClick="ShowHideDiv(\'pw_change_div\');">'
				));
				$this->login_template['password_change'] = <<<EOM
<div id="pw_change_div" class="hidden" style="position: absolute; top: 30px; left: 50px; width: 400px; height: 220px; background-color: white; border: 1px solid black; padding: 25px;">
<table>
<tr><td class="norm" align="center" colspan="2"><h3>{TITLE_PASSWORD_CHANGE}</h3></td></tr>
<tr><td class="norm" colspan="2">{ERROR_MSG}</td></tr>
<tr><td class="norm" align="right">{USERNAME}</td><td><input type="text" name="pw_username" value=""></td></tr>
<tr><td class="norm" align="right">{OLD_PASSWORD}</td><td><input type="password" name="pw_old_password" value=""></td></tr>
<tr><td class="norm" align="right">{NEW_PASSWORD}</td><td><input type="password" name="pw_new_password" value=""></td></tr>
<tr><td class="norm" align="right">{NEW_PASSWORD_CONFIRM}</td><td><input type="password" name="pw_new_password_confirm" value=""></td></tr>
<tr><td></td><td><input type="submit" name="change_password" value="{PASSWORD_CHANGE_BUTTON_VALUE}"><input type="button" name="pw_change" value="{CLOSE}" OnClick="ShowHideDiv('pw_change_div');"></td></tr>
</table>
</div>
EOM;
			}
			else
			{
				$strings = array_merge($strings, array (
					'JS_SHOW_HIDE' => '',
					'PASSWORD_CHANGE_BUTTON' => '',
					'PASSWORD_CHANGE_DIV' => ''
				));
			}

			// first check if all strings are set from outside, if not, set with default ones
			while (list($string, $data) = each($strings))
			{
				if (!array_key_exists($string, $this->login_template['strings']))
				{
					$this->login_template['strings'][$string] = $data;
				}
			}

			// error msgs the same
			while (list($code, $data) = each($error_msgs))
			{
				if (!array_key_exists($code, $this->login_error_msg))
				{
					$this->login_error_msg[$code] = $data;
				}
			}

			// now check templates
			if (!$this->login_template['template'])
			{
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
  <td align="right">
  </td>
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

		// METHOD: write_log
		// PARAMS: event -> string of what has been done
		//        data -> data information (id, etc)
		//        error -> if error, write error string (not enougth data, etc)
		// RETURN: none
		// DESC  : writes detailed data into the edit user log table (keep log what user does)
		private function write_log($event, $data, $error = "", $username = "", $password = "")
		{
			if ($this->login)
					$this->action = 'Login';
			elseif ($this->logout)
					$this->action = 'Logout';
			$_data_binary = array (
					'_SESSION' => $_SESSION,
					'_GET' => $_GET,
					'_POST' => $_POST,
					'_FILES' => $_FILES,
					'error' => $this->login_error
			);
			$data_binary = $this->db_escape_bytea(bzcompress(serialize($_data_binary)));
			// SQL querie for log entry
			$q = "INSERT INTO edit_log ";
			$q .= "(username, password, euid, event_date, event, error, data, data_binary, page, ";
			$q .= "ip, user_agent, referer, script_name, query_string, server_name, http_host, http_accept, http_accept_charset, http_accept_encoding, session_id, ";
			$q .= "action, action_id, action_yes, action_flag, action_menu, action_loaded, action_value, action_error) ";
			$q .= "VALUES ('".$this->db_escape_string($username)."', '".$this->db_escape_string($password)."', ".(($this->euid) ? $this->euid : 'NULL').", NOW(), '".$this->db_escape_string($event)."', '".$this->db_escape_string($error)."', '".$this->db_escape_string($data)."', '".$data_binary."', '".$this->page_name."', ";
			foreach (array('REMOTE_ADDR', 'HTTP_USER_AGENT', 'HTTP_REFERER', 'SCRIPT_FILENAME', 'QUERY_STRING', 'SERVER_NAME', 'HTTP_HOST', 'HTTP_ACCEPT', 'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING') as $server_code)
			{
				if (array_key_exists($server_code, $_SERVER))
					$q .= "'".$this->db_escape_string($_SERVER[$server_code])."', ";
				else
					$q .= "NULL, ";
			}
			$q .= "'".session_id()."', ";
			$q .= "'".$this->db_escape_string($this->action)."', '".$this->db_escape_string($this->username)."', NULL, '".$this->db_escape_string($this->login_error)."', NULL, NULL, '".$this->db_escape_string($this->permission_okay)."', NULL)";
			$this->db_exec($q);
		}

		// METHOD: login_check_edit_access_id
		// PARAMS: edit access id to check
		// RETURN: same edit access id if ok, or the default edit access id if given one is not valud
		// DESC  : checks that the given edit access id is valid for this user
		public function login_check_edit_access_id($edit_access_id)
		{
			if (!array_key_exists($edit_access_id, $_SESSION["UNIT"]))
				return $_SESSION["UNIT_DEFAULT"];
			else
				return $edit_access_id;
		}

	} // close class
?>

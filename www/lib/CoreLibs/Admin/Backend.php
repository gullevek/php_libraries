<?php

/*********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2006/08/15
* VERSION: 1.0.0
* RELEASED LICENSE: GNU GPL 3
* DESCRIPTION
* Basic Admin interface backend
* - sets action flags
* - menu creation
* - array vars for smarty
*
* CHANGE PLAN:
* loads DB\IO + Logger and returns one group object
* also checks all missing CONFIG vars from Basic class
*
* PUBLIC VARIABLES
*
* PRIVATE VARIABLES
*
* PUBLIC METHODS
*
* PRIVATE METHODS
*
* HISTORY:
*
*********************************************************************/

declare(strict_types=1);

namespace CoreLibs\Admin;

class Backend
{
	// page name
	/** @var array<mixed> */
	public $menu = [];
	/** @var int|string */
	public $menu_show_flag = 0; // top menu flag (mostly string)
	// action ids
	/** @var array<string> */
	public $action_list = [
		'action', 'action_id', 'action_sub_id', 'action_yes', 'action_flag',
		'action_menu', 'action_value', 'action_error', 'action_loaded'
	];
	/** @var string */
	public $action;
	/** @var string|int */
	public $action_id;
	/** @var string|int */
	public $action_sub_id;
	/** @var string|int|bool */
	public $action_yes;
	/** @var string */
	public $action_flag;
	/** @var string */
	public $action_menu;
	/** @var string */
	public $action_loaded;
	/** @var string */
	public $action_value;
	/** @var string */
	public $action_error;
	// ACL array variable if we want to set acl data from outisde
	/** @var array<mixed> */
	public $acl = [];
	/** @var int */
	public $default_acl;
	// queue key
	/** @var string */
	public $queue_key;
	// the current active edit access id
	/** @var int */
	public $edit_access_id;
	/** @var string */
	public $page_name;
	// error/warning/info messages
	/** @var array<mixed> */
	public $messages = [];
	/** @var bool */
	public $error = false;
	/** @var bool */
	public $warning = false;
	/** @var bool */
	public $info = false;
	// internal lang & encoding vars
	/** @var string */
	public $lang_dir = '';
	/** @var string */
	public $lang;
	/** @var string */
	public $lang_short;
	/** @var string */
	public $domain;
	/** @var string */
	public $encoding;
	/** @var \CoreLibs\Debug\Logging logger */
	public $log;
	/** @var \CoreLibs\DB\IO database */
	public $db;
	/** @var \CoreLibs\Language\L10n language */
	public $l;
	/** @var \CoreLibs\Create\Session session class */
	public $session;
	// smarty publics [end processing in smarty class]
	/** @var array<mixed> */
	public $DATA;
	/** @var array<mixed> */
	public $HEADER;
	/** @var array<mixed> */
	public $DEBUG_DATA;
	/** @var array<mixed> */
	public $CONTENT_DATA;

	// CONSTRUCTOR / DECONSTRUCTOR |====================================>
	/**
	 * main class constructor
	 *
	 * @param \CoreLibs\DB\IO          $db      Database connection class
	 * @param \CoreLibs\Debug\Logging  $log     Logging class
	 * @param \CoreLibs\Create\Session $session Session interface class
	 * @param \CoreLibs\Language\L10n  $l10n    l10n language class
	 * @param array<string,string>     $locale  locale data read from setLocale
	 */
	public function __construct(
		\CoreLibs\DB\IO $db,
		\CoreLibs\Debug\Logging $log,
		\CoreLibs\Create\Session $session,
		\CoreLibs\Language\L10n $l10n,
		array $locale,
		?int $set_default_acl_level = null
	) {
		// attach db class
		$this->db = $db;
		// set to log not per class
		$log->setLogPer('class', false);
		// attach logger
		$this->log = $log;
		// attach session class
		$this->session = $session;
		// get the language sub class & init it
		$this->l = $l10n;
		// parse and read, legacy stuff
		$this->encoding = $locale['encoding'];
		$this->lang = $locale['lang'];
		// get first part from lang
		$this->lang_short = explode('_', $locale['lang'])[0];
		$this->domain = $this->l->getDomain();
		$this->lang_dir = $this->l->getBaseLocalePath();

		// set the page name
		$this->page_name = \CoreLibs\Get\System::getPageName();

		// set the action ids
		foreach ($this->action_list as $_action) {
			$this->$_action = $_POST[$_action] ?? '';
		}

		if ($set_default_acl_level === null) {
			/** @deprecated Admin::__construct missing default_acl_level parameter */
			trigger_error(
				'Calling Admin::__construct without default_acl_level parameter is deprecated',
				E_USER_DEPRECATED
			);
		}
		$this->default_acl = $set_default_acl_level ?? DEFAULT_ACL_LEVEL;

		// queue key
		if (preg_match("/^(add|save|delete|remove|move|up|down|push_live)$/", $this->action)) {
			$this->queue_key = \CoreLibs\Create\RandomKey::randomKeyGen(3);
		}
	}

	/**
	 * class deconstructor
	 */
	public function __destruct()
	{
		// NO OP
	}

	// PUBLIC METHODS |=================================================>

	/**
	 * set internal ACL from login ACL
	 *
	 * @param array<mixed> $acl login acl array
	 */
	public function setACL(array $acl): void
	{
		$this->acl = $acl;
	}

	/**
	 * writes all action vars plus other info into edit_log table
	 *
	 * @param  string              $event      any kind of event description,
	 * @param  string|array<mixed> $data       any kind of data related to that event
	 * @param  string              $write_type write type can bei STRING or BINARY
	 * @param  string|null         $db_schema  override target schema
	 * @return void
	 */
	public function adbEditLog(
		string $event = '',
		string|array $data = '',
		string $write_type = 'STRING',
		?string $db_schema = null
	): void {
		$data_binary = '';
		$data_write = '';
		if ($write_type == 'BINARY') {
			$data_binary = $this->db->dbEscapeBytea((string)bzcompress(serialize($data)));
			$data_write = 'see bzip compressed data_binary field';
		}
		if ($write_type == 'STRING') {
			$data_binary = '';
			$data_write = $this->db->dbEscapeString(serialize($data));
		}

		/** @var string $DB_SCHEMA check schema */
		$DB_SCHEMA = 'public';
		if ($db_schema !== null) {
			$DB_SCHEMA = $db_schema;
		} elseif (!empty($this->db->dbGetSchema())) {
			$DB_SCHEMA = $this->db->dbGetSchema();
		}
		$q = "INSERT INTO " . $DB_SCHEMA . ".edit_log "
			. "(euid, event_date, event, data, data_binary, page, "
			. "ip, user_agent, referer, script_name, query_string, server_name, http_host, "
			. "http_accept, http_accept_charset, http_accept_encoding, session_id, "
			. "action, action_id, action_yes, action_flag, action_menu, action_loaded, action_value, action_error) "
			. "VALUES "
			. "(" . $this->db->dbEscapeString(isset($_SESSION['EUID']) && is_numeric($_SESSION['EUID']) ?
				$_SESSION['EUID'] :
				'NULL')
			. ", "
			. "NOW(), "
			. "'" . $this->db->dbEscapeString((string)$event) . "', "
			. "'" . $data_write . "', "
			. "'" . $data_binary . "', "
			. "'" . $this->db->dbEscapeString((string)$this->page_name) . "', "
			. "'" . ($_SERVER["REMOTE_ADDR"] ?? '') . "', "
			. "'" . $this->db->dbEscapeString($_SERVER['HTTP_USER_AGENT'] ?? '') . "', "
			. "'" . $this->db->dbEscapeString($_SERVER['HTTP_REFERER'] ?? '') . "', "
			. "'" . $this->db->dbEscapeString($_SERVER['SCRIPT_FILENAME'] ?? '') . "', "
			. "'" . $this->db->dbEscapeString($_SERVER['QUERY_STRING'] ?? '') . "', "
			. "'" . $this->db->dbEscapeString($_SERVER['SERVER_NAME'] ?? '') . "', "
			. "'" . $this->db->dbEscapeString($_SERVER['HTTP_HOST'] ?? '') . "', "
			. "'" . $this->db->dbEscapeString($_SERVER['HTTP_ACCEPT'] ?? '') . "', "
			. "'" . $this->db->dbEscapeString($_SERVER['HTTP_ACCEPT_CHARSET'] ?? '') . "', "
			. "'" . $this->db->dbEscapeString($_SERVER['HTTP_ACCEPT_ENCODING'] ?? '') . "', "
			. ($this->session->getSessionId() === false ?
				"NULL" :
				"'" . $this->session->getSessionId() . "'")
			. ", "
			. "'" . $this->db->dbEscapeString($this->action) . "', "
			. "'" . $this->db->dbEscapeString($this->action_id) . "', "
			. "'" . $this->db->dbEscapeString($this->action_yes) . "', "
			. "'" . $this->db->dbEscapeString($this->action_flag) . "', "
			. "'" . $this->db->dbEscapeString($this->action_menu) . "', "
			. "'" . $this->db->dbEscapeString($this->action_loaded) . "', "
			. "'" . $this->db->dbEscapeString($this->action_value) . "', "
			. "'" . $this->db->dbEscapeString($this->action_error) . "')";
		$this->db->dbExec($q, 'NULL');
	}

	/**
	 * Set the menu show flag
	 *
	 * @param string|int $menu_show_flag
	 * @return string|int
	 */
	public function adbSetMenuShowFlag(string|int $menu_show_flag): string|int
	{
		// must be string or int
		$this->menu_show_flag = $menu_show_flag;
		return $this->menu_show_flag;
	}

	/**
	 * Return the menu show flag
	 *
	 * @return string|int
	 */
	public function adbGetMenuShowFlag(): string|int
	{
		return $this->menu_show_flag;
	}

	/**
	 * menu creater (from login menu session pages)
	 *
	 * @param  string|null $set_content_path
	 * @param  int         $flag             visible flag trigger
	 * @return array<mixed> menu array for output on page (smarty)
	 */
	public function adbTopMenu(
		?string $set_content_path = null,
		int $flag = 0,
	): array {
		if (
			$set_content_path === null ||
			!is_string($set_content_path)
		) {
			/** @deprecated adbTopMenu missing set_content_path parameter */
			trigger_error(
				'Calling adbTopMenu without set_content_path parameter is deprecated',
				E_USER_DEPRECATED
			);
		}
		$set_content_path = $set_content_path ?? CONTENT_PATH;
		if ($this->menu_show_flag) {
			$flag = $this->menu_show_flag;
		}

		// get the session pages array
		$PAGES = $_SESSION['PAGES'] ?? null;
		if (!isset($PAGES) || !is_array($PAGES)) {
			$PAGES = [];
		}
		$pages = [];
		foreach ($PAGES as $PAGE_DATA) {
			$pages[] = $PAGE_DATA;
		}
		// $this->debug('pages', $this->print_ar($pages));
		// if flag is 0, then we show all, else, we show only the matching flagges array points
		// array is already sorted after correct order
		reset($pages);
		foreach ($pages as $data) {
		// for ($i = 0, $iMax = count($pages); $i < $iMax; $i ++) {
			$show = 0;
			// is it visible in the menu & is it online
			if ($data['menu'] && $data['online']) {
				// check if it falls into our flag if we have a flag
				if ($flag) {
					foreach ($data['visible'] as $name => $key) {
						if ($key == $flag) {
							$show = 1;
						}
					}
				} else {
					// if no flag given, show all menu points
					$show = 1;
				}

				if ($show) {
					// if it is popup, write popup arrayound
					if (isset($data['popup']) && $data['popup']) {
						$type = 'popup';
					} else {
						$type = 'normal';
						$data['popup'] = 0;
					}
					$query_string = '';

					if (
						isset($data['query']) &&
						is_array($data['query']) &&
						count($data['query'])
					) {
						// for ($j = 0, $jMax = count($pages[$i]['query']); $j < $jMax; $j ++) {
						foreach ($data['query'] as $j => $query) {
							if (
								!empty($query['name']) &&
								!empty($query['value'])
							) {
								if (strlen($query_string)) {
									$query_string .= '&';
								}
								$query_string .= $query['name'] . '=';
								if (
									isset($query['dynamic']) &&
									$query['dynamic']
								) {
									if (isset($_GET[$query['value']])) {
										$query_string .= urlencode($_GET[$query['value']]);
									} elseif (isset($_POST[$query['value']])) {
										$query_string .= urlencode($_POST[$query['value']]);
									}
								} else {
									$query_string .= urlencode($query['value']);
								}
							}
						}
					}
					$url = '';
					if (isset($data['hostname']) && $data['hostname']) {
						$url .= $data['hostname'];
					}
					$url .= $data['filename'] ?? '';
					if (strlen($query_string)) {
						$url .= '?' . $query_string;
					}
					$name = $data['page_name'] ?? '';
					// if page name matchs -> set selected flag
					$selected = 0;
					if (
						isset($data['filename']) &&
						\CoreLibs\Get\System::getPageName() == $data['filename'] &&
						(!isset($data['hostname']) || (
							isset($data['hostname']) &&
								(!$data['hostname'] || strstr($data['hostname'], $set_content_path) !== false)
						))
					) {
						$selected = 1;
						$this->page_name = $name;
					}
					// last check, is this menu point okay to show
					$enabled = 0;
					if (
						isset($data['filename']) &&
						$this->adbShowMenuPoint($data['filename'])
					) {
						$enabled = 1;
					}
					// write in to view menu array
					array_push($this->menu, [
						'name' => $this->l->__($name),
						'url' => $url,
						'selected' => $selected,
						'enabled' => $enabled,
						'popup' => $type == 'popup' ? 1 : 0,
						'type' => $type
					]);
				} // show page
			} // online and in menu
		} // for each page
		return $this->menu;
	}

	/**
	 * ONLY USED IN adbTopMenu
	 * checks if this filename is in the current situation (user id, etc) available
	 *
	 * @param  string|null $filename filename
	 * @return bool                  true for visible/accessable menu point, false for not
	 */
	private function adbShowMenuPoint(?string $filename): bool
	{
		$enabled = false;
		if ($filename === null) {
			return $enabled;
		}
		/** @phan-suppress-next-line PhanNoopSwitchCases */
		switch ($filename) {
			default:
				$enabled = true;
				break;
		};
		return $enabled;
	}
	/**
	 * wrapper function to fill up the mssages array
	 *
	 * @param  string       $level info/warning/error
	 * @param  string       $msg   string, can be printf formated
	 * @param  array<mixed> $vars  optional data for a possible printf formated msg
	 * @return void                has no return
	 */
	public function adbMsg(string $level, string $msg, array $vars = []): void
	{
		if (!preg_match("/^info|warning|error$/", $level)) {
			$level = "info";
		}
		$this->messages[] = [
			'msg' => vsprintf($this->l->__($msg), $vars),
			'class' => $level
		];
		switch ($level) {
			case 'info':
				$this->info = true;
				break;
			case 'warning':
				$this->warning = true;
				break;
			case 'error':
				$this->error = true;
				break;
		}
	}

	/**
	 * writes live queue
	 *
	 * @param  string      $queue_key string to identfy the queue
	 * @param  string      $type      [description]
	 * @param  string      $target    [description]
	 * @param  string      $data      [description]
	 * @param  string      $key_name  [description]
	 * @param  string      $key_value [description]
	 * @param  string|null $associate [description]
	 * @param  string|null $file      [description]
	 * @param  string|null $db_schema override target schema
	 * @return void
	 */
	public function adbLiveQueue(
		string $queue_key,
		string $type,
		string $target,
		string $data,
		string $key_name,
		string $key_value,
		string $associate = null,
		string $file = null,
		string $db_schema = null,
	): void {
		/** @var string $DB_SCHEMA check schema */
		$DB_SCHEMA = 'public';
		if ($db_schema !== null) {
			$DB_SCHEMA = $db_schema;
		} elseif (!empty($this->db->dbGetSchema())) {
			$DB_SCHEMA = $this->db->dbGetSchema();
		}
		$q = "INSERT INTO " . $DB_SCHEMA . ".live_queue ("
			. "queue_key, key_value, key_name, type, target, data, group_key, action, associate, file"
			. ") VALUES ("
			. "'" . $this->db->dbEscapeString($queue_key) . "', '" . $this->db->dbEscapeString($key_value) . "', "
			. "'" . $this->db->dbEscapeString($key_name) . "', '" . $this->db->dbEscapeString($type) . "', "
			. "'" . $this->db->dbEscapeString($target) . "', '" . $this->db->dbEscapeString($data) . "', "
			. "'" . $this->queue_key . "', '" . $this->action . "', "
			. "'" . $this->db->dbEscapeString((string)$associate) . "', "
			. "'" . $this->db->dbEscapeString((string)$file) . "')";
		$this->db->dbExec($q);
	}

	/**
	 * Basic class holds exact the same, except the Year/Month/Day/etc strings
	 * are translated in this call
	 *
	 * @param  int|string $year          year YYYY
	 * @param  int|string $month         month m
	 * @param  int|string $day           day d
	 * @param  int|string $hour          hour H
	 * @param  int|string $min           min i
	 * @param  string     $suffix        additional info printed after the date time
	 *                                   variable in the drop down
	 *                                   also used for ID in the on change JS call
	 * @param  int        $min_steps     default is 1 (minute), can set to anything,
	 *                                   is used as sum up from 0
	 * @param  bool       $name_pos_back default false, if set to true,
	 *                                   the name will be printend
	 *                                   after the drop down and not before the drop down
	 * @return string                    HTML formated strings for drop down lists
	 *                                   of date and time
	 */
	public function adbPrintDateTime(
		int|string $year,
		int|string $month,
		int|string $day,
		int|string $hour,
		int|string $min,
		string $suffix = '',
		int $min_steps = 1,
		bool $name_pos_back = false
	) {
		// get the build layout
		$html_time = \CoreLibs\Output\Form\Elements::printDateTime(
			$year,
			$month,
			$day,
			$hour,
			$min,
			$suffix,
			$min_steps,
			$name_pos_back
		);
		// translate the strings inside
		foreach (['Year ', 'Month ', 'Day ', 'Hour ', 'Minute '] as $_time) {
			$html_time = str_replace($_time, $this->l->__(str_replace(' ', '', $_time)) . ' ', $html_time);
		}
		// replace week days in short
		foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $_date) {
			$html_time = str_replace('(' . $_date . ')', '(' . $this->l->__($_date) . ')', $html_time);
		}
		// return the datetime select string with strings translated
		return $html_time;
	}
}

// __END__

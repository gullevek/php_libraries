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

use CoreLibs\Convert\Json;

class Backend
{
	// page name
	/** @var array<mixed> */
	public array $menu = [];
	/** @var int|string */
	public int|string $menu_show_flag = 0; // top menu flag (mostly string)
	// action ids
	/** @var array<string> */
	public array $action_list = [
		'action', 'action_id', 'action_sub_id', 'action_yes', 'action_flag',
		'action_menu', 'action_value', 'action_type', 'action_error', 'action_loaded'
	];
	/** @var string */
	public string $action;
	/** @var string|int */
	public string|int $action_id;
	/** @var string|int */
	public string|int $action_sub_id;
	/** @var string|int|bool */
	public string|int|bool $action_yes;
	/** @var string */
	public string $action_flag;
	/** @var string */
	public string $action_menu;
	/** @var string */
	public string $action_loaded;
	/** @var string */
	public string $action_value;
	/** @var string */
	public string $action_type;
	/** @var string */
	public string $action_error;

	// ACL array variable if we want to set acl data from outisde
	/** @var array<mixed> */
	public array $acl = [];
	/** @var int */
	public int $default_acl;

	// queue key
	/** @var string */
	public string $queue_key;

	/** @var array<string> list of allowed types for edit log write */
	private const WRITE_TYPES = ['BINARY', 'BZIP2', 'LZIP', 'STRING', 'SERIAL', 'JSON'];
	/** @var array<string> list of available write types for log */
	private array $write_types_available = [];

	// the current active edit access id
	/** @var int|null */
	public int|null $edit_access_id;
	/** @var string */
	public string $page_name;

	// error/warning/info messages
	/** @var array<mixed> */
	public array $messages = [];
	/** @var bool */
	public bool $error = false;
	/** @var bool */
	public bool $warning = false;
	/** @var bool */
	public bool $info = false;

	// internal lang & encoding vars
	/** @var string */
	public string $lang_dir = '';
	/** @var string */
	public string $lang;
	/** @var string */
	public string $lang_short;
	/** @var string */
	public string $domain;
	/** @var string */
	public string $encoding;

	/** @var \CoreLibs\Logging\Logging logger */
	public \CoreLibs\Logging\Logging $log;
	/** @var \CoreLibs\DB\IO database */
	public \CoreLibs\DB\IO $db;
	/** @var \CoreLibs\Language\L10n language */
	public \CoreLibs\Language\L10n $l;
	/** @var \CoreLibs\Create\Session session class */
	public \CoreLibs\Create\Session $session;

	// smarty publics [end processing in smarty class]
	/** @var array<mixed> */
	public array $DATA = [];
	/** @var array<mixed> */
	public array $HEADER = [];
	/** @var array<mixed> */
	public array $DEBUG_DATA = [];
	/** @var array<mixed> */
	public array $CONTENT_DATA = [];

	// CONSTRUCTOR / DECONSTRUCTOR |====================================>
	/**
	 * main class constructor
	 *
	 * @param \CoreLibs\DB\IO           $db      Database connection class
	 * @param \CoreLibs\Logging\Logging $log     Logging class
	 * @param \CoreLibs\Create\Session  $session Session interface class
	 * @param \CoreLibs\Language\L10n   $l10n    l10n language class
	 * @param int|null                  $set_default_acl_level [default=null] Default ACL level
	 * @param bool                      $init_action_vars [default=true] If the action vars should be set
	 */
	public function __construct(
		\CoreLibs\DB\IO $db,
		\CoreLibs\Logging\Logging $log,
		\CoreLibs\Create\Session $session,
		\CoreLibs\Language\L10n $l10n,
		?int $set_default_acl_level = null,
		bool $init_action_vars = true
	) {
		// attach db class
		$this->db = $db;
		// set to log not per class
		$log->unsetLogFlag(\CoreLibs\Logging\Logger\Flag::per_class);
		// attach logger
		$this->log = $log;
		// attach session class
		$this->session = $session;
		// get the language sub class & init it
		$this->l = $l10n;
		// parse and read, legacy stuff
		$locale = $this->l->getLocaleAsArray();
		$this->encoding = $locale['encoding'];
		$this->lang = $locale['lang'];
		$this->lang_short = $locale['lang_short'];
		$this->domain = $locale['domain'];
		$this->lang_dir = $locale['path'];

		// set the page name
		$this->page_name = \CoreLibs\Get\System::getPageName();

		// NOTE: if any of the "action" vars are used somewhere, it is recommended to NOT set them here
		if ($init_action_vars) {
			$this->adbSetActionVars();
		}

		if ($set_default_acl_level === null) {
			/** @deprecated Admin::__construct missing default_acl_level parameter */
			trigger_error(
				'Calling Admin::__construct without default_acl_level parameter is deprecated',
				E_USER_DEPRECATED
			);
		}
		$this->default_acl = $set_default_acl_level ?? DEFAULT_ACL_LEVEL;
		// if negative or larger than 100, reset to 0
		if ($this->default_acl < 0 || $this->default_acl > 100) {
			$this->default_acl = 0;
		}

		// queue key
		if (preg_match("/^(add|save|delete|remove|move|up|down|push_live)$/", $this->action ?? '')) {
			$this->queue_key = \CoreLibs\Create\RandomKey::randomKeyGen(3);
		}

		// check what edit log data write types are allowed
		$this->adbSetEditLogWriteTypeAvailable();
	}

	/**
	 * class deconstructor
	 */
	public function __destruct()
	{
		// NO OP
	}

	// MARK: PRIVATE METHODS

	/**
	 * set the write types that are allowed
	 *
	 * @return void
	 */
	private function adbSetEditLogWriteTypeAvailable()
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

	// MARK: PUBLIC METHODS |=================================================>

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
	 * Return current set ACL
	 *
	 * @return array<mixed>
	 */
	public function adbGetAcl(): array
	{
		return $this->acl;
	}

	/**
	 * Set _POST action vars if needed
	 *
	 * @return void
	 */
	public function adbSetActionVars()
	{
		// set the action ids
		foreach ($this->action_list as $_action) {
			$this->$_action = $_POST[$_action] ?? '';
		}
	}

	/**
	 * writes all action vars plus other info into edit_log table
	 *
	 * @param  string              $event [default='']        any kind of event description,
	 * @param  string|array<mixed> $data [default='']         any kind of data related to that event
	 * @param  string              $write_type [default=JSON] write type can be
	 *                                                        JSON, STRING/SERIEAL, BINARY/BZIP or ZLIB
	 * @param  string|null         $db_schema [default=null]  override target schema
	 * @return void
	 */
	public function adbEditLog(
		string $event = '',
		string|array $data = '',
		string $write_type = 'JSON',
		?string $db_schema = null
	): void {
		$data_binary = '';
		$data_write = '';
		// check if write type is valid, if not fallback to JSON
		if (!in_array($write_type, $this->write_types_available)) {
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
			euid, event_date, event, data, data_binary, page,
			ip, user_agent, referer, script_name, query_string, server_name, http_host,
			http_accept, http_accept_charset, http_accept_encoding, session_id,
			action, action_id, action_yes, action_flag, action_menu, action_loaded,
			action_value, action_type, action_error
		) VALUES (
			$1, NOW(), $2, $3, $4, $5,
			$6, $7, $8, $9, $10, $11, $12,
			$13, $14, $15, $16,
			$17, $18, $19, $20, $21, $22,
			$23, $24, $25
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
				isset($_SESSION['EUID']) && is_numeric($_SESSION['EUID']) ?
					$_SESSION['EUID'] : null,
				(string)$event,
				$data_write,
				$data_binary,
				(string)$this->page_name,
				// row 2
				$_SERVER["REMOTE_ADDR"] ?? '',
				$_SERVER['HTTP_USER_AGENT'] ?? '',
				$_SERVER['HTTP_REFERER'] ?? '',
				$_SERVER['SCRIPT_FILENAME'] ?? '',
				$_SERVER['QUERY_STRING'] ?? '',
				$_SERVER['SERVER_NAME'] ?? '',
				$_SERVER['HTTP_HOST'] ?? '',
				// row 3
				$_SERVER['HTTP_ACCEPT'] ?? '',
				$_SERVER['HTTP_ACCEPT_CHARSET'] ?? '',
				$_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
				$this->session->getSessionId() !== false ?
					$this->session->getSessionId() : null,
				// row 4
				$this->action ?? '',
				$this->action_id ?? '',
				$this->action_yes ?? '',
				$this->action_flag ?? '',
				$this->action_menu ?? '',
				$this->action_loaded ?? '',
				$this->action_value ?? '',
				$this->action_type ?? '',
				$this->action_error ?? '',
			],
			'NULL'
		);
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
		if ($set_content_path === null) {
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
		?string $associate = null,
		?string $file = null,
		?string $db_schema = null,
	): void {
		/** @var string $DB_SCHEMA check schema */
		$DB_SCHEMA = 'public';
		if ($db_schema !== null) {
			$DB_SCHEMA = $db_schema;
		} elseif (!empty($this->db->dbGetSchema())) {
			$DB_SCHEMA = $this->db->dbGetSchema();
		}
		$q = <<<SQL
		INSERT INTO {DB_SCHEMA}.live_queue (
			queue_key, key_value, key_name, type,
			target, data, group_key, action, associate, file
		) VALUES (
			$1, $2, $3, $4,
			$5, $6, $7, $8, $9, $10
		)
		SQL;
		// $this->db->dbExec($q);
		$this->db->dbExecParams(
			str_replace(
				['{DB_SCHEMA}'],
				[$DB_SCHEMA],
				$q
			),
			[
				$queue_key, $key_value,
				$key_name, $type,
				$target, $data,
				$this->queue_key, $this->action,
				(string)$associate, (string)$file
			]
		);
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
	): string {
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

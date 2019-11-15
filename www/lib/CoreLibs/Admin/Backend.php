<?php declare(strict_types=1);
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

namespace CoreLibs\Admin;

class Backend extends \CoreLibs\DB\IO
{
	// page name
	public $menu = array();
	public $menu_show_flag = 0; // top menu flag (mostly string)
	// action ids
	public $action_list = array('action', 'action_id', 'action_sub_id', 'action_yes', 'action_flag', 'action_menu', 'action_value', 'action_error', 'action_loaded');
	public $action;
	public $action_id;
	public $action_sub_id;
	public $action_yes;
	public $action_flag;
	public $action_menu;
	public $action_loaded;
	public $action_value;
	public $action_error;
	// ACL array variable if we want to set acl data from outisde
	public $acl = array();
	public $default_acl;
	// queue key
	public $queue_key;
	// the current active edit access id
	public $edit_access_id;
	// error/warning/info messages
	public $messages = array();
	public $error = 0;
	public $warning = 0;
	public $info = 0;
	// internal lang & encoding vars
	public $lang_dir = '';
	public $lang;
	public $lang_short;
	public $encoding;
	// language
	public $l;
	// smarty publics [end processing in smarty class]
	public $DATA;
	public $HEADER;
	public $DEBUG_DATA;
	public $CONTENT_DATA;

	// CONSTRUCTOR / DECONSTRUCTOR |====================================>
	/**
	 * main class constructor
	 * @param array       $db_config        db config array
	 * @param int|integer $set_control_flag class variable check flag
	 */
	public function __construct(array $db_config, int $set_control_flag = 0)
	{
		$this->setLangEncoding();
		// get the language sub class & init it
		$this->l = new \CoreLibs\Language\L10n($this->lang);

		// init the database class
		parent::__construct($db_config, $set_control_flag);

		// set the action ids
		foreach ($this->action_list as $_action) {
			$this->$_action = (isset($_POST[$_action])) ? $_POST[$_action] : '';
		}

		$this->default_acl = DEFAULT_ACL_LEVEL;

		// queue key
		if (preg_match("/^(add|save|delete|remove|move|up|down|push_live)$/", $this->action)) {
			$this->queue_key = $this->randomKeyGen(3);
		}
	}

	/**
	 * class deconstructor
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

	// INTERNAL METHODS |===============================================>

	/**
	 * set the language encoding and language settings
	 * the default charset from _SESSION login or from
	 * config DEFAULT ENCODING
	 * the lang full name for mo loading from _SESSION login
	 * or SITE LANG or DEFAULT LANG from config
	 * creates short lang (only first two chars) from the lang
	 * @return void
	 */
	private function setLangEncoding(): void
	{
		// just emergency fallback for language
		// set encoding
		if (isset($_SESSION['DEFAULT_CHARSET'])) {
			$this->encoding = $_SESSION['DEFAULT_CHARSET'];
		} else {
			$this->encoding = DEFAULT_ENCODING;
		}
		// gobal override
		if (isset($GLOBALS['OVERRIDE_LANG'])) {
			$this->lang = $GLOBALS['OVERRIDE_LANG'];
		} elseif (isset($_SESSION['DEFAULT_LANG'])) {
			// session (login)
			$this->lang = $_SESSION['DEFAULT_LANG'];
		} else {
			// mostly default SITE LANG or DEFAULT LANG
			$this->lang = defined('SITE_LANG') ? SITE_LANG : DEFAULT_LANG;
		}
		// create the char lang encoding
		$this->lang_short = substr($this->lang, 0, 2);
		// set the language folder
		$this->lang_dir = BASE.INCLUDES.LANG.CONTENT_PATH;
	}

	// PUBLIC METHODS |=================================================>

	/**
	 * set internal ACL from login ACL
	 * @param array $acl login acl array
	 */
	public function setACL(array $acl): void
	{
		$this->acl = $acl;
	}

	/**
	 * writes all action vars plus other info into edit_log tabl
	 * @param  string       $event      any kind of event description,
	 * @param  string|array $data       any kind of data related to that event
	 * @param  string       $write_type write type can bei STRING or BINARY
	 * @return void                     has no return
	 */
	public function adbEditLog(string $event = '', $data = '', string $write_type = 'STRING'): void
	{
		$data_binary = '';
		if ($write_type == 'BINARY') {
			$data_binary = $this->dbEscapeBytea(bzcompress(serialize($data)));
			$data = 'see bzip compressed data_binary field';
		}
		if ($write_type == 'STRING') {
			$data_binary = '';
			$data = $this->dbEscapeString(serialize($data));
		}

		// check schema
		if (defined('LOGIN_DB_SCHEMA')) {
			/** @phan-suppress-next-line PhanUndeclaredConstant */
			$SCHEMA = LOGIN_DB_SCHEMA;
		} elseif ($this->dbGetSchema()) {
			$SCHEMA = $this->dbGetSchema();
		} elseif (defined('PUBLIC_SCHEMA')) {
			$SCHEMA = PUBLIC_SCHEMA;
		} else {
			$SCHEMA = 'public';
		}

		$q = "INSERT INTO ".$SCHEMA.".edit_log ";
		$q .= "(euid, event_date, event, data, data_binary, page, ";
		$q .= "ip, user_agent, referer, script_name, query_string, server_name, http_host, http_accept, http_accept_charset, http_accept_encoding, session_id, ";
		$q .= "action, action_id, action_yes, action_flag, action_menu, action_loaded, action_value, action_error) ";
		$q .= "VALUES ";
		$q .= "(".$this->dbEscapeString(isset($_SESSION['EUID']) && is_numeric($_SESSION['EUID']) ? $_SESSION['EUID'] : 'NULL').", ";
		$q .= "NOW(), ";
		$q .= "'".$this->dbEscapeString((string)$event)."', '".$data."', '".$data_binary."', '".$this->dbEscapeString($this->page_name)."', ";
		$q .= "'".@$_SERVER["REMOTE_ADDR"]."', '".$this->dbEscapeString(@$_SERVER['HTTP_USER_AGENT'])."', ";
		$q .= "'".$this->dbEscapeString(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '')."', ";
		$q .= "'".$this->dbEscapeString(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '')."', ";
		$q .= "'".$this->dbEscapeString(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '')."', ";
		$q .= "'".$this->dbEscapeString(isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '')."', ";
		$q .= "'".$this->dbEscapeString(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '')."', ";
		$q .= "'".$this->dbEscapeString(isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : '')."', ";
		$q .= "'".$this->dbEscapeString(isset($_SERVER['HTTP_ACCEPT_CHARSET']) ? $_SERVER['HTTP_ACCEPT_CHARSET'] : '')."', ";
		$q .= "'".$this->dbEscapeString(isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '')."', ";
		$q .= "'".session_id()."', ";
		$q .= "'".$this->dbEscapeString($this->action)."', ";
		$q .= "'".$this->dbEscapeString($this->action_id)."', ";
		$q .= "'".$this->dbEscapeString($this->action_yes)."', ";
		$q .= "'".$this->dbEscapeString($this->action_flag)."', ";
		$q .= "'".$this->dbEscapeString($this->action_menu)."', ";
		$q .= "'".$this->dbEscapeString($this->action_loaded)."', ";
		$q .= "'".$this->dbEscapeString($this->action_value)."', ";
		$q .= "'".$this->dbEscapeString($this->action_error)."')";
		$this->dbExec($q, 'NULL');
	}

	/**
	 * helper function for PHP file upload error messgaes to messge string
	 * @param  int    $error_code integer _FILE upload error code
	 * @return string                     message string, translated
	 */
	public function fileUploadErrorMessage(int $error_code): string
	{
		switch ($error_code) {
			case UPLOAD_ERR_INI_SIZE:
				$message = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case UPLOAD_ERR_FORM_SIZE:
				$message = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case UPLOAD_ERR_PARTIAL:
				$message = 'The uploaded file was only partially uploaded';
				break;
			case UPLOAD_ERR_NO_FILE:
				$message = 'No file was uploaded';
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$message = 'Missing a temporary folder';
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$message = 'Failed to write file to disk';
				break;
			case UPLOAD_ERR_EXTENSION:
				$message = 'File upload stopped by extension';
				break;
			default:
				$message = 'Unknown upload error';
				break;
		}
		return $this->l->__($message);
	}

	/**
	 * menu creater (from login menu session pages)
	 * @param  int $flag visible flag trigger
	 * @return array     menu array for output on page (smarty)
	 */
	public function adbTopMenu(int $flag = 0): array
	{
		if ($this->menu_show_flag) {
			$flag = $this->menu_show_flag;
		}

		// get the session pages array
		$PAGES = isset($_SESSION['PAGES']) ? $_SESSION['PAGES'] : null;
		if (!isset($PAGES) || !is_array($PAGES)) {
			$PAGES = array();
		}
		$pages = array();
		foreach ($PAGES as $PAGE_CUID => $PAGE_DATA) {
			$pages[] = $PAGE_DATA;
		}
		// $this->debug('pages', $this->print_ar($pages));
		// if flag is 0, then we show all, else, we show only the matching flagges array points
		// array is already sorted after correct order
		reset($pages);
		for ($i = 0, $iMax = count($pages); $i < $iMax; $i ++) {
			$show = 0;
			// is it visible in the menu & is it online
			if ($pages[$i]['menu'] && $pages[$i]['online']) {
				// check if it falls into our flag if we have a flag
				if ($flag) {
					foreach ($pages[$i]['visible'] as $name => $key) {
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
					if (isset($pages[$i]['popup']) && $pages[$i]['popup']) {
						$type = 'popup';
					} else {
						$type = 'normal';
						$pages[$i]['popup'] = 0;
					}
					$query_string = '';
					if (isset($pages[$i]['query']) && count($pages[$i]['query'])) {
						for ($j = 0, $jMax = count($pages[$i]['query']); $j < $jMax; $j ++) {
							if (strlen($query_string)) {
								$query_string .= '&';
							}
							$query_string .= $pages[$i]['query'][$j]['name'].'=';
							if (!$pages[$i]['query'][$j]['dynamic']) {
								$query_string .= urlencode($pages[$i]['query'][$j]['value']);
							} else {
								$query_string .= $_GET[$pages[$i]['query'][$j]['value']] ? urlencode($_GET[$pages[$i]['query'][$j]['value']]) : urlencode($_POST[$pages[$i]['query'][$j]['value']]);
							}
						}
					}
					$url = $pages[$i]['filename'];
					if (strlen($query_string)) {
						$url .= '?'.$query_string;
					}
					$name = $pages[$i]['page_name'];
					// if page name matchs -> set selected flag
					$selected = 0;
					if ($this->getPageName() == $pages[$i]['filename']) {
						$selected = 1;
						$this->page_name = $name;
					}
					// last check, is this menu point okay to show
					$enabled = 0;
					if ($this->adbShowMenuPoint($pages[$i]['filename'])) {
						$enabled = 1;
					}
					// write in to view menu array
					array_push($this->menu, array(
						'name' => $this->l->__($name),
						'url' => $url,
						'selected' => $selected,
						'enabled' => $enabled,
						'popup' => $type == 'popup' ? 1 : 0,
						'type' => $type
					));
				} // show page
			} // online and in menu
		} // for each page
		return $this->menu;
	}

	/**
	 * checks if this filename is in the current situation (user id, etc) available
	 * @param  string $filename filename
	 * @return bool             true for visible/accessable menu point, false for not
	 */
	public function adbShowMenuPoint(string $filename): bool
	{
		$enabled = false;
		switch ($filename) {
			default:
				$enabled = true;
				break;
		};
		return $enabled;
	}

	/**
	 * @deprecated
	 * creates out of a normal db_return array an assoc array
	 * @param  array           $db_array input array
	 * @param  string|int|bool $key      key
	 * @param  string|int|bool $value    value
	 * @return array                     associative array
	 */
	public function adbAssocArray(array $db_array, $key, $value): array
	{
		trigger_error('Method '.__METHOD__.' is deprecated', E_USER_DEPRECATED);
		return $this->genAssocArray($db_array, $key, $value);
	}

	/**
	 * @deprecated
	 * converts bytes into formated string with KB, MB, etc
	 * @param  string|int|float $number string or int or number
	 * @return string                   formatted string
	 */
	public function adbByteStringFormat($number): string
	{
		trigger_error('Method '.__METHOD__.' is deprecated', E_USER_DEPRECATED);
		return $this->byteStringFormat($number);
	}

	/**
	 * @deprecated
	 * converts picture to a thumbnail with max x and max y size
	 * @param  string      $pic          source image file with or without path
	 * @param  int         $size_x       maximum size width
	 * @param  int         $size_y       maximum size height
	 * @param  string      $dummy        empty, or file_type to show an icon instead of nothing if file is not found
	 * @param  string      $path         if source start is not ROOT path, if empty ROOT is choosen
	 * @return string|bool               thumbnail name, or false for error
	 */
	public function adbCreateThumbnail($pic, $size_x, $size_y, $dummy = '', $path = "", $cache = "")
	{
		trigger_error('Method '.__METHOD__.' is deprecated', E_USER_DEPRECATED);
		return $this->createThumbnail($pic, $size_x, $size_y, $dummy, $path, $cache);
	}

	/**
	 * wrapper function to fill up the mssages array
	 * @param  string $level info/warning/error
	 * @param  string $msg   string, can be printf formated
	 * @param  array  $vars  optional data for a possible printf formated msg
	 * @return void          has no return
	 */
	public function adbMsg(string $level, string $msg, array $vars = array()): void
	{
		if (!preg_match("/^info|warning|error$/", $level)) {
			$level = "info";
		}
		$this->messages[] = array(
			'msg' => vsprintf($this->l->__($msg), $vars),
			'class' => $level
		);
		switch ($level) {
			case 'info':
				$this->info = 1;
				break;
			case 'warning':
				$this->warning = 1;
				break;
			case 'error':
				$this->error = 1;
				break;
		}
	}

	/**
	 * writes live queue
	 * @param  string  $queue_key string to identfy the queue
	 * @param  string  $type      [description]
	 * @param  string  $target    [description]
	 * @param  string  $data      [description]
	 * @param  string  $key_name  [description]
	 * @param  string  $key_value [description]
	 * @param  ?string $associate [description]
	 * @param  ?string $file      [description]
	 * @return void               has no return
	 */
	public function adbLiveQueue(
		string $queue_key,
		string $type,
		string $target,
		string $data,
		string $key_name,
		string $key_value,
		string $associate = null,
		string $file = null
	): void {
		if (defined('GLOBAL_DB_SCHEMA')) {
			/** @phan-suppress-next-line PhanUndeclaredConstant */
			$SCHEMA = GLOBAL_DB_SCHEMA;
		} elseif ($this->dbGetSchema()) {
			$SCHEMA = $this->dbGetSchema();
		} elseif (defined('PUBLIC_SCHEMA')) {
			$SCHEMA = PUBLIC_SCHEMA;
		} else {
			$SCHEMA = 'public';
		}
		$q = "INSERT INTO ".$SCHEMA.".live_queue (";
		$q .= "queue_key, key_value, key_name, type, target, data, group_key, action, associate, file";
		$q .= ") VALUES (";
		$q .= "'".$this->dbEscapeString($queue_key)."', '".$this->dbEscapeString($key_value)."', ";
		$q .= "'".$this->dbEscapeString($key_name)."', '".$this->dbEscapeString($type)."', ";
		$q .= "'".$this->dbEscapeString($target)."', '".$this->dbEscapeString($data)."', ";
		$q .= "'".$this->queue_key."', '".$this->action."', '".$this->dbEscapeString((string)$associate)."', ";
		$q .= "'".$this->dbEscapeString((string)$file)."')";
		$this->dbExec($q);
	}

	/**
	 * Basic class holds exact the same, except the Year/Month/Day/etc strings
	 * are translated in this call
	 * @param  int    $year          year YYYY
	 * @param  int    $month         month m
	 * @param  int    $day           day d
	 * @param  int    $hour          hour H
	 * @param  int    $min           min i
	 * @param  string $suffix        additional info printed after the date time variable in the drop down
	 *                               also used for ID in the on change JS call
	 * @param  int    $min_steps     default is 1 (minute), can set to anything, is used as sum up from 0
	 * @param  bool   $name_pos_back default false, if set to true, the name will be printend
	 *                               after the drop down and not before the drop down
	 * @return string                HTML formated strings for drop down lists of date and time
	 */
	public function adbPrintDateTime(
		$year,
		$month,
		$day,
		$hour,
		$min,
		string $suffix = '',
		int $min_steps = 1,
		bool $name_pos_back = false
	) {
		// get the build layout
		$html_time = $this->printDateTime($year, $month, $day, $hour, $min, $suffix, $min_steps, $name_pos_back);
		// translate the strings inside
		foreach (array('Year ', 'Month ', 'Day ', 'Hour ', 'Minute ') as $_time) {
			$html_time = str_replace($_time, $this->l->__(str_replace(' ', '', $_time)).' ', $html_time);
		}
		// replace week days in short
		foreach (array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun') as $_date) {
			$html_time = str_replace('('.$_date.')', '('.$this->l->__($_date).')', $html_time);
		}
		// return the datetime select string with strings translated
		return $html_time;
	}
}

// __END__

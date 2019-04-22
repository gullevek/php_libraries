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
	public $page_name; // the name of the current page
	public $menu = array();
	public $menu_show_flag = 0; // top menu flag (mostly string)
	// action ids
	public $action_list = array ('action', 'action_id', 'action_sub_id', 'action_yes', 'action_flag', 'action_menu', 'action_value', 'action_error', 'action_loaded');
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
	public $acl = array ();
	// the current active edit access id
	public $edit_access_id;
	// error/warning/info messages
	public $messages = array ();
	public $error = 0;
	public $warning = 0;
	public $info = 0;
	// smarty publics
	public $DATA;
	public $HEADER;
	public $DEBUG_DATA;
	public $CONTENT_DATA;

	// CONSTRUCTOR / DECONSTRUCTOR |====================================>
	public function __construct($db_config, $lang, $debug = 0, $db_debug = 0, $echo = 1, $print = 0)
	{
		// get the language sub class & init it
		$this->l = new \CoreLibs\Language\L10n($lang);

		// init the database class
		parent::__construct($db_config, $debug, $db_debug, $echo, $print);

		// internal
		$this->class_info["adbBackend"] = array(
			"class_name" => "Admin Interface Backend",
			"class_version" => "1.0.0",
			"class_created" => "2006/08/15",
			"class_author" => "Clemens Schwaighofer"
		);

		// set page name
		$this->page_name = $this->getPageName();

		// set the action ids
		foreach ($this->action_list as $_action) {
			$this->$_action = (isset($_POST[$_action])) ? $_POST[$_action] : '';
		}

		$this->default_acl = DEFAULT_ACL_LEVEL;

		// random key generation
		$this->key_range = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
		$GLOBALS["_KEY_RANGE"] = $this->key_range;
		$this->one_key_length = count($this->key_range);
		$this->key_length = 4; // pow($this->one_key_length, 4); // hardcoded, should be more than enought (62*62*62*62)

		// queue key
		if (preg_match("/^(add|save|delete|remove|move|up|down|push_live)$/", $this->action)) {
			$this->queue_key = join(
				'',
				array_map(
					function () {
						$range = $GLOBALS['_KEY_RANGE'];
						return $range[rand(0, (count($range) - 1))];
					},
					range(1, 3)
				)
			);
		}
	}

	// deconstructor
	public function __destruct()
	{
		parent::__destruct();
	}

	// INTERNAL METHODS |===============================================>


	// PUBLIC METHODS |=================================================>

	// METHOD: adbEditLog()
	// PARAMS: event -> any kind of event description, data -> any kind of data related to that event
	// RETURN: none
	// DESC  : writes all action vars plus other info into edit_log table
	public function adbEditLog($event = '', $data = '', $write_type = 'STRING')
	{
		if ($write_type == 'BINARY') {
			$data_binary = $this->dbEscapeBytea(bzcompress(serialize($data)));
			$data = 'see bzip compressed data_binary field';
		}
		if ($write_type == 'STRING') {
			$data = $this->dbEscapeString(serialize($data));
		}

		$q = "INSERT INTO ".LOGIN_DB_SCHEMA.".edit_log ";
		$q .= "(euid, event_date, event, data, data_binary, page, ";
		$q .= "ip, user_agent, referer, script_name, query_string, server_name, http_host, http_accept, http_accept_charset, http_accept_encoding, session_id, ";
		$q .= "action, action_id, action_yes, action_flag, action_menu, action_loaded, action_value, action_error) ";
		$q .= "VALUES ";
		$q .= "(".@$_SESSION['EUID'].", NOW(), '".$this->dbEscapeString($event)."', '".$data."', '".$data_binary."', '".$this->page_name."', ";
		$q .= "'".@$_SERVER["REMOTE_ADDR"]."', '".$this->dbEscapeString(@$_SERVER['HTTP_USER_AGENT'])."', ";
		$q .= "'".$this->dbEscapeString(@$_SERVER['HTTP_REFERER'])."', '".$this->dbEscapeString(@$_SERVER['SCRIPT_FILENAME'])."', ";
		$q .= "'".$this->dbEscapeString(@$_SERVER['QUERY_STRING'])."', '".$this->dbEscapeString(@$_SERVER['SERVER_NAME'])."', ";
		$q .= "'".$this->dbEscapeString(@$_SERVER['HTTP_HOST'])."', '".$this->dbEscapeString(@$_SERVER['HTTP_ACCEPT'])."', ";
		$q .= "'".$this->dbEscapeString(@$_SERVER['HTTP_ACCEPT_CHARSET'])."', '".$this->dbEscapeString(@$_SERVER['HTTP_ACCEPT_ENCODING'])."', ";
		$q .= "'".session_id()."', ";
		$q .= "'".$this->dbEscapeString($this->action)."', '".$this->dbEscapeString($this->action_id)."', ";
		$q .= "'".$this->dbEscapeString($this->action_yes)."', '".$this->dbEscapeString($this->action_flag)."', ";
		$q .= "'".$this->dbEscapeString($this->action_menu)."', '".$this->dbEscapeString($this->action_loaded)."', ";
		$q .= "'".$this->dbEscapeString($this->action_value)."', '".$this->dbEscapeString($this->action_error)."')";
		$this->dbExec($q, 'NULL');
	}

	// METHOD: adbTopMenu
	// PARAMS: level
	// RETURN: returns an array for the top menu with all correct settings
	// DESC  : menu creater
	public function adbTopMenu($flag = 0)
	{
		if ($this->menu_show_flag) {
			$flag = $this->menu_show_flag;
		}

		// get the session pages array
		$pages = $_SESSION["PAGES"];
		if (!is_array($pages)) {
			$pages = array ();
		}
		// $this->debug('pages', $this->print_ar($pages));
		// if flag is 0, then we show all, else, we show only the matching flagges array points
		// array is already sorted after correct order
		reset($pages);
		for ($i = 0, $iMax = count($pages); $i < $iMax; $i ++) {
			$show = 0;
			// is it visible in the menu & is it online
			if ($pages[$i]["menu"] && $pages[$i]["online"]) {
				// check if it falls into our flag if we have a flag
				if ($flag) {
					foreach ($pages[$i]["visible"] as $name => $key) {
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
					if ($pages[$i]["popup"]) {
						$type = "popup";
					} else {
						$type = "normal";
					}
					$query_string = '';
					if (count($pages[$i]["query"])) {
						for ($j = 0, $jMax = count($pages[$i]["query"]); $j < $jMax; $j ++) {
							if (strlen($query_string)) {
								$query_string .= "&";
							}
							$query_string .= $pages[$i]["query"][$j]["name"]."=";
							if (!$pages[$i]["query"][$j]["dynamic"]) {
								$query_string .= urlencode($pages[$i]["query"][$j]["value"]);
							} else {
								$query_string .= $_GET[$pages[$i]["query"][$j]["value"]] ? urlencode($_GET[$pages[$i]["query"][$j]["value"]]) : urlencode($_POST[$pages[$i]["query"][$j]["value"]]);
							}
						}
					}
					$url = $pages[$i]["filename"];
					if (strlen($query_string)) {
						$url .= "?".$query_string;
					}
					$name = $pages[$i]["page_name"];
					// if page name matchs -> set selected flag
					$selected = 0;
					if ($this->getPageName() == $pages[$i]["filename"]) {
						$selected = 1;
						$this->page_name = $name;
					}
					// last check, is this menu point okay to show
					$enabled = 0;
					if ($this->adbShowMenuPoint($pages[$i]["filename"])) {
						$enabled = 1;
					}
					// write in to view menu array
					array_push($this->menu, array("name" => $this->l->__($name), "url" => $url, "selected" => $selected, "enabled" => $enabled, "type" => $type));
				} // show page
			} // online and in menu
		} // for each page
		return $this->menu;
	}

	// METHOD: adbShowMenuPoint
	// PARAMS: filename
	// RETURN: returns boolean true/false
	// DESC  : checks if this filename is in the current situation (user id, etc) available
	public function adbShowMenuPoint($filename)
	{
		$enabled = 0;
		switch ($filename) {
			default:
				$enabled = 1;
				break;
		};
		return $enabled;
	}

	// REMARK: below function has moved to "Class.Basic"
	// METHOD: adbAssocArray
	// PARAMS: db array, key, value part
	// RETURN: returns and associative array
	// DESC  : creates out of a normal db_return array an assoc array
	public function adbAssocArray($db_array, $key, $value)
	{
		return $this->genAssocArray($db_array, $key, $value);
	}

	// REMARK: below function has moved to "Class.Basic"
	// METHOD: adbByteStringFormat
	// PARAMS: int
	// RETURN: string
	// DESC  : converts bytes into formated string with KB, MB, etc
	public function adbByteStringFormat($number)
	{
		return $this->byteStringFormat($number);
	}

	// REMARK: below function has moved to "Class.Basic"
	// METHOD: adbCreateThumbnail
	// PARAMS: id from picture where from we create a thumbnail
	//         x -> max x size of thumbnail
	//         y -> max y size of thumbnail
	//         dummy -> if set to true, then if no images was found we show a dummy image
	//         path -> if source start is not ROOT path, if empty ROOT is choosen
	//         cache -> cache path, if not given TMP is used
	// RETURN: thumbnail name
	// DESC  : converts picture to a thumbnail with max x and max y size
	public function adbCreateThumbnail($pic, $size_x, $size_y, $dummy = false, $path = "", $cache = "")
	{
		return $this->createThumbnail($pic, $size_x, $size_y, $dummy, $path, $cache);
	}

	// METHOD: adbMsg
	// PARAMS: level -> info/warning/error
	//         msg -> string, can be printf formated
	//         var array -> optional data for a possible printf formated msg
	// RETURN: none
	// DESC  : wrapper function to fill up the mssages array
	public function adbMsg($level, $msg, $vars = array ())
	{
		if (!preg_match("/^info|warning|error$/", $level)) {
			$level = "info";
		}
		$this->messages[] = array (
			'msg' => sprintf($this->l->__($msg), $vars),
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

	// METHOD: adbLiveQueue
	// PARAMS: queue_key -> string to identfy the queue
	//         type      -> INSERT/UPDATE/DELETE
	//         target    -> target table to write to
	//         data      -> SQL part to write, this can include #KEY_VALUE#, #KEY_NAME# for delete sub queries
	//         key_name  -> key name, mostly used for update search
	//         key_value -> data for the key
	//         associate -> NULL for free, LOCK for first insert, group key for reference to first entry
	//         file      -> string for special file copy actions; mostyle "test#live;..."
	// RETURN: none
	// DESC  : writes live queue
	public function adbLiveQueue($queue_key, $type, $target, $data, $key_name, $key_value, $associate = null, $file = null)
	{
		$q = "INSERT INTO ".GLOBAL_DB_SCHEMA.".live_queue (";
		$q .= "queue_key, key_value, key_name, type, target, data, group_key, action, associate, file";
		$q .= ") VALUES (";
		$q .= "'".$this->dbEscapeString($queue_key)."', '".$this->dbEscapeString($key_value)."', ";
		$q .= "'".$this->dbEscapeString($key_name)."', '".$this->dbEscapeString($type)."', ";
		$q .= "'".$this->dbEscapeString($target)."', '".$this->dbEscapeString($data)."', ";
		$q .= "'".$this->queue_key."', '".$this->action."', '".$this->dbEscapeString($associate)."', ";
		$q .= "'".$this->dbEscapeString($file)."')";
		$this->db_exec($q);
	}

	// METHOD: adbPrintDateTime
	// PARAMS: year, month, day, hour, min: the date and time values
	//         suffix: additional info printed after the date time variable in the drop down,
	//         also used for ID in the on change JS call
	//         minute steps: can be 1 (default), 5, 10, etc, if invalid (outside 1h range,
	//         it falls back to 1min)
	//         name pos back: default false, if set to true, the name will be printend
	//                        after the drop down and not before the drop down
	// RETURN: HTML formated strings for drop down lists of date and time
	// DESC  : print the date/time drop downs, used in any queue/send/insert at date/time place
	// NOTE  : Basic class holds exact the same, except the Year/Month/Day/etc strings
	//         are translated in this call
	public function adbPrintDateTime($year, $month, $day, $hour, $min, $suffix = '', $min_steps = 1, $name_pos_back = false)
	{
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

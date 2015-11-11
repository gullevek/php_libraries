<?
	/*********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2006/08/15
	* VERSION: 0.1.0
	* RELEASED LICENSE: BSD style (use it, u don't have to make YOUR source public)
	*                   but let me know if u made changes, and please don't redistribute it
	*                   with your name on it ...
	* DESCRIPTION
	* Basic Admin interface backend
	* - sets ACLs
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

	// try to include file from LIBS path, or from normal path
	_spl_autoload('Class.DB.IO.inc');

	class AdminBackend extends db_io
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
		// DEPRICATED -> use login acl
//		public $page_acl; // the acl the user has on this page
//		public $default_acl;
		// DEPRICATED access rights
//		public $access_rights = array ();
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
			_spl_autoload('Class.l10n.inc');

			$this->l = new l10n($lang);

			// init the database class
//			$this->db_io($db_config, $debug, $db_debug, $echo, $print);
			parent::__construct($db_config, $debug, $db_debug, $echo, $print);

			// internal
			$this->class_info["adbBackend"] = array(
				"class_name" => "Admin Interface Backend",
				"class_version" => "0.1.0",
				"class_created" => "2006/08/15",
				"class_author" => "cs/gullevek/jp"
			);

			// set page name
			$this->page_name = $this->get_page_name();
			
			// set the action ids
			foreach ($this->action_list as $_action)
			{
				$this->$_action = (isset($_POST[$_action])) ? $_POST[$_action] : '';
			}

			$this->default_acl = DEFAULT_ACL_LEVEL;

			// random key generation
			$this->key_range = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
			$GLOBALS["_KEY_RANGE"] = $this->key_range;
			$this->one_key_length = count($this->key_range);
			$this->key_length = 4; // pow($this->one_key_length, 4); // hardcoded, should be more than enought (62*62*62*62)

			// queue key
			if (preg_match("/^(add|save|delete|remove|move|up|down|push_live)$/", $this->action))
			{
				$this->queue_key = join('', array_map(create_function('', '$range = $GLOBALS["_KEY_RANGE"]; return $range[rand(0, (count($range) - 1))];'), range(1, 3)));
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
		// DESC:   writes all action vars plus other info into edit_log table
		public function adbEditLog($event = '', $data = '', $write_type = 'STRING')
		{
			if ($write_type == 'BINARY')
			{
				$data_binary = $this->db_escape_bytea(bzcompress(serialize($data)));
				$data = 'see bzip compressed data_binary field';
			}
			if ($write_type == 'STRING')
			{
				$data = $this->db_escape_string(serialize($data));
			}

			$q = "INSERT INTO ".LOGIN_DB_SCHEMA.".edit_log ";
			$q .= "(euid, event_date, event, data, data_binary, page, ";
			$q .= "ip, user_agent, referer, script_name, query_string, server_name, http_host, http_accept, http_accept_charset, http_accept_encoding, session_id, ";
			$q .= "action, action_id, action_yes, action_flag, action_menu, action_loaded, action_value, action_error) ";
			$q .= "VALUES ";
			$q .= "(".@$_SESSION['EUID'].", NOW(), '".$this->db_escape_string($event)."', '".$data."', '".$data_binary."', '".$this->page_name."', ";
			$q .= "'".@$_SERVER["REMOTE_ADDR"]."', '".$this->db_escape_string(@$_SERVER['HTTP_USER_AGENT'])."', '".$this->db_escape_string(@$_SERVER['HTTP_REFERER'])."', '".$this->db_escape_string(@$_SERVER['SCRIPT_FILENAME'])."', '".$this->db_escape_string(@$_SERVER['QUERY_STRING'])."', '".$this->db_escape_string(@$_SERVER['SERVER_NAME'])."', '".$this->db_escape_string(@$_SERVER['HTTP_HOST'])."', '".$this->db_escape_string(@$_SERVER['HTTP_ACCEPT'])."', '".$this->db_escape_string(@$_SERVER['HTTP_ACCEPT_CHARSET'])."', '".$this->db_escape_string(@$_SERVER['HTTP_ACCEPT_ENCODING'])."', '".session_id()."', ";
			$q .= "'".$this->db_escape_string($this->action)."', '".$this->db_escape_string($this->action_id)."', '".$this->db_escape_string($this->action_yes)."', '".$this->db_escape_string($this->action_flag)."', '".$this->db_escape_string($this->action_menu)."', '".$this->db_escape_string($this->action_loaded)."', '".$this->db_escape_string($this->action_value)."', '".$this->db_escape_string($this->action_error)."')";
			$this->db_exec($q, 'edit_log_id');
		}


		// ==================================
		// ALL THE PAGE RIGHTS/USER RIGHTS/ETC need to fixed and put into one
		// proper settings have to be done with the defined top down rights flow
		// ==================================
		// all ACLs are set in the login class

		// METHOD: adbTopMenu
		// PARAMS: level
		// RETURN: returns an array for the top menu with all correct settings
		// DESC:   menu creater
		public function adbTopMenu($flag = 0)
		{
			if ($this->menu_show_flag)
				$flag = $this->menu_show_flag;

			// get the session pages array
			$pages = $_SESSION["PAGES"];
			if (!is_array($pages))
				$pages = array ();

//$this->debug('pages', $this->print_ar($pages));

			// if flag is 0, then we show all, else, we show only the matching flagges array points
			// array is already sorted after correct order
			reset($pages);
			for ($i = 0; $i < count($pages); $i ++)
			{
				$show = 0;
				// is it visible in the menu & is it online
				if ($pages[$i]["menu"] && $pages[$i]["online"])
				{
					// check if it falls into our flag if we have a flag
					if ($flag)
					{
						foreach ($pages[$i]["visible"] AS $name => $key)
						{
							if ($key == $flag)
								$show = 1;
						}
					}
					else
					{
						// if no flag given, show all menu points
						$show = 1;
					}

					if ($show)
					{
						// if it is popup, write popup arrayound
						if ($pages[$i]["popup"])
						{
							$type = "popup";
						}
						else
						{
							$type = "normal";
						}
						$query_string = '';
						if (count($pages[$i]["query"]))
						{
							for ($j = 0; $j < count($pages[$i]["query"]); $j ++)
							{
								if (strlen($query_string))
									$query_string .= "&";
								$query_string .= $pages[$i]["query"][$j]["name"]."=";
								if (!$pages[$i]["query"][$j]["dynamic"])
									$query_string .= urlencode($pages[$i]["query"][$j]["value"]);
								else
									$query_string .= (($_GET[$pages[$i]["query"][$j]["value"]]) ? urlencode($_GET[$pages[$i]["query"][$j]["value"]]) : urlencode($_POST[$pages[$i]["query"][$j]["value"]]));
							}
						}
						$url = $pages[$i]["filename"];
						if (strlen($query_string))
							$url .= "?".$query_string;
						$name = $pages[$i]["page_name"];
						// if page name matchs -> set selected flag
						$selected = 0;
						if ($this->get_page_name() == $pages[$i]["filename"])
						{
							$selected = 1;
							$this->page_name = $name;
						}
						// last check, is this menu point okay to show
						$enabled = 0;
						if ($this->adbShowMenuPoint($pages[$i]["filename"]))
						{
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
		// DESC:   checks if this filename is in the current situation (user id, etc) available
		public function adbShowMenuPoint($filename)
		{
			$enabled = 0;
			switch ($filename)
			{
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
		// DESC:   creates out of a normal db_return array an assoc array
		public function adbAssocArray($db_array, $key, $value)
		{
			return $this->GenAssocArray($db_array, $key, $value);
		}

		// REMARK: below function has moved to "Class.Basic"
		// METHOD: adbByteStringFormat
		// PARAMS: int
		// RETURN: string
		// DESC:   converts bytes into formated string with KB, MB, etc
		public function adbByteStringFormat($number)
		{
			return $this->ByteStringFormat($number);
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
		// DESC:   converts picture to a thumbnail with max x and max y size
		public function adbCreateThumbnail($pic, $size_x, $size_y, $dummy = false, $path = "", $cache = "")
		{
			return $this->CreateThumbnail($pic, $size_x, $size_y, $dummy, $path, $cache);
		}

		// METHOD: adbMsg
		// PARAMS: level -> info/warning/error
		//         msg -> string, can be printf formated
		//         var array -> optional data for a possible printf formated msg
		// RETURN: none
		// DESC:   wrapper function to fill up the mssages array
		public function adbMsg($level, $msg, $vars = array ())
		{
			if (!preg_match("/^info|warning|error$/", $level))
				$level = "info";
			$this->messages[] = array (
				'msg' => sprintf($this->l->__($msg), $vars),
				'class' => $level
			);
			switch ($level)
			{
				case 'info': $this->info = 1; break;
				case 'warning': $this->warning = 1; break;
				case 'error': $this->error = 1; break;
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
		// DESC:   writes live queue
		public function adbLiveQueue($queue_key, $type, $target, $data, $key_name, $key_value, $associate = NULL, $file = NULL)
		{
			$q = "INSERT INTO ".GLOBAL_DB_SCHEMA.".live_queue (";
			$q .= "queue_key, key_value, key_name, type, target, data, group_key, action, associate, file";
			$q .= ") VALUES (";
			$q .= "'".$this->db_escape_string($queue_key)."', '".$this->db_escape_string($key_value)."', '".$this->db_escape_string($key_name)."', '".$this->db_escape_string($type)."', '".$this->db_escape_string($target)."', '".$this->db_escape_string($data)."', '".$this->queue_key."', '".$this->action."', '".$this->db_escape_string($associate)."', '".$this->db_escape_string($file)."')";
			$this->db_exec($q);
		}

	}
?>

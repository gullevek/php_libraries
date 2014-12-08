<?
	/********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2002/10/22
	* VERSION: 2.4.9
	* RELEASED LICENSE: BSD style (use it, u don't have to make YOUR source public)
	*                   but let me know if u made changes, and please don't redistribute it
	*                   with your name on it ...
	* SHORT DESCRIPTION:
	*   ~ 2003/02/26: decided to move away from single class and change this
	*   to extend db_array_io which extends db_io. this is much more efficient
	*   in use of vars and use of methods of other classes
	*
	*   ~ 2002/10/20: this class contains a set of functions that helps in creating
	*   more or less default forms, or supports u in handling normal
	*   form data
	*
	* description of the variables && arrays that have to be set ...
	*  $name_u_choose=array(
	* # this is the description of ALL fields in the main table
	*    "table_array" => array(
	*      "name_of_col_in_table" => array(
	*        "value" => $name_of_col_in_table",
	*        "pk" => 1/0 - sets the primary key (only one)
	*        "fk" => 1/0 - sets the foreign key (do not use at the moment ... buggy ;)
	*        "mandatory" => 1/0 - triggers * in output, but nor error check
	*        "output_name" => "text" - text put as label for the element
	*        "type" => "view/text/textarea/date/drop_down_db/drop_down_array/drop_down_db_input/drop_down_db_same_db/radio_array/binary/hidden/file/password"
	*					View is special, it just prints out the data as is, will not be saved
	*                   1) more will come
	*                   2) keep in mind that binary will not be checked, as it is always set to a value (default is "no")
	*        ---- the next four fields are only NECESSARY (!!!) for drop_down_db_input
	*        "table_name" => the name of the table for the drop down
	*        "pk_name" => the pk_name of the table for the drop down
	*        "input_name" => the text field name in the table for the drop down
	*        "input_value" => the $name of input_name (must be same)
	*        "order_by" => "order bY" string for drop_down_db(_input) if no query given but fields set      
	*        "query" => for drop_down_db/array if no outer query given
	*        "preset" => value to preset when array is unset (available for all types)
	*        "element_list" => array ( "true", "false") - MUST (!) be set for binary
	*        "length" => "nr" - only available for "text" (maxlength)
	*        "size" => "nr" - only available for "text" (size of input field)
	*        "rows" => "nr" - only available for "textarea"
	*        "cols" => "nr" - only available for "textarea"
	*        "error_check" => "custom/email/date/number/unique" - 1) more will come
	*        "error_regex" => "regex" - if error_check is custom regex here
	*        "error_example" => "text" - example input text for error_check (only custom right now) 
	*        "empty" => "value/text" - ONLY for view. If no data found, set this value
	*        --- file:
	*        "save_dir" => "directory where it should be saved to
	*        "accept_type" => "mime types accepted (mime/text,mime/jpeg ... etc)"
	*      ),
	*      ...
	*    ),
	* # all reference tables (n<->n)
	*    "reference_array" => array(
	*      "name_u_choose" => array(
	*        "table_name" => "table_u_choose_for_n_to_n_table",
	*        "other_table_pk" => "primary_key_name_of_reference_table",
	*        "output_name" => "Printed out next to multiple select field",
	*        "mandatory" => 1/0 for must be selected,
	*        "select_size" => size of multiple select field,
	*        "query" => "the query to load the multiple select field 
	*                    (select id, concat_ws(" ",name_a, name_b) from reference_table)",
	*        "selected" => $var_name for name="xx" in multiple select
	*      ),
	*      ...
	*    ),
	* # fields that should be shown from the load_query and with what aditions
	*    "show_fields" => array(
	*      array(
	*        "name" => "name_of_col_in_query" - col from the query that should be shown
	*        "before_value" => "text" - if set this text will be put in FRONT of the value from the col
	*        "binary" => array ("true","false") - for 1/0 fields in DB changes it int human readable format
	*      ),
	*      ...
	*    ),
	* # the laod query
	*    "load_query" => "query", - query for generting the list in "load" function
	* # the name of the main table
	*    "table_name" => "table_name" - the exakt name of the table ...
	* # security levels for load ... usefull is delete with a low number and load with a high
	*    "security_level" =>
	*      "load" => ... for load to appear
	*      "new" => 1... - security level minimum required for new part to appear (goes in hadn with save)
	*      "save" => ... - should be same level as new [or its a bit useless]
	*      "delete" => ... - for delete
	*
	* example for a page:
	*
	* $form->form_procedure_load(${$form->archive_pk_name});
	* $form->form_procedure_new();
	* $form->form_procedure_save();
	* $form->form_procedure_delete();
	* <HTML start>
	* $form->form_create_load();
	* $form->form_create_new();
	* if ($form->yes)
	* {
	*   $from->form_create_element("element_name");
	*   $from->form_create_hidden_fields();
	*   $form->form_creae_save_delete();
	* }
	* $form->_form();
	* <HTML end>
	*
	* list_of_functions:
	*   form_get_col_name_from_key($want_key)
	*     returns the value for the key (out of table_array)
	*   form_get_col_name_array_from_key($want_key)
	*     returns array of values for the searched key ...
	*   form_print_msg () [form_error_msg()]
	*     returns the HTML formated part with the error msg, if one exists
	*   form_procedure_load($id)
	*     starts the loading procedure
	*   form_procedure_new()
	*     starts the new procedure
	*   form_procedure_save()
	*     starts the save procedure
	*   form_procedure_delete()
	*     starts the delete procedure
	*   form_create_load () [form_load()]
	*     returns the HTML part for loading a table row, load_query & field_array have to be set for this!!!!!!
	*   form_create_new () [form_new()]
	*     returns the HTML part for creating a new table_row
	*   form_create_save_delete () [form_delete_save()]
	*     returns the HTML part for saveing and deleteing one table_row
	*   form_create_element ($element_name, $query="")
	*     creates and HTML element based on the description in the table_array array, second parameter is for drop_down fields, either a query for _db or an array for _array
	*   form_error_check()
	*     checks on errors after submit based on the settings in the table_array array
	*   form_set_order()
	*     if it finds the order flag set in the table_array sets the order for the current element to MAX+1 from the DB
	*   form_unset_table_array()
	*     unsets the table_array value fields for new entries
	*   form_create_hidden_fields($hidden_array)
	*     outputs a string with the HTML hidden fields (array must be $name["hidden_name"]=$hidden_value)
	*   form_create_element_reference_table($table_name) [form_show_reference_table()]
	*     creates and table tr part for the reference table name given
	*   form_load_table_array($pk_id=0)
	*     loads the table_array and the reference tables for the pk_id set in the class or given via parameter
	*   form_save_table_array($addslashes=0)
	*     save table array & reference tables
	*   form_delete_table_array()
	*     deletes table array & reference tables
	*
	*  // debug methods
	*   form_dump_table_array()
	*     returns a formatted string with alle table_array vars
	*
	* HISTORY:
	* 2005/07/14 (cs) fixed the insert for reference tables, prepared drop down text insert to be correct [untested]
	* 2005/07/08 (cs) added int set for integer insert values
	* 2005/07/07 (cs) bug with protected data, error got triggered even if no delete was pressed
	* 2005/06/30 (cs) changed color settings, they get set from CSS file now
	* 2005/06/29 (cs) finished full support for element_lists
	* 2005/06/24 (cs) added full support for a list in a form, a list is written to an other table and the other table has this forms PK as a FK
	* 2005/06/23 (cs) changed all HTML to Smarty Template Type
	* 2005/06/22 (cs) you can put more than one error check into the error field; alphanumeric check and unique in same table are new
	* 2005/06/21 (cs) changed the error_msg writings to debug
	* 2005/03/31 (cs) fixed the class call with all debug vars
	* 2004/11/10 (cs) fix bug with preset: don't check if set, check if variable is set at all
	* 2004/09/30 (cs) layout change
	*   2003-06-13: error with "protected" flag, fixed and added error msg, if protected flag is detected during
	*               delete
	*   2003-06-12: adapted class to register_global_vars off
	*   2003-06-10: in procedure_delete function I added "protected" variable clause, so if this field exists
	*               in the DB and is set, you are not able to delete [at the moment used for admin edit user 
	*               in DB]
	*   2003-05-30: _temp for drop_down_db was added always and not only for same_db
	*   2003-05-28: added drop_down_db_same_db for drop down/input combinations going into the same DB.
	*               WARNING!!! please be careful that input_value var name MUST have the ending _temp
	*               This might get change in future
	*               added a "where" field to the field list, this is only used for the drop_down for selecting
	*               only a certain field list. If where is filled out and used in combination with insert (not same_db)
	*               then this key will be SET when inserted into the DB !!!
	*   2003-04-09: added open_dir for download of file (URL), save_dir is only for upload (absolute path)
	*               added require once for class_db_array_io.inc
	*   2003-03-31: added a file upload module (type==file)
	*   2003-03-20: added form_procedure_new, etc functions so for default calls it is easier to write
	*               also added security levels to all functions where it is needed
	*   2003-03-14: changed the static error msgs to dynamic ones
	*   2003-03-13: very bad bug with getting key function. fixed it (set first array value always)
	*               reason was that in second if I forgot to check if the second method field was really
	*               set, so I compared to empty which was always right.
	*   2003-03-11: started renaming some functions:
	*               form_load, form_new, form_delete_save -> form_create_... (and _save_delete)
	*               .._show_reference_table -> create_element_reference_table
	*               added language array
	*               - kept old var names/function names for backward compatbile
	*   2003-03-10: added flag for form_delete_save, first flag hides delete part, second flag
	*               hides checkbox for delete, both are set 0 default
	*               added drop_down_db_input element type.
	*               next to a drop down with elements froma db, there is an input field,
	*               if something is input there and not yet in the DB it will be inserted into
	*               the db first and then selected in the drop down, if already in db, the element
	*               in the drop down will be selected
	*   2003-03-07: form_create_hidden_fields() has to be called mandatory
	*   2003-03-06: if nothing selected for reference table, do not write
	*               a wrong return in form_delete_table_array quit the function to early
	*   2003-03-04: drop_down_array value for option was left from array and
	*               not right
	*   2003-02-27: added another check in unset if reference array exists
	*   2003-02-26: change form to extend db_array_io and created load, save, 
	*               delete functions removed all reference table functions, 
	*               except show function rewrite config array
	*               re-wrote the class info vars into array
	*   2003-02-25: added reference table functions
	*   2002-10-22: create this class so creating basic and medium form pages 
	*               can be handled easy.
	*               with a given config file the class handles error checks, 
	*               save data, loads data, etc
	*********************************************************************/

	require_once(LIBS."Class.DB.Array.IO.inc");
	
	class form extends db_array_io
	{
		// rest
		public $field_array = array (); // for the load statetment describes which elements from the load query should be shown and i which format
		public $load_query; // the query needed for loading a data set (one row in the table)
		public $col_name; // the name of the columen (before _<type>) [used for order button]
		public $yes; // the yes flag that triggers the template to show ALL and not only new/load
		public $msg; // the error msg
		public $error; // the error flag set for printing red error msg
		public $warning; // warning flag, for information (saved, loaded, etc)
		public $archive_pk_name; // the pk name for the load select form
		private $int_pk_name; // primary key, only internal usage
		public $reference_array = array (); // reference arrays -> stored in $this->reference_array[$table_name]=>array();
		public $element_list; // element list for elements next to each other as a special sub group
		public $my_page_name; // the name of the page without .php extension
		// buttons and checkboxes
		public $archive;
		public $new;
		public $really_new;
		public $delete;
		public $really_delete;
		public $save;
		// security publics
		public $group_level_user;
		public $security_levels;
		// layout publics
		public $table_width;

		// now some default error msgs (english)
		public $language_array = array ();

		// METHOD constructor
		// PARAMS $db_config -> connect to DB
		//        $lang -> language code ("en", "ja", etc)
		//        $table_width -> width of table
		//        $db_debug -> turns db_io debug on/off (DB_DEBUG as global var does the same)
		public function __construct($db_config, $lang, $table_width = 750, $debug = 0, $db_debug = 0, $echo = 1, $print = 0)
		{
			$this->my_page_name = $this->get_page_name(1);
			// init the language class
			require_once(LIBS."Class.l10n.inc");
			$this->l = new l10n($lang);
			// load config array
			// get table array definitions for current page name
			include(TABLE_ARRAYS."array_".$this->my_page_name.".inc");

			$config_array = ${$this->my_page_name};

			// start the array_io class which will start db_io ...
			parent::__construct($db_config, $config_array["table_array"], $config_array["table_name"], $debug, $db_debug, $echo, $print);
			// here should be a check if the config_array is correct ...
			// 
			$this->field_array = $config_array["show_fields"];
			$this->load_query = $config_array["load_query"];
			$this->archive_pk_name = "a_".$this->pk_name;
			$this->col_name = str_replace("_id", "", $this->pk_name);
			$this->int_pk_name = $this->pk_name;
			// check if reference_arrays are given and proceed them
			if (is_array($config_array["reference_arrays"]))
			{
				while (list($key, $value) = each($config_array["reference_arrays"]))
				{
					$this->reference_array[$key] = $value;
				}
			} 
			if (is_array($config_array["element_list"]))
			{
				while (list($key, $value) = each($config_array["element_list"]))
				{
					$this->element_list[$key] = $value;
				}
			}

			// layout
			$this->table_width = $table_width;

			// set button vars
			$this->archive = $_POST["archive"];
			$this->new = $_POST["new"];
			$this->really_new = $_POST["really_new"];
			$this->delete = $_POST["delete"];
			$this->really_delete = $_POST["really_delete"];
			$this->save = $_POST["save"];
			$this->remove_button = $_POST["remove_button"];

			// security settings
			$this->group_level_user = $_SESSION["GROUP_LEVEL"];
			// security levels for buttons/actions
			// if array does not exists create basic
			if (!is_array($config_array["security_level"]) || count($config_array["security_level"]) < 4)
				$config_array["security_level"] = array("load" => 100, "new" => 100, "save" => 100, "delete" => 100);
			// write array to class var
			$this->security_level = $config_array["security_level"];

			// internal
			$this->class_info["form"] = array(
				"class_name" => "Form create",
				"class_version" => "2.4.9",
				"class_created" => "2002-10-22",
				"class_author" => "cs/gullevek/at"
			);
		}

		// dumps all values into output (for error msg)
		public function form_dump_table_array()
		{
			reset($this->table_array);
			$string .= "<b>TABLE ARRAY DUMP:</b> ".$this->table_name."<br>";
			while (list($key, $value) = each($this->table_array))
			{
				$string .= "<b>$key</b>: ".$value["value"]."<br>";
			}
			return $string;
		}

		// dekonstruktor
		// writes out error msg to global var
		// closes db connection
		public function __destruct()
		{
			// close DB connection
			parent::__destruct();
		}

		/*****************************************************
			Along here are wrapper functions for the former, old names
		*/

		public function form_load()
		{
			return $this->form_create_load();
		}

		public function form_new($hide_new_checkbox = 0)
		{
			return $this->form_create_new($hide_new_checkbox);
		}

		public function form_delete_save($hide_delete = 0, $hide_delete_checkbox = 0)
		{
			return $this->form_create_save_delete($hide_delete, $hide_delete_checkbox);
		}

		public function form_error_msg()
		{
			return $this->form_print_msg();
		}

		public function form_show_reference_table($table_name)
		{
			return $this->form_create_element_reference_table($table_name);
		}

		public function form_show_list_table($table_name)
		{
			return $this->form_create_element_list_table($table_name);
		}

		// END wrapper

		// METHOD form_get_col_name_from_key
		// PARAMS $want_key: the key where u want the data from
		//        $key_value: if set searches for special right value
		// RETURN the value of the $want_key array field
		//        works only with fields that appear only ONCE
		//        if multiple gets only FIRST
		public function form_get_col_name_from_key($want_key, $key_value = "")
		{
			reset($this->table_array);
			while (list($key, $value) = each($this->table_array))
			{
				if ($value[$want_key] && !$key_value)
					return $key;
				else if ($value[$want_key] == $key_value && $key_value)
					return $key;
			}
		}

		// METHOD form_get_col_name_array_from_key
		// PARAMS $want_key: the key where u want the data from
		//        $key_value: if set searches for special right value
		// RETURN array of fields
		public function form_get_col_name_array_from_key($want_key, $key_value = "")
		{
			$key_array = array();
			reset($this->table_array);
			while (list($key, $value) = each($this->table_array))
			{
				if ($value[$want_key] && !$key_value)
					array_push($key_array, $key);
				if ($value[$want_key] == $key_value)
					array_push($key_array, $key);
			}
			return $key_array;
		}

		// METHOD form_print_msg
		// PARAMS none
		// RETURN formated output for the error && warning msg
		public function form_print_msg()
		{
			if ($this->error)
			{
				$class = "error";
			}
			if ($this->warning)
			{
				$class = "warning";
			}
			return array('msg' => $this->msg, 'width' => $this->table_width, 'class' => $class);
		}

		// next for functions are pre_test fkts for easier default new,load, etc handling
		// METHOD form_procedure_load
		// PARAMS archive_id - which ID should be loaded
		// RETURN none
		// DESC   default load procedure
		public function form_procedure_load($archive_id)
		{
			if ($this->archive && $archive_id && $this->group_level_user <= $this->security_level["load"])
			{
				$this->form_load_table_array($archive_id);
				$this->yes = 1;
			}
		}

		// METHOD form_procedure_new
		// PARAMS none
		// RETURN none
		// DESC   default new procedure
		public function form_procedure_new()
		{
			if ($this->new && $this->group_level_user <= $this->security_level["new"])
			{
				if ($this->really_new == "yes")
				{
					$this->form_unset_table_array();
				} else
				{
					$this->msg .= $this->l->__("You have to select the <b>Checkbox for New</b>!<br>");
					$this->error = 2;
				}
				$this->yes = 1;
			}
		}

		// METHOD form_procedure_save
		// PARAMS none
		// RETURN none
		// DESC   default save procedure
		public function form_procedure_save()
		{
			if ($this->save && $this->group_level_user <= $this->security_level["save"])
			{
				$this->form_error_check();
				if (!$this->error)
				{  
					$this->form_save_table_array();
				}
				$this->yes = 1;
			}
		}

		// METHOD form_procedure_delete
		// PARAMS none
		// RETURN none
		// DESC   default delete procedure
		public function form_procedure_delete()
		{
			// delete is also by "protected"
			if ($this->delete && $this->group_level_user <= $this->security_level["delete"] && !$this->table_array["protected"]["value"])
			{
				if ($this->table_array["protected"]["value"])
				{
					$this->msg .= $this->l->__("Cannot delete this Dataset, because it is internaly protected!");
					$this->error = 2;
				}
				if ($this->really_delete == "yes")
				{
					$this->form_delete_table_array();
				}
				else
				{
					$this->msg .= $this->l->__("You have to select the <b>Checkbox for Delete</b>!<br>");
					$this->error = 2;
					$this->yes = 1;
				}
			}
		}

		// METHOD form_procedure_delete_from_element_list
		// PARAMS none
		// RETURN none
		// DESC   default delete procedure
		public function form_procedure_delete_from_element_list($element_list, $remove_name)
		{
			// only do if the user is allowed to delete
			if ($this->group_level_user <= $this->security_level["delete"] && !$this->table_array["protected"]["value"] && !$this->error)
			{
				for ($i = 0; $i < count($element_list); $i ++)
				{
//$this->debug('form_error', "Array: ".is_array($this->element_list[$element_list[$i]]["read_data"])." | ".$this->element_list[$element_list[$i]]["delete"]);
					// if prefix, set it
					$prfx = ($this->element_list[$element_list[$i]]["prefix"]) ? $this->element_list[$element_list[$i]]["prefix"]."_" : '';
					// get the primary key
					while (list($el_name, $data) = each($this->element_list[$element_list[$i]]["elements"]))
					{
						if ($data["pk_id"])
							$pk_name = $el_name;
					}
					// which key should be deleted
					$id = $remove_name[$i];
					if (($this->element_list[$element_list[$i]]["delete_name"] || $this->element_list[$element_list[$i]]["delete"]) && !$this->element_list[$element_list[$i]]["enable_name"])
					{
						// flag var name
						$flag = $remove_name[$i]."_flag";
	
						if ($_POST[$flag] == "true")
						{
							$q = "DELETE FROM ".$element_list[$i]." WHERE ".$pk_name." = ".$_POST[$id];
							$this->db_exec($q);
							$this->msg .= $this->l->__("Removed entry from list<br>");
							$this->warning = 1;
						} // post okay true -> delete
					}
					elseif (is_array($this->element_list[$element_list[$i]]["read_data"]) && !$this->element_list[$element_list[$i]]["delete"])
					{
						for ($j = 0; $j < count($_POST[$id]); $j ++)
						{
							// if it is not activated
							if (!$_POST[$remove_name[$i]][$j])
							{
								$q = "UPDATE ".$element_list[$i]." WHERE ".$pk_name." = ".$_POST[$prfx.$pk_name][$j];
//								$this->debug('edit_db', "UP: $q");
								// $this->db_exec($q);
								$this->msg .= $this->l->__("Disabled deselected entries from list<br>");
								$this->warning = 1;
							}
						}
					}
					elseif (is_array($this->element_list[$element_list[$i]]["read_data"]) && $this->element_list[$element_list[$i]]["delete"])
					{
//$this->debug('form_clean', "ID [$id] [$prfx.$pk_name]");
//$this->debug('form_clean', "ID arr: ".$this->print_ar($_POST[$id]));
//$this->debug('form_clean', "PK arr: ".$this->print_ar($_POST[$prfx.$pk_name]));
						for ($j = 0; $j < count($_POST[$prfx.$pk_name]); $j ++)
						{
							if (!$_POST[$remove_name[$i]][$j] && $_POST[$prfx.$pk_name][$j])
							{
								$q = "DELETE FROM ".$element_list[$i]." WHERE ".$pk_name." = ".$_POST[$prfx.$pk_name][$j];
//								$this->debug('edit_db', "DEL: $q");
								$this->db_exec($q);
								$this->msg .= $this->l->__("Deleted deselected entries from list<br>");
								$this->warning = 1;
							}
						}
					}
				} // for each element group
			}
			if ($this->remove_button)
				$this->yes = 1;
		}

		// METHOD form_create_load
		// PARAMS none
		// RETURN string from "load" part of form ...
		public function form_create_load()
		{
			// when security leve is okay ...
			if ($this->group_level_user <= $this->security_level["load"])
			{
				$t_pk_name = $this->archive_pk_name;

			 // lade liste
				$this->db_exec($this->load_query);
				while ($res = $this->db_fetch_array())
				{
					$pk_ids[] = $res[$this->int_pk_name];
					if ($res[$this->int_pk_name] == $this->table_array[$this->int_pk_name]["value"])
					{ 
						$pk_selected = $res[$this->int_pk_name];
					}
					unset($t_string);
					for ($i = 0; $i < count($this->field_array); $i ++)
					{
						if ($t_string)
							$t_string .= ", ";
						if ($this->field_array[$i]["before_value"])
							$t_string .= $this->field_array[$i]["before_value"];
						if ($this->field_array[$i]["binary"])
							$t_string .= ($res[$this->field_array[$i]["name"]]) ? $this->field_array[$i]["binary"][0] : $this->field_array[$i]["binary"][1];
						else
							$t_string .= $res[$this->field_array[$i]["name"]];
					}
/*
//<? echo $res["question"]." (Lang: ".$res["lang_name"].", Freigeschalten: ".ucfirst($res["enabled"]?"ja":"nein").")"; ?>
*/
					$pk_names[] = $t_string;
				}
			} // show it at all
			return array('t_pk_name' => $t_pk_name, 'pk_ids' => $pk_ids, 'pk_names' => $pk_names, 'pk_selected' => $pk_selected);
		}

		// METHOD form_create_new
		// PARAMS none
		// RETURN part for new
		public function form_create_new($hide_new_checkbox = 0)
		{
			// when security level is okay
			if ($this->group_level_user <= $this->security_level["new"])
			{
				if ($this->yes && !$hide_new_checkbox)
				{ 
					$show_checkbox = 1;
				}
				// set type of new name
				if ($this->yes)
					$new_name = $this->l->__("Clear all and create new");
				else
					$new_name = $this->l->__("New");
			} // security level okay
			return array('new_name' => $new_name, 'show_checkbox' => $show_checkbox);
		}

		// METHOD form_create_save_delete
		// PARAMS none
		// RETURN string for delete / save part
		public function form_create_save_delete($hide_delete = 0, $hide_delete_checkbox = 0)
		{
			if ($this->group_level_user <= $this->security_level["save"] || $this->group_level_user <= $this->security_level["delete"])
			{
				$old_school_hidden = 0;
				if ($this->group_level_user <= $this->security_level["save"])
				{
					$seclevel_okay = 1;
					if (!$this->table_array[$this->int_pk_name]["value"]) 
					{ 
						$save = $this->l->__("Save");
					}
					else 
					{ 
						$save = $this->l->__("Update");
					}
					// print the old_school hidden if requestet
					if ($old_school_hidden)
					{
						$pk_name = $this->int_pk_name;
						$pk_value = $this->table_array[$this->int_pk_name]["value"];
					}
				} // show save part
				// show delete part only if pk is set && we want to see the delete
				if ($this->table_array[$this->int_pk_name]["value"] && !$hide_delete && $this->group_level_user <= $this->security_level["delete"])
				{
					$show_delete = 1;
				}
			} // print save/delete row at all$
			return array('seclevel_okay' => $seclevel_okay, 'save' => $save, 'pk_name' => $pk_name, 'pk_value' => $pk_value, 'show_delete' => $show_delete, 'hide_delete_checkbox' => $hide_delete_checkbox);
		} // end of function

		// METHOD form_create_element
		// PARAMS $element_name: the name from the array, you want to have build
		//        $query: can overrule internal query data,
		//                for drop down, as data comes from a reference table
		//                for drop_down_text it has to be an array with $key->$value
		// RETURN element in HTML
		public function form_create_element ($element_name, $query = "")
		{
			// special 2nd color for "binary" attribut
			if ($this->table_array[$element_name]["type"] == "binary" && !$this->table_array[$element_name]["value"])
				$EDIT_FGCOLOR_T = 'edit_fgcolor_no';
			else
				$EDIT_FGCOLOR_T = 'edit_fgcolor';
			$output_name = $this->table_array[$element_name]["output_name"];
			if ($this->table_array[$element_name]["mandatory"])
				$output_name .= ' *';
			// create right side depending on "definiton" in table_array
			$type = $this->table_array[$element_name]["type"];
			// view only output
			if ($this->table_array[$element_name]["type"] == "view")
			{
				$data['value'] = !$this->table_array[$element_name]["value"] ? $this->table_array[$element_name]['empty'] : $this->table_array[$element_name]["value"];
			}
			// binary true/false element
			if ($this->table_array[$element_name]["type"] == "binary")
			{
				for ($i = (count($this->table_array[$element_name]["element_list"]) - 1); $i >= 0; $i --)
				{
					$data['value'][] = $i;
					$data['output'][] = $this->table_array[$element_name]["element_list"][$i];
					$data['name'] = $element_name;
					if (($i && $this->table_array[$element_name]["value"]) || (!$i && !$this->table_array[$element_name]["value"]))
						$data['checked'] = $this->table_array[$element_name]["value"]; 

					if ($i)
						$data['separator'] = '';
				}
			}
			// checkbox element
			if ($this->table_array[$element_name]["type"] == "checkbox")
			{
				$data['name'] = $element_name;
				$data['value'][] = $this->table_array[$element_name]["element_list"];
				$data['checked'] = $this->table_array[$element_name]["value"];
			}
			// normal text element
			if ($this->table_array[$element_name]["type"] == "text")
			{
				$data['name'] = $element_name;
				$data['value'] = $this->table_array[$element_name]["value"];
				$data['size'] = $this->table_array[$element_name]["size"];
				$data['length'] = $this->table_array[$element_name]["length"];
			}
			// password element, does not write back the value
			if ($this->table_array[$element_name]["type"] == "password")
			{
				$data['name'] = $element_name;
				$data['HIDDEN_value'] = $this->table_array[$element_name]["HIDDEN_value"];
				$data['size'] = $this->table_array[$element_name]["size"];
				$data['length'] = $this->table_array[$element_name]["length"];
			}
			// date (YYYY-MM-DD)
			if ($this->table_array[$element_name]["type"] == "date")
			{
				if (!$this->table_array[$element_name]["value"])
					$this->table_array[$element_name]["value"] = "YYYY-MM-DD";
				$data['name'] = $element_name;
				$data['value'] = $this->table_array[$element_name]["value"];
			}
			// textarea
			if ($this->table_array[$element_name]["type"] == "textarea")
			{
				$data['name'] = $element_name;
				$data['value'] = $this->table_array[$element_name]["value"];
				$data['rows'] = $this->table_array[$element_name]["rows"];
				$data['cols'] = $this->table_array[$element_name]["cols"];
			}
			// for drop_down_*
			if (preg_match("/^drop_down_/", $this->table_array[$element_name]["type"]))
			{
				$type = 'drop_down';
				// outer query overrules inner
				if (!$query)
					$query = $this->table_array[$element_name]["query"];
			}
			// for drop_down_db*
			if (preg_match("/^drop_down_db/", $this->table_array[$element_name]["type"]))
			{
				// if still NO query
				if (!$query)
				{
					// select pk_name, input_name from table_name (order by order_by)
					$query = "SELECT DISTINCT ".$this->table_array[$element_name]["pk_name"].", ".$this->table_array[$element_name]["input_name"]." FROM ".$this->table_array[$element_name]["table_name"];
					// possible where statements
					if ($this->table_array[$element_name]["where"])
						$query .= " WHERE ".$this->table_array[$element_name]["where"];
					// possible order statements
					if ($this->table_array[$element_name]["order_by"])
						$query .= " ORDER BY ".$this->table_array[$element_name]["order_by"];
				}
				// set output data
				$data['name'] = $element_name;
				$data['value'][] = "";
				$data['output'][] = $this->l->__("Please choose ...");
				while ($res = $this->db_return($query))
				{
					$data['value'][] = $res[0];
					$data['output'][] = $res[1];
					if ($this->table_array[$element_name]["value"] == $res[0])
						$data['selected'] = $this->table_array[$element_name]["value"];
				}
				// for _input put additional field next to drop down
				if (preg_match("/^drop_down_db_input/", $this->table_array[$element_name]["type"]))
				{
					$data['drop_down_input'] = 1;
					// pre fill the temp if empty and other side is selected, only for same_db
					if ($this->table_array[$element_name]["type"] == "drop_down_db_input_same_db" && !$this->table_array[$element_name]["input_value"] && $this->table_array[$element_name]["value"])
						$this->table_array[$element_name]["input_value"] = $this->table_array[$element_name]["value"];
					$data['input_value'] = $this->table_array[$element_name]["input_value"];
					$data['input_name'] = $this->table_array[$element_name]["input_name"].(($this->table_array[$element_name]["type"] == "drop_down_db_input_same_db") ? '_temp' : '');
					$data['input_size'] = $this->table_array[$element_name]["size"];
					$data['input_length'] = $this->table_array[$element_name]["length"];
				}
			}
			// drop down array
			if ($this->table_array[$element_name]["type"] == "drop_down_array")
			{
				$data['name'] = $element_name;
				$data['value'][] = "";
				$data['output'][] = $this->l->__("Please choose ...");
				// outer query overrules inner
				while (list($key, $value) = each($query))
				{
					$data['value'][] = $key;
					$data['output'][] = $value;
					if ($this->table_array[$element_name]["value"] == $key)
						$data['selected'] = $this->table_array[$element_name]["value"];
				}
			}
			// radio array
			if ($this->table_array[$element_name]["type"] == "radio_array")
			{
				if (!$query)
					$query = $this->table_array[$element_name]["query"];
				$data['name'] = $element_name;
				while (list($key, $value) = each($query))
				{
					$data['value'][] = $key;
					$data['output'][] = $value;
					if ($this->table_array[$element_name]["value"] == $key)
						$data['checked'] = $this->table_array[$element_name]["value"];
					$data['separator'] = '';
				}
			}
			// for media / not yet implemented
			if ($this->table_array[$element_name]["type"] == "media")
			{
				//media::insert_file($element_name,$this->table_array[$element_name]["value"],$query); 
			}
			// order button
			if ($this->table_array[$element_name]["type"] == "order")
			{
				$data['output_name'] = $this->table_array[$element_name]["output_name"];
				$data['name'] = $element_name;
				$data['value'] = $this->table_array[$element_name]["value"];
				$data['col_name'] = $this->col_name;
				$data['table_name'] = $this->table_name;
				$data['query'] = urlencode($query);
			}
			// file upload
			if ($this->table_array[$element_name]["type"] == "file")
			{
				$data['name'] = $element_name;
				// if file for this exsists, print "delete, view stuff"
				if ($this->table_array[$element_name]["value"])
				{
					$data['content'] = 1;
					$data['url'] = $this->table_array[$element_name]["open_dir"].$this->table_array[$element_name]["value"];
					$data['output'] = $this->table_array[$element_name]["value"];
					$data['value'] = $this->table_array[$element_name]["value"];
				}
			}
			return array('output_name' => $output_name, 'color' => $EDIT_FGCOLOR_T, 'type' => $type, 'data' => $data);
		}

		// METHOD form_error_check
		// PARAMS none
		// RETURNS full error message string for output
		//         should be cought like this ...
		//         if ($msg=$form->form_error_check())
		//           $error=1;
		public function form_error_check()
		{
			reset($this->table_array);
			while (list($key, $value) = each($this->table_array))
			{
				//if ($value["mandatory"] && $value["error_check"])
				// if error value set && somethign input, check if input okay
				if ($value["error_check"] && $this->table_array[$key]["value"])
				{
					// each error check can be a piped seperated value, lets split it
//$this->debug('edit', $value["error_check"]);
					$error_checks = explode("|", $value["error_check"]);
					foreach ($error_checks as $error_check)
					{
						switch ($error_check)
						{ 
							case "number":
								if (!preg_match("/^[0-9]+(['\,','.']?[0-9]+)*$/", $this->table_array[$key]["value"]))
									$this->msg .= sprintf($this->l->__("Please enter a vailid Number for the <b>%s</b> Field!<br>"), $this->table_array[$key]["output_name"]);
								break;
							case "date": // YYYY-MM-DD
								if (!check_date($this->table_array[$key]["value"], 1))
									$this->msg .= sprintf($this->l->__("Please enter a vailid date (YYYY-MM-DD) for the <b>%s</b> Field!<br>"), $this->table_array[$key]["output_name"]);
								break;
							case "time": // HH:MM[:SS]
								if (!check_time($this->table_array[$key]["value"]))
									$this->msg .= sprintf($this->l->__("Please enter a vailid time (HH:MM[:SS]) for the <b>%s</b> Field!<br>"), $this->table_array[$key]["output_name"]);
								break;
							case "datetime": // YYYY-MM-DD HH:MM[:SS]
								break;
							case "intervalshort": // ony interval n [Y/M/D] only
								if (preg_match("/^\d{1,3}\ ?[YMDymd]{1}$/", $this->table_array[$key]['value']))
									$this->msg .= sprintf($this->l->__('Please enter a valid time interval in the format <length> Y|M|D for the <b>%s</b> Field!<br>'), $this->table[$key]['output_name']);
							case "email":
								if (!preg_match("/$this->email_regex/", $this->table_array[$key]["value"]))
									$this->msg .= sprintf($this->l->__("Please enter a valid E-Mail Address for the <b>%s</b> Field!<br>"), $this->table_array[$key]["output_name"]);
								break;
							// check unique, check if field in table is not yet exist
							case "unique":
								$q = "SELECT ".$key." FROM ".$this->table_name." WHERE ".$key." = '".addslashes($this->table_array[$key]["value"])."'";
								if ($this->table_array[$this->int_pk_name]["value"])
									$q .= " AND ".$this->int_pk_name." <> ".$this->table_array[$this->int_pk_name]["value"];
								list($$key) = $this->db_return_row($q);
								if ($$key)
									$this->msg .= sprintf($this->l->__("The field <b>%s</b> can be used only once!<br>"), $this->table_array[$key]["output_name"]);
								break;
							case "custom":
								if (!preg_match($this->table_array[$key]["error_regex"], $this->table_array[$key]["value"]))
									$this->msg .= sprintf($this->l->__("Please enter a valid (%s) input for the <b>%s</b> Field!<br>"), $this->table_array[$key]["error_example"], $this->table_array[$key]["output_name"]);
								break;
							case "alphanumeric":
//$this->debug('edit', 'IN Alphanumeric');
								if (!preg_match("/^[0-9A-Za-z_-]+$/", $this->table_array[$key]["value"]))
									$this->msg .= sprintf($this->l->__("Please enter a valid alphanumeric (Numbers and Letters only also - and _, no spaces) value for the <b>%s</b> Field!<br>"), $this->table_array[$key]["output_name"]);
								break;
							// this one also allows @ and .
							case "alphanumericextended":
//$this->debug('edit', 'IN Alphanumeric');
								if (!preg_match("/^[0-9A-Za-z_-@\.]+$/", $this->table_array[$key]["value"]))
									$this->msg .= sprintf($this->l->__("Please enter a valid alphanumeric extended (Numbers, Letters, -,  _, @ and . only, no spaces) value for the <b>%s</b> Field!<br>"), $this->table_array[$key]["output_name"]);
								break;
							case "password":
								// password can only be alphanumeric + special chars
								// password and CONFIRM_password need to be the same
								if ($this->table_array[$key]["value"] != $this->table_array[$key]["CONFIRM_value"])
								{
									// error
								}
								break;
						} // switch
					} // for each error to check
				}
				// if mandatory && no input
				else if ($value["mandatory"] && 
							(
								   // for all "normal" fields
								   ($this->table_array[$key]["type"] != "password" && $this->table_array[$key]["type"] != "drop_down_db_input" && !$this->table_array[$key]["value"]) || 
								   // for drop_down_db_input check if one of both fields filled
								   ($this->table_array[$key]["type"] == "drop_down_db_input" && !$this->table_array[$key]["input_value"] && !$this->table_array[$key]["value"]) ||
								   // for password
								   ($this->table_array[$key]["type"] == "password" && !$this->table_array[$key]["value"] && !$this->table_array[$key]["HIDDEN_value"])
							  )
						) // main if end
				{
//$this->debug('form', "A: ".$this->table_array[$key]["type"]." -- ".$this->table_array[$key]["input_value"]." -- ".$this->table_array[$key]["value"]);
					if (!$this->table_array[$key]["value"] && $this->table_array[$key]["type"] != "binary")
						$this->msg .= sprintf($this->l->__("Please enter something into the <b>%s</b> field!<br>"), $this->table_array[$key]["output_name"]);
				} // mandatory
				// check file upload
				if ($this->table_array[$key]["type"] == "file" && $GLOBALS["_FILES"][$key."_file"]['name'] && is_array($this->table_array[$key]["accept_type"]))
				{
					// check against allowed types
					$mime_okay = 0;
					foreach ($this->table_array[$key]["accept_type"] as $mime_type)
					{
						if ($GLOBALS["_FILES"][$key."_file"]['type'] == $mime_type)
							$mime_okay = 1;
					}
					if (!$mime_okay)
					{
						$this->msg .= sprintf($this->l->__("Uploaded File <b>%s</b> has MIME Type <b>%s</b> which is not in theallowed MIME List for Upload Field <b>%s</b>!<br>"), $GLOBALS["_FILES"][$key."_file"]['name'], $GLOBALS["_FILES"][$key."_file"]['type'], $this->table_array[$key]["output_name"]);
					}
				}
			} // while
			if (is_array($this->reference_array))
			{
				// do check for reference tables
				reset($this->reference_array);
				while (list($key, $value) = each($this->reference_array))
				{
					if ($this->reference_array[$key]["mandatory"] && !$this->reference_array[$key]["selected"][0])
						$this->msg .= sprintf($this->l->__("Please select at least one Element from field <b>%s</b>!<br>"), $this->reference_array[$key]["output_name"]);
				}
			} 
//$this->debug('edit_error', "QS: <pre>".print_r($_POST, 1)."</pre>");
            if (is_array($this->element_list))
			{
				// check the mandatory stuff
				// if mandatory, check that at least on pk exists or if at least the mandatory field is filled
				while (list($table_name, $reference_array) = each($this->element_list))
				{
					// get the leasy of keys from the elements array
					$keys = array_keys($reference_array["elements"]);
					// prefix
					$prfx = ($reference_array["prefix"]) ? $reference_array["prefix"]."_" : '';
					// get max elements
					$max = 0;
					foreach ($keys as $key)
					{
						if (count($_POST[$prfx.$key]) > $max)
							$max = count($_POST[$prfx.$key]);
//$this->debug('edit_error_chk', "KEY: $prfx$key | count: ".count($_POST[$prfx.$key])." | M: $max");
//$this->debug('edit_error_chk', "K: ".$_POST[$prfx.$key]." | ".$_POST[$prfx.$key][0]);
					}
//$this->debug('post_array', $this->print_ar($_POST));
					# check each row
					for ($i = 0; $i < $max; $i ++)
					{
						// either one of the post pks is set, or the mandatory
						while (list($el_name, $data_array) = each($reference_array["elements"]))
						{
							if ($data_array["mandatory"])
								$mand_name = $data_array["output_name"];
							// check if there is a primary ket inside, so it is okay
							if ($data_array["pk_id"] && count($_POST[$prfx.$el_name]) && $reference_array["mandatory"])
							{
								$mand_okay = 1;
							}
						// we found a mandatory field. check now if one is set to satisfy the main mandatory
						// also check, if this field is mandatory and its not set, but any other, throw an error
//						for ($i = 0; $i < count($_POST[$prfx.$el_name]); $i ++)
//						{
//$this->debug('edit_error_chk', "RG error - Data[".$prfx.$el_name.": ".$_POST[$prfx.$el_name][$i]." | ".$_POST[$prfx.$el_name]." - ".$reference_array['enable_name']." - ".$_POST[$reference_array['enable_name']][$_POST[$prfx.$el_name][$i]]);
							if ($data_array["mandatory"] && $_POST[$prfx.$el_name][$i])
							{
								$mand_okay = 1;
								$row_okay[$i] = 1;
							}
							// radio group and set where one not active
							elseif ($data_array['type'] == 'radio_group' && !isset($_POST[$prfx.$el_name]))
							{
//$this->debug('edit_error_chk', "RADIO GROUP");
								$row_okay[$_POST[$prfx.$el_name][$i]] = 0;
								$default_wrong[$_POST[$prfx.$el_name][$i]] = 1;
								$error[$_POST[$prfx.$el_name][$i]] = 1;
							}
							elseif ($_POST[$prfx.$el_name][$i] && !$error[$i])
							{
//$this->debug('edit_error_chk', "[$i]");
								$element_set[$i] = 1;
								$row_okay[$i] = 1;
							}
							elseif ($data_array["mandatory"] && !$_POST[$prfx.$el_name][$i])
							{
								$row_okay[$i] = 0;
							}

//						}

						} // if main mandatory
					}

					// main mandatory is met -> error msg
					if (!$mand_okay && $reference_array["mandatory"])
					{
						$this->msg .= sprintf($this->l->__("You need to enter at least one data set for field <b>%s</b>!<Br>"), $reference_array["output_name"]);
					}
					for ($i = 0; $i < $max; $i ++)
					{
						if (!$row_okay[$i] && $element_set[$i])
						{
							$this->msg .= sprintf($this->l->__("The row <b>%s</b> has <b>%s</b> set as mandatory, please fill at least this field out<br>"), $i, $mand_name);
						}
						if (!$row_okay[$i] && $default_wrong[$i])
						{
							$this->msg .= sprintf($this->l->__("The row <b>%s</b> would have a default setting, but it would be disabled. Please change the default setting and save again<br>"), $i);
						}
					}
				} // each element list
			}
			if ($this->msg)
				$this->error = 1;
		}

		// METHOD form_set_order
		// PARAMS none
		// RETURN the table array
		// DOES   sets the order to the maximum, if order flag is set in array
		public function form_set_order()
		{
			// get order name
			$order_name = $this->form_get_col_name_from_key("order");
			if ($order_name)
			{
				// first check out of order ...

				if (!$this->table_array[$order_name]["value"])
				{
					// set order (read max)
					$q = "SELECT MAX(".$order_name.") + 1 AS max_page_order FROM ".$this->table_name;
					list($this->table_array[$order_name]["value"]) = $this->db_return_row($q);
					// frist element is 0 because NULL gets returned, set to 1
					if (!$this->table_array[$order_name]["value"])
						$this->table_array[$order_name]["value"] = 1;
				}
				else if ($this->table_array[$this->int_pk_name]["value"])
				{
					$q = "SELECT $order_name FROM ".$this->table_name." WHERE ".$this->int_pk_name." = ".$this->table_array[$this->int_pk_name]["value"];
					list($this->table_array[$order_name]["value"]) = $this->db_return_row($q);
				}
			}
			return $this->table_array;
		}

		// METHOD form_unsert_table_array
		// PARAMS none
		// RETURN none
		// DOES resets all values in table_array and in the reference tables
		public function form_unset_table_array()
		{
			unset($this->pk_id);
			reset($this->table_array);
			while (list($key, $value) = each($this->table_array))
			{
				unset($this->table_array[$key]["value"]);
				unset($this->table_array[$key]["input_value"]);
				// if preset var present preset
				if (isset($this->table_array[$key]["preset"]))
					$this->table_array[$key]["value"] = $this->table_array[$key]["preset"];
			}  
			if (is_array($this->reference_array))
			{
				reset($this->reference_array);
				while (list($key, $value) = each($this->reference_array))
				{
					unset($this->reference_array[$key]["selected"]);
				}
			}
			$this->warning = 1;
			$this->msg = $this->l->__("Cleared for new Dataset!");
		}

		// METHOD form_load_table_array
		// PARAMS pk_id - overrule pk_id
		// RETURN none
		// DESC   load a table & reference
		public function form_load_table_array($pk_id = 0)
		{
			if ($pk_id)
				$this->pk_id = $pk_id;
			$this->table_array = $this->db_read(1);

			// reset all temp fields
			reset($this->table_array);
			while (list($key, $value) = each($this->table_array))
				unset($this->table_array[$key]["input_value"]);

			if (is_array($this->reference_array))
			{
				// load each reference_table
				reset($this->reference_array);
				while (list($key, $value) = each($this->reference_array))
				{
					unset($this->reference_array[$key]["selected"]);
					$q = "SELECT ".$this->reference_array[$key]["other_table_pk"]." FROM ".$this->reference_array[$key]["table_name"]." WHERE ".$this->int_pk_name."=".$this->table_array[$this->int_pk_name]["value"];
					while ($res = $this->db_return($q))
						$this->reference_array[$key]["selected"][] = $res[$this->reference_array[$key]["other_table_pk"]];
				}
			}
			$this->warning = 1;
			$this->msg = $this->l->__("Dataset has been loaded!<br>");
		}

		// METHOD form_save_table_array
		// PARAMS addslashes - if one, passes 1 to the db_write function
		// RETURN none 
		// DESC   save a table, reference and all input fields
		public function form_save_table_array($addslashes = 0)
		{
//       global $_FILES;
			// for drop_down_db_input check if text field is filled and if, if not yet in db ...
			// and upload files
			reset($this->table_array);
			while (list($key, $value) = each($this->table_array))
			{
				// drop_down_db with input + reference table
//$this->debug('form', "A: ".$this->table_array[$key]["type"]." --- ".$this->table_array[$key]["input_value"]);
				if ($this->table_array[$key]["type"] == "drop_down_db_input" && $this->table_array[$key]["input_value"])
				{
//$this->debug('form', "HERE");
					// check if this text name already exists (lowercase compare)
					$q = "SELECT ".$this->table_array[$key]["pk_name"]." FROM ".$this->table_array[$key]["table_name"]." WHERE LCASE(".$this->table_array[$key]["input_name"].") = '".addslashes(strtolower($this->table_array[$key]["input_value"]))."'";
					// if a where was given, add here
					if ($this->table_array[$key]["where"])
						$q .= " AND ".$this->table_array[$key]["where"];      
					list($pk_name_temp) = $this->db_return_row($q);
					if ($this->num_rows >= 1)
					{
						$this->table_array[$key]["value"] = $pk_name_temp; 
					}
					else
					{
						// if a where was given, set this key also [dangerous!]
						
						// posgres compatible insert
						$q = "INSERT INTO ".$this->table_array[$key]["table_name"]." (".$this->table_array[$key]["input_name"].") VALUES ('".addslashes($this->table_array[$key]["input_value"])."')";
						$this->db_exec($q);
						if ($this->table_array[$key]["where"])
						{
							// make an update on the just inseted data with the where data als update values
							$q = "UPDATE ".$this->table_array[$key]["table_name"]." SET ";
							$q .= $this->table_array[$key]["where"]." ";
							$q .= "WHERE ".$this->table_array[$key]["pk_name"]." = ".$this->insert_id;
							$this->db_exec($q);
						}
						$this->table_array[$key]["value"] = $this->insert_id;
					} // set value from DB through select or insert
					unset($this->table_array[$key]["input_value"]);
				} // if it is certain field type && if there is something in the temp field
				// drop_down_db with input and in same table
				if ($this->table_array[$key]["type"] == "drop_down_db_input_same_db" && $this->table_array[$key]["input_value"])
				{
					// if drop down & input are different
					if ($this->table_array[$key]["input_value"] != $this->table_array[$key]["value"])
					{
						// check if "right input" is in DB
						$q = "SELECT ".$this->table_array[$key]["input_name"]." FROM ".$this->table_array[$key]["table_name"]." WHERE LCASE(".$this->table_array[$key]["input_name"].") = '".strtolower(addslashes($this->table_array[$key]["input_value"]))."'";
						// if a where was given, add here
						if ($this->table_array[$key]["where"])
							$q .= " AND ".$this->table_array[$key]["where"];
						list($temp) = $this->db_return_row($q);
						// nothing found in table, use new inserted key
						if (!$temp)
						{
							$this->table_array[$key]["value"] = $this->table_array[$key]["input_value"];
						} else // found in DB
						{
							$this->table_array[$key]["input_value"] = $this->table_array[$key]["value"];
						}
					} // key difference ?
				} // for same_db drop down

				// upload & save files to locations
				if ($this->table_array[$key]["type"] == "file")
				{
					// if smth in $$key_file -> save or overwrite
					// if smth in $key && $$key_delete && !$$key_file-> delte
					// if smth in $key, keep as is
//           $_file=$key."_file";
//           $_delete=$key."_delete";
//$this->debug('form', "UF: ".$GLOBALS["_FILES"][$key."_file"]['name']);
//$this->debug('form', "delete: ".$key."_delete => ".$GLOBALS[$key.'_delete']);
					if ($GLOBALS["_FILES"][$key."_file"]['name'])
					{
						// check if dir exists
						if (is_dir($this->table_array[$key]["save_dir"]))
						{
							//if a slash at the end (if not add slash)
							if (!preg_match("|/$|", $this->table_array[$key]["save_dir"]))
								$this->table_array[$key]["save_dir"] .= "/";
							if (move_uploaded_file($GLOBALS["_FILES"][$key."_file"]['tmp_name'], $this->table_array[$key]["save_dir"].$GLOBALS["_FILES"][$key."_file"]['name'])) 
							{
								// make it unique with a unique number at the beginning
								$this->table_array[$key]["value"] = uniqid(rand(), 1)."_".$GLOBALS["_FILES"][$key."_file"]['name'];
							}
							else
							{
								$this->msg .= $this->l->__("File could not be copied to target directory! Perhaps wrong directory permissions.");
								$this->error = 1;
							} // could not move file (dir permissions?)
						}
						else
						{
							$this->msg .= sprintf($this->l->__("Target Directory \"%s\" is not a vaild directory!"), $this->table_array[$key]["save_dir"]);
							$this->error = 1;
						} // could not dir check (dir wrong??)
					}
					if ($GLOBALS[$key.'_delete'] && $this->table_array[$key]["value"] && !$GLOBALS["_FILES"][$key."_file"]['name'])
					{
						unlink($this->table_array[$key]["save_dir"].$this->table_array[$key]["value"]);
						unset($this->table_array[$key]["value"]);
					}
				}

				// for password crypt it as blowfish, or if not available MD5
				if ($this->table_array[$key]['type'] == 'password')
				{
					if ($this->table_array[$key]["value"])
					{
						// password is stored in blowfish format, or in the format supported by this PHP version
						$this->table_array[$key]["value"] = $this->cryptString($this->table_array[$key]["value"]);
						$this->table_array[$key]["HIDDEN_value"] = $this->table_array[$key]["value"];
					}
					else
					{
//						$this->table_array[$key]["HIDDEN_value"] = 
					}
				}
			} // go through each field
			
			// set object order (if necessary)
			$this->form_set_order();
			// write the object
			$this->db_write($addslashes);
			// write reference array(s) if necessary
			if (is_array($this->reference_array))
			{
				reset($this->reference_array);
				foreach ($this->reference_array AS $reference_array)
				{
					$q = "DELETE FROM ".$reference_array["table_name"]." WHERE ".$this->int_pk_name."=".$this->table_array[$this->int_pk_name]["value"];
					$this->db_exec($q);
					$q = "INSERT INTO ".$reference_array["table_name"]." (".$reference_array["other_table_pk"].", ".$this->int_pk_name.") VALUES ";
					for ($i = 0; $i < count($reference_array["selected"]); $i ++)
					{
						$t_q = "(".$reference_array["selected"][$i].", ".$this->table_array[$this->int_pk_name]["value"].")";
						$this->db_exec($q.$t_q);
					}
				} // foreach reference arrays
			} // if reference arrays
			// write element list
			if (is_array($this->element_list))
			{
				reset($this->element_list);

				while (list($table_name, $reference_array) = each($this->element_list))
				{
					// get the number of keys from the elements array
					$keys = array_keys($reference_array["elements"]);
					// element prefix name
					$prfx = ($reference_array["prefix"]) ? $reference_array["prefix"]."_" : '';
					// get max elements
					$max = 0;
					foreach ($keys as $key)
					{
						if (count($_POST[$prfx.$key]) > $max)
							$max = count($_POST[$prfx.$key]);
					}
//$this->debug('edit_error', "MAX: $max");
					// check if there is a hidden key, update, else insert
					while (list($el_name, $data_array) = each($reference_array["elements"]))
					{
//$this->debug('edit_error_query', "QUERY: ".$this->print_ar($_POST));
						// go through all submitted data
//						for ($i = 0; $i < count($_POST[$el_name]); $i ++)
						for ($i = 0; $i < $max; $i ++)
						{
							// if we have enable name & delete set, then only insert/update those which are flagged as active
							// check if mandatory field is set, if not set "do not write flag"
							if ($data_array["mandatory"] && !$_POST[$prfx.$el_name][$i])
							{
								$no_write[$i] = 1;
							}
							if ($reference_array['enable_name'] && $reference_array['delete'] && !$_POST[$reference_array['enable_name']][$i])
							{
								$no_write[$i] = 1;
							}
							// set type and boundaries for insert/update
							if ($data_array["pk_id"] && $_POST[$prfx.$el_name][$i])
							{
								$q_begin[$i] = "UPDATE $table_name SET ";
								$q_end[$i] = " WHERE $el_name = ".$_POST[$prfx.$el_name][$i];
								$type[$i] = "update";
							}
							elseif ($data_array["pk_id"] && !$_POST[$prfx.$el_name][$i])
							{
								$q_begin[$i] = "INSERT INTO $table_name (";
								$q_middle[$i] = ") VALUES (";
								$q_end[$i] = ")";
								$type[$i] = "insert";
							}
							// write all data (insert/update) because I don't know until all are processed if it is insert or update
							// don't write primary key backup for update
$this->debug('edit_error', "I: $i | EL Name: $prfx$el_name | Data: ".$_POST[$prfx.$el_name][$i]." {".$_POST[$prfx.$el_name]."} | Type: ".$type[$i]." | PK: ".$data_array["pk_id"]." ");
							if (!$data_array["pk_id"])
							{
								// update
								if (strlen($q_data[$i]))
									$q_data[$i] .= ", ";
								// insert
								if ($q_names[$i])
									$q_names[$i] .= ", ";
								$q_names[$i] .= $el_name;
								if (strlen($q_values[$i]))
									$q_values[$i] .= ", ";
								// data part
								if ($data_array['type'] == 'radio_group')
								{
									if ($i == $_POST[$prfx.$el_name])
										$_value = $i + 1;
									else
										$_value = 'NULL';
								}
								else
								{
									$_value = $_POST[$prfx.$el_name][$i];
								}
								if ($data_array['int'] || $data_array['int_null'])
								{
									if (!$_value && $data_array['int_null'])
										$value = 'NULL';
									elseif (!isset($_value))
										$_value = 0;
									$q_data[$i] .= $el_name." = ".$_value;
									$q_values[$i] .= $_value;
								}
								else
								{
									$q_data[$i] .= $el_name." = '".addslashes($_value)."'";
									$q_values[$i] .= "'".addslashes($_value)."'";
								}
							}
						}
					} // eche table elements
					for ($i = 0; $i < count($type); $i ++)
					{
						if (!$no_write[$i])
						{
							if ($type[$i] == "update")
							{
								$q = $q_begin[$i].$q_data[$i].$q_end[$i];
							}
							else
							{
								$q = $q_begin[$i].$q_names[$i].", ".$this->int_pk_name.$q_middle[$i].$q_values[$i].", ".$this->table_array[$this->int_pk_name]["value"].$q_end[$i];
							}
//$this->debug('edit', "Q: ".$q."<br>");
							// write the dataset
							$this->db_exec($q);
						}
					} // for each created query
				} // each element list
			}
			$this->warning = 1;
			$this->msg = $this->l->__("Dataset has been saved!<Br>");
		} 

		// METHOD form_delete_table_array
		// PARAMS none
		// RETURN none
		// DESC   delete a table and reference fields
		public function form_delete_table_array()
		{
			// remove any reference arrays
			if (is_array($this->reference_array))
			{
				reset($this->reference_array);
				foreach ($this->reference_array AS $reference_array)
				{
					$q = "DELETE FROM ".$reference_array["table_name"]." WHERE ".$this->int_pk_name." = ".$this->table_array[$this->int_pk_name]["value"];
					$this->db_exec($q);
				}
			}
			// remove any element list references
			if (is_array($this->element_list))
			{
				reset($this->element_list);
				while (list($table_name, $data_array) = each($this->element_list))
				{
					$q = "DELETE FROM ".$table_name." WHERE ".$this->int_pk_name." = ".$this->table_array[$this->int_pk_name]["value"];
					$this->db_exec($q);
				}
			}
			// unlink ALL files
			reset($this->table_array);
			while (list($key, $value) = each($this->table_array))
			{
				if ($this->table_array[$key]["type"] == "file")
					unlink($this->table_array[$key]["save_dir"].$this->table_array[$key]["value"]);
			}
			$this->db_delete();
			$this->warning = 1;
			$this->msg = $this->l->__("Dataset has been deleted!");
		}

		// METHOD form_create_hidden_fields
		// PARAMS $hidden_array
		// RETURNS the input fields (html)
		// creates HTML hidden input fields out of an hash array
		public function form_create_hidden_fields($hidden_array = "")
		{
			reset ($this->table_array);
			while (list($key, $value) = each($this->table_array))
			{
				if ($this->table_array[$key]["type"] == "hidden")
				{
					$hidden_array[$key] = $this->table_array[$key]["value"];
				}
			}
			if ($hidden_array)
			{
				reset ($hidden_array);
				while (list($key, $value) = each($hidden_array))
				{
					$hidden[] = array('key' => $key, 'value' => $value);
				}
			}
			return $hidden;
		}

		// METHOD form_create_element_reference_table
		// PARAMS show which reference table
		// RETURN array for output
		// DESC   creates the multiple select part for a reference_table
		public function form_create_element_reference_table($table_name)
		{
			$output_name = $this->reference_array[$table_name]["output_name"];
			if ($this->reference_array[$table_name]["mandatory"])
				$output_name .= ' *';
			$data['name'] = $this->reference_array[$table_name]["other_table_pk"];
			$data['size'] = $this->reference_array[$table_name]["select_size"];
			while ($res = $this->db_return($this->reference_array[$table_name]["query"]))
			{
				$data['value'][] = $res[0];
				$data['output'][] = $res[1];
				$data['selected'][] = ($this->checked($this->reference_array[$table_name]["selected"], $res[0])) ? $res[0] : '';
			}
			$type = 'reference_table';
			return array('output_name' => $output_name, 'type' => $type, 'color' => 'edit_fgcolor', 'data' => $data);
		}

		// METHOD form_create_element_list
		// PARAMS show which element list
		// RETURN array for output
		// DESC   create list of elements next to each other for a group of data in an input field
		public function form_create_element_list_table($table_name)
		{
			$output_name = $this->element_list[$table_name]["output_name"];
			if ($this->element_list[$table_name]["mandatory"])
				$output_name .= ' *';
			// delete button name, if there is one set
			if ($this->element_list[$table_name]["delete_name"])
				$data['delete_name'] = $this->element_list[$table_name]["delete_name"];
			// set the enable checkbox name if there is one
			if ($this->element_list[$table_name]["enable_name"])
			{
				$data['enable_name'] = $this->element_list[$table_name]["enable_name"];
				if ($this->element_list[$table_name]["delete"])
					$data['delete'] = 1;
			}
			if ($this->element_list[$table_name]["prefix"])
				$data["prefix"] = $this->element_list[$table_name]["prefix"]."_";
			$data['table_name'] = $table_name;
			$pos = 0; // position in while for overwrite if needed
			// build the select part
			reset($this->element_list[$table_name]["elements"]);
			// generic data read in (counts for all rows)
			while (list($el_name, $data_array) = each($this->element_list[$table_name]["elements"]))
			{
				$_el_name = $el_name;
				$el_name = $data["prefix"].$el_name;
				// if the element name matches the read array, then set the table as a name prefix
				$q_select[] = $_el_name; // this is for reading the data
				$data['output_name'][$el_name] = $data_array["output_name"]; // this are the output names (if given)
				$data['type'][$el_name] = $data_array["type"]; /// this is the type of the field
				// set the primary key name
				if ($data_array['pk_id'])
					$data['pk_name'] = $el_name;
				// if drop down db read data for element list
				if ($data_array['type'] == 'drop_down_db')
				{
					$md_q = md5($data_array['query']);
					while ($res = $this->db_return($data_array['query']))
					{
//$this->debug('edit', "Q[$md_q] pos: ".$this->cursor_ext[$md_q]["pos"]." | want: ".$data_array["preset"]." | set: ".$data['preset'][$el_name]);
						// first is default for this element
						if (!$data['preset'][$el_name] && ($this->cursor_ext[$md_q]["pos"] == $data_array['preset']))
							$data['preset'][$el_name] = $res[0];
						// split up data, 0 is id, 1 name
						$data['element_list'][$el_name][] = $res[0];
						$data['output_data'][$el_name][] = $res[1];
					}
				}
				else
				{
					$data['element_list'][$el_name] = $data_array["element_list"]; // this is for the checkboxes
				}
				$proto[$el_name] = ($this->error) ? $_POST[$el_name][(count($_POST[$el_name]) - 1)] : ''; // this is for the new line
			}
			// query for reading in the data
//$this->debug('edit_error', "ERR: ".$this->error);
			// if we got a read data, build the read select for the read, and read out the "selected" data
			if ($this->element_list[$table_name]["read_data"])
			{
				array_unshift($q_select, $this->element_list[$table_name]["read_data"]["name"]);
				// set the rest of the data so we can print something out
				$data['type'][$data["prefix"].$this->element_list[$table_name]["read_data"]["name"]] = 'string';	
				// build the read query
				$q = "SELECT ";
//				if (!$this->table_array[$this->int_pk_name]["value"])
//					$q .= "DISTINCT ";
				// prefix join key with table name
				$q .= str_replace($this->element_list[$table_name]["read_data"]["pk_id"], $this->element_list[$table_name]["read_data"]["table_name"].".".$this->element_list[$table_name]["read_data"]["pk_id"], implode(", ", $q_select))." ";
//				if (!$this->table_array[$this->int_pk_name]["value"] && $this->element_list[$table_name]["read_data"]["order"])
//					$q .= ", ".$this->element_list[$table_name]["read_data"]["order"]." ";
				$q .= "FROM ".$this->element_list[$table_name]["read_data"]["table_name"]." ";
				$q .= "LEFT JOIN ".$table_name." ";
				$q .= "ON (";
				$q .= $this->element_list[$table_name]["read_data"]["table_name"].".".$this->element_list[$table_name]["read_data"]["pk_id"]." = ".$table_name.".".$this->element_list[$table_name]["read_data"]["pk_id"]." ";
//				if ($this->table_array[$this->int_pk_name]["value"])
				$q .= "AND ".$this->int_pk_name." = ".(($this->table_array[$this->int_pk_name]["value"]) ? $this->table_array[$this->int_pk_name]["value"] : 'NULL')." ";
				$q .= ") ";
				if ($this->element_list[$table_name]["read_data"]["order"])
					$q .= " ORDER BY ".$this->element_list[$table_name]["read_data"]["order"];
			}
			else
			{
				// only create query if we have a primary key
				if ($this->table_array[$this->int_pk_name]["value"])
					$q = "SELECT ".implode(", ", $q_select)." FROM ".$table_name." WHERE ".$this->int_pk_name." = ".$this->table_array[$this->int_pk_name]["value"];
			}
			// only run if we have query strnig
			if ($q)
			{
				// read out the list and add the selected data if needed
				while ($res = $this->db_return($q))
				{
					$prfx = $data["prefix"]; // short
					// go through each res
					for ($i = 0; $i < count($q_select); $i ++)
					{
						// query select part, set to the element name
						$el_name = $q_select[$i];
//$this->debug('edit_error', "[$i] POS[$prfx$el_name]: ".$_POST[$prfx.$el_name][$pos]." | RES: ".$res[$el_name]);
						// if we have an error, we take what we have in the vars, if not we take the data from the db
						if ($this->error)
						{
							// if we have a radio group, set a bit different
							if ($data['element_list'][$prfx.$el_name] == 'radio_group')
								$_data[$prfx.$el_name] = ($res[$el_name]) ? ($res[$el_name] - 1) : 0;
							else	 
								$_data[$prfx.$el_name] = $_POST[$prfx.$el_name][$pos];
						}
						else				
						{
							if ($data["preset"][$prfx.$el_name] && !$res[$el_name])
								$_data[$prfx.$el_name] = $data["preset"][$el_name];
							else
								$_data[$prfx.$el_name] = $res[$el_name];
						}
					}
					$data['content'][] = $_data;
					$data['pos'][] = array(0 => $pos); // this is for the checkboxes
					$pos ++; // move up one
					// reset and unset before next run
					unset($_data);
				}
			}
			// push in an empty line of this type, but only if we have a delete key
			if ($data['delete_name'])
				$data['content'][] = $proto;
//$this->debug('edit', "A:<pre>".print_r($data, 1)."</pre>");
			$type = 'element_list';
			return array('output_name' => $output_name, 'type' => $type, 'color' => 'edit_fgcolor', 'data' => $data);
		}
	} // end of class
?>

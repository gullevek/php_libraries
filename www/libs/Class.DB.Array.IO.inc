<?
	/*********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2002/12/17
	* VERSION: 0.4.0
	* RELEASED LICENSE: BSD style (use it, u don't have to make YOUR source public)
	*                   but let me know if u made changes, and please don't redistribute it
	*                   with your name on it ...
	* SHORT DESCRIPTION:
	*   DB Array IO Class:
	*   writes, reads or deletes a complete array (one data set) in/out a
	*   table from the connected DB.
	*   you don't have to write any SQL queries, worry over update/insert
	*
	*   PUBLIC VARIABLES
	*
	*   PRIVATE VARIABLES
	* 
	*   PUBLIC METHODS
	*
	*   PRIVATE METHODS
	*
	* HISTORY:
	* 2005/07/07 (cs) updated array class for postgres: set 0 & NULL if int field given, insert uses () values () syntax
	* 2005/03/31 (cs) fixed the class call with all debug vars
	* 2003-03-10: error_ids where still wrong chagned 11->21 and 12->22
	* 2003-02-26: db_array_io is no longer single class but extens db_io,
	*             as it needs it anyway
	*             moved the class info vars into class_info array into
	*             the constructor, removed info function
	* 2003-02-24: in db_delete moved query build to top, or pk_name/value
	*             will be reset before delete is done
	* 2002-12-20: just added info() method
	* 2002-12-17: splitted the class from other file (with main db wrapper)
	*********************************************************************/

	// picture upload should be taken out from here and out in media_class
	// as it actually has nothing to do with this one here ? (or at least
	// put into separete function in this class)

	// try to include file from LIBS path, or from normal path
	$include_file = 'Class.DB.IO.inc';
	foreach (array('', LIBS, __DIR__.'/') as $folder)
	{
		if (file_exists($folder.$include_file))
			require_once($folder.$include_file);
	}

	// subclass for one array handling
	class db_array_io extends db_io
	{
		// main calss variables
		public $table_array; // the array from the table to work on
		public $table_name; // the table_name
		public $pk_name; // the primary key from this table
		public $pk_id; // the PK id

		// METHOD db_array_io
		// PARAMS db_config -> db_io class init vars
		//        table_array -> the array from the table
		//        table_name -> name of the table (for the array)
		//        db_debug -> turn on db_io debug output (DB_DEBUG as global var does the same)
		// RETURN none
		// DESC   constructor for the array io class, set the
		//        primary key name automatically (from array)
		public function __construct($db_config, $table_array, $table_name, $debug = 0, $db_debug = 0, $echo = 1, $print = 0)
		{
			// instance db_io class
			parent::__construct($db_config, $debug, $db_debug, $echo, $print);
			// more error vars for this class
			$this->error_string["21"] = "No Primary Key given";
			$this->error_string["22"] = "Could not run Array Query";

			$this->table_array = $table_array;
			$this->table_name = $table_name;

			// set primary key for given table_array
			if ($this->table_array)
			{
				while (list($key, $value) = each($table_array))
				{
					if ($value["pk"])
						$this->pk_name = $key;
				}
			} // set pk_name IF table_array was given
			// internal
			$this->class_info["db_array_io"] = array(
				"class_name" => "DB Array IO",
				"class_version" => "0.4.0",
				"class_created" => "2002/12/17",
				"class_author" => "cs/gullevek/at"
			);
		}

		// deconstruktor
		public function __destruct()
		{
			parent::__destruct();
		}

		// METHOD convert_data
		// PARAMS string -> the string that should be changed
		// RETURN string -> the altered string
		// DESC   changes all previously alterd HTML code into visible one,
		//        works for <b>,<i>, and <a> (thought <a> can be / or should
		//        be handled with the magic links functions
		//        used with the read function
		public function convert_data($text)
		{
			$text = eregi_replace ('&lt;b&gt;', '<B>', $text);
			$text = eregi_replace ('&lt;/b&gt;', '</B>', $text);
			$text = eregi_replace ('&lt;i&gt;', '<I>', $text);
			$text = eregi_replace ('&lt;/i&gt;', '</I>', $text);
			// my need a change
			$text = eregi_replace ('&lt;a href=&quot;', '<A TARGET="_blank" HREF="', $text);
			$text = eregi_replace ('&quot;&gt;', '">', $text);
			$text = eregi_replace ('&lt;/a&gt;', '</A>', $text);
			return $text;
		}

		// METHOD convert_entities
		// PARAMS string -> string to be changed
		// RETURN string -> altered string
		// DESC   changeds all HTML entities into non HTML ones
		public function convert_entities($text)
		{
			$text = str_replace('&lt;', '<', $text);
			$text = str_replace('&gt;', '>', $text);
			$text = str_replace('&amp;', '&', $text);
			$text = str_replace('&quot;', '"', $text);
			$text = str_replace('&#039;', "'", $text); 
			return $text;
		}

		// METHOD db_dump_array
		// PARAMS none
		// RETURN returns the current array
		// DESC   dumps the current data 
		public function db_dump_array($write = 0)
		{
			reset($this->table_array);
			while(list($column, $data_array) = each($this->table_array))
			{
				$string .= "<b>".$column."</b> -> ".$data_array["value"]."<br>";
			}
			// add output to internal error_msg
			if ($write)
				$this->error_msg['db'] .= $string;
			return $string;
		}

		// METHOD _db_error
		// PARAMS none
		// RETURN none
		// DESC   writes errors to internal error string
/*     function _db_error()
		{
			// if error occured
			if ($this->error_id)
			{
				$this->error_msg['db'] .= "<b>-DB_ARRAY-error-></b> ".$this->error_id.": ".$this->error_string[$this->error_id]." <br>";
			}
		} */

		// METHOD db_check_pk_set
		// PARAMS none
		// RETURN none
		// DESC   checks if pk is set and if not, set from pk_id and if this also not set return 0
		public function db_check_pk_set()
		{
			// if pk_id is set, overrule ...
			if ($this->pk_id)
				$this->table_array[$this->pk_name]["value"] = $this->pk_id;
			// if not set ... produce error
			if (!$this->table_array[$this->pk_name]["value"])
			{
				// if no PK found, error ...
				$this->error_id = 21;
				$this->_db_error();
				return 0;
			}
			else
				return 1;
		}

		// METHOD db_reset_array
		// PARAMS reset_pk -> if set reset the pk too
		// RETURN none
		// DESC   resets the whole array
		public function db_reset_array($reset_pk = 0)
		{
			reset($this->table_array);
			while(list($column, $data_array) = each($this->table_array))
			{
				if (!$this->table_array[$column]["pk"])
					unset($this->table_array[$column]["value"]);
				else if ($reset_pk)
					unset($this->table_array[$column]["value"]);
			}
		}

		// METHOD db_delete
		// PARAMS optional the table_array, if not given uses class var
		// RETURN 1 for successfull delete or 0 for error
		// DESC   deletes one dataset
		public function db_delete($table_array = 0)
		{
			if (is_array($table_array))
				$this->table_array = $table_array;
			if (!$this->db_check_pk_set())
				return $this->table_array;
			// delete query
			$q = "DELETE FROM ".$this->table_name." WHERE ";
			$q .= $this->pk_name." = ".$this->table_array[$this->pk_name]["value"]." ";
			// delete files and build FK query
			reset($this->table_array);
			while(list($column, $data_array) = each($this->table_array))
			{
				// suchen nach bildern und löschen ...
				if ($this->table_array[$column]["file"] && file_exists($this->table_array[$column]["url"].$this->table_array[$column]["value"]))
				{
					if (file_exists($this->table_array[$column]["path"].$this->table_array[$column]["value"]))
						unlink($this->table_array[$column]["path"].$this->table_array[$column]["value"]);
					$dateiname = str_replace("_tn", "", $this->table_array[$column]["value"]);
					if (file_exists($this->table_array[$column]["path"].$dateiname))
						unlink($this->table_array[$column]["path"].$dateiname);
				}
 
				if ($this->table_array[$column]["fk"])
				{
					// zusammenstellen der FKs
					if ($q_where) 
						$q_where .= " AND ";
					$q_where .= $column." = ".$this->table_array[$column]["value"];
				}
				// allgemeines zurücksetzen des arrays
				unset($this->table_array[$column]["value"]);
			}

			// attach fk row if there ...
			if ($q_where)
				$q .= " AND ".$q_where;
			// if 0, error 
			unset ($this->pk_id);
			if (!$this->db_exec($q))
			{
				$this->error_id=22;
				$this->_db_error();
			} 
			return $this->table_array;
		}

		// METHOD db_read
		// PARAMS edit -> if 1 data will not be altered for output, optional the table_array, if not given uses class var
		// RETURN true or false for reading
		// DESC   reads one row into the array
		public function db_read($edit = 0, $table_array = 0)
		{
			// if array give, overrules internal array
			if (is_array($table_array))
				$this->table_array = $table_array;
			if (!$this->db_check_pk_set())
				return $this->table_array;
			reset($this->table_array);
			// create select part & addition FK part 
			while (list($column, $data_array)=each($this->table_array))
			{
				if ($q_select) 
					$q_select .= ", "; 
				$q_select .= $column;

				// check FK ...
				if ($this->table_array[$column]["fk"] && $this->table_array[$column]["value"])
				{
					if ($q_where)
						$q_where .= " AND ";
					$q_where .= $column .= " = ".$this->table_array[$column]["value"];
				}
			}
 
			$q = "SELECT ";
			$q .= $q_select;
			$q .= " FROM ".$this->table_name." WHERE ";
			$q .= $this->pk_name." = ".$this->table_array[$this->pk_name]["value"]." ";
			if ($q_where)
				$q .= " AND ".$q_where;

			// if query was executed okay, else set error
			if ($this->db_exec($q))
			{
				if ($res = $this->db_fetch_array())
				{  
					reset($this->table_array);
					while (list($column, $data_array) = each($this->table_array))
					{
						// wenn "edit" dann gib daten wie in DB zurück, ansonten aufbereiten für ausgabe
						// ?? sollte das nicht draußen ??? man weis ja net was da drin steht --> is noch zu überlegen
//						echo "EDIT: $edit | Spalte: $column | type: ".$this->table_array[$column]["type"]." | Res: ".$res[$column]."<br>";
						if ($edit)
						{
							$this->table_array[$column]["value"] = $res[$column];
							// if password, also write to hidden
							if ($this->table_array[$column]["type"] == "password")
							{
								$this->table_array[$column]["HIDDEN_value"] = $res[$column];
							}
						}
						else
						{
							$this->table_array[$column]["value"] = $this->convert_data(nl2br($res[$column]));
							// had to put out the htmlentities from the line above as it breaks japanese characters
						}
					}
				}
				// possible db_fetch_array errors ...
				$this->pk_id = $this->table_array[$this->pk_name]["value"];
			}
			else
			{
				$this->error_id = 22;
				$this->_db_error();
			}
			return $this->table_array;
		}

		// METHOD db_write
		// PARAMS addslashes -> if 1 will make an addslashes for each array field, optional the table_array, if not given uses class var
		// RETURN true or false on write
		// DESC   writes on set into DB or updates one set (if PK exists)
		public function db_write($addslashes = 0, $table_array = 0)
		{
			if (is_array($table_array))
				$this->table_array = $table_array;
			// PK ID check 
//       if ($this->pk_id && !$this->table_array[$this->pk_name]["value"])
//         $this->table_array[$this->pk_name]["value"]=$this->pk_id;
			// checken ob PKs gesetzt, wenn alle -> update, wenn keiner -> insert, wenn ein paar -> ERROR!
			if (!$this->table_array[$this->pk_name]["value"])
				$insert = 1;
			else
				$insert = 0;

			reset ($this->table_array);
			while (list($column, $data_array) = each($this->table_array))
			{

/********************************* START FILE *************************************/
				// file upload
				if ($this->table_array[$column]["file"])
				{
					// falls was im tmp drinnen, sprich ein upload, datei kopieren, Dateinamen in db schreiben
					// falls datei schon am server (physischer pfad), dann einfach url in db schreiben (update)
					// falls in "delete" "ja" dann loeschen (und gibts eh nur beim update)
					if ($this->table_array[$column]["delete"])
					{
						unset($this->table_array[$column]["delete"]);
						if (file_exists($this->table_array[$column]["path"].$this->table_array[$column]["value"]))
							unlink($this->table_array[$column]["path"].$this->table_array[$column]["value"]);
						$dateiname = str_replace("_tn", "", $this->table_array[$column]["value"]); 
						if (file_exists($this->table_array[$column]["path"].$dateiname))
							unlink($this->table_array[$column]["path"].$dateiname);
						$this->table_array[$column]["value"] = "";
					}
					else
					{
						if ($this->table_array[$column]["tmp"] != "none" && $this->table_array[$column]["tmp"])
						{
							// Dateiname zusammenbasteln: org-name + _pkid liste + .ext
							list($name, $ext) = explode(".",$this->table_array[$column]["dn"]);

							// mozilla, patch
							$fn_name = explode("/", $this->table_array[$column]["dn"]);
							$this->table_array[$column]["dn"] = $fn_name[count($fn_name)-1];
							$filename_parts = explode(".", $this->table_array[$column]["dn"]);
							$ext = end($filename_parts);
							array_splice($filename_parts, -1, 1);
							$name = str_replace(" ", "_", implode(".", $filename_parts));
							//echo "PK: $pk_ids_file<br>";
							$dateiname = $name.$pk_ids_file.".".$ext;
							//echo "Dn: $dateiname";
							copy($this->table_array[$column]["tmp"], $this->table_array[$column]["path"].$dateiname);
							// automatisch thumbnail generieren, geht nur mit convert (ImageMagic!!!), aber nur bei bild ..
							if (strtolower($ext) == "jpeg" || strtolower($ext) == "jpg" || strtolower($ext) == "gif" || strtolower($ext) == "png")
							{ 
								$dateiname_tn = $name.$pk_ids_file."_tn.".$ext;
								$eingang = $this->table_array[$column]["path"].$dateiname;
								$ausgang = $this->table_array[$column]["path"].$dateiname_tn;
								$com = "convert -geometry 115 $eingang $ausgang";
								exec($com);
								$this->table_array[$column]["value"] = $dateiname_tn;
							}
							else
								$this->table_array[$column]["value"] = $dateiname;
						}
						else if (file_exists($this->table_array[$column]["path"].$this->table_array[$column]["value"]))
						{
							// mach gar nix, wenn bild schon da ???
						}
					} // delete or upload
				} // file IF
/********************************* END FILE **************************************/

				// do not write 'pk' (primary key) or 'view' values
				if (!$this->table_array[$column]["pk"] && $this->table_array[$column]['type'] != 'view' && strlen($column) > 0 )
				{
					// for password use hidden value if main is not set
					if ($this->table_array[$column]["type"] == "password" && !$this->table_array[$column]["value"])
						$this->table_array[$column]["value"] = $this->table_array[$column]["HIDDEN_value"];
					if (!$insert)
					{
						if (strlen($q_data)) 
							$q_data .= ", ";
						$q_data .= $column." = ";
					}
					else
					// this is insert
					{
						if (strlen($q_data))
							$q_data .= ", ";
						if ($q_vars)
							$q_vars .= ", ";
						$q_vars .= $column;
						
					}
					// integer is different
					if ($this->table_array[$column]["int"] || $this->table_array[$column]["int_null"])
					{
$this->debug('write_check', "[$column][".$this->table_array[$column]["value"]."] Foo: ".isset($this->table_array[$column]["value"])." | ".$this->table_array[$column]["int_null"]);
						if (!$this->table_array[$column]["value"] && $this->table_array[$column]["int_null"])
							$_value = 'NULL';
						elseif (!isset($this->table_array[$column]["value"]))
							$_value = 0;
						else
							$_value = $this->table_array[$column]["value"];
						$q_data .= $_value;
					}
					else
					// normal string
					{
						$q_data .= "'";
						// if add slashes do convert & add slashes else write AS is
						if ($addslashes)
							$q_data .= $this->db_escape_string($this->convert_entities($this->table_array[$column]["value"]));
						else
							$q_data .= addslashes($this->table_array[$column]["value"]);
						$q_data .= "'";
					}
				}
			} // while ...

			// NOW get PK, and FK settings (FK only for update query)
			// get it at the end, cause now we can be more sure of no double IDs, etc
			reset($this->table_array);
			// create select part & addition FK part 
			while (list($column, $data_array) = each($this->table_array))
			{
				// check FK ...
				if ($this->table_array[$column]["fk"] && $this->table_array[$column]["value"])
				{
					if ($q_where)
						$q_where .= " AND ";
					$q_where .= $column .= " = ".$this->table_array[$column]["value"];
				}
			}

			// if no PK set, then get max ID from DB
			if (!$this->table_array[$this->pk_name]["value"])
			{
// max id, falls INSERT
				$q = "SELECT MAX(".$this->pk_name.") + 1 AS pk_id FROM ".$this->table_name;
				$res = $this->db_return_row($q);
				if (!$res["pk_id"])
					$res["pk_id"] = 1;
				$this->table_array[$this->pk_name]["value"] = $res["pk_id"];
			}

			if (!$insert)
			{
				$q = "UPDATE ".$this->table_name." SET ";
				$q .= $q_data;
				$q .= " WHERE ";
				$q .= $this->pk_name." = ".$this->table_array[$this->pk_name]["value"]." ";
				if ($q_where)
					$q .= " AND ".$q_where;
				// set pk_id ... if it has changed or so
				$this->pk_id = $this->table_array[$this->pk_name]["value"];
			}
			else
			{
				$q = "INSERT INTO ".$this->table_name." ";
				$q .= "(".$q_vars.") ";
				$q .=  "VALUES (".$q_data.")";
				// write primary key too
/*				if ($q_data)
					$q .= ", ";
				$q .= $this->pk_name." = ".$this->table_array[$this->pk_name]["value"]." ";
				$this->pk_id = $this->table_array[$this->pk_name]["value"];
*/
			}
			// return success or not
			if (!$this->db_exec($q))
			{
				$this->error_id = 22;
				$this->_db_error();
			} 
			// set primary key
			if ($insert)
				$this->ok = $this->table_array[$this->pk_name]["value"] = $this->insert_id;
			// return the table if needed
			return $this->table_array;
		}                                           
	} // end of class
?>

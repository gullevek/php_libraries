<?
	/********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2000/11/23
	* VERSION: 4.1.0
	* RELEASED LICENSE: BSD style (use it, u don't have to make YOUR source public)
	*                   but let me know if u made changes, and please don't redistribute it
	*                   with your name on it ...
	* SHORT DESCRIPTON:
	*   2013/10/10, prepare/excute were added, including auto RETURNING primary key if 
	*   possible for any INSERT query in exec or prepare/execute, better debugging and 
	*   data dumping. Proper string escape wrapper, special db exec writer for complex 
	*   array inserts in auto calls. boolean converter from postresql to php
	*
	*   2003/12/08, one major change: renamed db_exec_ext to db_return, as it has not 
	*   much in common with the normal db_exec wrapper, as it was written only for 
	*   SELECT statements and better handling of those.
	*
	*   2002/12/20, extended the "simple" functionality to what I wanted
	*   to have in first place, with db_return u execute a query and get
	*   automatically data return which is also cached, which means on next
	*   call (if not swichted of via paramter) u will no longer exec the DB
	*   again (save time, etc) but get the data from cache. I also started to
	*   test session use, but it is not yet fully tested so handle with care if
	*   you try it ... (session must be started AFTER this class is included, but you
	*   do not need to start it actually, only if u want to do some real DB calls)
	*
	*   ~2002/12/17, simple wrapper class for mysql php functions. when one query is
	*   executed (db_exec) a lot of other information os retrieved too,
	*   like num rows, etc which is needed quite often
	*   some other functions return one row or one array. all functions
	*   have build in error support for surpressing PHP errors & warnings
	*
	*   ~2000/11/23, just a function collection for db wrapper, so if you change DB,
	*   you don't have to worry about your code as long your SQL is erm ... basic ;)
	*
	*   Wrapper functions (via $class->XX(qeury))
	*
	*   PUBLIC VARIABLES
	*   $class_name
	*     - the name of the class
	*   $class_version
	*     - the version as an array (major, minor, patchlvl, daypatch)
	*   $class_last_changed
	*     - date (mysql format) for the last change
	*   $class_created
	*     - date this file was created (more or less a fun thing and memory user)
	*   $class_author
	*     - me
	*   $db_name
	*     - the name of the database connected to
	*   $db_user
	*     - the username
	*   $db_host
	*     - the hostname (where the DB is located)
	*   $db_schema
	*     - what schema to connect to, if not given "public" is used
	*   $db_encoding
	*     - automatic convert to this encoding on output, if not set, keeps default db encoding
	*   $db_port
	*     - the port to connect to
	*   $db_type
	*     - what kind of DB it is (pgsql, mysql, ...)
	*   $db_ssl
	*     - for postgresql, what kind of SSL we try (disable, allow, prefer, require), default is allow
	*   $query
	*     - sets the SQL query (will be set with the $query parameter from method)
	*       if u leave the parameter free the class will try to use this var, but this
	*       method is not so reccomended
	*   $num_rows
	*     - the number of rows returned by a SELECT or alterd bei UPDATE/INSERT
	*   $num_fields
	*     - the number of fields from the SELECT, is usefull if u do a SELECT *
	*   $field_names
	*     - array of field names (in the order of the return)
	*   $insert_id
	*     - for INSERT with auto_increment PK, the ID is stored here
	*   $error_msg
	*     - all error/debug messages, will be dumped to global $error_msg when db_close() is called
	*   $to_encoding
	*     - if this is set, then conversion will be done if needed. [no check yet on wrong encoding]
	*
	*   PRIVATE VARIABLES
	*   $db_pwd
	*     - password used for connecting [var might disappear of security reasons]
	*   $dbh
	*     - the DBH handler itself. DO NOT OVERWRITE OR CHANGE THIS VARIABLE!
	*   $db_debug
	*     - debug flag set via constructor or global $DB_DEBUG var (has to be set before class create)
	*   $cursor_ext
	*     - the extended cursor for db_return calls, stores all information (for cached use)
	*   $cursor
	*     - the normal cursor (will be filled from db_exec calles)
	*   $error_id
	*     - if an error occours this var will be filled, used by _db_error to write error information
	*   $error_string
	*     - array with descriptions to error
	*    $nbsp
	*     - used for recursive function [var might disappear if I have time to recode the recursive function]
	*
	*   PUBLIC METHODS
	*   $mixed db_return($query,$reset=0)
	*     - executes query, returns data & caches it (1 = reset/destroy, 2 = reset/cache, 3 = reset/no cache)
	*   1/0 db_cache_reset($query)
	*     - resets the cache for one query
	*   _db_io()
	*     - pseudo deconstructor - functionality moved to db_close
	*   $string info($show=1)
	*     - returns a string various info about class (version, authoer, etc), if $show set to 0, it will not be appended to the error_msgs string
	*   $string db_info($show=1)
	*     - returns a string with info about db connection, etc, if $show set to 0, it will not be appended to the error_msgs string
	*   $string db_dump_data($query=0)
	*     - returns a string with all data of that query or if no query given with all data in the cursor_ext
	*   0/$cursor db_exec($query=0)
	*     - mysql_query wrapper, writes num_rows, num_fields, etc
	*   $mixed db_fetch_array($cursor=0)
	*     - mysql_fetch_array, returns a mixed result
	*   $mixed db_return_row($query)
	*     - gibt die erste Zeile zurück (als array)
	*   $array_of_hashes db_return_array($query)
	*      - return an array of hashes with all data    
	*   db_close()
	*     - closes db connection and writes error_msg to global error_msg
	*	db_cursor_pos($query)
	*	  - returns the current position the db_return
	*	$array_of_hashes db_show_table_meta_Data($table_name)
	*	  - returns an hashed array of table column data
	*	function db_prepare($stm_name, $query)
	*     - prepares a query with the given stm name, returns false on error
	*   function db_execute($stm_name, $data = array())
	*     - execute a query that was previously prepared
	*   $string db_escape_string($string)
	*     - correctly escapes string for db insert
	*   $string db_boolean(string)
	*     - if the string value is 't' or 'f' it returns correct TRUE/FALSE for php
	*   $primary_key db_write_data($write_array, $not_write_array, $primary_key, $table, $data = array ())
	*     - writes into one table based on arrays of columns to write and not write, reads data from global vars or optional array
	*   $boolean db_set_schema(schema)
	*     - sets search path to a schema
	*   $boolean db_set_encoding(encoding)
	*     - sets an encoding for this database output and input
	*   $string db_time_format($age/datetime diff, $micro_time = false/true)
	*     - returns a nice formatted time string based on a age or datetime difference (postgres only), micro time is default false
	*
	*   PRIVATE METHODS
	*   _db_error()
	*     - INTERNAL ONLY!! error that occured during execution
	*   $string _print_array($array)
	*     - returns string of an array (only for interal use)
	*   1/0 _connect_to_db()
	*     - returns 1 for successfull DB connection or 0 for none
	*   1/0 _check_query_for_select($query)
	*     - checks if the query has select in it, and if not returns 0 (for db_return* methods)
	*   1/0 _check_query_for_insert($query)
	*     - checks if query is INSERT, UPDATE or DELETE
	*	row _db_convert_encoding($row)
	*     - converts the array from fetch_row to the correct output encoding if set
	*   string _db_debug_prepare($prepare_id, $data_array)
	*     - returns the prepared statement with the actual data. for debug purposes only
	*   none _db_debug($debug_id, $string, $id, $type)
	*     - wrapper for normal debug, adds prefix data from id & type and strips all HTML from the query data (color codes, etc) via flag to debug call
	*
	* HISTORY:
	* 2008/10/25 (cs) add db_boolean to fix the postgres to php boolean var problem (TODO: implement this in any select return)
	* 2008/07/03 (cs) add db_write_data function, original written for inventory tool "invSQLWriteData"
	* 2008/04/16 (cs) add db_escape_string function for correct string escape
	* 2007/11/14 (cs) add a prepare debug statement to replace the placeholders with the actual data in a prepared statement
	* 2007/01/17 (cs) update db_prepare & db_execute error handling
	* 2007/01/11 (cs) add prepare/execute pair (postgres only at the moment)
	* 2006/04/03 (cs) added function to return meta data for a table
	* 2005/07/19 (cs) add a function to get number for rows for a db cursor
	* 2005/07/12 (cs) add named only param to db_return_array
	* 2005/07/01 (cs) added db_cursor_pos to return the current pos in the db_return readout
	* 2005/06/20 (cs) changed the error msg output from just writing to the var, to using the debug method
	* 2005/06/17 (cs) adapted to the new error msg array format. all are to 'db' level
	* 2005/03/31 (cs) added echo/print vars to define where to print out the debug messages
	* 2004/11/15 (cs) error string is no longer echoed, but returned. methods _db_io changed
	* 2004/09/30 (cs) fixed all old layout to new layout
	* 2004/09/17 (cs) added the function to automatically convert the encoding to the correct output encoding
	* 2004/08/06 (cs) two debug parameters, debug and db_debug
	* 2004/07/15 (cs) changed the deconstructor to call _basic deconstructor
	*   2003-06-20: added a '3' flag to db_return so NO caching is done at all (if array might get too big)
	*   2003-06-19: made the error messages in DEBUG output red so they are better to see
	*   2003-06-09: never started class_basic, insert this, for mobile phone detection 
	*   2003-04-10: moved the error handling out of the db_pgsql.inc back to db_io class
	*   2003-04-09: major change as db_io does not hold any DB specific calls anymore,
	*               those are loaded dynamically during class start, from a include
	*               (db_dbname ...)
	*   2003-03-24: removed/moved some basic vars away from this class to basic class and
	*               moved init of these vars to constructor
	*   2003-02-26: adapted the error_msg var to 1x where 1 is for db_io error
	*               updated _db_error, moved mysql error printing into this function
	*               changed the "shape" of class info vars to fit into extend modell
	*   2003-02-13: in db_exec the setting for the last insert id was still via the function,
	*               changed this to call the internal PHP mysql command.
	*   2003-01-28: ugly bug within creating the field_names. The array was not reseted 
	*               before, and so the field for the db_exec where not correct.
	*   2003-01-16: fixed a "select" check in db_exec,
	*               added a privet method for checking query of INSERT, UPDATE, DELETE
	*   2003-01-09: code cleanups and more inline documentation
	*   2003-01-08: renamed db_exec_ext to db_return for obious reasons
	*               added a "check for select query" for all db_return* methods
	*   2003-01-08: db_return gets another functionality: if u use 1 or 2 as reset value, 
	*               the cursor will be reset BEFORE the read and no chaced data will be read.
	*               if you use 2, the md5 array will be kept so next read with no flag is cached,
	*               wheres with 1, the data gets DESTROYED at the end of the read
	*               (this makes the db_cache_reset function a bit obsolete)
	*               furthermore, the class trys to reconnect (db_exec & db_return) to the DB
	*               if no dbh was found (from session eg)
	*   2003-01-07: fixed a small bug in return_array as he mixed up the order if you used
	*               SELECT * FROM ...
	*   2002-12-26: changed strstr to stristr 'couse not everyone types SELECT, etc in capitals
	*   2002-12-24: moved the debug output in db_return to the call if, 
	*               so it is only printed once
	*   2002-12-20: added db_dump_data function for printing out all data in 
	*               cursor_ext (or from one query in it)
	*   2002-12-20: testing and implemtenting of session storing the class (not fully tested!)
	*               documenting all the functions and some code cleenup
	*   2002-12-19: implemented db_return which executes, returns & caches the query
	*   2002-12-18: started idea of putting db_exec and db_fetch_array together
	*   2002-12-17: splitted this file. basic db functions kept here, where the
	*               more complex (array based IO fkts) moved into a seperate file
	*   2002-12-16: further reconstruction ...
	*   2002-12-10: further improvment in changing db_mysql to a class
	*   2002-10-18: renamed lesen to db_read, speichern to db_save and 
	*               loeschen to db_delete
	*   19.08.2002: 1 convertiert &lt; &gt; &quot; &amp; &#309; in original
	*               HTML zeichen zurück (für htmlspecialcharsfct)
	*   09.08.2002: speichern() hat einen dritten parameter für
	*               addslashes (1=ja,0=nein/default)
	*   04.04.2002: FK added to lesen()
	*   10.07.2001: simple return row function geschrieben
	*   03.07.2001: kein Thumbnail erzeugen wenn Datei nicht: 
	*               JPG/JPEG/GIF/PNG als Endung hat
	*   22.06.2001: Mozilla Fix für File upload
	*   10.05.2001: alle fkt haben "db_" als pre zur identifizierung
	*   10.05.2001: kleines problem mit call zu "convert_data" fkt
	*   26.04.2001: umschreiben auf classen und einbiden db_io's
	*   24.11.2000: erweitern um num_rows
	*   23.11.2000: erster Test
	*********************************************************************/

	// try to include file from LIBS path, or from normal path
	_spl_autoload('Class.Basic.inc');

	class db_io extends basic
	{
		// recommend to set private/protected and only allow setting via method
		// can bet set from outside
		// encoding to
		public $to_encoding = '';
		public $query; // the query string at the moment
		// only inside
		// basic vars
		private $dbh; // the dbh handler
		private $db_debug; // DB_DEBUG ... (if set prints out debug msgs)
		private $db_name; // the DB connected to
		private $db_user; // the username used
		private $db_pwd; // the password used
		private $db_host; // the hostname
		private $db_port; // default db port
		private $db_schema; // optional DB schema, if not set uses public
		private $db_encoding; // optional auto encoding convert, not used if not set
		private $db_type; // type of db (mysql,postgres,...)
		private $db_ssl; // ssl flag (for postgres only), disable, allow, prefer, require
		// FOR BELOW: (This should be private and only readable through some method)
		// cursor array for cached readings
		public $cursor_ext; // hash of hashes
		// per query vars 
		public $cursor; // actual cursor (DBH)
		public $num_rows; // how many rows have been found
		public $num_fields; // how many fields has the query
		public $field_names; // array with the field names of the current query
		public $insert_id; // last inserted ID 
		// other vars
		private $nbsp = ''; // used by print_array recursion function
		// error & warning id
		private $error_id;
		private $warning_id;
		// sub include with the database functions
		private $db_functions;

		// endless loop protection
		private $MAX_QUERY_CALL;
		private $query_called = array ();
		// error string
		private $error_string = array ();
		// prepared list
		public $prepare_cursor = array ();
		// primary key per table list
		// format is 'table' => 'pk_name'
		public $pk_name_table = array ();
		// internal primary key name, for cross calls in async
		public $pk_name;
		// if we use RETURNING in the INSERT call
		private $returning_id = false;
		// if a sync is running holds the md5 key of the query
		private $async_running;

		// METHOD __construct
		// PARAMS db_config -> array with db, user, password & host
		//        debug -> turns debugging output on or of (default 0),
		//        debugging can also be triggerd via DB_DEBUG var on global level
		// RETURN nothing
		// DESC   constructor for db_clss
		public function __construct($db_config, $debug = 0, $db_debug = 0, $echo = 1, $print = 0)
		{
			// start basic class
			parent::__construct($debug, $echo, $print);
			// sets the names (for connect/reconnect)
			$this->db_name = $db_config['db_name'];
			$this->db_user = $db_config['db_user'];
			$this->db_pwd = $db_config['db_pass'];
			$this->db_host = $db_config['db_host'];
			$this->db_port = array_key_exists('db_port', $db_config) ? $db_config['db_port'] : '5432';
			$this->db_schema = array_key_exists('db_schema', $db_config) ? $db_config['db_schema'] : ''; // do not set to 'public' if not set, because the default is already public
			$this->db_encoding = array_key_exists('db_encoding', $db_config) ? $db_config['db_encoding'] : '';
			$this->db_type = 'db_'.$db_config['db_type'];
			$this->db_ssl = array_key_exists('db_ssl', $db_config) ? $db_config['db_ssl'] : 'allow';

			// set the target encoding to the DEFAULT_ENCODING if it is one of them: EUC, Shift_JIS, UTF-8
			// @ the moment set only from outside

			// set loop protection max count
			$this->MAX_QUERY_CALL = 20;
		
			// error & debug stuff, error & warning ids are the same, its just in which var they get written
			$this->error_string['11'] = 'No Querystring given';
			$this->error_string['12'] = 'No Cursor given, no correct query perhaps?';
			$this->error_string['13'] = 'Query could not be executed without errors';
			$this->error_string['14'] = 'Can\'t connect to DB server';
			$this->error_string['15'] = 'Can\'t select DB';
			$this->error_string['16'] = 'No DB Handler found / connect or reconnect failed';
			$this->error_string['17'] = 'all db_return* methods work only with SELECT statements, please use db_exec for everything else';
			$this->error_string['18'] = 'Query not found in cache. Nothing has been reset';
			$this->error_string['19'] = 'Wrong PK name given or no PK name given at all, can\'t get Insert ID';
			$this->error_string['20'] = 'Found given Prepare Statement Name in array, Query not prepared, will use existing one';
			$this->error_string['21'] = 'Query Prepare failed';
			$this->error_string['22'] = 'Query Execute failed';
			$this->error_string['23'] = 'Query Execute failed, data array does not match placeholders';
			$this->error_string['24'] = 'Missing prepared query entry for execute.';
			$this->error_string['25'] = 'Prepare query data is not in array format.';
			$this->error_string['30'] = 'Query call in a possible endless loop. Was called more than '.$this->MAX_QUERY_CALL.' times';
			$this->error_string['31'] = 'Could not fetch PK after query insert';
			$this->error_string['40'] = 'Query async call failed.';
			$this->error_string['41'] = 'Connection is busy with a different query. Cannot execute.';
			$this->error_string['42'] = 'Cannot check for async query, none has been started yet.';

			// set debug, either via global var, or debug var during call
			$this->db_debug = $db_debug;
			// global overrules local
			if (isset($GLOBALS['DB_DEBUG']))
				$this->db_debug = $GLOBALS['DB_DEBUG'];

			// includes sub class for db type
			_spl_autoload($this->db_type.'.inc');
			$this->db_functions = new $this->db_type();

			// connect to DB
			if (!$this->_connect_to_db())
			{
				$this->error_id = 16;
				$this->_db_error();
				return false;
			}
			
			$this->class_info['db_io']=array(
				'class_name' => 'DB IO',
				'class_version' => '4.1.0',
				'class_created' => '2000-11-23',
				'class_author' => 'Clemens Schwaighofer'
			);
		}

		// METHOD: __destruct
		// PARAMS: none
		// RETURN: none
		// DESC:   final desctruct method, closes the DB connection
		public function __destruct()
		{
			$this->_close_db();
			parent::__destruct();
		}

		// *************************************************************
		// PRIVATE METHODS
		// *************************************************************

		// METHOD: _connect_to_db
		// PARAMS: none
		// RETURN: true on successfull connect, false if failed
		// DESC:
		// internal connection function. Used to connect to the DB if there is no connection done yet.
		// Called before any execute 
		private function _connect_to_db()
		{
			// generate connect string 
			$this->dbh = $this->db_functions->_db_connect($this->db_host, $this->db_user, $this->db_pwd, $this->db_name, $this->db_port, $this->db_ssl);
			// if no dbh here, we couldn't connect to the DB itself
			if (!$this->dbh)
			{
				$this->error_id = 14;
				$this->_db_error();
				return false;
			}
			// 15 error (cant select to DB is not valid in postgres, as connect is different)
			// if returns 0 we couldn't select the DB
			if ($this->dbh == -1)
			{
				$this->error_id = 15;
				$this->_db_error();
				return false;
			} 
			// set search path if needed
			if ($this->db_schema)
			{
				$this->db_set_schema();
			}
			// set client encoding
			if ($this->db_encoding)
			{
				$this->db_set_encoding();
			}
			// all okay
			return true;
		}

		// METHOD: _close_db
		// PARAMS: none
		// RETURN: none
		// DESC  : close db connection
		//         only used by the deconstructor
		private function _close_db()
		{
			if ($this->dbh)
			{
				$this->db_functions->_db_close();
				unset($this->dbh);
			}
		}

		// METHOS: _check_query_for_select
		// PARAMS: query
		// RETURN: true if matching, false if not
		// DESC  : checks if query is a SELECT, if not error, 0 return
		//       : NOTE: Query needs to start with SELECT. if starts with "with" it is ignored
		private function _check_query_for_select($query)
		{
			// perhaps allow spaces before select ?!?
			if (!preg_match("/^select /i", $query))
			{
				return false;
			}
			return true;
		}

		// METHOD: _check_query_for_insert
		// PARAMS: query, pure flag (boolean)
		// RETURN: true if matching, flase if not
		// DESC  : check for DELETE, INSERT, UPDATE
		//       : if pure is set to true, only when INSERT is set will return true
		//       : NOTE: Queries need to start with INSERT, UPDATE, DELETE. Anything else is ignored
		private function _check_query_for_insert($query, $pure = FALSE)
		{
			if (!preg_match("/^insert /i", $query) && !preg_match("/^update /i", $query) && !preg_match("/^delete /i", $query))
			{
				return false;
			}
			if (!$pure)
				return true;
			elseif (preg_match("/^insert /i", $query))
				return true;
			else
				return false;
		}

		// METHOD: _print_array
		// PARAMS: array to print
		// RETURN: string with printed and formated array
		// DESC  : internal funktion that creates the array
		//       : used in db_dump_data only
		private function _print_array($array)
		{
			while (list($key, $value) = each($array))
			{
				$string .= $this->nbsp.'<b>'.$key.'</b> => ';
				if (is_array($value))
				{
					$this->nbsp .= '&nbsp;&nbsp;&nbsp;';
					$string .= '<br>';
					$string .= $this->_print_array($value);
				} else
				{
					$string .= $value.'<br>';
				}
			}
			$this->nbsp = substr_replace($this->nbsp, '', -18, 18);
			return $string;
		}

		// METHOD _db_debug
		// PARAMS debug_id -> group id for debug
		//        error_string -> error message or debug data
		//        id -> db debug group
		//        type -> query identifiery (Q, I, etc)
		// RETURN none
		// DESC   calls the basic class debug with strip command
		private function _db_debug($debug_id, $error_string, $id = '', $type = '')
		{
			$prefix = '';
			if ($id)
				$prefix .= '[<span style="color: #920069;">'.$id.'</span>] ';
			if ($type)
				$prefix .= '{<span style="font-style: italic; color: #3f0092;">'.$type.'</span>} '; 
			if ($prefix)
				$prefix .= '- ';
			$this->debug($debug_id, $prefix.$error_string, true);
		}

		// METHOD _db_error
		// PARAMS cursor -> current cursor for pg_result_error, mysql uses dbh, pg_last_error too, 
		//                  but pg_result_error is more accurate
		//        msg -> optional message
		// RETURN none
		// DESC   if error_id set, writes long error string into error_msg
		// MARK: needed to make public so it can be called from DB.Array.IO too
		public function _db_error($cursor = '', $msg = '')
		{
			$where_called = $this->get_caller_method();
			if ($cursor)
				$pg_error_string = $this->db_functions->_db_print_error($cursor);
			if (!$cursor)
				$pg_error_string = $this->db_functions->_db_print_error();
			if ($pg_error_string)
				$this->_db_debug('db', $pg_error_string, 'DB_ERROR', $where_called);
			// okay, an error occured
			if ($this->error_id)
			{
				// write error msg ...
				$this->_db_debug('db', '<span style="color: red;"><b>DB-Error</b> '.$this->error_id.': '.$this->error_string[$this->error_id].($msg ? ', '.$msg : '').'</span>', 'DB_ERROR', $where_called);
				$this->had_error = $this->error_id;
				// write detailed error log
			}
			if ($this->warning_id)
			{
				$this->_db_debug('db', '<span style="color: orange;"><b>DB-Warning</b> '.$this->warning_id.': '.$this->error_string[$this->warning_id].($msg ? ', '.$msg : '').'</span>', 'DB_ERROR', $where_called);
				$this->had_warning = $this->warning_id;
			}
			// unset the error/warning vars
			$this->error_id = 0;
			$this->warning_id = 0;
		}

		// METHOD _db_convert_encoding
		// PARAMS array from fetch_row
		// RETURN convert fetch_row array
		// DESC   if there is the 'to_encoding' var set, and the field is in the wrong encoding converts it to the target
		private function _db_convert_encoding($row)
		{
			if ($this->to_encoding && $this->db_encoding)
			{
				// go through each row and convert the encoding if needed
				for ($i = 0; $i < $this->num_fields; $i ++)
				{
					$from_encoding = mb_detect_encoding($row[$i]);
					// convert only if encoding doesn't match and source is not pure ASCII
					if ($from_encoding != $this->to_encoding && $from_encoding != 'ASCII')
					{
						$row[$i] = mb_convert_encoding($row[$i], $this->to_encoding, $from_encoding);
					}
				}
			}
			return $row;
		}

		// METHOD _db_debug_prepare
		// PARAMS $stm_name, data array
		// RETURN query in prepared form
		// DESC   for debug purpose replaces $1, $2, etc with actual data
		private function _db_debug_prepare($stm_name, $data = array())
		{
			// get the keys from data array
			$keys = array_keys($data);
			// because the placeholders start with $ and at 1, we need to increase each key and prefix it with a $ char
			for ($i = 0; $i < count($keys); $i ++)
			{
				$keys[$i] = '$'.($keys[$i] + 1);
			}
			// simply replace the $1, $2, ... with the actual data and return it
			return str_replace(array_reverse($keys), array_reverse($data), $this->prepare_cursor[$stm_name]['query']);
		}

		// METHOD _db_return_table
		// PARAMS insert/select/update/delete query
		// RETURN array with schema and table
		// DESC   extracts schema and table from the query, if no schema returns just empty string 
		private function _db_return_table($query)
		{
			if (preg_match("/^SELECT /i", $query))
				preg_match("/ (FROM) (([\w_]+)\.)?([\w_]+) /i", $query, $matches);
			else
				preg_match("/(INSERT INTO|DELETE FROM|UPDATE) (([\w_]+)\.)?([\w_]+) /i", $query, $matches);
			return array($matches[3], $matches[4]);
		}

		// METHOD _db_prepare_exec
		// PARAMS query, primary key [if set to NULL no returning will be added]
		// RETURN md5 OR boolean false on error
		// DESC   sub function for db_exec and db_exec_async
		//        * checks query is set
		//        * checks there is a database handler
		//        * checks that here is no other query executing
		//        * checks for insert if returning is set/pk name
		//        * sets internal md5 for query
		//        * checks multiple call count
		private function _db_prepare_exec($query, $pk_name)
		{
			// to either use the returning method or the guess method for getting primary keys
			$this->returning_id = false;
			// set the query
			if ($query)
				$this->query = $query;
			if (!$this->query)
			{
				$this->error_id = 11;
				$this->_db_error();
				return false;
			}
			// if no DB Handler drop out
			if (!$this->dbh)
			{
				// if reconnect fails drop out
				if (!$this->_connect_to_db())
				{
					$this->error_id = 16;
					$this->_db_error();
					return false;
				}
			}
			// check that no other query is running right now
			if ($this->db_functions->_db_connection_busy())
			{
				$this->error_id = 41;
				$this->_db_error();
				return false;
			}
			// if we do have an insert, check if there is no RETURNING pk_id, add it if I can get the PK id
			if ($this->_check_query_for_insert($this->query, true))
			{
				$this->pk_name = $pk_name;
				if ($pk_name != 'NULL')
				{
					if (!$pk_name)
					{
						// TODO: get primary key from table name
						list($schema, $table) = $this->_db_return_table($this->query);
						if (!array_key_exists($table, $this->pk_name_table) || !$this->pk_name_table[$table])
						{
							$this->pk_name_table[$table] = $this->db_functions->_db_primary_key($table, $schema);
						}
						$pk_name = $this->pk_name_table[$table];
					}
					if (!preg_match("/ returning /i", $this->query) && $this->pk_name)
					{
						$this->query .= " RETURNING ".$this->pk_name;
						$this->returning_id = true;
					}
					elseif (preg_match("/ returning (.*)/i", $this->query, $matches) && $this->pk_name)
					{
						if (!preg_match("/$this->pk_name/", $matches[1]))
						{
							$this->query .= " , ".$this->pk_name;
							$this->returning_id = true;
						}
					}
				}
			}
			// for DEBUG, only on first time ;)
			if ($this->db_debug)
				$this->_db_debug('db', $this->query, '_db_prepare_exec', 'Q');
			// import protection, md5 needed
			$md5 = md5($this->query);
			// if the array index does not exists set it 0
			if (!array_key_exists($md5, $this->query_called))
				$this->query_called[$md5] = 0;
			// if the array index exists, but it is not a numeric one, set it to 0
			if (!is_numeric($this->query_called[$md5]))
				$this->query_called[$md5] = 0;
			// count up the run, if this is run more than the max_run then exit with error
			if ($this->query_called[$md5] > $this->MAX_QUERY_CALL)
			{
					$this->error_id = 30;
					$this->_db_error();
					$this->_db_debug('db', $this->query, 'db_exec', 'Q[nc]');
					return false;
			}
			$this->query_called[$md5] ++;
			// return md5
			return $md5;
		}

		// METHOD _db_post_exec
		// PARAMS none
		// RETURN true on success or false if an error occured
		// DESC   runs post execute for rows affected, field names, inserted primary key, etc
		private function _db_post_exec()
		{
			// if FALSE returned, set error stuff
			// if either the cursor is false
			if (!$this->cursor || $this->db_functions->_db_last_error_query())
			{
				// printout Query if debug is turned on
				if (!$this->db_debug)
					$this->_db_debug('db', $this->query, 'db_exec', 'Q[nc]');
				// internal error handling
				$this->error_id = 13;
				$this->_db_error($this->cursor);
				return false;
			}
			else
			{
				// if SELECT do here ...
				if ($this->_check_query_for_select($this->query))
				{
					// count the rows returned (if select)
					$this->num_rows = $this->db_functions->_db_num_rows($this->cursor);
					// count the fields
					$this->num_fields = $this->db_functions->_db_num_fields($this->cursor);
					// set field names
					unset($this->field_names);
					for ($i = 0; $i < $this->num_fields; $i ++)
					{
						$this->field_names[] = $this->db_functions->_db_field_name($this->cursor, $i);
					}
				}
				else if ($this->_check_query_for_insert($this->query))
				{
					// if not select do here
					// count affected rows
					$this->num_rows = $this->db_functions->_db_affected_rows($this->cursor);
					if ($this->_check_query_for_insert($this->query, true) && $this->pk_name != 'NULL')
					{
						// set insert_id
						if (!$this->returning_id)
							$this->insert_id = $this->db_functions->_db_insert_id($this->query, $this->pk_name);
						else
							$this->insert_id = $this->db_functions->_db_fetch_array($this->cursor)[$this->pk_name];
						// this error handling is only for pgsql
						if (is_array($this->insert_id))
						{
							$this->warning_id = 19;
							$this->_db_error($this->insert_id[1], '[db_exec]');
							unset($this->insert_id);
						}
					}
				}
				return true;
			}
		}

		// *************************************************************
		// PUBLIC METHODS
		// *************************************************************

		// METHOD db_reset_query_called
		// PARAMS query
		// RETURN none
		// DESC   resets the call times for the max query called to 0
		//        USE CAREFULLY: rather make the query prepare -> execute
		public function db_reset_query_called($query)
		{
			$this->query_called[md5($query)] = 0;
		}

		// METHOD db_get_query_called
		// PARAMS query
		// RETURN count of query called
		// DESC   gets how often a query was called already
		public function db_get_query_called($query)
		{
			$md5 = md5($query);
			if ($this->query_called[$md5])
				return $this->query_called[$md5];
			else
				return 0;
		}

		// METHOD db_close
		// PARAMS none
		// RETURN none
		// DESC   closes the db_connection
		//        normally this is not used, as the class deconstructor closes the connection down
		public function db_close()
		{
			if ($this->dbh)
			{
				$this->db_functions->_db_close();
				unset($this->dbh);
			}
		}

		// METHOD db_set_schema
		// PARAMS db_schema: if not given tries internal default db schema
		// RETURN false on failure to find schema values, true of db exec schema set
		// DESC   sets new db schema
		public function db_set_schema($db_schema = '')
		{
			if (!$db_schema && $this->db_schema)
				$db_schema = $this->db_schema;
			if (!$db_schema)
				return false;
			$q = "SET search_path TO '".$this->db_escape_string($db_schema)."'";
			return $this->db_exec($q);
		}

		// METHOD db_get_schema
		// PARAMS none
		// RETURN db_schema current set
		// DESC   returns the current set db schema
		public function db_get_schema()
		{
			return $this->db_schema;
		}

		// METHOD db_set_encoding
		// PARAMS valid encoding name, so the the data gets converted to this encoding
		// RETURN false, or true of db exec encoding set
		// DESC   sets the client encoding in the postgres database
		public function db_set_encoding($db_encoding = '')
		{
			if (!$db_encoding && $this->db_encoding)
				$db_encoding = $this->db_encoding;
			if (!$db_encoding)
				return false;
			$q = "SET client_encoding TO '".$this->db_escape_string($db_encoding)."'";
			return $this->db_exec($q);
		}

		// METHOD db_info
		// PARAMS show, default 1, if set to 0 won't write to error_msg var
		// RETURN string with db_connection info
		// DESC   prints out status info from the connected DB (might be usefull for debug stuff)
		public function db_info($show = 1)
		{
			$string = '';
			$string .= '<b>-DB-info-></b> Connected to db <b>\''.$this->db_name.'\'</b> with schema <b>\''.$this->db_schema.'\'</b> as user <b>\''.$this->db_user.'\'</b> at host <b>\''.$this->db_host.'\'</b> on port <b>\''.$this->db_port.'\'</b> with ssl mode <b>\''.$this->db_ssl.'\'</b><br>';
			$string .= '<b>-DB-info-></b> DB IO Class debug output: <b>'.(($this->db_debug) ? 'Yes' : 'No').'</b>';
			if ($show)
				$this->_db_debug('db', '<br>'.$string, 'db_info');
			else
				$string = '<br>'.$string;
			return $string;
		}

		// METHOD db_dump_data
		// PARAMS query -> if given, only from this quey (if found)
		// RETURN formated string with all the data in the array 
		// DESC   dumps ALL data for this query, OR if no query given all in cursor_ext array
		public function db_dump_data($query = 0)
		{
			// set start array
			if ($query)
				$array = $this->cursor_ext[md5($query)];
			else
				$array = $this->cursor_ext;
			if (is_array($array))
			{
				$this->nbps = '';
				$string .= $this->_print_array($array);
				$this->_db_debug('db', $string, 'db_dump_data');
			}
			return $string;
		}

		// METHOD db_return
		// PARAMS query -> the query ...
		//        reset -> if set to 1, at the end of the query (last row returned), the stored array will be deleted ...
		//                 if set to 2, the data will be read new and cached (wheres 1 reads new AND destroys at end of read)
		//              -> if set to 3, after EACH row, the data will be reset, no caching is done except for basic (count, etc)
		// RETURN res mixed (array/hash)
		// DESC   single running function, if called creates md5 from 
		//        query string and so can itself call exec/return calls
		//        caches data, so next time called with IDENTICAL (!!!!)
		//        [this means 1:1 bit to bit identical query] returns cached
		//        data, or with reset flag set calls data from DB again 
		public function db_return($query, $reset = 0)
		{
			if (!$query)
			{
				$this->error_id = 11;
				$this->_db_error();
				return false;
			}
			// create MD5 from query ...
			$md5 = md5($query);
			// pre declare array
			if (!isset($this->cursor_ext[$md5]))
			{
				$this->cursor_ext[$md5] = array (
					'query' => '',
					'pos' => 0,
					'cursor' => 0,
					'firstcall' => 0,
					'num_rows' => 0,
					'num_fields' => 0,
					'read_rows' => 0
				);
			}
			// set the query
			$this->cursor_ext[$md5]['query'] = $query;
			// before doing ANYTHING check if query is "SELECT ..." everything else does not work
			if (!$this->_check_query_for_select($this->cursor_ext[$md5]['query']))
			{
				$this->error_id = 17;
				$this->_db_error('', $this->cursor_ext[$md5]['query']);
				return false;
			}
 
			// if it is a call with reset in it we reset the cursor, so we get an uncached return
			// but only for the FIRST call (pos == 0)
			if ($reset && !$this->cursor_ext[$md5]['pos'])
			{
				unset($this->cursor_ext[$md5]['cursor']);
			}
//			$this->debug('MENU', 'Reset: '.$reset.', Cursor: '.$this->cursor_ext[$md5]['cursor'].', Pos: '.$this->cursor_ext[$md5]['pos'].', Query: '.$query);

			// if no cursor yet, execute
			if (!$this->cursor_ext[$md5]['cursor'])
			{
				// for DEBUG, print out each query executed
				if ($this->db_debug)
					$this->_db_debug('db', $this->cursor_ext[$md5]['query'], 'db_return', 'Q');
				// if no DB Handler try to reconnect
				if (!$this->dbh)
				{
					// if reconnect fails drop out
					if (!$this->_connect_to_db())
					{
						$this->error_id = 16;
						$this->_db_error();
						return false;
					}
				}
				// check that no other query is running right now
				if ($this->db_functions->_db_connection_busy())
				{
					$this->error_id = 41;
					$this->_db_error();
					return false;
				}
				$this->cursor_ext[$md5]['cursor'] = $this->db_functions->_db_query($this->cursor_ext[$md5]['query']);
				// if still no cursor ...
				if (!$this->cursor_ext[$md5]['cursor'])
				{
					if (!$this->db_debug)
						$this->_db_debug('db', $this->cursor_ext[$md5]['query'], 'db_return', 'Q');
					// internal error handling
					$this->error_id = 13;
					$this->_db_error($this->cursor_ext[$md5]['cursor']);
					return false;
				}
				else
				{
					$this->cursor_ext[$md5]['firstcall'] = 1;
				}
			} // only go if NO cursor exists
			
			// if cursor exists ...
			if ($this->cursor_ext[$md5]['cursor']) 
			{
				if ($this->cursor_ext[$md5]['firstcall'] == 1)
				{
					// count the rows returned (if select)
					$this->cursor_ext[$md5]['num_rows'] = $this->db_functions->_db_num_rows($this->cursor_ext[$md5]['cursor']);
					// count the fields
					$this->cursor_ext[$md5]['num_fields'] = $this->db_functions->_db_num_fields($this->cursor_ext[$md5]['cursor']);
					// set field names
					for ($i = 0; $i < $this->cursor_ext[$md5]['num_fields']; $i ++)
					{
						$this->cursor_ext[$md5]['field_names'][] = $this->db_functions->_db_field_name($this->cursor_ext[$md5]['cursor'], $i);
					}
					// reset first call vars
					$this->cursor_ext[$md5]['firstcall'] = 0;
					// reset the internal pos counter
					$this->cursor_ext[$md5]['pos'] = 0;
					// reset the global (for cache) read counter
					$this->cursor_ext[$md5]['read_rows'] = 0;
				}
				// read data for further work ... but only if necessarry
				if ($this->cursor_ext[$md5]['read_rows'] == $this->cursor_ext[$md5]['num_rows']) 
					$return = 0;
				else
					$return = $this->_db_convert_encoding($this->db_functions->_db_fetch_array($this->cursor_ext[$md5]['cursor']));
				// check if cached call or reset call ...
				if (!$return && !$reset)
				{
					// check if end of output ...
					if ($this->cursor_ext[$md5]['pos'] >= $this->cursor_ext[$md5]['num_rows'])
					{
						$this->cursor_ext[$md5]['pos'] = 0;
						# if not reset given, set the cursor to true, so in a cached call on a different page we don't get problems from DB connection (as those will be LOST)
						$this->cursor_ext[$md5]['cursor'] = 1;
						$return = 0;
					}
					else 
					{
						// unset return value ...
						unset($return);
						for ($i = 0; $i < $this->cursor_ext[$md5]['num_fields']; $i ++)
						{
							// create mixed return array
							$field_value = $this->cursor_ext[$md5][$this->cursor_ext[$md5]['pos']][$this->cursor_ext[$md5]['field_names'][$i]];
							$return[$i] = $field_value;
							$return[$this->cursor_ext[$md5]['field_names'][$i]] = $field_value;
						} 
						$this->cursor_ext[$md5]['pos'] ++;
					}
				}
				else
				{
					// return row, if last && reset, then unset the hole md5 array
					if (!$return && ($reset == 1 || $reset == 3) && $this->cursor_ext[$md5]['pos'])
					{
						// unset only the field names here of course
						unset($this->cursor_ext[$md5]['field_names']);
						$this->cursor_ext[$md5]['pos'] = 0;
					}
					else if (!$return && $reset == 2 && $this->cursor_ext[$md5]['pos'])
					{
						// at end of read reset pos & set cursor to 1 (so it does not get lost in session transfer)
						$this->cursor_ext[$md5]['pos'] = 0;
						$this->cursor_ext[$md5]['cursor'] = 1;
						$return = 0;
					}
					// if something found, write data into hash array
					if ($return)
					{
						// internal position counter
						$this->cursor_ext[$md5]['pos'] ++;
						$this->cursor_ext[$md5]['read_rows'] ++;
						// if reset is <3 caching is done, else no
						if ($reset < 3)
						{
							while (list($field_name, $data) = each($return))
							{
								$temp[$field_name] = $data; 
							}
							$this->cursor_ext[$md5][] = $temp;
						}
					} // cached data if
				} // cached or not if
			} // cursor exists
			return $return;
		}

		// METHOD db_cache_reset
		// PARAMS $query -> The Query whose cache should be cleaned
		// RETURN 0 if failure (eg no query with this md5 found)
		//        1 if successfull
		// DESC   resets all data stored to this query
		public function db_cache_reset($query)
		{
			$md5 = md5($query);
			// clears cache for this query
			if (!$this->cursor_ext[$md5]['query'])
			{
				$this->error_id = 18;
				$this->_db_error();
				return false;
			}
			unset($this->cursor_ext[$md5]);
			return true;
		}

		// METHOD db_exec
		// PARAMS query -> the query, if not given, the query class var will be used 
		//                 (if this was not set, method will quit with a 0 (failure)
		//        pk_name -> optional primary key name, for insert id return if the pk name is very different
		//                   if pk name is table name and _id, pk_name is not needed to be set
		//                   if NULL is given here, no RETURNING will be auto added
		// RETURN cursor for this query
		// DESC   executes the query and returns & sets the internal cursor
		//        fruthermore this functions also sets varios other vars 
		//        like num_rows, num_fields, etc depending on query
		//        for INSERT INTO queries it is highly recommended to set the pk_name to avoid an additional 
		//        read from the database for the PK NAME
		public function db_exec($query = 0, $pk_name = '')
		{
			// prepare and check if we can actually run it
			if (($md5 = $this->_db_prepare_exec($query, $pk_name)) === false)
				// bail if no md5 set
				return false;
			// ** actual db exec call
			$this->cursor = $this->db_functions->_db_query($this->query);
			// if FALSE returned, set error stuff
			// run the post exec processing
			if (!$this->_db_post_exec())
				return false;
			else
				return $this->cursor;
		}
	
		// METHOD db_exec_async
		// PARAMS query -> query to run
		//        pk_name -> optional primary key name, only used with insert for returning call
		// RETURN true if async query was sent ok, false if error happened
		// DESC   executres the query async so other methods can be run during this
		//        for INSERT INTO queries it is highly recommended to set the pk_name to avoid an additional 
		//        read from the database for the PK NAME
		// NEEDS  db_check_async
		public function db_exec_async($query, $pk_name = '')
		{
			// prepare and check if we can actually run the query
			if (($md5 = $this->_db_prepare_exec($query, $pk_name)) === false)
				// bail if no md5 set
				return false;
			// run the async query
			if (!$this->db_functions->_db_send_query($this->query))
			{
				// if failed, process here
				$this->error_id = 40;
				$this->_db_error();
				return false;
			}
			else
			{
				$this->async_running = $md5;
				// all ok, we return true (as would be from the original send query function)
				return true;
			}
		}

		// METHOD db_check_async
		// PARAMS none
		// RETURN true if the query is still running, false if an error occured or cursor of that query
		// DESC   checks a previous async query and returns data if finished
		// NEEDS  db_exec_async
		public function db_check_async()
		{
			// if there is actually a async query there
			if ($this->async_running)
			{
				if ($this->db_functions->_db_connection_busy())
				{
					return true;
				}
				else
				{
					// get the result/or error
					$this->cursor = $this->db_functions->_db_get_result();
					$this->async_running = '';
					// run the post exec processing
					if (!$this->_db_post_exec())
						return false;
					else
						return $this->cursor;
				}
			}
			else
			{
				// if no async running print error
				$this->error_id = 42;
				$this->_db_debug('db', '<span style="color: red;"><b>DB-Error</b> No async query has been started yet.</span>', 'DB_ERROR');
				return false;
			}
		}

		// METHOD db_fetch_array
		// PARAMS cusors -> the cursor from db_exec or pg_query/pg_exec/mysql_query
		//                  if not set will use internal cursor, if not found, stops with 0 (error)
		// RETURN a mixed row
		// DESC   executes a cursor and returns the data, if no more data 0 will be returned
		public function db_fetch_array($cursor = 0)
		{
			// return false if no query or cursor set ...
			if (!$cursor)
				$cursor = $this->cursor;
			if (!$cursor)
			{
				$this->error_id = 12;
				$this->_db_error();
				return false;
			}
			return $this->_db_convert_encoding($this->db_functions->_db_fetch_array($cursor));
		}
	
		// METHOD db_return_row
		// PARAMS query -> the query to be executed
		// RETURN mixed db result
		// DESC   returns the FIRST row of the given query
		public function db_return_row($query)
		{
			if (!$query)
			{
				$this->error_id = 11;
				$this->_db_error();
				return false;
			}
			// before doing ANYTHING check if query is "SELECT ..." everything else does not work
			if (!$this->_check_query_for_select($query))
			{
				$this->error_id = 17;
				$this->_db_error('', $query);
				return false;
			}
			$cursor = $this->db_exec($query);
			$result = $this->db_fetch_array($cursor);
			return $result;
		}

		// METHOD db_return_array
		// PARAMS query -> the query to be executed, named_only -> if true, only name ref are returned
		// RETURN array of hashes (row -> fields)
		// DESC   createds an array of hashes of the query (all data)
		public function db_return_array($query, $named_only = 0)
		{
			if (!$query)
			{
				$this->error_id = 11;
				$this->_db_error();
				return false;
			}
			// before doing ANYTHING check if query is "SELECT ..." everything else does not work
			if (!$this->_check_query_for_select($query))
			{
				$this->error_id = 17;
				$this->_db_error('', $query);
				return false;
			}
			$cursor = $this->db_exec($query);
			while ($res = $this->db_fetch_array($cursor))
			{
				for ($i = 0; $i < $this->num_fields; $i ++)
				{
					// cereated mixed, first name
					$data[$this->field_names[$i]] = $res[$this->field_names[$i]];
					// then number
					if (!$named_only)
						$data[$i] = $res[$this->field_names[$i]];
				}
				$rows[] = $data;
			}
			return $rows;
		}

		// METHOD db_cursor_pos
		// PARAMS $query -> query to find in cursor_ext
		// RETURN position (int)
		// DESC   returns the current position the read out
		public function db_cursor_pos($query)
		{
			if (!$query)
			{
				$this->error_id = 11;
				$this->_db_error();
				return false;
			}
			$md5 = md5($query);
			return $this->cursor_ext[$md5]['pos'];
		}

		// METHOD db_cursor_num_rows
		// PARAMS $query -> query to find in cursor_ext
		// RETURN row count (int)
		// DESC   returns the number of rows for the current select query
		public function db_cursor_num_rows($query)
		{
			if (!$query)
			{
				$this->error_id = 11;
				$this->_db_error();
				return false;
			}
			$md5 = md5($query);
			return $this->cursor_ext[$md5]['num_rows'];
		}

		// METHOD db_show_table_meta_data
		// PARAMS $table -> table name
		//        $schema -> optional schema name
		// RETURN array of table data
		// DESC   returns an array of the table with columns and values. FALSE on no table found
		public function db_show_table_meta_data($table, $schema = '')
		{
			$table = ($schema ? $schema.'.' : '').$table;

			$array = $this->db_functions->_db_meta_data($table);
			if (!is_array($array))
				$array = FALSE;
			return $array;
		}

		// METHOD db_prepare
		// PARAMS $stm_name, $query, $pk_name: optional
		// RETURN false on error
		// DESC   prepares a query
		//        for INSERT INTO queries it is highly recommended to set the pk_name to avoid an additional 
		//        read from the database for the PK NAME
		public function db_prepare($stm_name, $query, $pk_name = '')
		{
			if (!$query)
			{
				$this->error_id = 11;
				$this->_db_error();
				return false;
			}
			// if no DB Handler drop out
			if (!$this->dbh)
			{
				// if reconnect fails drop out
				if (!$this->_connect_to_db())
				{
					$this->error_id = 16;
					$this->_db_error();
					return false;
				}
			}
			// check that no other query is running right now
			if ($this->db_functions->_db_connection_busy())
			{
				$this->error_id = 41;
				$this->_db_error();
				return false;
			}
			// check if this was already prepared
			if (!array_key_exists($stm_name, $this->prepare_cursor) || !is_array($this->prepare_cursor[$stm_name]))
			{
				// if this is an insert query, check if we can add a return
				if ($this->_check_query_for_insert($query, true))
				{
					// set primary key name
					// current: only via parameter
					if (!$pk_name)
					{
						// read the primary key from the table, if we do not have one, we get nothing in return
						list($schema, $table) = $this->_db_return_table($query);
						if (!$this->pk_name_table[$table])
						{
							$this->pk_name_table[$table] = $this->db_functions->_db_primary_key($table, $schema);
						}
						$pk_name = $this->pk_name_table[$table];
					}
					if ($pk_name)
						$this->prepare_cursor[$stm_name]['pk_name'] = $pk_name;
					// if no returning, then add it
					if (!preg_match("/ returning /i", $query) && $this->prepare_cursor[$stm_name]['pk_name'])
					{
						$query .= " RETURNING ".$this->prepare_cursor[$stm_name]['pk_name'];
						$this->prepare_cursor[$stm_name]['returning_id'] = true;
					}
					// if returning exists but not pk_name, add it
					else if (preg_match("/ returning (.*)/i", $query, $matches) && $this->prepare_cursor[$stm_name]['pk_name'])
					{
						if (!preg_match("/{$this->prepare_cursor[$stm_name]['pk_name']}/", $matches[1]))
						{
							$query .= " , ".$this->prepare_cursor[$stm_name]['pk_name'];
							$this->prepare_cursor[$stm_name]['returning_id'] = true;
						}
					}
				}
				// search for $1, $2, in the query and push it into the control array
				preg_match_all('/(\$[0-9]{1,})/', $query, $match);
				$this->prepare_cursor[$stm_name]['count'] = count($match[1]);
				$this->prepare_cursor[$stm_name]['query'] = $query;
				$result = $this->db_functions->_db_prepare($stm_name, $query);
				if ($result)
				{
					$this->prepare_cursor[$stm_name]['result'] = $result;
					return $result;
				}
				else
				{
					$this->error_id = 21;
					$this->_db_error();
					$this->_db_debug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': Prepare field with: '.$stm_name.' | '.$query.'</span>', 'DB_ERROR');
					return $result;
				}
			}
			else
			{
				// thrown warning
				$this->warning_id = 20;
				return true;
			}
		}

		// METHOD db_execute
		// PARAMS $stm_name, data array
		// RETURN false on error
		// DESC   runs a prepare query
		public function db_execute($stm_name, $data = array())
		{
			// if we do not have no prepare cursor array entry for this statement name, abort
			if (!is_array($this->prepare_cursor[$stm_name]))
			{
				$this->error_id = 24;
				$this->_db_debug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': We do not have a prepared query entry for this statement name.</span>', 'DB_ERROR');
				return FALSE;
			}
			if (!is_array($data))
			{
				$this->error_id = 25;
				$this->_db_debug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': Prepared query Data has to be given in array form.</span>', 'DB_ERROR');
				return FALSE;
			}
			if ($this->prepare_cursor[$stm_name]['count'] != count($data))
			{
				$this->error_id = 23;
				$this->_db_debug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': Array data count does not match prepared fields. Need: '.$this->prepare_cursor[$stm_name]['count'].', has: '.count($data).'</span>', 'DB_ERROR');
				return FALSE;
			}
			else
			{
				if ($this->db_debug)
					$this->_db_debug('db', $this->_db_debug_prepare($stm_name, $data), 'db_exec_prep', 'Q');
				$code = $this->db_functions->_db_execute($stm_name, $data);
				if (!$code)
				{
$this->debug('ExecuteData', 'ERROR in STM['.$stm_name.'|'.$this->prepare_cursor[$stm_name]['result'].']: '.$this->print_ar($data));
					$this->error_id = 22;
					$this->_db_error($this->prepare_cursor[$stm_name]['result']);
					$this->_db_debug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': Execution failed</span>', 'DB_ERROR');
				}
				if ($this->_check_query_for_insert($this->prepare_cursor[$stm_name]['query'], true))
				{
					if (!$this->prepare_cursor[$stm_name]['returning_id'])
						$this->insert_id = $this->db_functions->_db_insert_id($this->prepare_cursor[$stm_name]['query'], $this->prepare_cursor[$stm_name]['pk_name']);
					elseif ($code)
						$this->insert_id = $this->db_functions->_db_fetch_array($code)[$this->prepare_cursor[$stm_name]['pk_name']];
					// this error handling is only for pgsql
					if (is_array($this->insert_id))
					{
						$this->warning_id = 19;
						$this->_db_error($this->insert_id[1]);
						$this->_db_debug('db', '<span style="color: orange;"><b>DB-Warning</b> '.$stm_name.': Could not get insert id</span>', 'DB_WARNING');
						unset($this->insert_id);
					}
					elseif (!$this->insert_id)
					{
						$this->warning_id = 31;
						$this->_db_error();
						$this->_db_debug('db', '<span style="color: orange;"><b>DB-Warning</b> '.$stm_name.': Could not get insert id</span>', 'DB_WARNING');
					}
				}
				return $code;
			}
		}

		// METHOD db_escape_string
		// PARAMS $string -> string to escape
		// RETURN escaped string
		// DESC   neutral function to escape a string for DB writing
		public function db_escape_string($string)
		{
			return $this->db_functions->_db_escape_string($string);
		}
		
		// METHOD db_escape_bytea
		// PARAMS $bytea -> bytea to escape
		// RETURN escaped bytea
		// DESC   neutral function to escape a bytea for DB writing
		public function db_escape_bytea($bytea)
		{
			return $this->db_functions->_db_escape_bytea($bytea);
		}

		// METHOD db_version
		// PARAMS none
		// RETURN database version as string
		// DESC   return current database version
		public function db_version()
		{   
			return $this->db_functions->_db_version();
		}

		// METHOD db_compare_version
		// PARAMS string to which the return will return true or false
		//        =X.Y, >X.Y, <X.Y
		// RETURN true/false
		// DESC   returns boolean true or false if the string matches the database version
		public function db_compare_version($compare)
		{   
			// compare has =, >, < prefix, and gets stripped, if the rest is not X.Y format then error
			preg_match("/^([<>=]{1,2})(\d{1,2})\.(\d{1,2})/", $compare, $matches);
			$compare = $matches[1];
			$to_master = $matches[2];
			$to_minor = $matches[3];
			if (!$compare || !$to_master || !$to_minor)
				return false;
			else
				$to_version = $to_master.($to_minor < 10 ? '0' : '').$to_minor;
			// db_version can return X.Y.Z
			// we only compare the first two
			list ($master, $minor, $_other) = explode('.', $this->db_version());
				$version = $master.($minor < 10 ? '0' : '').$minor;
			$return = false;
			// compare
			switch ($compare)
			{   
				case '=':
					if ($to_version == $version)
						$return = true;
					break;
				case '<':
					if ($version < $to_version)
						$return = true;
					break;
				case '<=':
					if ($version <= $to_version)
						$return = true;
					break;
				case '>':
					if ($version > $to_version)
						$return = true;
					break;
				case '>=':
					if ($version >= $to_version)
						$return = true;
					break;
				default;
					$return = false;
			}
			return $return;
		}

		// METHOD db_boolean
		// PARAMS 't' / 'f' or any string
		// RETURN correct php boolean true/false
		// DESC   if the input is a single char 't' or 'f' it will return the boolean value instead
		public function db_boolean($string, $rev = false)
		{
			if (!$rev)
			{
				if ($string == 't' || $string == 'true')
					return true;
				if ($string == 'f' || $string == 'false')
					return false;
			}
			else
			{
				if ($string)
					return 't';
				else
					return 'f';
			}
			// if neither, just return data as is
			return $string;
		}

		// ** REMARK **
		// db_write_data is the old without separate update no write list
		// db_write_data_ext is the extended with additional array for no write list for update

		// METHOD: db_write_data
		// PARAMS: write_array -> list of elements to write
		//         not_write_array -> list of elements not to write
		//         primary_key -> id key to decide if we write insert or update
		//         table -> name for the target table
		// RETURN: primary key id
		// DESC:   writes into one table based on array of table columns
		public function db_write_data($write_array, $not_write_array, $primary_key, $table, $data = array ())
		{
			$not_write_upodate_array = array ();
			return $this->db_write_data_ext($write_array, $primary_key, $table, $not_write_array, $not_write_update_array, $data);
		}
		
		// METHOD: db_write_data_ext
		// PARAMS: write_array -> list of elements to write
		//         primary_key -> id key to decide if we write insert or update
		//                     -> alternate the primary key can be an array with 'row' => 'row name', 'value' => 'data' to use a different column as the primary key
		//         table -> name for the target table
		// (optional)
		//         not_write_array -> list of elements not to write
		//         not_write_update_array -> list of elements not to write during update
		//         data -> optional array with data, if not _POST vars are used
		// RETURN: primary key id
		// DESC:   writes into one table based on array of table columns
		public function db_write_data_ext($write_array, $primary_key, $table, $not_write_array = array (), $not_write_update_array = array (), $data = array ())
		{
			if (!is_array($primary_key))
			{
				$primary_key = array (
					'row' => $table.'_id',
					'value' => $primary_key
				);
			}

			// get the table layout and row types
			$table_data = $this->db_show_table_meta_data(($this->db_schema ? $this->db_schema.'.' : '').$table);
			foreach ($write_array as $field)
			{
				if ((!$primary_key['value'] || ($primary_key['value'] && !in_array($field, $not_write_update_array))) && !in_array($field, $not_write_array))
				{
					// data from external or data field
					$_data = (count($data) >= 1) ? $data[$field] : $GLOBALS[$field];
					$has_default = $table_data[$field]['has default'];
					$not_null = $table_data[$field]['not null'];
					// write if the field has to be not null, or if there is no data and the field has no default values or if there is data
					if (($not_null && !$_data) || (!$has_default && !$_data) || (is_numeric($_data) && isset($_data)) || $_data)
					{
						if ($q_sub_value && !$primary_key['value'])
							$q_sub_value .= ', ';
						if ($q_sub_data)// && (!$primary_key || ($primary_key && !in_array($field, $not_write_array))))
							$q_sub_data .= ', ';
						if ($primary_key['value'])
							$q_sub_data .= $field.' = ';
						else
							$q_sub_value .= $field;
						// if field is "date" and -- -> reset
						if ($_data == '--' && strstr($table_data[$field]['type'], 'date'))
							$_data = '';
						// write data into sql string
						if (strstr($table_data[$field]['type'], 'int'))
							$q_sub_data .= (is_numeric($_data) && isset($_data)) ? $_data : ($has_default ? $has_default : 'NULL');
						else
							$q_sub_data .= ($_data) ? "'".$this->db_escape_string($_data)."'" : ($has_default ? "'".$this->db_escape_string($has_default)."'" : 'NULL');
					}
				}
			}

			// first work contact itself (we need contact id for everything else)
			if ($primary_key['value'])
			{
				$q = 'UPDATE '.$table.' SET ';
				$q .= $q_sub_data.' ';
				$q .= 'WHERE '.$primary_key['row'].' = '.$primary_key['value'];
				$this->temp_sql = $q_sub_data;
			}
			else
			{
				$q = 'INSERT INTO '.$table.' (';
				$q .= $q_sub_value;
				$q .= ') VALUES (';
				$q .= $q_sub_data;
				$q .= ')';
				$this->temp_sql = $q;
			}
			if (!$this->db_exec($q))
				return false;
			if (!$primary_key['value'])
				$primary_key['value'] = $this->insert_id;

			return $primary_key['value'];
		}

		// METHOD: db_time_format
		// PARAMS: age or datetime difference
		//         micro on off (default false)
		// RETURN: Y/M/D/h/m/s formatted string (like TimeStringFormat
		// DESC:   only for postgres. pretty formats an age or datetime difference string
		public function db_time_format($age, $show_micro = false)
		{
			// in string (datetime diff): 1786 days 22:11:52.87418
			// or (age): 4 years 10 mons 21 days 12:31:11.87418
			// also -09:43:54.781021 or without - prefix

			preg_match("/(.*)?(\d{2}):(\d{2}):(\d{2})(\.(\d+))/", $age, $matches);

			$prefix = $matches[1] != '-' ? $matches[1] : '';
			$hour = $matches[2] != '00' ? preg_replace('/^0/', '', $matches[2]) : '';
			$minutes = $matches[3] != '00' ? preg_replace('/^0/', '', $matches[3]) : '';
			$seconds = $matches[4] != '00' ? preg_replace('/^0/', '', $matches[4]) : '';
			$milliseconds = $matches[6];

			return $prefix.($hour ? $hour.'h ' : '').($minutes ? $minutes.'m ' : '').($seconds ? $seconds.'s' : '').($show_micro && $milliseconds? ' '.$milliseconds.'ms' : ''); 
		}

		// METHOD: db_array_parse
		// PARAMS: text: input text to parse to an array
		// RETURN: PHP array of the parsed data
		// DESC:   this is only needed for Postgresql. Converts postgresql arrays to PHP
		public function db_array_parse($text)
		{
			return $this->db_functions->_db_array_parse($text, $output);
		}

		// METHOD: db_sql_escape
		// PARAMS: value -> to escape data
		//         kbn -> escape trigger type
		// RETURN: escaped value
		// DESC  : clear up any data for valid DB insert
		public function db_sql_escape($value, $kbn = "")
		{
			switch ($kbn)
			{
				case "i":
					$value = (!isset($value) || $value === "") ? "NULL" : intval($value);
					break;
				case "f":
					$value = (!isset($value) || $value === "") ? "NULL" : floatval($value);
					break;
				case "t":
					$value = (!isset($value) || $value === "") ? "NULL" : "'".$this->db_escape_string($value)."'";
					break;
				case "d":
					$value = (!isset($value) || $value === "") ? "NULL" : "'".$this->db_escape_string($value)."'";
					break;
				case "i2":
					$value = (!isset($value) || $value === "") ? 0 : intval($value);
					break;
			}
			return $value;
		}
	} // end if db class
?>

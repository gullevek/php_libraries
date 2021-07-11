<?php declare(strict_types=1);
/********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2000/11/23
* VERSION: 5.0.0
* RELEASED LICENSE: GNU GPL 3
* SHORT DESCRIPTON:
*   2018/3/23, the whole class system is transformed to namespaces
*   also all internal class calls are converted to camel case
*
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
*     - the version as an array [major, minor, patchlvl, daypatch]
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
*	$array_of_hashes db_show_table_meta_data($table_name)
*	  - returns an hashed array of table column data
*	function db_prepare($stm_name, $query)
*     - prepares a query with the given stm name, returns false on error
*   function db_execute($stm_name, $data = [])
*     - execute a query that was previously prepared
*   $string db_escape_string($string)
*     - correctly escapes string for db insert
*   $string db_boolean(string)
*     - if the string value is 't' or 'f' it returns correct TRUE/FALSE for php
*   $primary_key db_write_data($write_array, $not_write_array, $primary_key, $table, $data = [])
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

namespace CoreLibs\DB;

class IO extends \CoreLibs\Basic
{
	// recommend to set private/protected and only allow setting via method
	// can bet set from outside
	// encoding to
	public $to_encoding = '';
	public $query; // the query string at the moment
	// only inside
	// basic vars
	private $dbh; // the dbh handler
	public $db_debug; // DB_DEBUG ... (if set prints out debug msgs)
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
	public $field_names = []; // array with the field names of the current query
	public $insert_id; // last inserted ID
	public $insert_id_ext; // extended insert ID (for data outside only primary key)
	public $insert_id_arr; // always return as array, even if only one
	private $temp_sql;
	// other vars
	private $nbsp = ''; // used by print_array recursion function
	// error & warning id
	protected $error_id;
	private $had_error;
	private $warning_id;
	private $had_warning;
	// error thrown on class init if we cannot connect to db
	protected $db_init_error = false;
	// sub include with the database functions
	private $db_functions;

	// endless loop protection
	private $MAX_QUERY_CALL;
	private $DEFAULT_MAX_QUERY_CALL = 20; // default
	private $query_called = [];
	// error string
	protected $error_string = [];
	// prepared list
	public $prepare_cursor = [];
	// primary key per table list
	// format is 'table' => 'pk_name'
	public $pk_name_table = [];
	// internal primary key name, for cross calls in async
	public $pk_name;
	// if we use RETURNING in the INSERT call
	private $returning_id = false;
	// if a sync is running holds the md5 key of the query
	private $async_running;

	/**
	 * main DB concstructor with auto connection to DB and failure set on failed connection
	 * @param array $db_config DB configuration array
	 */
	public function __construct(array $db_config)
	{
		// start basic class
		parent::__construct();
		// dummy init array for db config if not array
		if (!is_array($db_config)) {
			$db_config = [];
		}
		// TODO: check must set CONSTANTS
		// sets the names (for connect/reconnect)
		$this->db_name = $db_config['db_name'] ?? '';
		$this->db_user = $db_config['db_user'] ?? '';
		$this->db_pwd = $db_config['db_pass'] ?? '';
		$this->db_host = $db_config['db_host'] ?? '';
		$this->db_port = !empty($db_config['db_port']) ? $db_config['db_port'] : 5432;
		$this->db_schema = !empty($db_config['db_schema']) ? $db_config['db_schema'] : ''; // do not set to 'public' if not set, because the default is already public
		$this->db_encoding = !empty($db_config['db_encoding']) ? $db_config['db_encoding'] : '';
		$this->db_type = $db_config['db_type'] ?? '';
		$this->db_ssl = !empty($db_config['db_ssl']) ? $db_config['db_ssl'] : 'allow';

		// set the target encoding to the DEFAULT_ENCODING if it is one of them: EUC, Shift_JIS, UTF-8
		// @ the moment set only from outside

		// set loop protection max count
		$this->MAX_QUERY_CALL = 20;

		// error & debug stuff, error & warning ids are the same, its just in which var they get written
		$this->error_string['10'] = 'Could not load DB interface functions';
		$this->error_string['11'] = 'No Querystring given';
		$this->error_string['12'] = 'No Cursor given, no correct query perhaps?';
		$this->error_string['13'] = 'Query could not be executed without errors';
		$this->error_string['14'] = 'Can\'t connect to DB server';
		$this->error_string['15'] = 'Can\'t select DB';
		$this->error_string['16'] = 'No DB Handler found / connect or reconnect failed';
		$this->error_string['17'] = 'All dbReturn* methods work only with SELECT statements, please use dbExec for everything else';
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
		$this->error_string['32'] = 'Multiple PK return as array';
		$this->error_string['33'] = 'Returning PK was not found';
		$this->error_string['40'] = 'Query async call failed.';
		$this->error_string['41'] = 'Connection is busy with a different query. Cannot execute.';
		$this->error_string['42'] = 'Cannot check for async query, none has been started yet.';
		$this->error_string['50'] = 'Setting max query call to -1 will disable loop protection for all subsequent runs';
		$this->error_string['51'] = 'Max query call needs to be set to at least 1';

		// set debug, either via global var, or debug var during call
		$this->db_debug = false;
		// global overrules local
		if (isset($GLOBALS['DB_DEBUG'])) {
			$this->db_debug = $GLOBALS['DB_DEBUG'];
		}

		// based on $this->db_type
		// here we need to load the db pgsql include one
		// How can we do this dynamic? eg for non PgSQL
		// OTOH this whole class is so PgSQL specific
		// that non PgSQL doesn't make much sense anymore
		if ($this->db_type == 'pgsql') {
			$this->db_functions = new \CoreLibs\DB\SQL\PgSQL();
		} else {
			// abort error
			$this->error_id = 10;
			$this->__dbError();
			$this->db_init_error = true;
		}

		// connect to DB
		if (!$this->__connectToDB()) {
			$this->error_id = 16;
			$this->__dbError();
			$this->db_init_error = true;
		}
	}

	/**
	 * final desctruct method, closes the DB connection
	 */
	public function __destruct()
	{
		$this->__closeDB();
		// parent::__destruct();
	}

	// *************************************************************
	// PRIVATE METHODS
	// *************************************************************

	/**
	 * internal connection function. Used to connect to the DB if there is no connection done yet.
	 * Called before any execute
	 * @return bool true on successfull connect, false if failed
	 */
	private function __connectToDB(): bool
	{
		// generate connect string
		$this->dbh = $this->db_functions->__dbConnect($this->db_host, $this->db_user, $this->db_pwd, $this->db_name, $this->db_port, $this->db_ssl);
		// if no dbh here, we couldn't connect to the DB itself
		if (!$this->dbh) {
			$this->error_id = 14;
			$this->__dbError();
			return false;
		}
		// 15 error (cant select to DB is not valid in postgres, as connect is different)
		// if returns 0 we couldn't select the DB
		if ($this->dbh == -1) {
			$this->error_id = 15;
			$this->__dbError();
			return false;
		}
		// set search path if needed
		if ($this->db_schema) {
			$this->dbSetSchema();
		}
		// set client encoding
		if ($this->db_encoding) {
			$this->dbSetEncoding();
		}
		// all okay
		return true;
	}

	/**
	 * close db connection
	 * only used by the deconstructor
	 * @return void has no return
	 */
	private function __closeDB(): void
	{
		if (isset($this->dbh) && $this->dbh) {
			$this->db_functions->__dbClose();
			$this->dbh = null;
		}
	}

	/**
	 * checks if query is a SELECT, SHOW or WITH, if not error, 0 return
	 * NOTE:
	 * Query needs to start with SELECT, SHOW or WITH. if starts with "with" it is ignored
	 * @param  string $query query to check
	 * @return bool          true if matching, false if not
	 */
	private function __checkQueryForSelect(string $query): bool
	{
		// perhaps allow spaces before select ?!?
		if (preg_match("/^(select|show|with) /i", $query)) {
			return true;
		}
		return false;
	}

	/**
	 * check for DELETE, INSERT, UPDATE
	 * if pure is set to true, only when INSERT is set will return true
	 * NOTE:
	 * Queries need to start with INSERT, UPDATE, DELETE. Anything else is ignored
	 * @param  string $query query to check
	 * @param  bool   $pure  pure check (only insert), default false
	 * @return bool          true if matching, false if not
	 */
	private function __checkQueryForInsert(string $query, bool $pure = false): bool
	{
		if ($pure && preg_match("/^insert /i", $query)) {
			return true;
		}
		if (!$pure && preg_match("/^(insert|update|delete) /i", $query)) {
			return true;
		}
		return false;
	}

	/**
	 * returns true if the query starts with UPDATE
	 * query NEEDS to start with UPDATE
	 * @param  string $query query to check
	 * @return bool          returns true if the query starts with UPDATE
	 */
	private function __checkQueryForUpdate(string $query): bool
	{
		if (preg_match("/^update /i", $query)) {
			return true;
		}
		return false;
	}

	/**
	 * internal funktion that creates the array
	 * NOTE:
	 * used in db_dump_data only
	 * @param  array  $array array to print
	 * @return string        string with printed and formated array
	 */
	private function __printArray(array $array): string
	{
		$string = '';
		if (!is_array($array)) {
			$array = [];
		}
		foreach ($array as $key => $value) {
			$string .= $this->nbsp.'<b>'.$key.'</b> => ';
			if (is_array($value)) {
				$this->nbsp .= '&nbsp;&nbsp;&nbsp;';
				$string .= '<br>';
				$string .= $this->__printArray($value);
			} else {
				$string .= $value.'<br>';
			}
		}
		$this->nbsp = substr_replace($this->nbsp, '', -18, 18);
		return $string;
	}

	/**
	 * calls the basic class debug with strip command
	 * @param  string $debug_id     group id for debug
	 * @param  string $error_string error message or debug data
	 * @param  string $id           db debug group
	 * @param  string $type         query identifier (Q, I, etc)
	 * @return void                 has no return
	 */
	protected function __dbDebug(string $debug_id, string $error_string, string $id = '', string $type = ''): void
	{
		$prefix = '';
		if ($id) {
			$prefix .= '[<span style="color: #920069;">'.$id.'</span>] ';
		}
		if ($type) {
			$prefix .= '{<span style="font-style: italic; color: #3f0092;">'.$type.'</span>} ';
		}
		if ($prefix) {
			$prefix .= '- ';
		}
		$this->log->debug($debug_id, $error_string, true, $prefix);
	}

	/**
	 * if error_id set, writes long error string into error_msg
	 * NOTE:
	 * needed to make public so it can be called from DB.Array.IO too
	 * @param  resource|bool $cursor current cursor for pg_result_error, mysql uses dbh,
	 *                               pg_last_error too, but pg_result_error is more accurate
	 * @param  string    $msg        optional message
	 * @return void                  has no return
	 */
	public function __dbError($cursor = false, string $msg = ''): void
	{
		$pg_error_string = '';
		$where_called = (string)\CoreLibs\Debug\Support::getCallerMethod();
		if ($cursor) {
			$pg_error_string = $this->db_functions->__dbPrintError($cursor);
		}
		if (!$cursor && method_exists($this->db_functions, '__dbPrintError')) {
			$pg_error_string = $this->db_functions->__dbPrintError();
		}
		if ($pg_error_string) {
			$this->__dbDebug('db', $pg_error_string, 'DB_ERROR', $where_called);
		}
		// okay, an error occured
		if ($this->error_id) {
			// write error msg ...
			$this->__dbDebug('db', '<span style="color: red;"><b>DB-Error</b> '.$this->error_id.': '.$this->error_string[$this->error_id].($msg ? ', '.$msg : '').'</span>', 'DB_ERROR', $where_called);
			$this->had_error = $this->error_id;
			// write detailed error log
		}
		if ($this->warning_id) {
			$this->__dbDebug('db', '<span style="color: orange;"><b>DB-Warning</b> '.$this->warning_id.': '.$this->error_string[$this->warning_id].($msg ? ', '.$msg : '').'</span>', 'DB_WARNING', $where_called);
			$this->had_warning = $this->warning_id;
		}
		// unset the error/warning vars
		$this->error_id = 0;
		$this->warning_id = 0;
	}

	/**
	 * if there is the 'to_encoding' var set, and the field is in the wrong encoding converts it to the target
	 * @param  array|bool|null $row array from fetch_row
	 * @return array|bool|null            convert fetch_row array, or false
	 */
	private function __dbConvertEncoding($row)
	{
		// only do if array, else pass through row (can be false)
		if (!is_array($row) || empty($this->to_encoding) || empty($this->db_encoding)
		) {
			return $row;
		}
		// go through each row and convert the encoding if needed
		foreach ($row as $key => $value) {
			$from_encoding = mb_detect_encoding($value);
			// convert only if encoding doesn't match and source is not pure ASCII
			if ($from_encoding != $this->to_encoding && $from_encoding != 'ASCII') {
				$row[$key] = mb_convert_encoding($value, $this->to_encoding, $from_encoding);
			}
		}
		return $row;
	}

	/**
	 * for debug purpose replaces $1, $2, etc with actual data
	 * @param  string $stm_name prepared statement name
	 * @param  array  $data     the data array
	 * @return string           string of query with data inside
	 */
	private function __dbDebugPrepare(string $stm_name, array $data = []): string
	{
		// get the keys from data array
		$keys = array_keys($data);
		// because the placeholders start with $ and at 1, we need to increase each key and prefix it with a $ char
		for ($i = 0, $iMax = count($keys); $i < $iMax; $i ++) {
			$keys[$i] = '$'.($keys[$i] + 1);
		}
		// simply replace the $1, $2, ... with the actual data and return it
		return str_replace(array_reverse($keys), array_reverse($data), $this->prepare_cursor[$stm_name]['query']);
	}

	/**
	 * extracts schema and table from the query, if no schema returns just empty string
	 * @param  string $query insert/select/update/delete query
	 * @return array         array with schema and table
	 */
	private function __dbReturnTable(string $query): array
	{
		$matches = [];
		if (preg_match("/^SELECT /i", $query)) {
			preg_match("/ (FROM) (([\w_]+)\.)?([\w_]+) /i", $query, $matches);
		} else {
			preg_match("/(INSERT INTO|DELETE FROM|UPDATE) (([\w_]+)\.)?([\w_]+) /i", $query, $matches);
		}
		return [$matches[3], $matches[4]];
	}

	/**
	 * sub function for dbExec and dbExecAsync
	 * - checks query is set
	 * - checks there is a database handler
	 * - checks that here is no other query executing
	 * - checks for insert if returning is set/pk name
	 * - sets internal md5 for query
	 * - checks multiple call count
	 * @param  string      $query   query string
	 * @param  string      $pk_name primary key [if set to NULL no returning will be added]
	 * @return string|bool          md5 OR boolean false on error
	 */
	private function __dbPrepareExec(string $query, string $pk_name)
	{
		$matches= [];
		// to either use the returning method or the guess method for getting primary keys
		$this->returning_id = false;
		// set the query
		if ($query) {
			$this->query = $query;
		}
		if (!$this->query) {
			$this->error_id = 11;
			$this->__dbError();
			return false;
		}
		// if no DB Handler drop out
		if (!$this->dbh) {
			// if reconnect fails drop out
			if (!$this->__connectToDB()) {
				$this->error_id = 16;
				$this->__dbError();
				return false;
			}
		}
		// check that no other query is running right now
		if ($this->db_functions->__dbConnectionBusy()) {
			$this->error_id = 41;
			$this->__dbError();
			return false;
		}
		// if we do have an insert, check if there is no RETURNING pk_id, add it if I can get the PK id
		if ($this->__checkQueryForInsert($this->query, true)) {
			$this->pk_name = $pk_name;
			if ($this->pk_name != 'NULL') {
				if (!$this->pk_name) {
					// TODO: get primary key from table name
					list($schema, $table) = $this->__dbReturnTable($this->query);
					if (!array_key_exists($table, $this->pk_name_table) || !$this->pk_name_table[$table]) {
						$this->pk_name_table[$table] = $this->db_functions->__dbPrimaryKey($table, $schema);
					}
					$this->pk_name = $this->pk_name_table[$table] ? $this->pk_name_table[$table] : 'NULL';
				}
				if (!preg_match("/ returning /i", $this->query) && $this->pk_name && $this->pk_name != 'NULL') {
					// check if this query has a ; at the end and remove it
					$this->query = preg_replace("/(;\s*)$/", '', $this->query);
					$this->query .= " RETURNING ".$this->pk_name;
					$this->returning_id = true;
				} elseif (preg_match("/ returning (.*)/i", $this->query, $matches)) {
					if ($this->pk_name && $this->pk_name != 'NULL') {
						// add the primary key if it is not in the returning set
						if (!preg_match("/$this->pk_name/", $matches[1])) {
							$this->query .= " , ".$this->pk_name;
						}
					}
					$this->returning_id = true;
				}
			}
		}
		// if we have an UPDATE and RETURNING, flag for true, but do not add anything
		if ($this->__checkQueryForUpdate($this->query) && preg_match("/ returning (.*)/i", $this->query, $matches)) {
			$this->returning_id = true;
		}
		// $this->debug('DB IO', 'Q: '.$this->query.', RETURN: '.$this->returning_id);
		// for DEBUG, only on first time ;)
		if ($this->db_debug) {
			$this->__dbDebug('db', $this->query, '__dbPrepareExec', 'Q');
		}
		// import protection, md5 needed
		$md5 = md5($this->query);
		// if the array index does not exists set it 0
		if (!array_key_exists($md5, $this->query_called)) {
			$this->query_called[$md5] = 0;
		}
		// if the array index exists, but it is not a numeric one, set it to 0
		if (!is_numeric($this->query_called[$md5])) {
			$this->query_called[$md5] = 0;
		}
		// count up the run, if this is run more than the max_run then exit with error
		// if set to -1, then ignore it
		if ($this->MAX_QUERY_CALL != -1 &&
			$this->query_called[$md5] > $this->MAX_QUERY_CALL
		) {
				$this->error_id = 30;
				$this->__dbError();
				$this->__dbDebug('db', $this->query, 'dbExec', 'Q[nc]');
				return false;
		}
		$this->query_called[$md5] ++;
		// return md5
		return $md5;
	}

	/**
	 * runs post execute for rows affected, field names, inserted primary key, etc
	 * @return bool true on success or false if an error occured
	 */
	private function __dbPostExec(): bool
	{
		// if FALSE returned, set error stuff
		// if either the cursor is false
		if (!$this->cursor || $this->db_functions->__dbLastErrorQuery()) {
			// printout Query if debug is turned on
			if ($this->db_debug) {
				$this->__dbDebug('db', $this->query, 'dbExec', 'Q[nc]');
			}
			// internal error handling
			$this->error_id = 13;
			$this->__dbError($this->cursor);
			return false;
		} else {
			// if SELECT do here ...
			if ($this->__checkQueryForSelect($this->query)) {
				// count the rows returned (if select)
				$this->num_rows = $this->db_functions->__dbNumRows($this->cursor);
				// count the fields
				$this->num_fields = $this->db_functions->__dbNumFields($this->cursor);
				// set field names
				$this->field_names = [];
				for ($i = 0; $i < $this->num_fields; $i ++) {
					$this->field_names[] = $this->db_functions->__dbFieldName($this->cursor, $i);
				}
			} elseif ($this->__checkQueryForInsert($this->query)) {
				// if not select do here
				// count affected rows
				$this->num_rows = $this->db_functions->__dbAffectedRows($this->cursor);
				if (($this->__checkQueryForInsert($this->query, true) && $this->pk_name != 'NULL') ||
					($this->__checkQueryForUpdate($this->query) && $this->returning_id)
				) {
					// set insert_id
					// if we do not have a returning, we try to get it via the primary key and another select
					if (!$this->returning_id) {
						$this->insert_id = $this->db_functions->__dbInsertId($this->query, $this->pk_name);
						$this->insert_id_ext = $this->insert_id;
						$this->insert_id_arr[] = $this->insert_id;
					} else {
						$this->insert_id = [];
						$this->insert_id_ext = [];
						$this->insert_id_arr = [];
						// echo "** PREPARE RETURNING FOR CURSOR: ".$this->cursor."<br>";
						// we have returning, now we need to check if we get one or many returned
						// we'll need to loop this, if we have multiple insert_id returns
						while ($_insert_id = $this->db_functions->__dbFetchArray(
							$this->cursor,
							$this->db_functions->__dbResultType(true)
						)) {
							// echo "*** RETURNING: ".print_r($_insert_id, true)."<br>";
							$this->insert_id[] = $_insert_id;
							$this->insert_id_arr[] = $_insert_id;
						}
						// if we have only one, revert from array to single
						if (count($this->insert_id) == 1) {
							// echo "* SINGLE DATA CONVERT: ".count($this->insert_id[0])." => ".array_key_exists($this->pk_name, $this->insert_id[0])."<br>";
							// echo "* PK DIRECT: ".(isset($this->insert_id[0][$this->pk_name]) ? $this->insert_id[0][$this->pk_name] : '[NO PK NAME SET]' )."<Br>";
							// if this has only the pk_name, then only return this, else array of all data (but without the position)
							// example if insert_id[0]['foo'] && insert_id[0]['bar'] it will become insert_id['foo'] & insert_id['bar']
							// if only ['foo_id'] and it is the PK then the PK is directly written to the insert_id
							if (count($this->insert_id[0]) > 1 ||
								!array_key_exists($this->pk_name, $this->insert_id[0])
							) {
								$this->insert_id_ext = $this->insert_id[0];
								if (isset($this->insert_id[0][$this->pk_name])) {
									$this->insert_id = $this->insert_id[0][$this->pk_name];
								}
							} elseif (isset($this->insert_id[0][$this->pk_name])) {
								$this->insert_id = $this->insert_id[0][$this->pk_name];
							}
						} elseif (count($this->insert_id) == 0) {
							// if we have non -> error
							// failed to get insert id
							$this->insert_id = '';
							$this->warning_id = 33;
							$this->__dbError($this->cursor, '[dbExec]');
						}
						// if we have multiple, do not set the insert_id different, keep as array
					}
					// this warning handling is only for pgsql
					// we returned an array of PKs instread of a single one
					if (is_array($this->insert_id)) {
						$this->warning_id = 32;
						$this->__dbError($this->cursor, '[dbExec]');
					}
				}
			}
			return true;
		}
	}

	// *************************************************************
	// PUBLIC METHODS
	// *************************************************************

	/**
	 * switches the debug flag on or off
	 * if none given, then the debug flag auto switches from
	 * the previous setting to either then on or off
	 * else override with boolean true/false
	 * @param  bool|null $debug true/false or null for no set
	 * @return int              debug flag in int
	 */
	public function dbSetDebug($debug = null): int
	{
		if ($debug === true) {
			$this->db_debug = 1;
		} elseif ($debug === false) {
			$this->db_debug = 0;
		} elseif ($this->db_debug) {
			$this->db_debug = 0;
		} elseif (!$this->db_debug) {
			$this->db_debug = 1;
		}
		return $this->db_debug;
	}

	/**
	 * Switches db debug flag on or off
	 * OR
	 * with the optional parameter fix sets debug
	 * returns current set stats
	 * @param  bool|null $debug Flag to turn debug on off
	 * @return bool True for debug is on, False for off
	 */
	public function dbToggleDebug(?bool $debug = null)
	{
		if ($debug !== null) {
			$this->db_debug = $debug;
		} else {
			$this->db_debug = $this->db_debug ? false : true;
		}
		return $this->db_debug;
	}

	/**
	 * set max query calls, set to --1 to disable loop
	 * protection. this will generate a warning
	 * empty call (null) will reset to default
	 * @param  int|null $max_calls Set the max loops allowed
	 * @return bool                True for succesfull set
	 */
	public function dbSetMaxQueryCall(?int $max_calls = null): bool
	{
		$success = false;
		// if null then reset to default
		if ($max_calls === null) {
			$max_calls = $this->DEFAULT_MAX_QUERY_CALL;
		}
		// if -1 then disable loop check
		// DANGEROUS, WARN USER
		if ($max_calls == -1) {
			$this->warning_id = 50;
			$this->__dbError();
		}
		// negative or 0
		if ($max_calls < -1 || $max_calls == 0) {
			$this->error_id = 51;
			$this->__dbError();
			// early abort
			return false;
		}
		// ok entry, set
		if ($max_calls == -1 ||
			$max_calls > 0
		) {
			$this->MAX_QUERY_CALL = $max_calls;
			$success = true;
		}
		return $success;
	}

	/**
	 * returns current set max query calls for loop avoidance
	 * @return int Integer number, if -1 the loop check is disabled
	 */
	public function dbGetMaxQueryCall(): int
	{
		return $this->MAX_QUERY_CALL;
	}

	/**
	 * resets the call times for the max query called to 0
	 * USE CAREFULLY: rather make the query prepare -> execute
	 * @param  string $query query string
	 * @return void          has no return
	 */
	public function dbResetQueryCalled(string $query): void
	{
		$this->query_called[md5($query)] = 0;
	}

	/**
	 * gets how often a query was called already
	 * @param  string $query query string
	 * @return int           count of times the query was executed
	 */
	public function dbGetQueryCalled(string $query): int
	{
		$md5 = md5($query);
		if ($this->query_called[$md5]) {
			return $this->query_called[$md5];
		} else {
			return 0;
		}
	}

	/**
	 * closes the db_connection
	 * normally this is not used, as the class deconstructor closes the connection down
	 * @return void has no return
	 */
	public function dbClose(): void
	{
		if ($this->dbh) {
			$this->db_functions->__dbClose();
			$this->dbh = null;
		}
	}

	/**
	 * returns the db init error
	 * if failed to connect it is set to true
	 * else false
	 * @return bool connection failure status
	 */
	public function getConnectionStatus(): bool
	{
		return $this->db_init_error;
	}

	/**
	 * sets new db schema
	 * @param  string $db_schema schema name, if not given tries internal default db schema
	 * @return bool              false on failure to find schema values, true of db exec schema set
	 */
	public function dbSetSchema(string $db_schema = '')
	{
		if (!$db_schema && $this->db_schema) {
			$db_schema = $this->db_schema;
		}
		if (!$db_schema) {
			return false;
		}
		$q = "SET search_path TO '".$this->dbEscapeString($db_schema)."'";
		return $this->dbExec($q);
	}

	/**
	 * returns the current set db schema
	 * @return string db schema name
	 */
	public function dbGetSchema(): string
	{
		return $this->db_schema;
	}

	/**
	 * sets the client encoding in the postgres database
	 * @param  string $db_encoding valid encoding name, so the the data gets converted to this encoding
	 * @return bool                false, or true of db exec encoding set
	 */
	public function dbSetEncoding(string $db_encoding = ''): bool
	{
		if (!$db_encoding && $this->db_encoding) {
			$db_encoding = $this->db_encoding;
		}
		if (!$db_encoding) {
			return false;
		}
		$q = "SET client_encoding TO '".$this->dbEscapeString($db_encoding)."'";
		return $this->dbExec($q);
	}

	/**
	 * returns the current set client encoding from the connected DB
	 * @return string current client encoding
	 */
	public function dbGetEncoding(): string
	{
		return $this->dbReturnRow('SHOW client_encoding')['client_encoding'];
	}

	/**
	 * get certain settings like username, db name
	 * @param  string $name what setting to query
	 * @return mixed        setting value, if not allowed name return false
	 */
	public function dbGetSetting(string $name)
	{
		$setting = '';
		switch ($name) {
			case 'name':
				$setting = $this->db_name;
				break;
			case 'user':
				$setting = $this->db_user;
				break;
			default:
				$setting = false;
				break;
		}
		return $setting;
	}

	/**
	 * prints out status info from the connected DB (might be usefull for debug stuff)
	 * @param  bool|boolean $show show db connection info, default true
	 *                            if set to false won't write to error_msg var
	 * @return string             db connection information string
	 */
	public function dbInfo(bool $show = true): string
	{
		$string = '';
		$string .= '{b}-DB-info->{/b} Connected to db {b}\''.$this->db_name.'\'{/b} ';
		$string .= 'with schema {b}\''.$this->db_schema.'\'{/b} ';
		$string .= 'as user {b}\''.$this->db_user.'\'{/b} ';
		$string .= 'at host {b}\''.$this->db_host.'\'{/b} ';
		$string .= 'on port {b}\''.$this->db_port.'\'{/b} ';
		$string .= 'with ssl mode {b}\''.$this->db_ssl.'\'{/b}{br}';
		$string .= '{b}-DB-info->{/b} DB IO Class debug output: {b}'.($this->db_debug ? 'Yes' : 'No').'{/b}';
		if ($show === true) {
			// if debug, remove / change b
			$this->__dbDebug('db', str_replace(['{b}', '{/b}', '{br}'], ['', '', ' **** '], $string), 'dbInfo');
		} else {
			$string = $string.'{br}';
		}
		// for direct print, change to html
		return str_replace(['{b}', '{/b}', '{br}'], ['<b>', '</b>', '<br>'], $string);
	}

	/**
	 * dumps ALL data for this query, OR if no query given all in cursor_ext array
	 * @param  string $query query, if given, only from this quey (if found), else current cursor
	 * @return string        formated string with all the data in the array
	 */
	public function dbDumpData($query = ''): string
	{
		// set start array
		if ($query) {
			$array = $this->cursor_ext[md5($query)];
		} else {
			$array = $this->cursor_ext;
		}
		$string = '';
		if (is_array($array)) {
			$this->nbsp = '';
			$string .= $this->__printArray($array);
			$this->__dbDebug('db', $string, 'dbDumpData');
		}
		return $string;
	}

	/**
	 * single running function, if called creates md5 from
	 * query string and so can itself call exec/return calls
	 * caches data, so next time called with IDENTICAL (!!!!)
	 * [this means 1:1 bit to bit identical query] returns cached
	 * data, or with reset flag set calls data from DB again
	 * NOTE on $reset param:
	 * - if set to 1, at the end of the query (last row returned),
	 *   the stored array will be deleted ...
	 * - if set to 2, the data will be read new and cached
	 *   (wheres 1 reads cache AND destroys at end of read)
	 * - if set to 3, after EACH row, the data will be reset,
	 *   no caching is done except for basic (count, etc)
	 * @param  string     $query Query string
	 * @param  int        $reset reset status: 1: read cache, clean at the end, 2: read new, clean at end, 3: never cache
	 * @param  bool       $assoc_only true to only returned the named and not index position ones
	 * @return array|bool             return array data or false on error/end
	 * @suppress PhanTypeMismatchDimFetch
	 */
	public function dbReturn(string $query, int $reset = 0, bool $assoc_only = false)
	{
		if (!$query) {
			$this->error_id = 11;
			$this->__dbError();
			return false;
		}
		// create MD5 from query ...
		$md5 = md5($query);
		// pre declare array
		if (!isset($this->cursor_ext[$md5])) {
			$this->cursor_ext[$md5] = [
				'query' => '',
				'pos' => 0,
				'cursor' => 0,
				'firstcall' => 0,
				'num_rows' => 0,
				'num_fields' => 0,
				'read_rows' => 0
			];
		}
		// set the query
		$this->cursor_ext[$md5]['query'] = $query;
		// before doing ANYTHING check if query is "SELECT ..." everything else does not work
		if (!$this->__checkQueryForSelect($this->cursor_ext[$md5]['query'])) {
			$this->error_id = 17;
			$this->__dbError(false, $this->cursor_ext[$md5]['query']);
			return false;
		}
		// init return als false
		$return = false;
		// if it is a call with reset in it we reset the cursor, so we get an uncached return
		// but only for the FIRST call (pos == 0)
		if ($reset && !$this->cursor_ext[$md5]['pos']) {
			$this->cursor_ext[$md5]['cursor'] = null;
		}
		// $this->debug('MENU', 'Reset: '.$reset.', Cursor: '.$this->cursor_ext[$md5]['cursor'].', Pos: '.$this->cursor_ext[$md5]['pos'].', Query: '.$query);

		// if no cursor yet, execute
		if (!$this->cursor_ext[$md5]['cursor']) {
			// for DEBUG, print out each query executed
			if ($this->db_debug) {
				$this->__dbDebug('db', $this->cursor_ext[$md5]['query'], 'dbReturn', 'Q');
			}
			// if no DB Handler try to reconnect
			if (!$this->dbh) {
				// if reconnect fails drop out
				if (!$this->__connectToDB()) {
					$this->error_id = 16;
					$this->__dbError();
					return false;
				}
			}
			// check that no other query is running right now
			if ($this->db_functions->__dbConnectionBusy()) {
				$this->error_id = 41;
				$this->__dbError();
				return false;
			}
			$this->cursor_ext[$md5]['cursor'] = $this->db_functions->__dbQuery($this->cursor_ext[$md5]['query']);
			// if still no cursor ...
			if (!$this->cursor_ext[$md5]['cursor']) {
				if ($this->db_debug) {
					$this->__dbDebug('db', $this->cursor_ext[$md5]['query'], 'dbReturn', 'Q');
				}
				// internal error handling
				$this->error_id = 13;
				$this->__dbError($this->cursor_ext[$md5]['cursor']);
				return false;
			} else {
				$this->cursor_ext[$md5]['firstcall'] = 1;
			}
		} // only go if NO cursor exists

		// if cursor exists ...
		if ($this->cursor_ext[$md5]['cursor']) {
			if ($this->cursor_ext[$md5]['firstcall'] == 1) {
				// count the rows returned (if select)
				$this->cursor_ext[$md5]['num_rows'] = $this->db_functions->__dbNumRows($this->cursor_ext[$md5]['cursor']);
				// count the fields
				$this->cursor_ext[$md5]['num_fields'] = $this->db_functions->__dbNumFields($this->cursor_ext[$md5]['cursor']);
				// set field names
				for ($i = 0; $i < $this->cursor_ext[$md5]['num_fields']; $i ++) {
					$this->cursor_ext[$md5]['field_names'][] = $this->db_functions->__dbFieldName($this->cursor_ext[$md5]['cursor'], $i);
				}
				// reset first call vars
				$this->cursor_ext[$md5]['firstcall'] = 0;
				// reset the internal pos counter
				$this->cursor_ext[$md5]['pos'] = 0;
				// reset the global (for cache) read counter
				$this->cursor_ext[$md5]['read_rows'] = 0;
			}
			// read data for further work ... but only if necessarry
			if ($this->cursor_ext[$md5]['read_rows'] == $this->cursor_ext[$md5]['num_rows']) {
				$return = false;
			} else {
				$return = $this->__dbConvertEncoding(
					$this->db_functions->__dbFetchArray(
						$this->cursor_ext[$md5]['cursor'],
						$this->db_functions->__dbResultType($assoc_only)
					)
				);
				// if returned is NOT an array, abort to false
				if (!is_array($return)) {
					$return = false;
				}
			}
			// check if cached call or reset call ...
			if (!$return && !$reset) {
				// check if end of output ...
				if ($this->cursor_ext[$md5]['pos'] >= $this->cursor_ext[$md5]['num_rows']) {
					$this->cursor_ext[$md5]['pos'] = 0;
					# if not reset given, set the cursor to true, so in a cached call on a different page we don't get problems from DB connection (as those will be LOST)
					$this->cursor_ext[$md5]['cursor'] = 1;
					$return = false;
				} else {
					// unset return value ...
					$return = [];
					for ($i = 0; $i < $this->cursor_ext[$md5]['num_fields']; $i ++) {
						// create mixed return array
						if ($assoc_only === false && isset($this->cursor_ext[$md5]['data'][$this->cursor_ext[$md5]['pos']][$i])) {
							$return[$i] = $this->cursor_ext[$md5]['data'][$this->cursor_ext[$md5]['pos']][$i];
						}
						// named part
						if (isset($this->cursor_ext[$md5]['data'][$this->cursor_ext[$md5]['pos']][$i])) {
							$return[$this->cursor_ext[$md5]['field_names'][$i]] = $this->cursor_ext[$md5]['data'][$this->cursor_ext[$md5]['pos']][$i];
						} else {
							// throws PhanTypeMismatchDimFetch error, but in this case we know we will access only named array parts
							// @suppress PhanTypeMismatchDimFetch
							$return[$this->cursor_ext[$md5]['field_names'][$i]] = $this->cursor_ext[$md5]['data'][$this->cursor_ext[$md5]['pos']][$this->cursor_ext[$md5]['field_names'][$i]];
						}
					}
					$this->cursor_ext[$md5]['pos'] ++;
				}
			} else {
				// return row, if last && reset, then unset the hole md5 array
				if (!$return && ($reset == 1 || $reset == 3) && $this->cursor_ext[$md5]['pos']) {
					// unset only the field names here of course
					$this->cursor_ext[$md5]['field_names'] = null;
					$this->cursor_ext[$md5]['pos'] = 0;
				} elseif (!$return && $reset == 2 && $this->cursor_ext[$md5]['pos']) {
					// at end of read reset pos & set cursor to 1 (so it does not get lost in session transfer)
					$this->cursor_ext[$md5]['pos'] = 0;
					$this->cursor_ext[$md5]['cursor'] = 1;
					$return = false;
				}
				// if something found, write data into hash array
				if ($return) {
					// internal position counter
					$this->cursor_ext[$md5]['pos'] ++;
					$this->cursor_ext[$md5]['read_rows'] ++;
					// if reset is <3 caching is done, else no
					if ($reset < 3) {
						$temp = [];
						foreach ($return as $field_name => $data) {
							$temp[$field_name] = $data;
						}
						$this->cursor_ext[$md5]['data'][] = $temp;
					}
				} // cached data if
			} // cached or not if
		} // cursor exists
		return $return;
	}

	/**
	 * resets all data stored to this query
	 * @param  string $query The Query whose cache should be cleaned
	 * @return bool          false if query not found, true if success
	 */
	public function dbCacheReset(string $query): bool
	{
		$md5 = md5($query);
		// clears cache for this query
		if (!$this->cursor_ext[$md5]['query']) {
			$this->error_id = 18;
			$this->__dbError();
			return false;
		}
		unset($this->cursor_ext[$md5]);
		return true;
	}

	/**
	 * executes the query and returns & sets the internal cursor
	 * furthermore this functions also sets varios other vars
	 * like num_rows, num_fields, etc depending on query
	 * for INSERT INTO queries it is highly recommended to set the pk_name to avoid an
	 * additional read from the database for the PK NAME
	 * @param  string         $query   the query, if not given, the query class var will be used
	 *                                 (if this was not set, method will quit with a 0 (failure)
	 * @param  string         $pk_name optional primary key name, for insert id return if the pk name is very different
	 *                                 if pk name is table name and _id, pk_name is not needed to be set
	 *                                 if NULL is given here, no RETURNING will be auto added
	 * @return resource|false          cursor for this query or false on error
	 */
	public function dbExec(string $query = '', string $pk_name = '')
	{
		// prepare and check if we can actually run it
		if (($md5 = $this->__dbPrepareExec($query, $pk_name)) === false) {
			// bail if no md5 set
			return false;
		}
		// ** actual db exec call
		$this->cursor = $this->db_functions->__dbQuery($this->query);
		// if FALSE returned, set error stuff
		// run the post exec processing
		if (!$this->__dbPostExec()) {
			return false;
		} else {
			return $this->cursor;
		}
	}

	/**
	 * executres the query async so other methods can be run during this
	 * for INSERT INTO queries it is highly recommended to set the pk_name to avoid an additional
	 * read from the database for the PK NAME
	 * NEEDS : dbCheckAsync
	 * @param  string $query   query to run
	 * @param  string $pk_name optional primary key name, only used with insert for returning call
	 * @return bool            true if async query was sent ok, false if error happened
	 */
	public function dbExecAsync(string $query, string $pk_name = ''): bool
	{
		// prepare and check if we can actually run the query
		if (($md5 = $this->__dbPrepareExec($query, $pk_name)) === false) {
			// bail if no md5 set
			return false;
		}
		// run the async query
		if (!$this->db_functions->__dbSendQuery($this->query)) {
			// if failed, process here
			$this->error_id = 40;
			$this->__dbError();
			return false;
		} else {
			$this->async_running = $md5;
			// all ok, we return true (as would be from the original send query function)
			return true;
		}
	}

	/**
	 * checks a previous async query and returns data if finished
	 * NEEDS : dbExecAsync
	 * @return bool true if the query is still running, false if an error occured or cursor of that query
	 */
	public function dbCheckAsync(): bool
	{
		// if there is actually a async query there
		if ($this->async_running) {
			if ($this->db_functions->__dbConnectionBusy()) {
				return true;
			} else {
				// get the result/or error
				$this->cursor = $this->db_functions->__dbGetResult();
				$this->async_running = '';
				// run the post exec processing
				if (!$this->__dbPostExec()) {
					return false;
				} else {
					return $this->cursor;
				}
			}
		} else {
			// if no async running print error
			$this->error_id = 42;
			$this->__dbDebug('db', '<span style="color: red;"><b>DB-Error</b> No async query has been started yet.</span>', 'DB_ERROR');
			return false;
		}
	}

	/**
	 * executes a cursor and returns the data, if no more data 0 will be returned
	 * @param  resource|false $cursor     the cursor from db_exec or pg_query/pg_exec/mysql_query
	 *                                    if not set will use internal cursor, if not found, stops with 0 (error)
	 * @param  bool           $assoc_only false is default, if true only assoc rows
	 * @return array|bool                 row array or false on error
	 */
	public function dbFetchArray($cursor = false, bool $assoc_only = false)
	{
		// return false if no query or cursor set ...
		if (!$cursor) {
			$cursor = $this->cursor;
		}
		if (!$cursor) {
			$this->error_id = 12;
			$this->__dbError();
			return false;
		}
		return $this->__dbConvertEncoding(
			$this->db_functions->__dbFetchArray(
				$cursor,
				$this->db_functions->__dbResultType($assoc_only)
			)
		);
	}

	/**
	 * returns the FIRST row of the given query
	 * @param  string     $query      the query to be executed
	 * @param  bool       $assoc_only if true, only return assoc entry, else both (pgsql)
	 * @return array|bool             row array or false on error
	 */
	public function dbReturnRow(string $query, bool $assoc_only = false)
	{
		if (!$query) {
			$this->error_id = 11;
			$this->__dbError();
			return false;
		}
		// before doing ANYTHING check if query is "SELECT ..." everything else does not work
		if (!$this->__checkQueryForSelect($query)) {
			$this->error_id = 17;
			$this->__dbError(false, $query);
			return false;
		}
		$cursor = $this->dbExec($query);
		$result = $this->dbFetchArray($cursor, $assoc_only);
		return $result;
	}

	/**
	 * createds an array of hashes of the query (all data)
	 * @param  string     $query      the query to be executed
	 * @param  bool       $assoc_only if true, only name ref are returned
	 * @return array|bool             array of hashes (row -> fields), false on error
	 */
	public function dbReturnArray(string $query, bool $assoc_only = false)
	{
		if (!$query) {
			$this->error_id = 11;
			$this->__dbError();
			return false;
		}
		// before doing ANYTHING check if query is "SELECT ..." everything else does not work
		if (!$this->__checkQueryForSelect($query)) {
			$this->error_id = 17;
			$this->__dbError(false, $query);
			return false;
		}
		$cursor = $this->dbExec($query);
		$rows = [];
		while ($res = $this->dbFetchArray($cursor, $assoc_only)) {
			$data = [];
			for ($i = 0; $i < $this->num_fields; $i ++) {
				$data[$this->field_names[$i]] = $res[$this->field_names[$i]];
			}
			$rows[] = $data;
		}
		return $rows;
	}

	/**
	 * returns the current position the read out
	 * @param  string   $query query to find in cursor_ext
	 * @return int|bool        query position (row pos), false on error
	 */
	public function dbCursorPos(string $query)
	{
		if (!$query) {
			$this->error_id = 11;
			$this->__dbError();
			return false;
		}
		$md5 = md5($query);
		return (int)$this->cursor_ext[$md5]['pos'];
	}

	/**
	 * returns the number of rows for the current select query
	 * @param  string   $query query to find in cursor_ext
	 * @return int|bool        query position (row pos), false on error
	 */
	public function dbCursorNumRows(string $query)
	{
		if (!$query) {
			$this->error_id = 11;
			$this->__dbError();
			return false;
		}
		$md5 = md5($query);
		return (int)$this->cursor_ext[$md5]['num_rows'];
	}

	/**
	 * returns an array of the table with columns and values. FALSE on no table found
	 * @param  string     $table  table name
	 * @param  string     $schema optional schema name
	 * @return array|bool         array of table data, false on error (table not found)
	 */
	public function dbShowTableMetaData(string $table, string $schema = '')
	{
		$table = ($schema ? $schema.'.' : '').$table;

		$array = $this->db_functions->__dbMetaData($table);
		if (!is_array($array)) {
			$array = false;
		}
		return $array;
	}

	/**
	 * prepares a query
	 * for INSERT INTO queries it is highly recommended to set the pk_name to avoid an additional
	 * read from the database for the PK NAME
	 * @param  string        $stm_name statement name
	 * @param  string        $query    queryt string to run
	 * @param  string        $pk_name  optional primary key
	 * @return bool|resource           false on error, true on warning or result on full ok
	 */
	public function dbPrepare(string $stm_name, string $query, string $pk_name = '')
	{
		$matches = [];
		if (!$query) {
			$this->error_id = 11;
			$this->__dbError();
			return false;
		}
		// if no DB Handler drop out
		if (!$this->dbh) {
			// if reconnect fails drop out
			if (!$this->__connectToDB()) {
				$this->error_id = 16;
				$this->__dbError();
				return false;
			}
		}
		// check that no other query is running right now
		if ($this->db_functions->__dbConnectionBusy()) {
			$this->error_id = 41;
			$this->__dbError();
			return false;
		}
		// check if this was already prepared
		if (!array_key_exists($stm_name, $this->prepare_cursor) || !is_array($this->prepare_cursor[$stm_name])) {
			// if this is an insert query, check if we can add a return
			if ($this->__checkQueryForInsert($query, true)) {
				if ($pk_name != 'NULL') {
					// set primary key name
					// current: only via parameter
					if (!$pk_name) {
						// read the primary key from the table, if we do not have one, we get nothing in return
						list($schema, $table) = $this->__dbReturnTable($query);
						if (empty($this->pk_name_table[$table])) {
							$this->pk_name_table[$table] = $this->db_functions->__dbPrimaryKey($table, $schema);
						}
						$pk_name = $this->pk_name_table[$table];
					}
					if ($pk_name) {
						$this->prepare_cursor[$stm_name]['pk_name'] = $pk_name;
					}
					// if no returning, then add it
					if (!preg_match("/ returning /i", $query) && $this->prepare_cursor[$stm_name]['pk_name']) {
						$query .= " RETURNING ".$this->prepare_cursor[$stm_name]['pk_name'];
						$this->prepare_cursor[$stm_name]['returning_id'] = true;
					} elseif (preg_match("/ returning (.*)/i", $query, $matches) && $this->prepare_cursor[$stm_name]['pk_name']) {
						// if returning exists but not pk_name, add it
						if (!preg_match("/{$this->prepare_cursor[$stm_name]['pk_name']}/", $matches[1])) {
							$query .= " , ".$this->prepare_cursor[$stm_name]['pk_name'];
						}
						$this->prepare_cursor[$stm_name]['returning_id'] = true;
					}
				} else {
					$this->prepare_cursor[$stm_name]['pk_name'] = $pk_name;
				}
			}
			$match = [];
			// search for $1, $2, in the query and push it into the control array
			// skip counts for same eg $1, $1, $2 = 2 and not 3
			preg_match_all('/(\$[0-9]{1,})/', $query, $match);
			$this->prepare_cursor[$stm_name]['count'] = count(array_unique($match[1]));
			$this->prepare_cursor[$stm_name]['query'] = $query;
			$result = $this->db_functions->__dbPrepare($stm_name, $query);
			if ($result) {
				$this->prepare_cursor[$stm_name]['result'] = $result;
				return $result;
			} else {
				$this->error_id = 21;
				$this->__dbError();
				$this->__dbDebug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': Prepare field with: '.$stm_name.' | '.$query.'</span>', 'DB_ERROR');
				return $result;
			}
		} else {
			// thrown warning
			$this->warning_id = 20;
			return true;
		}
	}

	/**
	 * runs a prepare query
	 * @param  string        $stm_name statement name for the query to run
	 * @param  array         $data     data to run for this query, empty array for none
	 * @return ?mixed                  false on error, or result on OK
	 */
	public function dbExecute(string $stm_name, array $data = [])
	{
		// if we do not have no prepare cursor array entry for this statement name, abort
		if (!is_array($this->prepare_cursor[$stm_name])) {
			$this->error_id = 24;
			$this->__dbDebug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': We do not have a prepared query entry for this statement name.</span>', 'DB_ERROR');
			return false;
		}
		if (!is_array($data)) {
			$this->error_id = 25;
			$this->__dbDebug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': Prepared query Data has to be given in array form.</span>', 'DB_ERROR');
			return false;
		}
		if ($this->prepare_cursor[$stm_name]['count'] != count($data)) {
			$this->error_id = 23;
			$this->__dbDebug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': Array data count does not match prepared fields. Need: '.$this->prepare_cursor[$stm_name]['count'].', has: '.count($data).'</span>', 'DB_ERROR');
			return false;
		}
		if ($this->db_debug) {
			$this->__dbDebug('db', $this->__dbDebugPrepare($stm_name, $data), 'dbExecPrep', 'Q');
		}
		$result = $this->db_functions->__dbExecute($stm_name, $data);
		if (!$result) {
			$this->log->debug('ExecuteData', 'ERROR in STM['.$stm_name.'|'.$this->prepare_cursor[$stm_name]['result'].']: '.$this->log->prAr($data));
			$this->error_id = 22;
			$this->__dbError($this->prepare_cursor[$stm_name]['result']);
			$this->__dbDebug('db', '<span style="color: red;"><b>DB-Error</b> '.$stm_name.': Execution failed</span>', 'DB_ERROR');
			return false;
		}
		if ($this->__checkQueryForInsert($this->prepare_cursor[$stm_name]['query'], true) &&
			$this->prepare_cursor[$stm_name]['pk_name'] != 'NULL'
		) {
			if (!$this->prepare_cursor[$stm_name]['returning_id']) {
				$this->insert_id = $this->db_functions->__dbInsertId($this->prepare_cursor[$stm_name]['query'], $this->prepare_cursor[$stm_name]['pk_name']);
				$this->insert_id_ext = $this->insert_id;
				$this->insert_id_arr[] = $this->insert_id;
			} elseif ($result) {
				$this->insert_id = [];
				$this->insert_id_ext = [];
				$this->insert_id_arr = [];
				// we have returning, now we need to check if we get one or many returned
				// we'll need to loop this, if we have multiple insert_id returns
				while ($_insert_id = $this->db_functions->__dbFetchArray(
					$result,
					$this->db_functions->__dbResultType(true)
				)) {
					$this->insert_id[] = $_insert_id;
					$this->insert_id_arr[] = $_insert_id;
				}
				// if we have only one, revert from arry to single
				if (count($this->insert_id) == 1) {
					// echo "+ SINGLE DATA CONVERT: ".count($this->insert_id[0])." => ".array_key_exists($this->prepare_cursor[$stm_name]['pk_name'], $this->insert_id[0])."<br>";
					// echo "+ PK DIRECT: ".$this->insert_id[0][$this->prepare_cursor[$stm_name]['pk_name']]."<Br>";
					// if this has only the pk_name, then only return this, else array of all data (but without the position)
					// example if insert_id[0]['foo'] && insert_id[0]['bar'] it will become insert_id['foo'] & insert_id['bar']
					// if only ['foo_id'] and it is the PK then the PK is directly written to the insert_id
					if (count($this->insert_id[0]) > 1 ||
						!array_key_exists($this->prepare_cursor[$stm_name]['pk_name'], $this->insert_id[0])
					) {
						$this->insert_id_ext = $this->insert_id[0];
						$this->insert_id = $this->insert_id[0][$this->prepare_cursor[$stm_name]['pk_name']];
					} elseif ($this->insert_id[0][$this->prepare_cursor[$stm_name]['pk_name']]) {
						$this->insert_id = $this->insert_id[0][$this->prepare_cursor[$stm_name]['pk_name']];
					}
				} elseif (count($this->insert_id) == 0) {
					// failed to get insert id
					$this->insert_id = '';
					$this->warning_id = 33;
					$this->__dbError();
					$this->__dbDebug('db', '<span style="color: orange;"><b>DB-Warning</b> '.$stm_name.': insert id returned no data</span>', 'DB_WARNING');
				}
			}
			// this error handling is only for pgsql
			if (is_array($this->insert_id)) {
				$this->warning_id = 32;
				$this->__dbError();
				$this->__dbDebug('db', '<span style="color: orange;"><b>DB-Warning</b> '.$stm_name.': insert id data returned as array</span>', 'DB_WARNING');
			} elseif (!$this->insert_id) {
				// NOTE should we keep this inside
				$this->warning_id = 31;
				$this->__dbError();
				$this->__dbDebug('db', '<span style="color: orange;"><b>DB-Warning</b> '.$stm_name.': Could not get insert id</span>', 'DB_WARNING');
			}
		}
		return $result;
	}

	/**
	 * neutral function to escape a string for DB writing
	 * @param  string|int|float|bool $string string to escape
	 * @return string                        escaped string
	 */
	public function dbEscapeString($string): string
	{
		return $this->db_functions->__dbEscapeString($string);
	}

	/**
	 * neutral function to escape a string for DB writing
	 * this one adds '' quotes around the string
	 * @param  string|int|float|bool $string string to escape
	 * @return string                        escaped string
	 */
	public function dbEscapeLiteral($string): string
	{
		return $this->db_functions->__dbEscapeLiteral($string);
	}

	/**
	 * neutral function to escape a bytea for DB writing
	 * @param  string $bytea bytea to escape
	 * @return string        escaped bytea
	 */
	public function dbEscapeBytea($bytea)
	{
		return $this->db_functions->__dbEscapeBytea($bytea);
	}

	/**
	 * return current database version
	 * @return string database version as string
	 */
	public function dbVersion(): string
	{
		return $this->db_functions->__dbVersion();
	}

	/**
	 * returns boolean true or false if the string matches the database version
	 * @param  string $compare string to match in type =X.Y, >X.Y, <X.Y, <=X.Y, >=X.Y
	 * @return bool            true for ok, false on not ok
	 */
	public function dbCompareVersion(string $compare): bool
	{
		$matches = [];
		// compare has =, >, < prefix, and gets stripped, if the rest is not X.Y format then error
		preg_match("/^([<>=]{1,})(\d{1,})\.(\d{1,})/", $compare, $matches);
		$compare = $matches[1];
		$to_master = $matches[2];
		$to_minor = $matches[3];
		if (!$compare || !$to_master || !$to_minor) {
			return false;
		} else {
			$to_version = $to_master.($to_minor < 10 ? '0' : '').$to_minor;
		}
		// db_version can return X.Y.Z
		// we only compare the first two
		preg_match("/^(\d{1,})\.(\d{1,})\.?(\d{1,})?/", $this->dbVersion(), $matches);
		$master = $matches[1];
		$minor = $matches[2];
		$version = $master.($minor < 10 ? '0' : '').$minor;
		$return = false;
		// compare
		switch ($compare) {
			case '=':
				if ($to_version == $version) {
					$return = true;
				}
				break;
			case '<':
				if ($version < $to_version) {
					$return = true;
				}
				break;
			case '<=':
				if ($version <= $to_version) {
					$return = true;
				}
				break;
			case '>':
				if ($version > $to_version) {
					$return = true;
				}
				break;
			case '>=':
				if ($version >= $to_version) {
					$return = true;
				}
				break;
			default:
				$return = false;
		}
		return $return;
	}

	/**
	 * if the input is a single char 't' or 'f' it will return the boolean value instead
	 * @param  string|bool  $string 't' / 'f' or any string, or bool true/false
	 * @param  boolean      $rev    do reverse (bool to string)
	 * @return bool|string          correct php boolean true/false or postgresql 't'/'f'
	 */
	public function dbBoolean($string, $rev = false)
	{
		if (!$rev) {
			if ($string == 't' || $string == 'true') {
				return true;
			}
			if ($string == 'f' || $string == 'false') {
				return false;
			}
			// fallback in case top is not t/f, default on set unset
			if ($string) {
				return true;
			} else {
				return false;
			}
		} else {
			if ($string) {
				return 't';
			} else {
				return 'f';
			}
		}
		// if neither, just return data as is
		return $string;
	}

	// ** REMARK **
	// db_write_data is the old without separate update no write list
	// db_write_data_ext is the extended with additional array for no write list for update

	/**
	 * writes into one table based on array of table columns
	 * @param  array    $write_array     list of elements to write
	 * @param  array    $not_write_array list of elements not to write
	 * @param  int      $primary_key     id key to decide if we write insert or update
	 * @param  string   $table           name for the target table
	 * @param  array    $data            data array to override _POST data
	 * @return int|bool                  primary key
	 */
	public function dbWriteData(array $write_array, array $not_write_array, $primary_key, string $table, $data = [])
	{
		if (!is_array($write_array)) {
			$write_array = [];
		}
		if (!is_array($not_write_array)) {
			$not_write_array = [];
		}
		if (is_array($table)) {
			return false;
		}
		$not_write_update_array = [];
		return $this->dbWriteDataExt($write_array, $primary_key, $table, $not_write_array, $not_write_update_array, $data);
	}

	/**
	 * writes into one table based on array of table columns
	 * PARAM INFO: $primary key
	 * this can be a plain string/int and will be internal transformed into the array form
	 * or it takes the array form of array [row => column, value => pk value]
	 * @param  array            $write_array            list of elements to write
	 * @param  int|string|array $primary_key            primary key string or array set
	 * @param  string           $table                  name for the target table
	 * @param  array            $not_write_array        list of elements not to write (optional)
	 * @param  array            $not_write_update_array list of elements not to write during update (optional)
	 * @param  array            $data                   optional array with data, if not _POST vars are used
	 * @return int|bool                          primary key
	 */
	public function dbWriteDataExt(
		array $write_array,
		$primary_key,
		string $table,
		array $not_write_array = [],
		array $not_write_update_array = [],
		array $data = []
	) {
		if (!is_array($primary_key)) {
			$primary_key = [
				'row' => $table.'_id',
				'value' => $primary_key
			];
		} else {
			if (!isset($primary_key['row'])) {
				$primary_key['row'] = '';
			}
			if (!isset($primary_key['value'])) {
				$primary_key['value'] = '';
			}
		}
		// var set for strings
		$q_sub_value = '';
		$q_sub_data = '';
		// get the table layout and row types
		$table_data = $this->dbShowTableMetaData(($this->db_schema ? $this->db_schema.'.' : '').$table);
		// @phan HACK
		$primary_key['value'] = $primary_key['value'] ?? '';
		$primary_key['row'] = $primary_key['row'] ?? '';
		// loop through the write array and each field to build the query
		foreach ($write_array as $field) {
			if ((!$primary_key['value'] ||
					($primary_key['value'] &&
					!in_array($field, $not_write_update_array))
				) &&
				!in_array($field, $not_write_array)
			) {
				// data from external or data field
				$_data = null;
				if (count($data) >= 1 && array_key_exists($field, $data)) {
					$_data = $data[$field];
				} elseif (array_key_exists($field, $GLOBALS)) {
					$_data = $GLOBALS[$field];
				}
				$has_default = $table_data[$field]['has default'];
				$not_null = $table_data[$field]['not null'];
				// if not null and string => '', if not null and int or numeric => 0, if bool => skip, all others skip
				if ($not_null && $_data == null) {
					if (strstr($table_data[$field]['type'], 'int') || strstr($table_data[$field]['type'], 'numeric')) {
						$_data = 0;
					} else {
						$_data = '';
					}
				}
				// we detect bool, so we can force a write on "false"
				$is_bool = $table_data[$field]['type'] == 'bool' ? true : false;
				// write if the field has to be not null, or if there is no data and the field has no default values or if there is data or if this is an update and there is no data (set null)
				if (($not_null && $_data) ||
					(!$has_default && !$_data) ||
					(is_numeric($_data) && $_data) ||
					($primary_key['value'] && !$_data) ||
					$_data
				) {
					if ($q_sub_value && !$primary_key['value']) {
						$q_sub_value .= ', ';
					}
					if ($q_sub_data) { // && (!$primary_key || ($primary_key && !in_array($field, $not_write_array))))
						$q_sub_data .= ', ';
					}
					if ($primary_key['value']) {
						$q_sub_data .= $field.' = ';
					} else {
						$q_sub_value .= $field;
					}
					// if field is "date" and -- -> reset
					if ($_data == '--' && strstr($table_data[$field]['type'], 'date')) {
						$_data = '';
					}
					// write data into sql string
					if (strstr($table_data[$field]['type'], 'int')) {
						$q_sub_data .= is_numeric($_data) ? $_data : 'NULL';
					} else {
						// if bool -> set bool, else write data
						$q_sub_data .= isset($_data) ? "'".($is_bool ? $this->dbBoolean($_data, true) : $this->dbEscapeString($_data))."'" : 'NULL';
					}
				}
			}
		}

		// first work contact itself (we need contact id for everything else)
		if ($primary_key['value'] && $primary_key['row']) {
			$q = 'UPDATE '.$table.' SET ';
			$q .= $q_sub_data.' ';
			$q .= 'WHERE '.$primary_key['row'].' = '.$primary_key['value'];
			$this->temp_sql = $q_sub_data;
		} else {
			$q = 'INSERT INTO '.$table.' (';
			$q .= $q_sub_value;
			$q .= ') VALUES (';
			$q .= $q_sub_data;
			$q .= ')';
			$this->temp_sql = $q;
		}
		if (!$this->dbExec($q)) {
			return false;
		}
		if (!$primary_key['value']) {
			$primary_key['value'] = $this->insert_id;
		}
		// if there is not priamry key value field return false
		return $primary_key['value'] ?? false;
	}

	/**
	 * only for postgres. pretty formats an age or datetime difference string
	 * @param  string  $age        age or datetime difference
	 * @param  bool    $show_micro micro on off (default false)
	 * @return string              Y/M/D/h/m/s formatted string (like TimeStringFormat)
	 */
	public function dbTimeFormat(string $age, bool $show_micro = false): string
	{
		$matches = [];
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

	/**
	 * this is only needed for Postgresql. Converts postgresql arrays to PHP
	 * @param  string $text input text to parse to an array
	 * @return array        PHP array of the parsed data
	 */
	public function dbArrayParse(string $text): array
	{
		$output = [];
		return $this->db_functions->__dbArrayParse($text, $output);
	}

	/**
	 * clear up any data for valid DB insert
	 * @param  int|float|string $value to escape data
	 * @param  string           $kbn   escape trigger type
	 * @return string                  escaped value
	 */
	public function dbSqlEscape($value, string $kbn = '')
	{
		switch ($kbn) {
			case 'i':
				$value = $value === '' ? "NULL" : intval($value);
				break;
			case 'f':
				$value = $value === '' ? "NULL" : floatval($value);
				break;
			case 't':
				$value = $value === '' ? "NULL" : "'".$this->dbEscapeString($value)."'";
				break;
			case 'd':
				$value = $value === '' ? "NULL" : "'".$this->dbEscapeString($value)."'";
				break;
			case 'i2':
				$value = $value === '' ? 0 : intval($value);
				break;
		}
		return $value;
	}

	// ***************************
	// INTERNAL VARIABLES READ
	// ***************************

	/**
	 * return current set insert_id as is
	 * @return array|string|int|null Primary key value, most likely int
	 *                               Array for multiple return set
	 *                               Empty string for unset
	 *                               Null for error
	 */
	public function dbGetReturning()
	{
		return $this->insert_id;
	}

	/**
	 * alternative name, returns insert_id
	 * @return array|string|int|null Primary key value, most likely int
	 *                               Array for multiple return set
	 *                               Empty string for unset
	 *                               Null for error
	 */
	public function dbGetInsertPK()
	{
		return $this->dbGetReturning();
	}

	/**
	 * return the extended insert return string set
	 * Most likely Array
	 * @param  string|null       $key Optional key for insert_id_ext array
	 *                                if found will return only this element,
	 *                                else will return null
	 * @return array|string|null      RETURNING values as array
	 *                                Empty string for unset
	 *                                Null for error
	 */
	public function dbGetReturningExt($key = null)
	{
		if ($key !== null) {
			if (isset($this->insert_id_ext[$key])) {
				return $this->insert_id_ext[$key];
			} else {
				return null;
			}
		}
		return $this->insert_id_ext;
	}

	/**
	 * Always returns the returning block as an array
	 * @return array All returning data as array. even if one row only
	 */
	public function dbGetReturningArray(): array
	{
		return $this->insert_id_arr;
	}

	/**
	 * returns the full array for cursor ext
	 * @param  string|null $q Query string, if not null convert to md5
	 *                        and return set cursor ext for only this
	 *                        if not found or null return null
	 * @return array|null     Cursor Extended array
	 *                        Key is md5 string from query run
	 */
	public function dbGetCursorExt($q = null)
	{
		if ($q !== null) {
			$q_md5 = md5($q);
			if (isset($this->cursor_ext[$q_md5])) {
				return $this->cursor_ext[$q_md5];
			} else {
				return null;
			}
		}
		return $this->cursor_ext;
	}

	/**
	 * returns current number of rows that where
	 * affected by UPDATE/SELECT, etc
	 * null on empty
	 * @return int|null Number of rows
	 */
	public function dbGetNumRows()
	{
		return $this->num_rows ?? null;
	}

	// DEPEREACTED CALLS

	/**
	 * old call for getInserReturnExt
	 * @param  string|null       $key See above
	 * @return array|string|null      See above
	 * @deprecated use getReturningExt($key = null) instead
	 */
	public function getInsertReturn($key = null)
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use getReturningExt($key = null)', E_USER_DEPRECATED);
		return $this->dbGetReturningExt($key);
	}

	/**
	 * DEPRECATED: getReturning
	 * @return array|string|int|null [DEPRECATED]
	 * @deprecated use dbGetReturning() instead
	 */
	public function getReturning()
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use dbGetReturning()', E_USER_DEPRECATED);
		return $this->dbGetReturning();
	}

	/**
	 * DEPRECATED: getInsertPK
	 * @return array|string|int|null [DEPRECATED]
	 * @deprecated use dbGetInsertPK() instead
	 */
	public function getInsertPK()
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use dbGetInsertPK()', E_USER_DEPRECATED);
		return $this->dbGetReturning();
	}

	/**
	 * DEPRECATED: getReturningExt
	 * @param  string|null $key [DEPRECATED]
	 * @return array|string|null [DEPRECATED]
	 * @deprecated use dbGetReturningExt($key = null) instead
	 */
	public function getReturningExt($key = null)
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use dbGetReturningExt($key = null)', E_USER_DEPRECATED);
		return $this->dbGetReturningExt($key);
	}

	/**
	 * DEPRECATED: getCursorExt
	 * @param  string|null $q [DEPRECATED]
	 * @return array|null [DEPRECATED]
	 * @deprecated use dbGetCursorExt($q = null) instead
	 */
	public function getCursorExt($q = null)
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use dbGetCursorExt($q = null)', E_USER_DEPRECATED);
		return $this->dbGetCursorExt($q);
	}

	/**
	 * DEPRECATED: getNumRows
	 * @return int|null [DEPRECATED]
	 * @deprecated use dbGetNumRows() instead
	 */
	public function getNumRows()
	{
		trigger_error('Method '.__METHOD__.' is deprecated, use dbGetNumRows()', E_USER_DEPRECATED);
		return $this->dbGetNumRows();
	}
} // end if db class

// __END__

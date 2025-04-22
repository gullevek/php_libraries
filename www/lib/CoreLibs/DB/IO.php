<?php

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
*   array inserts in auto calls. bool converter from postresql to php
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
*   $params
*     - array for query parameters, if not set, ignored
*   $num_rows
*     - the number of rows returned by a SELECT or alterd bei UPDATE/INSERT
*   $num_fields
*     - the number of fields from the SELECT, is usefull if you do a SELECT *
*   $field_names
*     - array of field names (in the order of the return)
*   $field_types
*     - array of field types
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
*   $mixed db_return($query,$reset=self::USE_CACHE)
*     - executes query, returns data & caches it (1 = reset/destroy, 2 = reset/cache, 3 = reset/no cache)
*   1/0 db_cache_reset($query)
*     - resets the cache for one query
*   _db_io()
*     - pseudo deconstructor - functionality moved to db_close
*   $string info($show=1)
*     - returns a string various info about class (version, authoer, etc)
*     - if $show set to 0, it will not be appended to the error_msgs string
*   $string db_info($show=1)
*     - returns a string with info about db connection, etc, if $show set to 0,
*     - it will not be appended to the error_msgs string
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
*     - writes into one table based on arrays of columns to write and not write,
*     - reads data from global vars or optional array
*   $boolean db_set_schema(schema)
*     - sets search path to a schema
*   $boolean db_set_encoding(encoding)
*     - sets an encoding for this database output and input
*   $string db_time_format($age/datetime diff, $micro_time = false/true)
*     - returns a nice formatted time string based on a age or datetime difference
*     - (postgres only), micro time is default false
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
*     - wrapper for normal debug, adds prefix data from id & type and strips
*     - all HTML from the query data (color codes, etc) via flag to debug call
*
* HISTORY:
* 2008/10/25 (cs) add db_boolean to fix the postgres to php bool var problem
*                 (TODO: implement this in any select return)
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

declare(strict_types=1);

namespace CoreLibs\DB;

use CoreLibs\Create\Hash;
use CoreLibs\Debug\Support;
use CoreLibs\Create\Uids;
use CoreLibs\Convert\Json;
use CoreLibs\DB\Options\Convert;
use CoreLibs\DB\Support\ConvertPlaceholder;

// below no ignore is needed if we want to use PgSql interface checks with PHP 8.0
// as main system. Currently all @var sets are written as object

class IO
{
	// 0: normal read, store in cache
	// 1: read new, keep at end, clean before new run
	// 2: read new, clean at the end (temporary cache)
	// 3: never cache
	/** @var int use cache (default) in dbReturn */
	public const USE_CACHE = 0;
	/** @var int reset cache and read new in dbReturn */
	public const READ_NEW = 1;
	/** @var int clear cache after read in dbeEturn */
	public const CLEAR_CACHE = 2;
	/** @var int do not use any cache in dbReturn */
	public const NO_CACHE = 3;
	/** @var string default hash type */
	public const ERROR_HASH_TYPE = 'adler32';
	/** @var string regex to get returning with matches at position 1 */
	public const REGEX_RETURNING = '/\s+returning\s+(.+\s*(?:.+\s*)+);?$/i';
	/** @var array<string> allowed convert target for placeholder:
	 * pg or pdo (currently not available) */
	public const DB_CONVERT_PLACEHOLDER_TARGET = ['pg'];
	// REGEX_SELECT
	// REGEX_UPDATE
	// REGEX INSERT
	// REGEX_INSERT_UPDATE_DELETE
	// REGEX_FROM_TABLE
	// REGEX_INSERT_UPDATE_DELETE_TABLE

	// recommend to set private/protected and only allow setting via method
	// can bet set from outside
	// encoding to
	/** @var string */
	private string $to_encoding = '';
	/** @var string the query string at the moment */
	private string $query = '';
	/** @var array<mixed> current params for query */
	private array $params = [];
	/** @var string current hash build from query and params */
	private string $query_hash = '';
	// if we do have a convert call, store the convert data in here, else it will be empty
	/** @var array{}|array{original:array{query:string,params:array<mixed>},type:''|'named'|'numbered'|'question_mark',found:int,matches:array<string>,params_lookup:array<mixed>,query:string,params:array<mixed>} */
	private array $placeholder_converted = [];
	// only inside
	// basic vars
	// the dbh handler, if disconnected by command is null, bool:false on error,
	/** @var \PgSql\Connection|false|null */
	private \PgSql\Connection|false|null $dbh = null;
	/** @var bool DB_DEBUG ... (if set prints out debug msgs) */
	private bool $db_debug = false;
	/** @var string the DB connected to */
	private string $db_name;
	/** @var string the username used */
	private string $db_user;
	/** @var string the password used*/
	private string $db_pwd;
	/** @var string the hostname */
	private string $db_host;
	/** @var int default db port */
	private int $db_port;
	/** @var string optional DB schema, if not set uses public*/
	private string $db_schema;
	/** @var string optional auto encoding convert, not used if not set */
	private string $db_encoding;
	/** @var string type of db (mysql,postgres,...) */
	private string $db_type;
	/** @var string ssl flag (for postgres only), disable, allow, prefer, require */
	private string $db_ssl;
	/** @var array<string> flag for converting types from settings */
	private array $db_convert_type = [];
	/** @var bool convert placeholders from pdo to Pg or the other way around */
	private bool $db_convert_placeholder = false;
	/** @var string convert placeholders target, default is 'pg', other allowed is 'pdo' */
	private string $db_convert_placeholder_target = 'pg';
	/** @var bool Replace the placeholders in a query for debug output, defaults to false */
	private bool $db_debug_replace_placeholder = false;
	// convert type settings
	// 0: OFF (CONVERT_OFF)
	// >0: ON
	// 1: convert int/bool (CONVERT_ON)
	// 2: convert json/jsonb to array (CONVERT_JSON)
	// 4: convert numeric/floatN to float (CONVERT_NUMERIC)
	// 8: convert bytea to string data (CONVERT_BYTEA)
	/** @var int type settings as bit mask, 0 for off, anything >2 will aways set 1 too */
	/** @phan-suppress-next-line PhanInvalidConstantExpression, PhanUndeclaredClassConstant */
	private int $convert_type = Convert::off->value;
	// FOR BELOW: (This should be private and only readable through some method)
	// cursor array for cached readings
	/** @var array<string,mixed> extended cursoers string index with content */
	private array $cursor_ext = [];
	// per query vars
	/** @var \PgSql\Result|false actual cursor (DBH) */
	private \PgSql\Result|false $cursor;
	/** @var int how many rows have been found */
	private int $num_rows;
	/** @var int how many fields has the query */
	private int $num_fields;
	/** @var array<string> array with the field names of the current query */
	private array $field_names = [];
	/** @var array<string> field type names */
	private array $field_types = [];
	/** @var array<string,string> field name to type */
	private array $field_name_types = [];
	/** @var array<mixed> always return as array, even if only one */
	private array $insert_id_arr = [];
	/** @var string primary key name for insert recovery from insert_id_arr */
	private string $insert_id_pk_name;
	// other vars
	/** @var string used by print_array recursion function */
	private string $nbsp = '';
	// error & warning id
	/** @var string */
	private string $error_id;
	/** @var string */
	private string $warning_id;
	/** @var string */
	private string $error_history_id;
	// timestamp:string,level:string,id:string,error:string,source:string,pg_error:string,message:string,context:array<mixed>
	/** @var array<mixed> Stores warning and errors combinded with detail info */
	private array $error_history_long = [];
	/** @var bool error thrown on class init if we cannot connect to db */
	protected bool $db_connection_closed = false;
	// sub include with the database functions
	/** @var \CoreLibs\DB\SQL\PgSQL if we have other DB types we need to add them here */
	private \CoreLibs\DB\SQL\PgSQL $db_functions;
	// endless loop protection
	/** @var int */
	private int $MAX_QUERY_CALL;
	/** @var int maxium query calls allowed in a dbReturnRow loop before we error out */
	public const DEFAULT_MAX_QUERY_CALL = 20;
	/** @var array<mixed> */
	private array $query_called = [];
	// error string
	/** @var array<mixed> */
	protected array $error_string = [];
	// prepared list
	/** @var array<mixed> */
	private array $prepare_cursor = [];
	// primary key per table list
	// format is 'table' => 'pk_name'
	/** @var array<mixed> */
	private array $pk_name_table = [];
	/** @var string internal primary key name, for cross calls in async */
	private string $pk_name = '';
	/** @var bool if we use RETURNING in the INSERT call */
	private bool $returning_id = false;
	/** @var string if a sync is running holds the hash key of the query */
	private string $async_running = '';
	// logging class, must be public so settings can be changed
	/** @var \CoreLibs\Logging\Logging */
	public \CoreLibs\Logging\Logging $log;

	/**
	 * main DB concstructor with auto connection to DB
	 * and failure set on failed connection
	 *
	 * phpcs:ignore
	 * @param array{db_name:string,db_user:string,db_pass:string,db_host:string,db_port:int,db_schema:string,db_encoding:string,db_type:string,db_ssl:string,db_convert_type?:string[],db_convert_placeholder?:bool,db_convert_placeholder_target?:string,db_debug_replace_placeholder?:bool} $db_config DB configuration array
	 * @param \CoreLibs\Logging\Logging $log Logging class
	 * @throws \RuntimeException If no DB connection can be established on launch
	 */
	public function __construct(
		array $db_config,
		\CoreLibs\Logging\Logging $log
	) {
		// attach logger
		$this->log = $log;
		// set the config options
		$this->__setConfigOptions($db_config);
		// set debug, either via global var, or from config, else set to false
		$this->dbSetDebug(
			// set if logging level is Debug
			$this->log->getLoggingLevel()->includes(
				\CoreLibs\Logging\Logger\Level::Debug
			)
		);

		// set loop protection max count
		$this->MAX_QUERY_CALL = self::DEFAULT_MAX_QUERY_CALL;

		// error & debug stuff, error & warning ids are the same, its just in which var they get written
		$this->error_string = [
			'10' => 'Could not load DB interface functions',
			'11' => 'No Querystring given',
			'12' => 'No Cursor given, no correct query perhaps?',
			'13' => 'Query could not be executed without errors',
			'14' => 'No DB Handler found / connect or reconnect failed',
			'15' => 'Cannot select DB or no db name given',
			// '16' => 'No DB Handler found / connect or reconnect failed', // 16 merged into 14
			'17' => 'All dbReturn* methods work only with SELECT statements, '
				. 'please use dbExec for everything else',
			'18' => 'Query not found in cache. Nothing has been reset',
			'19' => 'Wrong PK name given or no PK name given at all, can\'t get Insert ID',
			'20' => 'Query has already been prepared',
			'26' => 'Same prepare statement name has been used for a different query',
			'21' => 'Query Prepare failed',
			'22' => 'Query Execute failed',
			'23' => 'Query Execute failed, data array does not match placeholders',
			'24' => 'Missing prepared query entry for execute.',
			'25' => 'Missing Statement name',
			'30' => 'Query call in a possible endless loop. '
				. 'Was called more than ' . $this->MAX_QUERY_CALL . ' times',
			'31' => 'Could not fetch PK after query insert',
			'32' => 'Multiple PK return as array',
			'33' => 'Returning PK was not found',
			'34' => 'Cursor invalid for fetch PK after query insert',
			'40' => 'Query async call failed.',
			'41' => 'Connection is busy with a different query. Cannot execute.',
			'42' => 'Cannot check for async query, none has been started yet.',
			'43' => 'No Async query result',
			'50' => 'Setting max query call to -1 will disable loop protection '
				. 'for all subsequent runs',
			'51' => 'Max query call needs to be set to at least 1',
			'60' => 'table not found for reading meta data',
			'70' => 'Trying to set an empty search path/schema',
			'71' => 'Failed to set search path/schema',
			'80' => 'Trying to set an empty encoding',
			'81' => 'Failed to set client encoding',
			// for prepared cursor return
			'101' => 'Statement name empty for get prepare cursor',
			'102' => 'Key empty for get prepare cursir',
			'103' => 'No prepared cursor with this name',
			'104' => 'No Key with this name in the prepared cursor array',
			// abort on Placeholder convert
			'200' => 'Cannot have named, question mark or numbered placeholders in the same query',
			'210' => 'Cannot lookup param named in param list',
			'211' => 'Cannot lookup param named in param lookup list',
			'220' => 'Cannot lookup param number in param list',
			'221' => 'Cannot lookup param number in param lookup list',
		];

		// load the core DB functions wrapper class
		if (($db_functions = $this->__loadDBFunctions()) === null) {
			// abort
			die('<!-- Cannot load db functions class for: ' . $this->db_type . ' -->');
		}
		// write to internal one, once OK
		$this->db_functions = $db_functions; /** @phan-suppress-current-line PhanPossiblyNullTypeMismatchProperty */

		// connect to DB
		if (!$this->__connectToDB()) {
			$this->db_connection_closed = true;
			throw new \RuntimeException('INIT: No DB Handler found / connect or reconnect failed', 16);
		}
	}

	/**
	 * final desctruct method, closes the DB connection
	 */
	public function __destruct()
	{
		$this->__closeDB();
	}

	// *************************************************************
	// PRIVATE METHODS
	// *************************************************************

	/**
	 * Setup DB config and options
	 *
	 * phpcs:ignore
	 * @param array{db_name:string,db_user:string,db_pass:string,db_host:string,db_port:int,db_schema:string,db_encoding:string,db_type:string,db_ssl:string,db_convert_type?:string[],db_convert_placeholder?:bool,db_convert_placeholder_target?:string,db_debug_replace_placeholder?:bool} $db_config
	 * @return bool
	 */
	private function __setConfigOptions(array $db_config): bool
	{
		// sets the names (for connect/reconnect)
		$this->db_name = $db_config['db_name'] ?? '';
		$this->db_user = $db_config['db_user'] ?? '';
		$this->db_pwd = $db_config['db_pass'] ?? '';
		$this->db_host = $db_config['db_host'] ?? '';
		// port
		$this->db_port = !empty($db_config['db_port']) ?
			(int)$db_config['db_port'] : 5432;
		if ($this->db_port < 0 || $this->db_port > 65535) {
			$this->db_port = 5432;
		}
		// do not set to 'public' if not set, because the default is already public
		$this->db_schema = !empty($db_config['db_schema']) ?
			$db_config['db_schema'] : '';
		$this->db_encoding = !empty($db_config['db_encoding']) ?
			$db_config['db_encoding'] : '';
		// db type
		$this->db_type = $db_config['db_type'] ?? '';
		if (!in_array($this->db_type, ['pgsql'])) {
			$this->db_type = 'pgsql';
		}
		// ssl setting
		$this->db_ssl = !empty($db_config['db_ssl']) ?
			$db_config['db_ssl'] : 'allow';
		if (!in_array($this->db_ssl, ['allow', 'disable', 'require', 'prefer'])) {
			$this->db_ssl = 'allow';
		}
		// trigger convert type
		// ['on', 'json', 'numeric', 'bytea'] allowed
		// if on is not set but other valid than on is assumed
		foreach ($db_config['db_convert_type'] ?? [] as $db_convert_type) {
			if (!in_array($db_convert_type, ['on', 'json', 'numeric', 'bytea'])) {
				continue;
			}
			$this->db_convert_type[] = $db_convert_type;
			$this->__setConvertType($db_convert_type);
		}
		// set placeholder convert flag and target
		if (
			isset($db_config['db_convert_placeholder']) &&
			is_bool($db_config['db_convert_placeholder'])
		) {
			$this->db_convert_placeholder = $db_config['db_convert_placeholder'];
		}
		if (
			isset($db_config['db_convert_placeholder_target']) &&
			in_array($db_config['db_convert_placeholder_target'], self::DB_CONVERT_PLACEHOLDER_TARGET)
		) {
			$this->db_convert_placeholder_target = $db_config['db_convert_placeholder_target'];
		}

		if (
			isset($db_config['db_debug_replace_placeholder']) &&
			is_bool($db_config['db_debug_replace_placeholder'])
		) {
			$this->db_debug_replace_placeholder = $db_config['db_debug_replace_placeholder'];
		}

		// return status true: ok, false: options error
		return true;
	}

	/**
	 * Set the convert bit flags
	 *
	 * @param  string $db_convert_type One of 'on', 'json', 'numeric', 'bytea'
	 * @return void
	 */
	private function __setConvertType(string $db_convert_type): void
	{
		switch ($db_convert_type) {
			case 'on':
				$this->convert_type |= Convert::on->value;
				break;
			case 'json':
				$this->convert_type |= Convert::on->value;
				$this->convert_type |= Convert::json->value;
				break;
			case 'numeric':
				$this->convert_type |= Convert::on->value;
				$this->convert_type |= Convert::numeric->value;
				break;
			case 'bytea':
				$this->convert_type |= Convert::on->value;
				$this->convert_type |= Convert::bytea->value;
				break;
		}
	}

	/**
	 * based on $this->db_type
	 * here we need to load the db pgsql include one
	 * How can we do this dynamic? eg for non PgSQL
	 * OTOH this whole class is so PgSQL specific
	 * that non PgSQL doesn't make much sense anymore
	 *
	 * @return SQL\PgSQL|null DB functions object or false on error
	 */
	private function __loadDBFunctions(): SQL\PgSQL|null
	{
		$db_functions = null;
		switch ($this->db_type) {
			// list of valid DB function objects
			case 'pgsql':
				$db_functions = new SQL\PgSQL();
				break;
			// if non set or none matching abort
			default:
				// abort error
				$this->__dbError(10, context: ['db_type' => $this->db_type]);
				$this->db_connection_closed = true;
				break;
		}
		return $db_functions;
	}

	/**
	 * internal connection function.
	 * Used to connect to the DB if there is no connection done yet.
	 * Called before any execute
	 *
	 * @return bool true on successfull connect, false if failed
	 */
	private function __connectToDB(): bool
	{
		// no DB name set, abort
		if (empty($this->db_name)) {
			$this->__dbError(15, context: [
				'host' => $this->db_host,
				'user' => $this->db_user,
				'password' => 'sha256:' . hash('sha256', $this->db_pwd),
				'database' => $this->db_name,
				'port' => $this->db_port,
				'ssl' => $this->db_ssl
			]);
			return false;
		}
		// generate connect string
		$this->dbh = $this->db_functions->__dbConnect(
			$this->db_host,
			$this->db_user,
			$this->db_pwd,
			$this->db_name,
			$this->db_port,
			$this->db_ssl
		);
		// if no dbh here, we couldn't connect to the DB itself
		if (!$this->dbh) {
			$this->__dbError(14, context: [
				'host' => $this->db_host,
				'user' => $this->db_user,
				'password' => 'sha256:' . hash('sha256', $this->db_pwd),
				'database' => $this->db_name,
				'port' => $this->db_port,
				'ssl' => $this->db_ssl
			]);
			return false;
		}
		// 15 error (cant select to DB is not valid in postgres, as connect is different)
		// if returns 0 we couldn't select the DB
		// if ($this->dbh == -1) {;
		// 	$this->dbError(15));
		// 	return false;
		// }
		// set search path if needed
		if (!empty($this->db_schema)) {
			$this->dbSetSchema($this->db_schema);
		}
		// set client encoding
		if (!empty($this->db_encoding)) {
			$this->dbSetEncoding($this->db_encoding);
		}
		// all okay
		return true;
	}

	/**
	 * close db connection
	 * only used by the deconstructor
	 *
	 * @return void has no return
	 */
	private function __closeDB(): void
	{
		if (!empty($this->dbh)) {
			$this->db_functions->__dbClose();
			$this->dbh = null;
		}
	}

	/**
	 * internal funktion that creates the array
	 * NOTE:
	 * used in db_dump_data only
	 *
	 * @param  array<mixed> $array array to print
	 * @return string              string with printed and formated array
	 */
	private function __printArray(array $array): string
	{
		$string = '';
		foreach ($array as $key => $value) {
			$string .= $this->nbsp . '<b>' . $key . '</b> => ';
			if (is_array($value)) {
				$this->nbsp .= '&nbsp;&nbsp;&nbsp;';
				$string .= '<br>';
				$string .= $this->__printArray($value);
			} else {
				$string .= $value . '<br>';
			}
		}
		$this->nbsp = substr_replace($this->nbsp, '', -18, 18);
		return $string;
	}

	/**
	 * calls the basic class debug with strip command
	 * for internal calls, will always create a message
	 *
	 * @param  string       $debug_id     group id for debug
	 * @param  string       $error_string error message or debug data
	 * @param  string       $id           db debug group
	 * @param  string       $type         query identifier (Q, I, etc)
	 * @param  array<mixed> $error_data   Optional error data as array
	 *                                    Will be printed after main error string
	 * @return void
	 */
	private function __dbDebugMessage(
		string $debug_id,
		string $error_string,
		string $id = '',
		string $type = '',
		array $error_data = []
	): void {
		// NOTE prefix allows html for echo output, will be stripped on file print
		$prefix = '';
		if ($id) {
			$prefix .= '[' . $id . '] ';
		}
		if ($type) {
			$prefix .= '{' . $type . '} ';
		}
		switch ($id) {
			case 'DB_ERROR':
				$prefix .= 'DB-Error:';
				break;
			case 'DB_WARNING':
				$prefix .= 'DB-Warning:';
				break;
		}
		if ($prefix) {
			$prefix .= '- ';
		}
		if ($error_data !== []) {
			$error_string .= "\n" . '['
				. \CoreLibs\Debug\Support::prAr($error_data)
				. ']';
		}
		// we need to trace back where the first non class call was done
		// add this stack trace to the context
		$call_stack = [];
		foreach (array_reverse(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)) as $call_trace) {
			if (
				!empty($call_trace['file']) &&
				str_ends_with($call_trace['file'], '/DB/IO.php')
			) {
				break;
			}
			$call_stack[] =
				($call_trace['file'] ?? 'n/f') . ':'
				. ($call_trace['line'] ?? '-') . ':'
				. (!empty($call_trace['class']) ?
					$call_trace['class'] . ($call_trace['type'] ?? '') :
					''
				)
				. $call_trace['function'];
		}
		$context = [
			'call_trace' => array_reverse($call_stack)
		];
		switch ($id) {
			case 'DB_ERROR':
				$this->log->error(
					$prefix . $error_string,
					$context
				);
				break;
			case 'DB_WARNING':
				$this->log->warning(
					$prefix . $error_string,
					$context
				);
				break;
			default:
				// no context on DB_INFO
				if ($id == 'DB_INFO') {
					$context = [];
				}
				// used named arguments so we can easy change the order of debug
				$this->log->debug(
					group_id: $debug_id,
					message: $error_string,
					prefix: $prefix,
					context: $context
				);
				break;
		}
	}

	/**
	 * main call from anywhere in all classes for launching debug messages
	 * will abort if dbDebug not set
	 *
	 * @param  string       $debug_id     group id for debug
	 * @param  string       $error_string error message or debug data
	 * @param  string       $id           db debug group
	 * @param  string       $type         query identifier (Q, I, etc)
	 * @param  array<mixed> $error_data   Optional error data as array
	 * @return void
	 */
	protected function __dbDebug(
		string $debug_id,
		string $error_string,
		string $id = '',
		string $type = '',
		array $error_data = []
	): void {
		if (!$this->dbGetDebug()) {
			return;
		}
		$this->__dbDebugMessage($debug_id, $error_string, $id, $type, $error_data);
	}

	/**
	 * Reset warnings and errors before run
	 * Is called on base queries to reset error before each run
	 * Recent error history can be checked with
	 * dbGetErrorHistory or dbGetWarningHistory
	 *
	 * @return void
	 */
	private function __dbErrorReset(): void
	{
		$this->error_id = '';
		$this->warning_id = '';
		$this->error_history_id = Uids::uniqId(self::ERROR_HASH_TYPE);
	}

	/**
	 * Check if there is a cursor and write this cursors error info
	 *
	 * @param \PgSql\Result|false $cursor current cursor for pg_result_error,
	 *                             pg_last_error too, but pg_result_error
	 *                             is more accurate (PgSql\Result)
	 * @param bool $force_log [=false] if we want to log this error to log, on default logging is handled in the
	 *                                 dbError/dbWarning calls
	 * @return array<mixed> Pos 0: if we could get the method where it was called
	 *                             if not found [Uknown Method]
	 *                      Pos 1: if we have the pg_error_string from last error
	 *                             if nothing then empty string
	 *                      Pos 2: context array for detailed logging
	 */
	private function __dbErrorPreprocessor(\PgSql\Result|false $cursor = false, bool $force_log = false): array
	{
		$db_prefix = '';
		$db_error_string = '';
		$db_prefix_last = '';
		$db_error_string_last = '';
		// 1 = self/__dbErrorPreprocessor, 2 = __dbError, __dbWarning,
		// 3+ == actual source
		// loop until we get a null, build where called chain
		// left to right for last called rightmost
		$level = 3;
		$where_called = null;
		foreach (Support::getCallerMethodList($level) as $method) {
			$where_called .= (!empty($where_called) ? '::' : '')
				. $method;
		}
		if ($where_called === null) {
			$where_called = '[Unknown Method]';
		}
		[$db_prefix_last, $db_error_string_last] = $this->db_functions->__dbPrintLastError();
		if ($cursor !== false) {
			[$db_prefix, $db_error_string] = $this->db_functions->__dbPrintError($cursor);
		}
		if ($cursor === false && method_exists($this->db_functions, '__dbPrintError')) { /** @phpstan-ignore-line */
			[$db_prefix, $db_error_string] = $this->db_functions->__dbPrintError();
		}
		// prefix the master if not the same
		if (
			!empty($db_error_string_last) &&
			trim($db_error_string) != trim($db_error_string_last)
		) {
			$db_error_string =
				$db_prefix_last . ' ' . $db_error_string_last . ';'
				. $db_prefix . ' ' . $db_error_string;
		} elseif (!empty($db_error_string)) {
			$db_error_string = $db_prefix . ' ' . $db_error_string;
		}
		if ($db_error_string && $force_log) {
			$this->__dbDebugMessage('db', $db_error_string, 'DB_ERROR', $where_called);
		}
		$context = [];
		if ($db_error_string) {
			$context = [
				'pg_error_string' => $db_error_string,
				'where_called' => $where_called,
			];
		}
		return [
			$where_called,
			$db_error_string,
			$context
		];
	}

	/**
	 * Build combined error history
	 * contains timestamp, error/warning id and message
	 * error level, source as :: separated string
	 * additional pg error message if exists and optional msg given on error call
	 * all error messages are grouped by error_history_id set when errors are reset
	 *
	 * @param string $level           warning or error
	 * @param string $error_id        error id
	 * @param string $where_called    context wher eerror was colled
	 * @param string $pg_error_string if set, postgresql error string
	 * @param string $message         additional message
	 * @param array<mixed> $context   array with more context information (eg query, params, etc)
	 * @return void
	 */
	private function __dbErrorHistory(
		string $level,
		string $error_id,
		string $where_called,
		string $pg_error_string,
		string $message,
		array $context
	): void {
		if (empty($this->error_history_id)) {
			$this->error_history_id = Uids::uniqId(self::ERROR_HASH_TYPE);
		}
		$this->error_history_long[$this->error_history_id][] = [
			'timestamp' => \CoreLibs\Combined\DateTime::dateStringFormat(microtime(true), true, true),
			'level' => $level,
			'id' => $error_id,
			'error' => $this->error_string[$error_id] ?? '[UNKNOWN ERROR]',
			'source' => $where_called,
			'pg_error' => $pg_error_string,
			'message' => $message,
			'context' => $context,
		];
	}

	/**
	 * write an error
	 *
	 * @param integer $error_id           Any Error ID, used in debug message string
	 * @param \PgSql\Result|false $cursor Optional cursor, passed on to preprocessor
	 * @param string $message             Optional message added to debug
	 * @param array<mixed> $context       Optional Context array, passed on as error_data to the main error
	 * @return void
	 */
	protected function __dbError(
		int $error_id,
		\PgSql\Result|false $cursor = false,
		string $message = '',
		array $context = []
	): void {
		$error_id = (string)$error_id;
		[$where_called, $pg_error_string, $_context] = $this->__dbErrorPreprocessor($cursor);
		// write error msg ...
		$this->__dbDebugMessage(
			'db',
			$error_id . ': ' . ($this->error_string[$error_id] ?? '[UNKNOWN ERROR]')
				. ($message ? ', ' . $message : ''),
			'DB_ERROR',
			$where_called,
			array_merge($context, $_context)
		);
		$this->error_id = $error_id;
		// keep error history
		$this->__dbErrorHistory('error', $error_id, $where_called, $pg_error_string, $message, $context);
	}

	/**
	 * write a warning
	 *
	 * @param integer $warning_id         Integer warning id added to debug
	 * @param \PgSql\Result|false $cursor Optional cursor, passed on to preprocessor
	 * @param string $message             Optional message added to debug
	 * @param array<mixed> $context       Optional Context array, passed on as error_data to the main error
	 * @return void
	 */
	protected function __dbWarning(
		int $warning_id,
		\PgSql\Result|false $cursor = false,
		string $message = '',
		array $context = []
	): void {
		$warning_id = (string)$warning_id;
		[$where_called, $pg_error_string, $_context] = $this->__dbErrorPreprocessor($cursor);
		$this->__dbDebugMessage(
			'db',
			$warning_id . ': ' . ($this->error_string[$warning_id] ?? '[UNKNOWN WARNING')
				. ($message ? ', ' . $message : ''),
			'DB_WARNING',
			$where_called,
			array_merge($context, $_context)
		);
		$this->warning_id = $warning_id;
		// keep warning history
		$this->__dbErrorHistory('warning', $warning_id, $where_called, $pg_error_string, $message, $context);
	}

	/**
	 * if there is the 'to_encoding' var set,
	 * and the field is in the wrong encoding converts it to the target
	 *
	 * @param  array<mixed>|false $row Array from fetch_row
	 * @return array<mixed>|false      Convert fetch_row array, or false
	 */
	private function __dbConvertEncoding(array|false $row): array|false
	{
		if (is_bool($row)) {
			return false;
		}
		// do not do anything if no to encoding is set
		if (empty($this->to_encoding)) {
			return $row;
		}
		// go through each row and convert the encoding if needed
		foreach ($row as $key => $value) {
			$from_encoding = mb_detect_encoding($value);
			// convert only if encoding doesn't match and source is not pure ASCII
			if (
				$from_encoding !== false &&
				$from_encoding != $this->to_encoding &&
				$from_encoding != 'ASCII'
			) {
				$row[$key] = mb_convert_encoding(
					$value,
					$this->to_encoding,
					$from_encoding
				);
			}
		}
		return $row;
	}

	/**
	 * Convert column content to the type in the name/pos field
	 * Note that on default it will only convert types that 100% map to PHP
	 * - intN
	 * - bool
	 * everything else will stay string.
	 * Fruther flags in the conert_type allow to convert:
	 * - json/jsonb to array
	 * - bytea to string
	 * Dangerous convert:
	 * - numeric/float to float (precision can be lost)
	 *
	 * @param  array<mixed>|false $row
	 * @return array<mixed>|false
	 */
	private function __dbConvertType(array|false $row): array|false
	{
		if (is_bool($row)) {
			return false;
		}
		// if convert type is not turned on
		if (!$this->convert_type) {
			return $row;
		}
		foreach ($row as $key => $value) {
			// always bool/int
			if (
				$this->dbGetFieldType($key) != 'interval' &&
				str_starts_with($this->dbGetFieldType($key) ?: '', 'int')
			) {
				$row[$key] = (int)$value;
			}
			if ($this->dbGetFieldType($key) == 'bool') {
				$row[$key] = $this->dbBoolean($value);
			}
			if (
				$this->convert_type & Convert::json->value &&
				str_starts_with($this->dbGetFieldType($key) ?: '', 'json')
			) {
				$row[$key] = Json::jsonConvertToArray($value);
			}
			if (
				$this->convert_type & Convert::numeric->value &&
				(
					str_starts_with($this->dbGetFieldType($key) ?: '', 'numeric') ||
					str_starts_with($this->dbGetFieldType($key) ?: '', 'float')
					// $this->dbGetFieldType($key) == 'real'
				)
			) {
				$row[$key] = (float)$value;
			}
			if (
				$this->convert_type & Convert::bytea->value &&
				$this->dbGetFieldType($key) == 'bytea'
			) {
				$row[$key] = $this->dbUnescapeBytea($value);
			}
		}
		return $row;
	}

	/**
	 * for debug purpose replaces $1, $2, etc with actual data
	 * TODO: :name and ? params
	 * Also works with :name parameters
	 * ? parameters, will be ignored
	 *
	 * @param  string       $query Query to replace values in
	 * @param  array<mixed> $params  The data param array
	 * @return string              string of query with data inside
	 */
	private function __dbDebugPrepare(string $query, array $params = []): string
	{
		// skip anything if there is no data
		if ($params === []) {
			return $query;
		}
		// get the keys from data array
		$keys = array_keys($params);
		// check if there is ? or :name i the keys list
		// because the placeholders start with $ and at 1,
		// we need to increase each key and prefix it with a $ char
		for ($i = 0, $iMax = count($keys); $i < $iMax; $i++) {
			// note: if I use $ here, the str_replace will
			//       replace it again. eg $11 '$1'1would be replaced with $1 again
			// prefix data set with parameter pos
			$params[$i] = '#' . ($keys[$i] + 1) . ':' . ($params[$i] === null ?
				'"NULL"' : (string)$params[$i]
			);
			// search part
			$keys[$i] = '$' . ($keys[$i] + 1);
		}
		// simply replace the $1, $2, ... with the actual data and return it
		// note that we do this in return to go from highest number to lowest
		return str_replace(
			array_reverse($keys),
			array_reverse($params),
			$query
		);
	}

	/**
	 * Created the context/error_data error for debug messages
	 *
	 * @param  string       $query  Query called
	 * @param  array<mixed> $params Params
	 * @return array{}|array<string,mixed> Empty array if no params, or params
	 *                                     with optional prepared statement
	 */
	private function __dbDebugPrepareContext(string $query, array $params = []): array
	{
		if ($params === []) {
			return [];
		}
		$error_data = [
			'params' => $params
		];
		if ($this->dbGetDebugReplacePlaceholder()) {
			$error_data['prepared'] = $this->__dbDebugPrepare(
				$query,
				$params
			);
		}
		return $error_data;
	}

	/**
	 * extracts schema and table from the query,
	 * if no schema returns just empty string
	 *
	 * @param  string       $query insert/select/update/delete query
	 * @return array<mixed>        array with schema and table
	 */
	private function __dbReturnTable(string $query): array
	{
		$matches = [];
		$schema_table = [];
		if ($this->dbCheckQueryForSelect($query)) {
			// only selects the first one, this is more a fallback
			// MATCHES 1 (call), 3 (schema), 4 (table)
			preg_match("/\s+?(FROM)\s+?([\"'])?(?:([\w_]+)\.)?([\w_]+)(?:\2)?\s?/i", $query, $matches);
			$schema_table = [
				$matches[3] ?? '',
				$matches[4] ?? '',
			];
		} else {
			preg_match(
				// must start with
				// INSERT INTO (table)
				// DELETE FROM (table)
				// UPDATE (table) SET
				// MATCHES 1 (call), 4 (schema), 5 (table)
				"/^\s*(INSERT\s+?INTO|DELETE\s+?FROM|(UPDATE))\s+?"
					. "([\"'])?(?:([\w_]+)\.)?([\w_]+)(?:\3)?\s?(?(2)\s+?SET|)/i",
				$query,
				$matches
			);
			$schema_table = [
				$matches[4] ?? '',
				$matches[5] ?? ''
			];
		}
		return $schema_table;
	}

	/**
	 * check if there is another query running, or do we hang after a
	 * PHP error
	 *
	 * @param  integer $timeout_seconds For complex timeout waits, default 3 seconds
	 * @return bool                  True for connection OK, else false
	 */
	private function __dbCheckConnectionOk(int $timeout_seconds = 3): bool
	{
		// check that no other query is running right now
		// below does return false after error too
		// if ($this->db_functions->__dbConnectionBusy()) {
		if ($this->db_functions->__dbConnectionBusySocketWait($timeout_seconds)) {
			$this->__dbError(41);
			return false;
		}
		return true;
	}

	/**
	 * dbReturn
	 * Read data from previous written data cache
	 *
	 * @param  string  $query_hash The hash for the current query
	 * @param  bool $assoc_only Only return assoc value (key named)
	 * @return array<mixed>        Current position query data from cache
	 */
	private function __dbReturnCacheRead(string $query_hash, bool $assoc_only): array
	{
		// unset return value ...
		$return = [];
		// current cursor hash element
		$cursor_hash = $this->cursor_ext[$query_hash];
		// position in reading for current cursor hash
		$cursor_pos = $cursor_hash['pos'];
		// max fields in current cursor hash
		$max_fields = $cursor_hash['num_fields'] ?? 0;
		// read voer each field
		for ($pos = 0; $pos < $max_fields; $pos++) {
			// numbered pos element data (only exists in full read)
			$cursor_data_pos = $cursor_hash['data'][$cursor_pos][$pos] ?? null;
			// field name position from field names
			$field_name_pos = $cursor_hash['field_names'][$pos] ?? null;
			// field name data
			$cursor_data_name = $cursor_hash['data'][$cursor_pos][$field_name_pos] ?? null;
			// create mixed return array
			if (
				$assoc_only === false &&
				$cursor_data_pos !== null
			) {
				$return[$pos] = $cursor_data_pos;
			}
			// named part (is alreays read)
			if (!empty($field_name_pos)) {
				// read pos first, fallback to name if not set
				if ($cursor_data_pos !== null) {
					$return[$field_name_pos] = $cursor_data_pos;
				} else {
					$return[$field_name_pos] = $cursor_data_name;
				}
			}
		}
		$this->cursor_ext[$query_hash]['pos']++;
		return $return;
	}

	/**
	 * count placeholder entries in the query
	 *
	 * @param  string $query Query to check
	 * @return int           Number of parameters found
	 */
	private function __dbCountQueryParams(string $query): int
	{
		return count($this->db_functions->__dbGetQueryParams($query));
	}

	/**
	 * Checks if the placeholder count in the query matches the params given
	 * on call
	 *
	 * @param  string       $query  Query to check
	 * @param  array<mixed> $params The parms to count count expected
	 * @return bool                 True for params count ok, else false
	 */
	private function __dbCheckQueryParams(string $query, array $params): bool
	{
		$placeholder_count = $this->__dbCountQueryParams($query);
		$params_count = count($params);
		if ($params_count != $placeholder_count) {
			$this->__dbError(
				23,
				false,
				'Need: ' . $placeholder_count . ', has: ' . $params_count,
				[
					'query' => $query,
					'params' => $params,
					'placeholder_needed' => $placeholder_count,
					'placeholder_provided' => $params_count,
				]
			);
			return false;
		}
		return true;
	}

	/**
	 * sub function for dbExec and dbExecAsync
	 * - checks query is set
	 * - checks there is a database handler
	 * - checks that here is no other query executing
	 * - checks for insert if returning is set/pk name
	 * - sets internal hash for query
	 * - checks multiple call count
	 *
	 * @param  string       $query   Query string
	 * @param  array<mixed> $params  Query params, needed for hash creation
	 * @param  string       $pk_name primary key
	 *                               [if set to NULL no returning will be added]
	 * @return string|false          queryt hash OR bool false on error
	 */
	private function __dbPrepareExec(
		string $query,
		array $params,
		string $pk_name
	): string|false {
		// reset current cursor before exec
		$this->cursor = false;
		// clear matches for regex lookups
		$matches = [];
		// to either use the returning method
		// or the guess method for getting primary keys
		$this->returning_id = false;
		// set the query
		$this->query = $query;
		// current params
		$this->params = $params;
		// empty on new
		$this->query_hash = '';
		// no query set
		if (empty($this->query)) {
			$this->__dbError(11);
			return false;
		}
		// if no DB Handler try to reconnect
		if (!$this->dbh) {
			// if reconnect fails drop out
			if (!$this->__connectToDB()) {
				return false;
			}
		}
		// check that no other query is running right now
		if (!$this->__dbCheckConnectionOk()) {
			return false;
		}
		// if we do have an insert, check if there is no RETURNING pk_id,
		// add it if I can get the PK id
		if ($this->dbCheckQueryForInsert($this->query, true)) {
			$this->pk_name = $pk_name;
			if ($this->pk_name != 'NULL') {
				if (!$this->pk_name) {
					// TODO: get primary key from table name
					list($schema, $table) = $this->__dbReturnTable($this->query);
					if (!array_key_exists($table, $this->pk_name_table) || !$this->pk_name_table[$table]) {
						$this->pk_name_table[$table] = $this->db_functions->__dbPrimaryKey($table, $schema);
					}
					$this->pk_name =
						$this->pk_name_table[$table] ?
							$this->pk_name_table[$table] : 'NULL';
				}
				if (!preg_match(self::REGEX_RETURNING, $this->query) && $this->pk_name != 'NULL') {
					// check if this query has a ; at the end and remove it
					$__query = preg_replace("/(;\s*)$/", '', $this->query);
					// must be query, if preg replace failed, use query as before
					$this->query = !is_string($__query) ? $this->query : $__query;
					$this->query .= " RETURNING " . $this->pk_name;
					$this->returning_id = true;
				} elseif (
					preg_match(self::REGEX_RETURNING, $this->query, $matches)
				) {
					if ($this->pk_name != 'NULL') {
						// add the primary key if it is not in the returning set
						if (!preg_match("/$this->pk_name/", $matches[1])) {
							$this->query .= " , " . $this->pk_name;
						}
					}
					$this->returning_id = true;
				}
			}
		}
		// if we have an UPDATE and RETURNING, flag for true, but do not add anything
		if (
			$this->dbCheckQueryForUpdate($this->query) &&
			preg_match(self::REGEX_RETURNING, $this->query, $matches)
		) {
			$this->returning_id = true;
		}
		// import protection, hash needed
		$query_hash = $this->dbBuildQueryHash($this->query, $this->params);
		// QUERY PARAMS: run query params check and rewrite
		if ($this->dbGetConvertPlaceholder() === true) {
			try {
				$this->placeholder_converted = ConvertPlaceholder::convertPlaceholderInQuery(
					$this->query,
					$this->params,
					$this->dbGetConvertPlaceholderTarget()
				);
				// write the new queries over the old
				if (!empty($this->placeholder_converted['query'])) {
					$this->query = $this->placeholder_converted['query'];
					$this->params = $this->placeholder_converted['params'];
				}
			} catch (\OutOfRangeException $e) {
				$this->__dbError($e->getCode(), context:[
					'query' => $this->query,
					'params' => $this->params,
					'location' => '__dbPrepareExec',
					'error' => 'OutOfRangeException',
					'exception' => $e
				]);
				return false;
			} catch (\RuntimeException $e) {
				$this->__dbError($e->getCode(), context:[
					'query' => $this->query,
					'params' => $this->params,
					'location' => '__dbPrepareExec',
					'error' => 'RuntimeException',
					'exception' => $e
				]);
				return false;
			}
		}
		// set query hash
		$this->query_hash = $query_hash;
		// $this->debug('DB IO', 'Q: ' . $this->query . ', RETURN: ' . $this->returning_id);
		// for DEBUG, only on first time ;)
		$this->__dbDebug(
			'db',
			$this->query,
			'__dbPrepareExec',
			($this->params === [] ? 'Q' : 'Qp'),
			error_data: $this->__dbDebugPrepareContext($this->query, $this->params)
		);
		// if the array index does not exists set it 0
		if (!array_key_exists($query_hash, $this->query_called)) {
			$this->query_called[$query_hash] = 0;
		}
		// if the array index exists, but it is not a numeric one, set it to 0
		if (!is_numeric($this->query_called[$query_hash])) {
			$this->query_called[$query_hash] = 0;
		}
		// count up the run, if this is run more than the max_run then exit with error
		// if set to -1, then ignore it
		if (
			$this->MAX_QUERY_CALL != -1 &&
			$this->query_called[$query_hash] > $this->MAX_QUERY_CALL
		) {
				$this->__dbError(30, false, context: [
					'query' => $this->query,
					'params' => $this->params,
					'location' => '__dbPrepareExec'
				]);
				return false;
		}
		$this->query_called[$query_hash]++;
		// return hash
		return $query_hash;
	}

	/**
	 * runs post execute for rows affected, field names, inserted primary key, etc
	 *
	 * @return bool true on success or false if an error occured
	 */
	private function __dbPostExec(): bool
	{
		// always reset insert array after exec
		$this->insert_id_arr = [];
		// if FALSE returned, set error stuff
		// if either the cursor is false
		if ($this->cursor === false || $this->db_functions->__dbLastErrorQuery()) {
			// internal error handling
			$this->__dbError(13, $this->cursor, context: [
				'query' => $this->query,
				'params' => $this->params,
				'location' => 'dbExec',
				'query_id' => 'Q[nc]',
			]);
			return false;
		} else {
			// if SELECT do here ...
			if ($this->dbCheckQueryForSelect($this->query)) {
				// count the rows returned (if select)
				$this->num_rows = $this->db_functions->__dbNumRows($this->cursor);
				// count the fields
				$this->num_fields = $this->db_functions->__dbNumFields($this->cursor);
				// set field names
				$this->field_names = [];
				for ($i = 0; $i < $this->num_fields; $i++) {
					$this->field_names[] = $this->db_functions->__dbFieldName($this->cursor, $i) ?: '';
					// if (!empty($this->field_names[$i]))
					// $this->field_name_types[$this->field_names[$i]] = null;
				}
				$this->field_types = [];
				for ($i = 0; $i < $this->num_fields; $i++) {
					$this->field_types[] = $this->db_functions->__dbFieldType($this->cursor, $i) ?: '';
				}
				// combined array
				$this->field_name_types = array_combine(
					$this->field_names,
					$this->field_types
				);
			} elseif ($this->dbCheckQueryForInsert($this->query)) {
				// if not select do here
				// count affected rows
				$this->num_rows = $this->db_functions->__dbAffectedRows($this->cursor);
				if (
					// ONLY insert with set pk name
					($this->dbCheckQueryForInsert($this->query, true) && $this->pk_name != 'NULL') ||
					// insert or update with returning add
					($this->dbCheckQueryForInsert($this->query) && $this->returning_id)
				) {
					$this->__dbSetInsertId(
						$this->returning_id,
						$this->query,
						$this->pk_name,
						$this->cursor
					);
				}
			}
			return true;
		}
	}

	/**
	 * Get all returning variables
	 * try to primary get the OK into insert_id
	 * - one if single
	 * - array if many to return
	 * - if many this will also hold all non pk names too
	 * then try to fill insert_id_arr, this is always multi level
	 * - fill key: value as single array or multi array
	 * - holds all returning as array
	 *
	 * @param bool                $returning_id False if no RETURNING, try to get different via insert id
	 * @param string              $query        Query with RETURNING
	 * @param string|null         $pk_name      Primary key name
	 * @param \PgSql\Result|false $cursor       (PgSql\Result)
	 * @param string|null         $stm_name     [null] If not null, is dbExecute run and not a prepared call
	 * @return void
	 */
	private function __dbSetInsertId(
		bool $returning_id,
		string $query,
		?string $pk_name,
		\PgSql\Result|false $cursor,
		?string $stm_name = null
	): void {
		// $this->log->debug('DB SET INSERT ID', 'Ret: ' . ($returning_id ? 'Y' : 'N')
		// 	. 'Q: ' . $query . ', PK: ' . $pk_name . ', S: ' . ($stm_name ?? '{-}'));
		// as internval user only
		$insert_id = null;
		// reset internal array
		$this->insert_id_arr = [];
		// set the primary key name
		$this->insert_id_pk_name = $pk_name ?? '';
		// abort if cursor is empty
		if ($cursor === false) {
			// failed to get insert id
			if ($stm_name === null) {
				$this->__dbWarning(34, $cursor, '[dbExec]', context: [
					'query' => $query,
					'pk_name' => $pk_name,
				]);
			} else {
				$this->__dbWarning(34, false, 'CURSOR is null', [
					'statement_name' => $stm_name,
					'query' => $query,
					'pk_name' => $pk_name,
				]);
			}
			return;
		}
		// set insert_id
		// if we do not have a returning
		// we try to get it via the primary key and another select
		if (!$returning_id) {
			$insert_id = $this->db_functions->__dbInsertId($query, $pk_name);
			$this->insert_id_arr[] = $insert_id;
			// throw warning that no pk was found
			if ($insert_id === false) {
				$this->__dbWarning(31, $cursor, '[dbExec]', context: [
					'query' => $query,
					'pk_name' => $pk_name,
					'returning_id' => $returning_id,
				]);
			}
		} else { // was stm_name null or not null and cursor
			// we have returning, now we need to check if we get one or many returned
			// we'll need to loop this, if we have multiple insert_id returns
			while (
				is_array($insert_id = $this->db_functions->__dbFetchArray(
					$cursor,
					$this->db_functions->__dbResultType(true)
				))
			) {
				$this->insert_id_arr[] = $insert_id;
			}
			// warning if we didn't get any returning data
			if (count($this->insert_id_arr) == 0) {
				// failed to get insert id
				if ($stm_name === null) {
					$this->__dbWarning(33, $cursor, '[dbExec]', context: [
						'query' => $query,
						'pk_name' => $pk_name,
						'returning_id' => $returning_id,
					]);
				} else {
					$this->__dbWarning(
						33,
						false,
						'RETURNING returned no data',
						context: [
							'statement_name' => $stm_name,
							'query' => $query,
							'pk_name' => $pk_name,
							'returning_id' => $returning_id,
						]
					);
				}
			} elseif (count($this->insert_id_arr) > 1) {
				// this error handling is only for INSERT (), (), ... sets
				if ($stm_name === null) {
					$this->__dbWarning(32, $cursor, '[dbExec]', context: [
						'query' => $query,
						'pk_name' => $pk_name,
						'returning_id' => $returning_id,
					]);
				} else {
					$this->__dbWarning(
						32,
						false,
						'RETURNING returned an array (possible multiple insert)',
						context: [
							'statement_name' => $stm_name,
							'query' => $query,
							'pk_name' => $pk_name,
							'returning_id' => $returning_id,
						]
					);
				}
			}
		}
	}

	// *************************************************************
	// PUBLIC METHODS
	// *************************************************************

	// ***************************
	// CLOSE, STATUS, SETTINGS VARIABLE READ
	// ***************************

	/**
	 * closes the db_connection
	 * normally this is not used, as the class deconstructor closes
	 * the connection down
	 *
	 * @return void has no return
	 */
	public function dbClose(): void
	{
		if (
			!empty($this->dbh) &&
			$this->dbh instanceof \PgSql\Connection /** @phpstan-ignore-line future could be other */
		) {
			// reset any client encodings set
			$this->dbResetEncoding();
			// calls db close
			$this->db_functions->__dbClose();
			$this->dbh = null;
			$this->db_connection_closed = true;
		}
	}

	/**
	 * returns the db init error
	 * if failed to connect it is set to false
	 * else true
	 *
	 * @return bool Connection status
	 */
	public function dbGetConnectionStatus(): bool
	{
		return $this->db_connection_closed ? false : true;
	}

	/**
	 * get certain settings like username, db name
	 *
	 * @param  string          $name what setting to query
	 * @return int|string|bool       setting value, if not allowed name return false
	 */
	public function dbGetSetting(string $name): int|string|bool
	{
		$setting = '';
		switch ($name) {
			case 'name':
				$setting = $this->db_name;
				break;
			case 'user':
				$setting = $this->db_user;
				break;
			case 'encoding':
				$setting = $this->db_encoding;
				break;
			case 'schema':
				$setting = $this->db_schema;
				break;
			case 'host':
				$setting = $this->db_host;
				break;
			case 'port':
				$setting = $this->db_port;
				break;
			case 'ssl':
				$setting = $this->db_ssl;
				break;
			case 'debug':
				$setting = $this->db_debug;
				break;
			// we return *** and never the actual password
			case 'password':
				$setting = '***';
				break;
			default:
				$setting = false;
				break;
		}
		return $setting;
	}

	/**
	 * prints out status info from the connected DB (might be usefull for debug stuff)
	 *
	 * @param  bool   $log   Show db connection info, default true
	 *                       if set to false won't write to error_msg var
	 * @param  bool   $strip Strip all HTML
	 * @return string        db connection information string
	 */
	public function dbInfo(bool $log = true, bool $strip = false): string
	{
		$html_tags = ['{b}', '{/b}', '{br}'];
		$replace_html = ['<b>', '</b>', '<br>'];
		$replace_text = ['', '', ' **** '];
		$string = '{b}-DB-info->{/b} Connected to db {b}\'' . $this->db_name . '\'{/b} '
			. 'with schema {b}\'' . $this->db_schema . '\'{/b} '
			. 'as user {b}\'' . $this->db_user . '\'{/b} '
			. 'at host {b}\'' . $this->db_host . '\'{/b} '
			. 'on port {b}\'' . $this->db_port . '\'{/b} '
			. 'with ssl mode {b}\'' . $this->db_ssl . '\'{/b}{br}'
			. '{b}-DB-info->{/b} DB IO Class debug output: {b}'
			. ($this->dbGetDebug() ? 'Yes' : 'No') . '{/b}';
		if ($log === true) {
			// if debug, remove / change b
			$this->__dbDebugMessage('db', str_replace(
				$html_tags,
				$replace_text,
				$string
			), 'DB_INFO');
		} else {
			$string = $string . '{br}';
		}
		// for direct print, change to html or strip if flagged
		return str_replace(
			$html_tags,
			$strip === false ? $replace_html : $replace_text,
			$string
		);
	}

	/**
	 * Server version as integer value
	 *
	 * @return integer Version as integer
	 */
	public function dbVersionNumeric(): int
	{
		return $this->db_functions->__dbVersionNumeric();
	}

	/**
	 * return current database version (server side) as string
	 *
	 * @return string database version as string
	 */
	public function dbVersion(): string
	{
		return $this->db_functions->__dbVersion();
	}

	/**
	 * extended version info, can access all additional information data
	 *
	 * @param  string  $parameter Array parameter name, if not valid returns
	 *                            empty string
	 * @param  bool $strip     Strip extended server info string, default true
	 *                            eg nn.n (other info) will only return nn.n
	 * @return string             Parameter value
	 */
	public function dbVersionInfo(string $parameter, bool $strip = true): string
	{
		return $this->db_functions->__dbVersionInfo($parameter, $strip);
	}

	/**
	 * All possible parameter names for dbVersionInfo
	 *
	 * @return array<mixed> List of all parameter names
	 */
	public function dbVersionInfoParameters(): array
	{
		return $this->db_functions->__dbVersionInfoParameterList();
	}

	/**
	 * returns bool true or false if the string matches the database version
	 *
	 * @param  string $compare string to match in type =X.Y, >X.Y, <X.Y, <=X.Y, >=X.Y
	 * @return bool            true for ok, false on not ok
	 */
	public function dbCompareVersion(string $compare): bool
	{
		$matches = [];
		// compare has =, >, < prefix, and gets stripped
		// if the rest is not X.Y format then error
		if (!preg_match("/^([<>=]{1,})(\d{1,})\.(\d{1,})/", $compare, $matches)) {
			$this->log->error('Could not regex match compare version string', [
				"compare" => $compare
			]);
			return false;
		}
		$compare = $matches[1];
		$to_master = $matches[2];
		$to_minor = $matches[3];
		$to_version = '';
		if (!$compare || !strlen($to_master) || !strlen($to_minor)) {
			return false;
		} else {
			$to_version = $to_master . ($to_minor < 10 ? '0' : '') . $to_minor;
		}
		// db_version can return X.Y.Z
		// we only compare the first two
		if (
			!preg_match(
				"/^(\d{1,})\.(\d{1,})\.?(\d{1,})?/",
				$this->dbVersion(),
				$matches
			)
		) {
			$this->log->error('Could not regex match dbVersion string', [
				"dbVersion" => $this->dbVersion()
			]);
			return false;
		}
		$master = $matches[1];
		$minor = $matches[2];
		$version = $master . ($minor < 10 ? '0' : '') . $minor;
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

	// ***************************
	// DEBUG DATA DUMP
	// ***************************

	/**
	 * dumps ALL data for this query, OR if no query given all in cursor_ext array
	 *
	 * @param  string $query Query, if given, only from this quey (if found)
	 *                       else current cursor
	 * @return string        Formated string with all the data in the array
	 */
	public function dbDumpData(string $query = ''): string
	{
		// set start array
		if ($query) {
			$array = $this->cursor_ext[$this->dbBuildQueryHash($query)] ?? [];
		} else {
			$array = $this->cursor_ext;
		}
		$string = '';
		if (is_array($array)) {
			$this->nbsp = '';
			$string .= $this->__printArray($array);
			$this->__dbDebugMessage('db', $string, 'DB_INFO');
		}
		return $string;
	}

	// ***************************
	// CHECK QUERY TYPE
	// ***************************

	/**
	 * checks if query is a SELECT, SHOW or WITH, if not error, 0 return
	 * NOTE:
	 * Query needs to start with SELECT, SHOW or WITH
	 *
	 * @param  string $query query to check
	 * @return bool          true if matching, false if not
	 */
	public function dbCheckQueryForSelect(string $query): bool
	{
		// change to string starts with?
		if (preg_match("/^\s*(?:SELECT|SHOW|WITH)\s/i", $query)) {
			return true;
		}
		return false;
	}

	/**
	 * check for DELETE, INSERT, UPDATE
	 * if pure is set to true, only when INSERT is set will return true
	 * NOTE:
	 * Queries need to start with INSERT, UPDATE, DELETE. Anything else is ignored
	 *
	 * @param  string $query query to check
	 * @param  bool   $pure  pure check (only insert), default false
	 * @return bool          true if matching, false if not
	 */
	public function dbCheckQueryForInsert(string $query, bool $pure = false): bool
	{
		if ($pure && preg_match("/^\s*INSERT\s+?INTO\s/i", $query)) {
			return true;
		}
		if (!$pure && preg_match("/^\s*(?:INSERT\s+?INTO|DELETE\s+?FROM|UPDATE)\s/i", $query)) {
			return true;
		}
		return false;
	}

	/**
	 * returns true if the query starts with UPDATE
	 * query NEEDS to start with UPDATE
	 *
	 * @param  string $query query to check
	 * @return bool          returns true if the query starts with UPDATE
	 */
	public function dbCheckQueryForUpdate(string $query): bool
	{
		if (preg_match("/^\s*UPDATE\s?(.+)/i", $query)) {
			return true;
		}
		return false;
	}

	// ***************************
	// DATA WRITE CONVERSION
	// ***************************

	/**
	 * neutral function to escape a string for DB writing
	 *
	 * @param  string|int|float|bool $string string to escape
	 * @return string                        escaped string
	 */
	public function dbEscapeString(string|int|float|bool $string): string
	{
		return $this->db_functions->__dbEscapeString($string);
	}

	/**
	 * neutral function to escape a string for DB writing
	 * this one adds '' quotes around the string
	 *
	 * @param  string|int|float|bool $string string to escape
	 * @return string                        escaped string
	 */
	public function dbEscapeLiteral(string|int|float|bool $string): string
	{
		return $this->db_functions->__dbEscapeLiteral($string);
	}

	/**
	 * string escape for column and table names
	 *
	 * @param  string $string string to escape
	 * @return string         escaped string
	 */
	public function dbEscapeIdentifier(string $string): string
	{
		return $this->db_functions->__dbEscapeIdentifier($string);
	}

	/**
	 * escape data for writing to bytea type column field
	 *
	 * @param  string $data data to escape to bytea
	 * @return string       escaped bytea string
	 */
	public function dbEscapeBytea(string $data): string
	{
		return $this->db_functions->__dbEscapeBytea($data);
	}

	/**
	 * unescape bytea data back to normal binrary data
	 *
	 * @param  string $bytea bytea data stream
	 * @return string        binary data string
	 */
	public function dbUnescapeBytea(string $bytea): string
	{
		return $this->db_functions->__dbUnescapeBytea($bytea);
	}

	/**
	 * clear up any data for valid DB insert
	 *
	 * @param  int|float|string|bool|null $value to escape data
	 * @param  string                     $kbn   escape trigger type
	 * @return string                            escaped value
	 */
	public function dbSqlEscape(int|float|string|bool|null $value, string $kbn = ''): string
	{
		switch ($kbn) {
			case 'i':
				$value = empty($value) ? 'NULL' : intval($value);
				break;
			case 'f':
				$value = empty($value) ? 'NULL' : floatval($value);
				break;
			// string (null is null, else is string)
			case 't':
				$value = $value === null ?
					'NULL' :
					"'" . $this->dbEscapeString($value) . "'";
				break;
			// string litereal (null is null, else is stirng)
			case 'tl':
				$value = $value === null ?
					'NULL' :
					$this->dbEscapeLiteral($value);
				break;
			// escape string, set empty to null
			case 'd':
				$value = empty($value) ?
					'NULL' :
					"'" . $this->dbEscapeString($value) . "'";
				break;
			// escape string literal, set empty to null
			case 'dl':
				$value = empty($value) ?
					'NULL' :
					$this->dbEscapeLiteral($value);
				break;
			// bytea data
			case 'by':
				$value = empty($value) ? 'NULL' : $this->dbEscapeBytea((string)$value);
				break;
			// bool
			case 'b':
				if (is_float($value)) {
					$value = (int)$value;
				}
				$value = $value === '' || $value === null ?
					'NULL' :
					"'" . $this->dbBoolean($value, true) . "'";
				break;
			// int, but with empty value is 0
			case 'i2':
				$value = empty($value) ? 0 : intval($value);
				break;
		}
		return (string)$value;
	}

	// ***************************
	// DATA READ/WRITE CONVERSION
	// ***************************

	/**
	 * if the input is a single char 't' or 'f
	 * it will return the bool value instead
	 * also converts smallint 1/0 to true false
	 *
	 * @param  string|bool|int $string 't' / 'f' or any string, or bool true/false
	 * @param  bool            $rev    do reverse (bool to string)
	 * @return bool|string             [default=false]: corretc postgresql -> php,
	 *                                 true: convert php to postgresql
	 */
	public function dbBoolean(string|bool|int $string, bool $rev = false): bool|string
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
	}

	// ***************************
	// DATA READ CONVERSION
	// ***************************

	/**
	 * only for postgres. pretty formats an age or datetime difference string
	 *
	 * @param  string  $interval   Age or interval/datetime difference
	 * @param  bool    $show_micro micro on off (default false)
	 * @return string              Y/M/D/h/m/s formatted string (like timeStringFormat)
	 */
	public function dbTimeFormat(string $interval, bool $show_micro = false): string
	{
		$matches = [];
		// in string (datetime diff): 1786 days 22:11:52.87418
		// or (age): 4 years 10 mons 21 days 12:31:11.87418
		// also -09:43:54.781021 or without - prefix
		// can have missing parts, but day/time must be fully set
		preg_match(
			"/^(-)?((\d+) year[s]? ?)?((\d+) mon[s]? ?)?((\d+) day[s]? ?)?"
				. "((\d{1,2}):(\d{1,2}):(\d{1,2}))?(\.(\d+))?$/",
			$interval,
			$matches
		);

		// prefix (-)
		$prefix = $matches[1] ?? '';
		// date, years (2, 3), month (4, 5), days (6, 7)
		$years = $matches[2] ?? '';
		$months = $matches[4] ?? '';
		$days = $matches[6] ?? '';
		// time (8), hour (9), min (10), sec (11)
		$hour = $matches[9] ?? '';
		$minutes = $matches[10] ?? '';
		$seconds = $matches[11] ?? '';
		// micro second block (12), ms (13)
		$milliseconds = $matches[13] ?? '';

		// clean up, hide entries that have 00 in the time group
		$hour = $hour != '00' ? preg_replace('/^0/', '', $hour) : '';
		$minutes = $minutes != '00' ? preg_replace('/^0/', '', $minutes) : '';
		$seconds = $seconds != '00' ? preg_replace('/^0/', '', $seconds) : '';

		// strip any leading or trailing spaces
		$time_string = trim(
			$prefix . $years . $months . $days
			. (!empty($hour) && is_string($hour) ? $hour . 'h ' : '')
			. (!empty($minutes) && is_string($minutes) ? $minutes . 'm ' : '')
			. (!empty($seconds) && is_string($seconds) ? $seconds . 's ' : '')
			. ($show_micro && !empty($milliseconds) ? $milliseconds . 'ms' : '')
		);
		// if the return string is empty, return 0s instead
		return empty($time_string) ? '0s' : $time_string;
	}

	/**
	 * this is only needed for Postgresql. Converts postgresql arrays to PHP
	 * Recommended to rather user 'array_to_json' instead and convet JSON in PHP
	 * or if ARRAY_AGG -> JSONB_AGG
	 *
	 * @param  string $text input text to parse to an array
	 * @return array<mixed> PHP array of the parsed data
	 * @deprecated Recommended to use 'array_to_json/jsonb_agg' in PostgreSQL instead
	 */
	public function dbArrayParse(string $text): array
	{
		$__db_array_parse = $this->db_functions->__dbArrayParse($text);
		return is_array($__db_array_parse) ? $__db_array_parse : [];
	}

	// ***************************
	// TABLE META DATA READ
	// ***************************

	/**
	 * returns an array of the table with columns and values. FALSE on no table found
	 *
	 * @param  string     $table  table name
	 * @param  string     $schema optional schema name
	 * @return array<mixed>|false array of table data, false on error (table not found)
	 */
	public function dbShowTableMetaData(string $table, string $schema = ''): array|false
	{
		$this->__dbErrorReset();
		$table = (!empty($schema) ? $schema . '.' : '') . $table;
		$array = $this->db_functions->__dbMetaData($table);
		if (!is_array($array)) {
			$this->__dbError(60, context: [
				'table' => $table,
				'schema' => $schema
			]);
			$array = false;
		}
		return $array;
	}

	// ***************************
	// QUERY EXECUSION AND DATA READ
	// ***************************

	/**
	 * single running function, if called creates hash from
	 * query string and so can itself call exec/return calls
	 * caches data, so next time called with IDENTICAL (!!!!)
	 * [this means 1:1 bit to bit identical query] returns cached
	 * data, or with reset flag set calls data from DB again
	 * NOTE on $cache param:
	 * - if set to 0, if same query run again, will read from cache
	 * - if set to 1, the data will be read new and cached, cache reset a new run
	 *   (wheres 1 reads cache AND destroys at end of read)
	 * - if set to 2, at the end of the query (last row returned),
	 *   the stored array will be deleted ...
	 * - if set to 3, after EACH row, the data will be reset,
	 *   no caching is done except for basic (count, etc)
	 * Wrapper for dbReturnParams
	 *
	 * @param  string $query      Query string
	 * @param  int    $cache      reset status: default: NO_CACHE
	 *                            USE_CACHE/0: normal read from cache on second run
	 *                            READ_NEW/1: write to cache, clean before new run
	 *                            CLEAR_CACHE/2: write cache, clean after finished
	 *                            NO_CACHE/3: don't write cache
	 * @param  bool   $assoc_only True to only returned the named and not
	 *                            index position ones
	 * @return array<mixed>|false return array data or false on error/end
	 * @#suppress PhanTypeMismatchDimFetch
	 */
	public function dbReturn(
		string $query,
		int $cache = self::NO_CACHE,
		bool $assoc_only = false
	): array|false {
		return $this->dbReturnParams($query, [], $cache, $assoc_only);
	}

	/**
	 * single running function, if called creates hash from
	 * query string and so can itself call exec/return calls
	 * caches data, so next time called with IDENTICAL (!!!!)
	 * [this means 1:1 bit to bit identical query] returns cached
	 * data, or with reset flag set calls data from DB again
	 * NOTE on $cache param:
	 * - if set to 0, if same query run again, will read from cache
	 * - if set to 1, the data will be read new and cached, cache reset a new run
	 *   (wheres 1 reads cache AND destroys at end of read)
	 * - if set to 2, at the end of the query (last row returned),
	 *   the stored array will be deleted ...
	 * - if set to 3, after EACH row, the data will be reset,
	 *   no caching is done except for basic (count, etc)
	 *
	 * @param  string       $query      Query string
	 * @param  array<mixed> $params     Query parameters
	 * @param  int          $cache      reset status: default: NO_CACHE
	 *                                  USE_CACHE/0: normal read from cache on second run
	 *                                  READ_NEW/1: write to cache, clean before new run
	 *                                  CLEAR_CACHE/2: write cache, clean after finished
	 *                                  NO_CACHE/3: don't write cache
	 * @param  bool         $assoc_only True to only returned the named and not
	 *                                  index position ones
	 * @return array<mixed>|false       return array data or false on error/end
	 */
	public function dbReturnParams(
		string $query,
		array $params = [],
		int $cache = self::NO_CACHE,
		bool $assoc_only = false
	): array|false {
		$this->__dbErrorReset();
		if (!$query) {
			$this->__dbError(11);
			return false;
		}
		// create hash from query ...
		$query_hash = $this->dbBuildQueryHash($query, $params);
		// pre declare array
		if (!isset($this->cursor_ext[$query_hash])) {
			$this->cursor_ext[$query_hash] = [
				// cursor, null: unset, 1: finished read/cache, 2: object reading
				'cursor' => null,
				// cached data
				'data' => [],
				// field names as array
				'field_names' => [],
				// field types as array (pos in field names is pos here)
				'field_types' => [],
				// name to type assoc array (from field names and field types)
				'field_name_types' => [],
				// number of fields (field names)
				'num_fields' => 0,
				// number of rows that will be maximum returned
				'num_rows' => 0,
				// how many rows have been read from db
				'read_rows' => 0,
				// current read pos (db/cache), 0 on last read (finished)
				'pos' => 0,
				// the query used in this call
				'query' => '',
				// parameter
				'params' => [],
				// if we convert placeholders, conversion data is stored here
				'placeholder_converted' => [],
				// cache flag from method call
				'cache_flag' => $cache,
				// flag if we only have assoc data
				'assoc_flag' => $assoc_only,
				// flag if we have cache data stored at the moment
				'cached' => false,
				// when fetch array or cache read returns false
				// in loop read that means dbReturn retuns false without error
				'finished' => false,
				 // read from cache/db (pos == rows)
				'read_finished' => false,
				 // read from db only (read == rows)
				'db_read_finished' => false,
				// for debug
				'log_pos' => 1, // how many times called overall
				'log' => [], // current run log
			];

			// set the query
			$this->cursor_ext[$query_hash]['query'] = $query;
			// set the query parameters
			$this->cursor_ext[$query_hash]['params'] = $params;
			// before doing ANYTHING check if query is "SELECT ..." everything else does not work
			if (!$this->dbCheckQueryForSelect($this->cursor_ext[$query_hash]['query'])) {
				$this->__dbError(17, false, context: [
					'query' => $this->cursor_ext[$query_hash]['query'],
					'params' => $this->cursor_ext[$query_hash]['params'],
					'location' => 'dbReturn',
				]);
				return false;
			}
			// QUERY PARAMS: run query params check and rewrite
			if ($this->dbGetConvertPlaceholder() === true) {
				try {
					$this->cursor_ext[$query_hash]['placeholder_converted'] =
						ConvertPlaceholder::convertPlaceholderInQuery(
							$this->cursor_ext[$query_hash]['query'],
							$this->cursor_ext[$query_hash]['params'],
							$this->dbGetConvertPlaceholderTarget()
						);
					if (!empty($this->cursor_ext[$query_hash]['placeholder_converted']['query'])) {
						$this->cursor_ext[$query_hash]['query'] =
							$this->cursor_ext[$query_hash]['placeholder_converted']['query'];
						$this->cursor_ext[$query_hash]['params'] =
							$this->cursor_ext[$query_hash]['placeholder_converted']['params'];
					}
				} catch (\OutOfRangeException $e) {
					$this->__dbError($e->getCode(), context:[
						'query' => $this->cursor_ext[$query_hash]['query'],
						'params' => $this->cursor_ext[$query_hash]['params'],
						'location' => 'dbReturn',
						'error' => 'OutOfRangeException',
						'exception' => $e
					]);
					return false;
				} catch (\RuntimeException $e) {
					$this->__dbError($e->getCode());
					$this->__dbError($e->getCode(), context:[
						'query' => $this->cursor_ext[$query_hash]['query'],
						'params' => $this->cursor_ext[$query_hash]['params'],
						'location' => 'dbReturn',
						'error' => 'RuntimeException',
						'exception' => $e
					]);
					return false;
				}
			}
			// check if params count matches
			// checks if the params count given matches the expected count
			if (
				$this->__dbCheckQueryParams(
					$this->cursor_ext[$query_hash]['query'],
					$this->cursor_ext[$query_hash]['params']
				) === false
			) {
				return false;
			}
		} else {
			$this->cursor_ext[$query_hash]['log_pos']++;
		}
		// reset log for each read
		$this->cursor_ext[$query_hash]['log'] = [];
		// set first call to false
		$first_call = false;
		// init return als false
		$return = false;
		// if it is a call with reset in it we reset the cursor,
		// so we get an uncached return
		// but only for the FIRST call (pos == 0)
		if ($cache > self::USE_CACHE && !$this->cursor_ext[$query_hash]['pos']) {
			$this->cursor_ext[$query_hash]['log'][] = 'Reset cursor';
			$this->cursor_ext[$query_hash]['cursor'] = null;
			if ($cache == self::READ_NEW) {
				$this->cursor_ext[$query_hash]['log'][] = 'Cache reset';
				$this->cursor_ext[$query_hash]['data'] = [];
			}
		}

		// if no cursor yet, execute
		if (!$this->cursor_ext[$query_hash]['cursor']) {
			$this->cursor_ext[$query_hash]['log'][] = 'No cursor';
			// for DEBUG, print out each query executed
			$this->__dbDebug(
				'db',
				$this->cursor_ext[$query_hash]['query'],
				'dbReturn',
				($this->cursor_ext[$query_hash]['params'] === [] ? 'Q' : 'Qp'),
				error_data: $this->__dbDebugPrepareContext(
					$this->cursor_ext[$query_hash]['query'],
					$this->cursor_ext[$query_hash]['params']
				)
			);
			// if no DB Handler try to reconnect
			if (!$this->dbh) {
				// if reconnect fails drop out
				if (!$this->__connectToDB()) {
					return false;
				}
			}
			// check that no other query is running right now
			if (!$this->__dbCheckConnectionOk()) {
				return false;
			}
			if ($this->cursor_ext[$query_hash]['params'] === []) {
				$this->cursor_ext[$query_hash]['cursor'] =
					$this->db_functions->__dbQuery(
						$this->cursor_ext[$query_hash]['query']
					);
			} else {
				$this->cursor_ext[$query_hash]['cursor'] =
					$this->db_functions->__dbQueryParams(
						$this->cursor_ext[$query_hash]['query'],
						$this->cursor_ext[$query_hash]['params']
					);
			}
			// if still no cursor ...
			if (!$this->cursor_ext[$query_hash]['cursor']) {
				// internal error handling
				$this->__dbError(13, $this->cursor_ext[$query_hash]['cursor'], context: [
					'query' => $this->cursor_ext[$query_hash]['query'],
					'params' => $this->cursor_ext[$query_hash]['params'],
					'location' => 'dbReturn'
				]);
				return false;
			} else {
				$first_call = true;
			}
		} // only go if NO cursor exists

		// if cursor exists ...
		if (
			$this->cursor_ext[$query_hash]['cursor'] instanceof \PgSql\Result ||
			$this->cursor_ext[$query_hash]['cursor'] == 1
		) {
			if ($first_call === true) {
				$this->cursor_ext[$query_hash]['log'][] = 'First call';
				// count the rows returned (if select)
				$this->cursor_ext[$query_hash]['num_rows'] =
					$this->db_functions->__dbNumRows($this->cursor_ext[$query_hash]['cursor']);
				// also set last return
				$this->num_rows = $this->cursor_ext[$query_hash]['num_rows'];
				// count the fields
				$this->cursor_ext[$query_hash]['num_fields'] =
					$this->db_functions->__dbNumFields($this->cursor_ext[$query_hash]['cursor']);
				$this->num_fields = $this->cursor_ext[$query_hash]['num_fields'];
				// set field names
				$this->cursor_ext[$query_hash]['field_names'] = [];
				for ($i = 0; $i < $this->cursor_ext[$query_hash]['num_fields']; $i++) {
					$this->cursor_ext[$query_hash]['field_names'][] =
						$this->db_functions->__dbFieldName(
							$this->cursor_ext[$query_hash]['cursor'],
							$i
						);
				}
				$this->field_names = $this->cursor_ext[$query_hash]['field_names'];
				// field types
				$this->cursor_ext[$query_hash]['field_types'] = [];
				for ($i = 0; $i < $this->cursor_ext[$query_hash]['num_fields']; $i++) {
					$this->cursor_ext[$query_hash]['field_types'][] =
						$this->db_functions->__dbFieldType(
							$this->cursor_ext[$query_hash]['cursor'],
							$i
						);
				}
				$this->field_types = $this->cursor_ext[$query_hash]['field_types'];
				// combined name => type
				$this->cursor_ext[$query_hash]['field_name_types'] = array_combine(
					$this->field_names,
					$this->field_types
				);
				$this->field_name_types = $this->cursor_ext[$query_hash]['field_name_types'];
				// reset first call var
				$first_call = false;
				// reset the internal pos counter
				$this->cursor_ext[$query_hash]['pos'] = 0;
				// reset the global (for cache) read counter
				$this->cursor_ext[$query_hash]['read_rows'] = 0;
				// reset read finished flag
				$this->cursor_ext[$query_hash]['finished'] = false;
				$this->cursor_ext[$query_hash]['read_finished'] = false;
				$this->cursor_ext[$query_hash]['db_read_finished'] = false;
				// set cursor ccached flag based on cache flag
				if ($cache < self::NO_CACHE) {
					$this->cursor_ext[$query_hash]['cached'] = true;
				}
			}
			// main database read if not all read and we have an active cursor
			if (
				$this->cursor_ext[$query_hash]['read_rows'] !=
					$this->cursor_ext[$query_hash]['num_rows'] &&
				!is_int($this->cursor_ext[$query_hash]['cursor'])
			) {
				$return = $this->__dbConvertEncoding(
					$this->__dbConvertType(
						$this->db_functions->__dbFetchArray(
							$this->cursor_ext[$query_hash]['cursor'],
							$this->db_functions->__dbResultType($assoc_only)
						)
					)
				);
				$this->cursor_ext[$query_hash]['log'][] = 'DB Reading data: '
					. (is_bool($return) ? 'EOF' : 'DATA');
				// if returned is NOT an array, abort to false
				if (!is_array($return)) {
					$return = false;
				}
			}
			// read from cache, or do partial cache set and caching
			if (!$return && $cache == self::USE_CACHE) {
				// check if end of output ...
				if (
					$this->cursor_ext[$query_hash]['pos'] >=
						$this->cursor_ext[$query_hash]['num_rows']
				) {
					$this->cursor_ext[$query_hash]['log'][] = 'USE CACHE, end';
					// finish read
					$this->cursor_ext[$query_hash]['finished'] = true;
					// reset pos for next read
					$this->cursor_ext[$query_hash]['pos'] = 0;
					// if not reset given, set the cursor to true, so in a cached
					// call on a different page we don't get problems from
					// DB connection (as those will be LOST)
					$this->cursor_ext[$query_hash]['cursor'] = 1;
					$return = false;
				} else {
					$this->cursor_ext[$query_hash]['log'][] = 'USE CACHE, read data';
					$this->cursor_ext[$query_hash]['read_finished'] = false;
					$this->cursor_ext[$query_hash]['finished'] = false;
					// cached data read
					$return = $this->__dbReturnCacheRead($query_hash, $assoc_only);
					if (
						$this->cursor_ext[$query_hash]['pos'] ==
						$this->cursor_ext[$query_hash]['num_rows']
					) {
						$this->cursor_ext[$query_hash]['log'][] = 'USE CACHE, all cache rows read';
						$this->cursor_ext[$query_hash]['read_finished'] = true;
					}
				}
			} else {
				// return row, if last && reset, then unset the whole hash array
				if (!$return && $this->cursor_ext[$query_hash]['pos']) {
					$this->cursor_ext[$query_hash]['pos'] = 0;
					$this->cursor_ext[$query_hash]['cursor'] = 1;
					$this->cursor_ext[$query_hash]['finished'] = true;
					// for clear cache, clear cache, else only write log info
					if ($cache == self::CLEAR_CACHE) {
						$this->cursor_ext[$query_hash]['log'][] = 'CLEAR CACHE, end';
						// unset data block only
						$this->cursor_ext[$query_hash]['data'] = [];
						$this->cursor_ext[$query_hash]['cached'] = false;
					} elseif ($cache == self::READ_NEW) {
						$this->cursor_ext[$query_hash]['log'][] = 'READ NEW, end';
					} elseif ($cache == self::NO_CACHE) {
						$this->cursor_ext[$query_hash]['log'][] = 'NO CACHE, end';
					}
				}
				// if something found, write data into hash array
				if ($return) {
					$this->cursor_ext[$query_hash]['log'][] = 'Return Data';
					// internal position counter
					$this->cursor_ext[$query_hash]['pos']++;
					$this->cursor_ext[$query_hash]['read_rows']++;
					// read is finished
					if (
						$this->cursor_ext[$query_hash]['read_rows'] ==
							$this->cursor_ext[$query_hash]['num_rows']
					) {
						$this->cursor_ext[$query_hash]['log'][] = 'Return data all db rows read';
						$this->cursor_ext[$query_hash]['db_read_finished'] = true;
						$this->cursor_ext[$query_hash]['read_finished'] = true;
					}
					// if reset is < NO_CACHE level caching is done, else no
					if ($cache < self::NO_CACHE) {
						$this->cursor_ext[$query_hash]['log'][] = 'Cache Data';
						// why was this here?
						// $temp = [];
						// foreach ($return as $field_name => $data) {
						// 	$temp[$field_name] = $data;
						// }
						$this->cursor_ext[$query_hash]['data'][] = $return;
					}
				} // cached data if
			} // cached or not if
		} // cursor exists
		return $return;
	}

	/**
	 * executes the query and returns & sets the internal cursor
	 * furthermore this functions also sets varios other vars
	 * like num_rows, num_fields, etc depending on query
	 * for INSERT INTO queries it is highly recommended to set the pk_name to avoid an
	 * additional read from the database for the PK NAME
	 * Wrapper for dbExecParams without params
	 *
	 * @param  string $query   the query, if not given,
	 *                         the query class var will be used
	 *                         if this was not set, method will quit with false
	 * @param  string $pk_name optional primary key name, for insert id
	 *                         return if the pk name is very different
	 *                         if pk name is table name and _id, pk_name
	 *                         is not needed to be set
	 *                         if NULL is given here, no RETURNING will be auto added
	 * @return \PgSql\Result|false    cursor for this query or false on error
	 */
	public function dbExec(
		string $query = '',
		string $pk_name = ''
	): \PgSql\Result|false {
		// just calls the same without any params
		// which will trigger normal pg_query call
		return $this->dbExecParams($query, [], $pk_name);
	}

	/**
	 * Execute any query, but with the use of placeholders
	 *
	 * @param  string $query   Query, if not given, query class var will be used
	 *                         if this was not set, method will quit with false
	 * @param  array<mixed> $params  Parameters to be replaced.
	 *         NOTE: bytea data cannot be used here (pg_query_params)
	 * @param  string $pk_name Optional primary key name, for insert id
	 *                         return if the pk name is very different
	 *                         if pk name is table name and _id, pk_name
	 *                         is not needed to be set
	 *                         if NULL is given here, no RETURNING will be auto added
	 * @return \PgSql\Result|false  cursor for this query or false on error
	 */
	public function dbExecParams(
		string $query = '',
		array $params = [],
		string $pk_name = ''
	): \PgSql\Result|false {
		$this->__dbErrorReset();
		// prepare and check if we can actually run it
		if (($query_hash = $this->__dbPrepareExec($query, $params, $pk_name)) === false) {
			// bail if no query hash set
			return false;
		}
		// checks if the params count given matches the expected count
		if ($this->__dbCheckQueryParams($this->query, $this->params) === false) {
			return false;
		}
		// ** actual db exec call
		if ($this->params === []) {
			$cursor = $this->db_functions->__dbQuery($this->query);
		} else {
			$cursor = $this->db_functions->__dbQueryParams($this->query, $this->params);
		}
		// if we faield, just set the master cursors to false too
		$this->cursor = $cursor;
		if ($cursor === false) {
			$this->__dbError(13, context: [
				'query' => $this->query,
				'params' => $this->params,
				'location' => 'dbExecParams',
			]);
			return false;
		}
		// if FALSE returned, set error stuff
		// run the post exec processing
		if (!$this->__dbPostExec()) {
			return false;
		} else {
			return $this->cursor;
		}
	}

	/**
	 * executes a cursor and returns the data, if no more data 0 will be returned
	 *
	 * @param  \PgSql\Result|false $cursor the cursor from db_exec or
	 *                                       pg_query/pg_exec/mysql_query
	 *                                       if not set will use internal cursor,
	 *                                       if not found, stops with 0 (error)
	 *                                       (PgSql\Result)
	 * @param  bool             $assoc_only  false is default,
	 *                                       if true only named rows,
	 *                                       not numbered index rows
	 * @return array<mixed>|false            row array or false on error
	 */
	public function dbFetchArray(\PgSql\Result|false $cursor = false, bool $assoc_only = false): array|false
	{
		$this->__dbErrorReset();
		// set last available cursor if none set or false
		if ($cursor === false) {
			$cursor = $this->cursor;
		}
		if ($cursor === false) {
			$this->__dbError(12);
			return false;
		}
		return $this->__dbConvertEncoding(
			$this->__dbConvertType(
				$this->db_functions->__dbFetchArray(
					$cursor,
					$this->db_functions->__dbResultType($assoc_only)
				)
			)
		);
	}

	/**
	 * returns the FIRST row of the given query
	 * wrapper for dbReturnRowParms
	 *
	 * @param  string $query      the query to be executed
	 * @param  bool   $assoc_only if true, only return assoc entry (default false)
	 * @return array<mixed>|false row array or false on error
	 */
	public function dbReturnRow(string $query, bool $assoc_only = false): array|false
	{
		return $this->dbReturnRowParams($query, [], $assoc_only);
	}

	/**
	 * Returns the first row only for the given query
	 * Uses db_query_params
	 *
	 * @param  string       $query      the query to be executed
	 * @param  array<mixed> $params     params to be used in query
	 * @param  bool         $assoc_only if true, only return assoc entry (default false)
	 * @return array<mixed>|false       row array or false on error
	 */
	public function dbReturnRowParams(
		string $query,
		array $params = [],
		bool $assoc_only = false
	): array|false {
		$this->__dbErrorReset();
		if (!$query) {
			$this->__dbError(11);
			return false;
		}
		// before doing ANYTHING check if query is
		// "SELECT ..." everything else does not work
		if (!$this->dbCheckQueryForSelect($query)) {
			$this->__dbError(17, false, context: [
				'query' => $query,
				'params' => $params,
				'assoc_only' => $assoc_only,
				'location' => 'dbReturnRowParams'
			]);
			return false;
		}
		$cursor = $this->dbExecParams($query, $params);
		if ($cursor === false) {
			return false;
		}
		$result = $this->dbFetchArray($cursor, $assoc_only);
		return $result;
	}

	/**
	 * creates an array of hashes of the query (all data)
	 * Wrapper for dbReturnArrayParams
	 *
	 * @param  string $query      the query to be executed
	 * @param  bool   $assoc_only if true, only name ref are returned (default true)
	 * @return array<mixed>|false array of hashes (row -> fields), false on error
	 */
	public function dbReturnArray(string $query, bool $assoc_only = true): array|false
	{
		return $this->dbReturnArrayParams($query, [], $assoc_only);
	}

	/**
	 * Creates an array of hashes of all data returned from the query
	 * uses db_query_param
	 *
	 * @param  string       $query      the query to be executed
	 * @param  array<mixed> $params     params to be used in query
	 * @param  bool         $assoc_only if true, only name ref are returned (default true)
	 * @return array<mixed>|false       array of hashes (row -> fields), false on error
	 */
	public function dbReturnArrayParams(
		string $query,
		array $params = [],
		bool $assoc_only = true
	): array|false {
		$this->__dbErrorReset();
		if (!$query) {
			$this->__dbError(11);
			return false;
		}
		// before doing ANYTHING check if query is "SELECT ..." everything else does not work
		if (!$this->dbCheckQueryForSelect($query)) {
			$this->__dbError(17, false, context: [
				'query' => $query,
				'params' => $params,
				'assoc_only' => $assoc_only,
				'location' => 'dbReturnArrayParams'
			]);
			return false;
		}
		$cursor = $this->dbExecParams($query, $params);
		if ($cursor === false) {
			return false;
		}
		$rows = [];
		while (is_array($res = $this->dbFetchArray($cursor, $assoc_only))) {
			$rows[] = $res;
		}
		return $rows;
	}

	// ***************************
	// CURSOR RETURN
	// ***************************

	/**
	 * Get current set cursor or false if not set or error
	 *
	 * @return \PgSql\Result|false
	 */
	public function dbGetCursor(): \PgSql\Result|false
	{
		return $this->cursor ?? false;
	}

	// ***************************
	// CURSOR EXT CACHE RESET
	// ***************************

	/**
	 * resets all data stored to this query
	 * @param  string       $query  The Query whose cache should be cleaned
	 * @param  array<mixed> $params If the query is params type we need params
	 *                              data to create a unique call one, optional
	 * @return bool                 False if query not found, true if success
	 */
	public function dbCacheReset(string $query, array $params = [], bool $show_warning = true): bool
	{
		$query_hash = $this->dbBuildQueryHash($query, $params);
		// clears cache for this query
		if (
			$show_warning &&
			empty($this->cursor_ext[$query_hash]['query'])
		) {
			$this->__dbWarning(18, context: [
				'query' => $query,
				'params' => $params,
				'hash' => $query_hash,
			]);
			return false;
		}
		unset($this->cursor_ext[$query_hash]);
		return true;
	}

	// ***************************
	// CURSOR EXT DATA CHECK
	// ***************************

	/**
	 * returns the full array for cursor ext
	 * or cursor for one query
	 * or detail data fonr one query cursor data
	 *
	 * @param  string|null $query        Query string, if not null convert to hash
	 *                                   and return set cursor ext for only this
	 *                                   if not found or null return null
	 * @param  array<mixed> $params      Optional params for query hash get
	 * @param  string       $query_field [=''] optional query field to get
	 * @return array<mixed>|string|int|\PgSql\Result|null
	 *                                   Cursor Extended array full if no parameter
	 *                                   Key is hash string from query run
	 *                                   Or cursor data entry if query field is set
	 *                                   If nothing found return null
	 */
	public function dbGetCursorExt(
		?string $query = null,
		array $params = [],
		string $query_field = ''
	): array|string|int|\PgSql\Result|null {
		if ($query === null) {
			return $this->cursor_ext;
		}
		$query_hash = $this->dbBuildQueryHash($query, $params);
		if (
			!empty($this->cursor_ext) &&
			isset($this->cursor_ext[$query_hash])
		) {
			if (empty($query_field)) {
				return $this->cursor_ext[$query_hash];
			} else {
				return $this->cursor_ext[$query_hash][$query_field] ?? null;
			}
		} else {
			return null;
		}
	}

	/**
	 * returns the current position the read out
	 *
	 * @param  string       $query  Query to find in cursor_ext
	 * @param  array<mixed> $params If the query is params type we need params
	 *                              data to create a unique call one, optional
	 * @return int|false|null       query position (row pos), false on error
	 */
	public function dbGetCursorPos(string $query, array $params = []): int|false|null
	{
		$this->__dbErrorReset();
		if (!$query) {
			$this->__dbError(11);
			return false;
		}
		$query_hash = $this->dbBuildQueryHash($query, $params);
		if (
			!empty($this->cursor_ext) &&
			isset($this->cursor_ext[$query_hash])
		) {
			return (int)$this->cursor_ext[$query_hash]['pos'];
		} else {
			return null;
		}
	}

	/**
	 * returns the number of rows for the current select query
	 *
	 * @param  string       $query  Query to find in cursor_ext
	 * @param  array<mixed> $params If the query is params type we need params
	 *                              data to create a unique call one, optional
	 * @return int|false|null       numer of rows returned, false on error
	 */
	public function dbGetCursorNumRows(string $query, array $params = []): int|false|null
	{
		$this->__dbErrorReset();
		if (!$query) {
			$this->__dbError(11);
			return false;
		}
		$query_hash = $this->dbBuildQueryHash($query, $params);
		if (
			!empty($this->cursor_ext) &&
			isset($this->cursor_ext[$query_hash])
		) {
			return (int)$this->cursor_ext[$query_hash]['num_rows'];
		} else {
			return null;
		}
	}

	// ***************************
	// MAXIMUM QUERY EXECUTION CHECK HELPERS
	// ***************************

	/**
	 * resets the call times for the max query called to 0
	 * USE CAREFULLY: rather make the query prepare -> execute
	 *
	 * @param  string       $query  query string
	 * @param  array<mixed> $params If the query is params type we need params
	 *                              data to create a unique call one, optional
	 * @return void
	 */
	public function dbResetQueryCalled(string $query, array $params = []): void
	{
		$this->query_called[$this->dbBuildQueryHash($query, $params)] = 0;
	}

	/**
	 * gets how often a query was called already
	 *
	 * @param  string       $query  query string
	 * @param  array<mixed> $params If the query is params type we need params
	 *                              data to create a unique call one, optional
	 * @return int                  count of times the query was executed
	 */
	public function dbGetQueryCalled(string $query, array $params = []): int
	{
		$query_hash = $this->dbBuildQueryHash($query, $params);
		if (!empty($this->query_called[$query_hash])) {
			return $this->query_called[$query_hash];
		} else {
			return 0;
		}
	}

	// ***************************
	// PREPARED QUERY WORK
	// ***************************

	/**
	 * prepares a query
	 * for INSERT INTO queries it is highly recommended
	 * to set the pk_name to avoid an additional
	 * read from the database for the PK NAME
	 *
	 * @param  string        $stm_name statement name
	 * @param  string        $query    queryt string to run
	 * @param  string        $pk_name  optional primary key
	 * @return \PgSql\Result|bool      false on error, true on warning or
	 *                                 result on full ok
	 */
	public function dbPrepare(
		string $stm_name,
		string $query,
		string $pk_name = ''
	): \PgSql\Result|bool {
		$this->__dbErrorReset();
		$matches = [];
		if (!$query) {
			$this->__dbError(11);
			return false;
		}
		// if no DB Handler drop out
		if (!$this->dbh) {
			// if reconnect fails drop out
			if (!$this->__connectToDB()) {
				return false;
			}
		}
		// check that no other query is running right now
		if (!$this->__dbCheckConnectionOk()) {
			return false;
		}
		// no statement name
		if (empty($stm_name)) {
			$this->__dbError(25);
			return false;
		}
		// check if this was already prepared
		if (
			!array_key_exists($stm_name, $this->prepare_cursor) ||
			!is_array($this->prepare_cursor[$stm_name])
		) {
			// init cursor
			$this->prepare_cursor[$stm_name] = [
				'pk_name' => '',
				'count' => 0,
				'query' => '',
				'query_raw' => $query,
				'result' =>  null,
				'returning_id' => false,
				'placeholder_converted' => [],
			];
			// if this is an insert query, check if we can add a return
			if ($this->dbCheckQueryForInsert($query, true)) {
				if ($pk_name != 'NULL') {
					// set primary key name
					// current: only via parameter
					if (!$pk_name) {
						// read the primary key from the table,
						// if we do not have one, we get nothing in return
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
					if (
						!preg_match(self::REGEX_RETURNING, $query) &&
						$this->prepare_cursor[$stm_name]['pk_name']
					) {
						$query .= " RETURNING " . $this->prepare_cursor[$stm_name]['pk_name'];
						$this->prepare_cursor[$stm_name]['returning_id'] = true;
					} elseif (
						preg_match(self::REGEX_RETURNING, $query, $matches) &&
						$this->prepare_cursor[$stm_name]['pk_name']
					) {
						// if returning exists but not pk_name, add it
						if (!preg_match("/{$this->prepare_cursor[$stm_name]['pk_name']}/", $matches[1])) {
							$query .= " , " . $this->prepare_cursor[$stm_name]['pk_name'];
						}
						$this->prepare_cursor[$stm_name]['returning_id'] = true;
					}
				} else {
					$this->prepare_cursor[$stm_name]['pk_name'] = $pk_name;
				}
			}
			// QUERY PARAMS: run query params check and rewrite
			if ($this->dbGetConvertPlaceholder() === true) {
				try {
					$this->placeholder_converted = ConvertPlaceholder::convertPlaceholderInQuery(
						$query,
						null,
						$this->dbGetConvertPlaceholderTarget()
					);
					// write the new queries over the old
					if (!empty($this->placeholder_converted['query'])) {
						$query = $this->placeholder_converted['query'];
					}
					$this->prepare_cursor[$stm_name]['placeholder_converted'] = $this->placeholder_converted;
				} catch (\OutOfRangeException $e) {
					$this->__dbError($e->getCode(), context:[
						'statement_name' => $stm_name,
						'query' => $query,
						'location' => 'dbPrepare',
						'error' => 'OutOfRangeException',
						'exception' => $e
					]);
					return false;
				} catch (\RuntimeException $e) {
					$this->__dbError($e->getCode(), context:[
						'statement_name' => $stm_name,
						'query' => $query,
						'location' => 'dbPrepare',
						'error' => 'RuntimeException',
						'exception' => $e
					]);
					return false;
				}
			}
			// check prepared curser parameter count
			$this->prepare_cursor[$stm_name]['count'] = $this->__dbCountQueryParams($query);
			$this->prepare_cursor[$stm_name]['query'] = $query;
			$result = $this->db_functions->__dbPrepare($stm_name, $query);
			if ($result) {
				$this->prepare_cursor[$stm_name]['result'] = $result;
				return $result;
			} else {
				$this->__dbError(
					21,
					false,
					context: [
						'statement_name' => $stm_name,
						'query' => $query,
						'pk_name' => $pk_name,
					]
				);
				return $result;
			}
		} else {
			// if we try to use the same statement name for a differnt query, error abort
			if ($this->prepare_cursor[$stm_name]['query_raw'] != $query) {
				// thrown error
				$this->__dbError(26, false, context: [
					'statement_name' => $stm_name,
					'prepared_query' => $this->prepare_cursor[$stm_name]['query'],
					'prepared_query_raw' => $this->prepare_cursor[$stm_name]['query_raw'],
					'query' => $query,
					'pk_name' => $pk_name,
				]);
				return false;
			} else {
				// thrown warning
				$this->__dbWarning(20, false, context: [
					'statement_name' => $stm_name,
					'query' => $query,
					'pk_name' => $pk_name,
				]);
				return true;
			}
		}
	}

	/**
	 * runs a prepare query
	 *
	 * @param  string       $stm_name statement name for the query to run
	 * @param  array<mixed> $data     data to run for this query, empty array for none
	 * @return \PgSql\Result|false     false on error, or result on OK
	 */
	public function dbExecute(string $stm_name, array $data = []): \PgSql\Result|false
	{
		$this->__dbErrorReset();
		// if no DB Handler drop out
		if (!$this->dbh) {
			// if reconnect fails drop out
			if (!$this->__connectToDB()) {
				return false;
			}
		}
		// check that no other query is running right now
		if (!$this->__dbCheckConnectionOk()) {
			return false;
		}
		// no statement name
		if (empty($stm_name)) {
			$this->__dbError(25);
			return false;
		}
		// if we do not have no prepare cursor array entry
		// for this statement name, abort
		if (
			empty($this->prepare_cursor[$stm_name]) ||
			!is_array($this->prepare_cursor[$stm_name])
		) {
			$this->__dbError(
				24,
				false,
				$stm_name . ': We do not have a prepared query entry for this statement name.',
				context: ['statement_name' => $stm_name]
			);
			return false;
		}
		$this->__dbDebug(
			'db',
			$this->prepare_cursor[$stm_name]['query'],
			'dbExecute',
			'Qpe',
			error_data: array_merge([
				'statement_name' => $stm_name,
			], $this->__dbDebugPrepareContext(
				$this->prepare_cursor[$stm_name]['query'],
				$data
			))
		);
		// if the count does not match
		if ($this->prepare_cursor[$stm_name]['count'] != count($data)) {
			$this->__dbError(
				23,
				false,
				'(' . $stm_name . ') '
					. 'Need: ' . $this->prepare_cursor[$stm_name]['count'] . ', has: ' . count($data),
				context: [
					'statement_name' => $stm_name,
					'query' => $this->prepare_cursor[$stm_name]['query'],
					'params' => $data,
					'placeholder_needed' => $this->prepare_cursor[$stm_name]['count'],
					'placeholder_provided' => count($data)
				]
			);
			return false;
		}
		$result = $this->db_functions->__dbExecute($stm_name, $data);
		if ($result === false) {
			$this->log->error('ExecuteData: ERROR in STM[' . $stm_name . '|'
				. $this->prepare_cursor[$stm_name]['result'] . ']: '
				. \CoreLibs\Debug\Support::prAr($data));
			$this->__dbError(
				22,
				$this->prepare_cursor[$stm_name]['result'],
				context: [
					'statement_name' => $stm_name,
					'query' => $this->prepare_cursor[$stm_name]['query'],
					'params' => $data
				]
			);
			return false;
		}
		if (
			// pure insert wth pk name
			($this->dbCheckQueryForInsert($this->prepare_cursor[$stm_name]['query'], true) &&
			$this->prepare_cursor[$stm_name]['pk_name'] != 'NULL') ||
			// insert or update with returning set
			($this->dbCheckQueryForInsert($this->prepare_cursor[$stm_name]['query']) &&
			$this->prepare_cursor[$stm_name]['returning_id'] === true
			)
		) {
			$this->__dbSetInsertId(
				$this->prepare_cursor[$stm_name]['returning_id'],
				$this->prepare_cursor[$stm_name]['query'],
				$this->prepare_cursor[$stm_name]['pk_name'],
				$result,
				$stm_name
			);
		}
		return $result;
	}

	// ***************************
	// ASYNCHRONUS EXECUTION/CHECK
	// ***************************

	/**
	 * executes the query async so other methods can be run at the same time
	 * Wrapper for dbExecParamsAsync
	 * NEEDS : dbCheckAsync
	 *
	 * @param  string $query   query to run
	 * @param  string $pk_name optional primary key name, only used with
	 *                         insert for returning call
	 * @return bool            true if async query was sent ok,
	 *                         false on error
	 */
	public function dbExecAsync(string $query, string $pk_name = ''): bool
	{
		return $this->dbExecParamsAsync($query, [], $pk_name);
	}

	/**
	 * eexecutes the query async so other methods can be run at the same time
	 * Runs with db_send_query_params
	 * NEEDS : dbCheckAsync
	 *
	 * @param  string $query query to run
	 * @param  array<mixed> $params
	 * @param  string $pk_name optional primary key name, only used with
	 *                         insert for returning call
	 * @return bool            true if async query was sent ok,
	 *                         false on error
	 */
	public function dbExecParamsAsync(
		string $query,
		array $params = [],
		string $pk_name = ''
	): bool {
		$this->__dbErrorReset();
		// prepare and check if we can actually run the query
		if (
			($query_hash = $this->__dbPrepareExec($query, $params, $pk_name)) === false
		) {
			// bail if no hash set
			return false;
		}
		// checks if the params count given matches the expected count
		if ($this->__dbCheckQueryParams($this->query, $this->params) === false) {
			return false;
		}
		// ** actual db exec call
		if ($params === []) {
			$status = $this->db_functions->__dbSendQuery($this->query);
		} else {
			$status = $this->db_functions->__dbSendQueryParams($this->query, $this->params);
		}
		// run the async query, this just returns true or false
		// the actually result is in dbCheckAsync
		if (!$status) {
			// if failed, process here
			$this->__dbError(40, context: [
				'query' => $this->query,
				'params' => $this->params,
				'pk_name' => $pk_name,
			]);
			return false;
		} else {
			$this->async_running = (string)$query_hash;
			// all ok, we return true
			// (as would be from the original send query function)
			return true;
		}
	}

	/**
	 * TODO write dbPrepareAsync
	 * Asnychronus prepare call
	 * NEEDS : dbCheckAsync
	 *
	 * @param  string $stm_name
	 * @param  string $query
	 * @param  string $pk_name
	 * @return bool
	 */
	public function dbPrepareAsync(
		string $stm_name,
		string $query,
		string $pk_name = ''
	): bool {
		$status = $this->db_functions->__dbSendPrepare($stm_name, $query);
		return $status;
	}

	/**
	 * TODO write dbExecuteAsync
	 * Asynchronus execute call
	 * NEEDS : dbCheckAsync
	 *
	 * @param  string       $stm_name
	 * @param  array<mixed> $data
	 * @return bool
	 */
	public function dbExecuteAsync(
		string $stm_name,
		array $data = []
	): bool {
		$status = $this->db_functions->__dbSendExecute($stm_name, $data);
		return $status;
	}

	/**
	 * checks a previous async query and returns data if finished
	 * NEEDS : dbExecAsync
	 *
	 * @return \PgSql\Result|bool cursor resource if the query is still running,
	 *                            false if an error occured or cursor of that query
	 */
	public function dbCheckAsync(): \PgSql\Result|bool
	{
		$this->__dbErrorReset();
		// if there is actually a async query there
		if (!empty($this->async_running)) {
			// alternative try __dbConnectionBusySocketWait
			if ($this->db_functions->__dbConnectionBusy()) {
				return true;
			} else {
				$cursor = $this->db_functions->__dbGetResult();
				if ($cursor === false) {
					$this->__dbError(43);
					return false;
				}
				// get the result/or error
				$this->cursor = $cursor;
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
			$this->__dbError(
				42,
				false
			);
			return false;
		}
	}

	/**
	 * Returns the current async running query hash
	 *
	 * @return string Current async running query hash, empty string for nothing
	 */
	public function dbGetAsyncRunning(): string
	{
		return $this->async_running;
	}

	// ***************************
	// COMPLEX WRITE WITH CONFIG ARRAYS
	// ***************************

	// ** REMARK **
	// db_write_data is the old without separate update no write list
	// db_write_data_ext is the extended with additional array
	// for no write list for update

	/**
	 * writes into one table based on array of table columns
	 *
	 * @param  array<mixed> $write_array     list of elements to write
	 * @param  array<mixed> $not_write_array list of elements not to write
	 * @param  int|null     $primary_key     id key to decide if we write insert or update
	 * @param  string       $table           name for the target table
	 * @param  array<mixed> $data            data array to override _POST data
	 * @return int|false                     primary key
	 */
	public function dbWriteData(
		array $write_array,
		array $not_write_array,
		?int $primary_key,
		string $table,
		array $data = []
	): int|false {
		$not_write_update_array = [];
		return $this->dbWriteDataExt(
			$write_array,
			$primary_key,
			$table,
			$not_write_array,
			$not_write_update_array,
			$data
		);
	}

	/**
	 * writes into one table based on array of table columns
	 * PARAM INFO: $primary key
	 * this can be a plain string/int and will be internal transformed into the array form
	 * or it takes the array form of array [row => column, value => pk value]
	 * @param  array<mixed>                 $write_array     list of elements to write
	 * @param  null|int|string|array<mixed> $primary_key     primary key string or array set
	 * @param  string                       $table           name for the target table
	 * @param  array<mixed>                 $not_write_array list of elements not to write (optional)
	 * @param  array<mixed>                 $not_write_update_array list of elements not
	 *                                                       to write during update (optional)
	 * @param  array<mixed>                 $data            optional array with data
	 *                                                       if not _POST vars are used
	 * @return int|false                                     primary key
	 */
	public function dbWriteDataExt(
		array $write_array,
		null|int|string|array $primary_key,
		string $table,
		array $not_write_array = [],
		array $not_write_update_array = [],
		array $data = []
	): int|false {
		if (!is_array($primary_key)) {
			$primary_key = [
				'row' => $table . '_id',
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
		$table_data = $this->dbShowTableMetaData(($this->db_schema ? $this->db_schema . '.' : '') . $table);
		if (!is_array($table_data)) {
			return false;
		}
		// @phan HACK
		$primary_key['value'] = $primary_key['value'] ?? '';
		$primary_key['row'] = $primary_key['row'] ?? '';
		// loop through the write array and each field to build the query
		foreach ($write_array as $field) {
			if (
				(
					empty($primary_key['value']) ||
					!in_array($field, $not_write_update_array)
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
				// write if the field has to be not null, or if there is
				// no data and the field has no default values or if there
				// is data or if this is an update and there is no data (set null)
				if (
					($not_null && $_data) ||
					(!$has_default && !$_data) ||
					(is_numeric($_data) && $_data) ||
					($primary_key['value'] && !$_data) ||
					$_data
				) {
					if ($q_sub_value && !$primary_key['value']) {
						$q_sub_value .= ', ';
					}
					if ($q_sub_data) {
						// && (!$primary_key ||
						// ($primary_key && !in_array($field, $not_write_array))))
						$q_sub_data .= ', ';
					}
					if ($primary_key['value']) {
						$q_sub_data .= $field . ' = ';
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
						$q_sub_data .= isset($_data) ?
							"'" . (
								$is_bool ?
									$this->dbBoolean($_data, true) :
									$this->dbEscapeString($_data)
							) . "'" :
							'NULL';
					}
				}
			}
		}

		// first work contact itself (we need contact id for everything else)
		if ($primary_key['value'] && $primary_key['row']) {
			$q = 'UPDATE ' . $table . ' SET ';
			$q .= $q_sub_data . ' ';
			$q .= 'WHERE ' . $primary_key['row'] . ' = ' . $primary_key['value'];
		} else {
			$q = 'INSERT INTO ' . $table . ' (';
			$q .= $q_sub_value;
			$q .= ') VALUES (';
			$q .= $q_sub_data;
			$q .= ')';
		}
		if (!$this->dbExec($q)) {
			return false;
		}
		if (!$primary_key['value']) {
			$primary_key['value'] = $this->dbGetInsertPK();
		}
		// if there is not priamry key value field return false
		if (!is_numeric($primary_key['value'])) {
			return false;
		}
		return (int)$primary_key['value'];
	}

	// ***************************
	// INTERNAL SETTINGS READ/CHANGE
	// ***************************

	/**
	 * switches the debug flag on or off
	 *
	 * @param  bool $debug True/False to turn debugging in this calss on or off
	 * @return void
	 */
	public function dbSetDebug(bool $debug): void
	{
		$this->db_debug = $debug;
	}

	/**
	 * Switches db debug flag on or off
	 * OR
	 * with the optional parameter fix sets debug
	 * returns current set stats
	 *
	 * @param  bool|null $debug Flag to turn debug on off or null for toggle
	 * @return bool             Current debug status
	 *                          True for debug is on, False for off
	 * @deprecated Use dbSetDebug and dbGetDebug
	 */
	public function dbToggleDebug(?bool $debug = null): bool
	{
		if ($debug !== null) {
			$this->db_debug = $debug;
		} else {
			$this->db_debug = $this->db_debug ? false : true;
		}
		return $this->db_debug;
	}

	/**
	 * Return current set db debug flag status
	 *
	 * @return bool Current debug status
	 */
	public function dbGetDebug(): bool
	{
		return $this->db_debug;
	}

	/**
	 * convert db values (set) to php matching types
	 *
	 * @param  Convert $convert
	 * @return void
	 */
	public function dbSetConvertFlag(Convert $convert): void
	{
		$this->convert_type |= $convert->value;
	}

	/**
	 * unsert convert db values flag for converting db to php matching types
	 *
	 * @param  Convert $convert
	 * @return void
	 */
	public function dbUnsetConvertFlag(Convert $convert): void
	{
		$this->convert_type &= ~$convert->value;
	}

	/**
	 * Reset to original config file set for converting db to php matching type
	 *
	 * @return void
	 */
	public function dbResetConvertFlag(): void
	{
		foreach ($this->db_convert_type as $db_convert_type) {
			$this->__setConvertType($db_convert_type);
		}
	}

	/**
	 * check if a convert flag is set for converting db to php matching type
	 *
	 * @param  Convert $convert
	 * @return bool
	 */
	public function dbGetConvertFlag(Convert $convert): bool
	{
		if ($this->convert_type & $convert->value) {
			return true;
		}
		return false;
	}

	/**
	 * Set if we want to auto convert to PDO/\Pg placeholders
	 *
	 * @param  bool $flag
	 * @return void
	 */
	public function dbSetConvertPlaceholder(bool $flag): void
	{
		$this->db_convert_placeholder = $flag;
	}

	/**
	 * get the flag status if we want to auto convert placeholders in the query
	 *
	 * @return bool
	 */
	public function dbGetConvertPlaceholder(): bool
	{
		return $this->db_convert_placeholder;
	}

	/**
	 * Set convert target for placeholders, returns false on error, true on ok
	 *
	 * @param  string $target 'pg' or 'pdo', defined in DB_CONVERT_PLACEHOLDER_TARGET
	 * @return bool
	 */
	public function dbSetConvertPlaceholderTarget(string $target): bool
	{
		if (in_array($target, self::DB_CONVERT_PLACEHOLDER_TARGET)) {
			$this->db_convert_placeholder_target = $target;
			return true;
		}
		return false;
	}

	/**
	 * Get the current placeholder convert target
	 *
	 * @return string
	 */
	public function dbGetConvertPlaceholderTarget(): string
	{
		return $this->db_convert_placeholder_target;
	}

	/**
	 * Set flag if we print the query with replaced placeholders or not
	 *
	 * @param  bool $flag
	 * @return void
	 */
	public function dbSetDebugReplacePlaceholder(bool $flag): void
	{
		$this->db_debug_replace_placeholder = $flag;
	}

	/**
	 * get the current setting for the debug replace placeholder
	 *
	 * @return bool True for replace query, False for not
	 */
	public function dbGetDebugReplacePlaceholder(): bool
	{
		return $this->db_debug_replace_placeholder;
	}

	/**
	 * set max query calls, set to -1 to disable loop
	 * protection. this will generate a warning
	 * empty call (null) will reset to default
	 * @param  int|null $max_calls Set the max loops allowed
	 * @return bool                True for succesfull set
	 */
	public function dbSetMaxQueryCall(?int $max_calls = null): bool
	{
		$this->__dbErrorReset();
		// if null then reset to default
		if ($max_calls === null) {
			$max_calls = self::DEFAULT_MAX_QUERY_CALL;
		}
		// if -1 then disable loop check
		// DANGEROUS, WARN USER
		if ($max_calls == -1) {
			$this->__dbWarning(50, context: ['max_calls' => $max_calls]);
		}
		// negative or 0
		if ($max_calls < -1 || $max_calls == 0) {
			$this->__dbError(51, context: ['max_calls' => $max_calls]);
			// early abort
			return false;
		}
		// ok entry, set
		$this->MAX_QUERY_CALL = $max_calls;
		return true;
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
	 * sets new db schema
	 * @param  string $db_schema Schema name
	 * @return bool              False on failure to find schema value or set schema,
	 *                           True on successful set
	 */
	public function dbSetSchema(string $db_schema): bool
	{
		$this->__dbErrorReset();
		if (empty($db_schema)) {
			$this->__dbError(70);
			return false;
		}
		$status = false;
		$set_value = $this->db_functions->__dbSetSchema($db_schema);
		switch ($set_value) {
			// no problem
			case 0:
				$this->db_schema = $db_schema;
				$status = true;
				break;
			// cursor failed (1) or schema does not exists (2)
			case 1:
			case 2:
			// setting schema failed (3)
			case 3:
				$this->__dbError(71, context: ['schema' => $db_schema]);
				$status = false;
				break;
		}
		return $status;
	}

	/**
	 * returns the current set db schema
	 * @param  bool $class_var If set to true, will return class var and
	 *                         not DB setting
	 * @return string          DB schema name or schema string
	 */
	public function dbGetSchema(bool $class_var = false): string
	{
		// return $this->db_schema;
		if ($class_var === true) {
			return $this->db_schema;
		}
		return $this->db_functions->__dbGetSchema();
	}

	/**
	 * sets the client encoding in the postgres database
	 * @param  string $db_encoding Valid encoding name,
	 *                             so the the data gets converted to this encoding
	 * @return bool                false, or true of db exec encoding set
	 */
	public function dbSetEncoding(string $db_encoding): bool
	{
		$this->__dbErrorReset();
		// no encding, abort
		if (empty($db_encoding)) {
			$this->__dbError(80);
			return false;
		}
		// set client encoding on database side
		$status = false;
		$set_value = $this->db_functions->__dbSetEncoding($db_encoding);
		switch ($set_value) {
			// no problem
			case 0:
				$this->db_encoding = $db_encoding;
				$status = true;
				break;
			// 1 & 2 are for encoding not found
			case 1:
			case 2:
			// 3 is set failed
			case 3:
				$this->__dbError(81, context: ['encoding' => $db_encoding]);
				$status = false;
				break;
		}
		return $status;
	}

	/**
	 * returns the current set client encoding from the connected DB
	 * @param  bool $class_var If set to true, will return class var and
	 *                         not DB setting
	 * @return string          current client encoding
	 */
	public function dbGetEncoding(bool $class_var = false): string
	{
		if ($class_var === true) {
			return $this->db_encoding;
		}
		return $this->db_functions->__dbGetEncoding();
	}

	/**
	 * Resets client encodig back to databse encoding
	 * @return void
	 */
	public function dbResetEncoding(): void
	{
		$this->dbExec('RESET client_encoding');
	}

	/**
	 * Set the to_encoding var that will trigger on return of selected data
	 * on the fly PHP encoding conversion
	 * Alternative use dbSetEcnoding to trigger encoding change on the DB side
	 * Set to empty string to turn off
	 * @param  string $encoding PHP Valid encoding to set
	 * @return void
	 */
	public function dbSetToEncoding(string $encoding): void
	{
		$this->to_encoding = $encoding;
	}

	/**
	 * Returns current set to encoding
	 * @return string Current set encoding
	 */
	public function dbGetToEncoding(): string
	{
		return $this->to_encoding;
	}

	// ***************************
	// QUERY DATA AND DB HANDLER
	// ***************************

	/**
	 * Return current database handler
	 *
	 * @return \PgSql\Connection|false|null
	 */
	public function dbGetDbh(): \PgSql\Connection|false|null
	{
		return $this->dbh;
	}

	/**
	 * Creates hash for query and parameters
	 * Hash is used in all internal storage systems for return data
	 *
	 * @param  string       $query  The query to create the hash from
	 * @param  array<mixed> $params If the query is params type we need params
	 *                              data to create a unique call one, optional
	 * @return string               Hash, as set by hash long
	 */
	public function dbBuildQueryHash(string $query, array $params = []): string
	{
		return Hash::hashLong(
			$query . (
				$params !== [] ?
					'#' . json_encode($params) : ''
			)
		);
	}

	/**
	 * Get current set query
	 *
	 * @return string Current set query string
	 */
	public function dbGetQuery(): string
	{
		return $this->query;
	}

	/**
	 * Clear current query
	 *
	 * @return void
	 */
	public function dbResetQuery(): void
	{
		$this->query = '';
	}

	/**
	 * Get current set params
	 *
	 * @return array<mixed>
	 */
	public function dbGetParams(): array
	{
		return $this->params;
	}

	/**
	 * Rset current set params
	 *
	 * @return void
	 */
	public function dbResetParams(): void
	{
		$this->params = [];
	}

	/**
	 * get the current set query hash
	 *
	 * @return string Current Query hash
	 */
	public function dbGetQueryHash(): string
	{
		return $this->query_hash;
	}

	/**
	 * reset query hash
	 *
	 * @return void
	 */
	public function dbResetQueryHash(): void
	{
		$this->query_hash = '';
	}

	/**
	 * Returns the placeholder convert set or empty
	 *
	 * @return array{}|array{original:array{query:string,params:array<mixed>},type:''|'named'|'numbered'|'question_mark',found:int,matches:array<string>,params_lookup:array<mixed>,query:string,params:array<mixed>}
	 */
	public function dbGetPlaceholderConverted(): array
	{
		return $this->placeholder_converted;
	}

	// ***************************
	// INTERNAL VARIABLES READ POST QUERY RUN
	// ***************************

	/**
	 * returns current set primary key name for last run query
	 * Is empty string if not setable
	 *
	 * @return string Primary key name
	 */
	public function dbGetInsertPKName(): string
	{
		if (!isset($this->insert_id_pk_name)) {
			return '';
		}
		return (string)$this->insert_id_pk_name;
	}

	/**
	 * Returns current primary key for inserted row.
	 * Either a single element for a single insert or an array
	 * if multiple insert values where used.
	 *
	 * @return array<mixed>|string|int|null Current insert query primary key, null on not set
	 */
	public function dbGetInsertPK(): array|string|int|null
	{
		if (empty($this->insert_id_pk_name)) {
			return null;
		}
		return $this->dbGetReturningExt($this->insert_id_pk_name);
	}

	/**
	 * Returns the full RETURNING array
	 * If no parameter given returns as is:
	 * Either as single array level for single insert
	 * Or nested array for multiple insert values
	 *
	 * If key was set only returns those values directly or as array
	 *
	 * On multiple insert return the position for which to return can be set too
	 *
	 * Replacement for insert_id_ext array access before
	 *
	 * @param  string|null  $key            Key to find in insert_id_arr
	 * @param  integer|null $pos            Multiple in array, which row to search in
	 * @return array<mixed>|string|int|null Return value, null for error/not found
	 */
	public function dbGetReturningExt(
		?string $key = null,
		?int $pos = null
	): array|string|int|null {
		// return as is if key is null
		if ($key === null) {
			if (count($this->insert_id_arr) == 1) {
				// return as null if not found
				return $this->insert_id_arr[0] ?? null;
			} else {
				return $this->insert_id_arr;
			}
		}
		// no key string set
		if (empty($key)) {
			return null;
		}
		if (
			count($this->insert_id_arr) == 1 &&
			isset($this->insert_id_arr[0][$key])
		) {
			return $this->insert_id_arr[0][$key];
		} elseif (count($this->insert_id_arr) > 1) {
			// do we try to find at one position
			if ($pos !== null) {
				if (isset($this->insert_id_arr[$pos][$key])) {
					return $this->insert_id_arr[$pos][$key];
				} else {
					return null;
				}
			} else {
				// find in all inside the array
				$__arr = array_column($this->insert_id_arr, $key);
				if (count($__arr)) {
					return $__arr;
				} else {
					return null;
				}
			}
		} else {
			// not found
			return null;
		}
	}

	/**
	 * Always returns the returning block as an array
	 *
	 * @return array<mixed> All returning data as array. even if one row only
	 */
	public function dbGetReturningArray(): array
	{
		return $this->insert_id_arr;
	}

	/**
	 * returns current number of rows that where
	 * affected by UPDATE/SELECT, etc
	 * null on empty
	 *
	 * @return int|null Number of rows or null if not set
	 */
	public function dbGetNumRows(): ?int
	{
		return $this->num_rows ?? null;
	}

	/**
	 * Number of fields in select query
	 *
	 * @return integer|null Number of fields in select or null if not set
	 */
	public function dbGetNumFields(): ?int
	{
		return $this->num_fields ?? null;
	}

	/**
	 * Return field names from query
	 * Order based on order in query
	 *
	 * @return array<string> Field names as array
	 */
	public function dbGetFieldNames(): array
	{
		return $this->field_names;
	}

	/**
	 * Return field types from query
	 * Order based on order in query, use field names to get position
	 *
	 * @return array<string> Field types as array
	 */
	public function dbGetFieldTypes(): array
	{
		return $this->field_types;
	}

	/**
	 * Get the field name to type connection list
	 *
	 * @return array<string,string>
	 */
	public function dbGetFieldNameTypes(): array
	{
		return $this->field_name_types;
	}

	/**
	 * Get the field name for a position
	 *
	 * @param  int          $pos Position number in query
	 * @return false|string      Field name or false for not found
	 */
	public function dbGetFieldName(int $pos): false|string
	{
		return $this->field_names[$pos] ?? false;
	}

	/**
	 * get all the $ placeholders
	 *
	 * @param  string $query
	 * @return array<string>
	 */
	public function dbGetQueryParamPlaceholders(string $query): array
	{
		return $this->db_functions->__dbGetQueryParams($query);
	}

	/**
	 * Return a field type for a field name or pos,
	 * will return false if field is not found in list
	 *
	 * @param  string|int   $name_pos Field name or pos to get the type for
	 * @return false|string           Either the field type or
	 *                                false for not found in list
	 */
	public function dbGetFieldType(int|string $name_pos): false|string
	{
		if (is_numeric($name_pos)) {
			$field_type = $this->field_types[$name_pos] ?? false;
		} else {
			$field_type = $this->field_name_types[$name_pos] ?? false;
		}
		return $field_type;
	}

	/**
	 * Returns the value for given key in statement
	 * Will write error if statemen id does not exist
	 * or key is invalid
	 *
	 * @param string $stm_name  The name of the stored statement
	 * @param string $key       Key field name in prepared cursor array
	 *                          Allowed are: pk_name, count, query, returning_id
	 * @return null|string|int|bool|array<string,mixed> Entry from each of the valid keys
	 *                          Will return false on error
	 *                          Not ethat returnin_id also can return false
	 *                          but will not set an error entry
	 */
	public function dbGetPrepareCursorValue(
		string $stm_name,
		string $key
	): null|string|int|bool|array {
		// if no statement name
		if (empty($stm_name)) {
			$this->__dbError(
				101,
				false,
				'No statement name given'
			);
			return false;
		}
		// if not a valid key
		if (!in_array($key, ['pk_name', 'count', 'query', 'returning_id', 'placeholder_converted'])) {
			$this->__dbError(
				102,
				false,
				'Invalid key name',
				context: ['key' => $key]
			);
			return false;
		}
		// statement name not in prepared list
		if (empty($this->prepare_cursor[$stm_name])) {
			$this->__dbError(
				103,
				false,
				'Statement name does not exist in prepare cursor array',
				context: ['statement_name' => $stm_name]
			);
			return false;
		}
		// key doest not exists, this will never hit as we filter out invalid ones
		if (!isset($this->prepare_cursor[$stm_name][$key])) {
			$this->__dbError(
				104,
				false,
				'Key does not exist in prepare cursor array',
				context: [
					'statement_name' => $stm_name,
					'key' => $key
				]
			);
			return false;
		}
		return $this->prepare_cursor[$stm_name][$key];
	}

	/**
	 * Checks if a prepared query eixsts
	 *
	 * @param  string $stm_name Statement to check
	 * @param  string $query [default=''] If set then query must also match
	 * @return false|int<0,2>             False on missing stm_name
	 *                                    0: ok, 1: stm_name matchin, 2: stm_name and query matching
	 */
	public function dbPreparedCursorStatus(string $stm_name, string $query = ''): false|int
	{
		if (empty($stm_name)) {
			$this->__dbError(
				101,
				false,
				'No statement name given'
			);
			return false;
		}
		// does not exist
		$return_value = 0;
		if (!empty($this->prepare_cursor[$stm_name]['query_raw'])) {
			// statement name eixts
			$return_value = 1;
			if ($this->prepare_cursor[$stm_name]['query_raw'] == $query) {
				// query also matches
				$return_value = 2;
			}
		}
		return $return_value;
	}

	// ***************************
	// ERROR AND WARNING DATA
	// ***************************

	/**
	 * Sets error number that was last
	 * So we always have the last error number stored even if a new
	 * one is created
	 * @param  bool   $transform Set to true to transform into id + error message
	 * @return string            Last error number as string or error message
	 */
	public function dbGetLastError(bool $transform = false): string
	{
		// if no error, return empty
		if (empty($this->error_id)) {
			return '';
		}
		// either error number or error detail string
		if (!$transform) {
			return $this->error_id;
		} else {
			return $this->error_id . ': '
				. ($this->error_string[$this->error_id] ?? '[NO ERROR MESSAGE]');
		}
	}

	/**
	 * Sets warning number that was last
	 * So we always have the last warning number stored even if a new one is created
	 * @param  bool   $transform Set to true to transform into id + warning message
	 * @return string            Last Warning number as string or warning message
	 */
	public function dbGetLastWarning(bool $transform = false): string
	{
		// if no warning, return empty
		if (empty($this->warning_id)) {
			return '';
		}
		// either warning number or warning detail string
		if (!$transform) {
			return $this->warning_id;
		} else {
			return $this->warning_id . ': '
				. ($this->error_string[$this->warning_id] ?? '[NO WARNING MESSAGE]');
		}
	}

	/**
	 * Return the combined warning and error history
	 * TODO: add options to return only needed (Eg error, time based)
	 * @return array<mixed> Complete long error history string
	 */
	public function dbGetCombinedErrorHistory(): array
	{
		return $this->error_history_long;
	}

	// ***************************
	// DEPEREACTED CALLS
	// all call below are no longer in use and throw deprecated errors
	// ***************************

	/**
	 * return current set insert_id as is
	 * @return array<mixed>|string|int|bool|null Primary key value, most likely int
	 *                                           Array for multiple return set
	 *                                           Empty string for unset
	 *                                           Null for error
	 * @deprecated Use ->dbGetInsertPK();
	 */
	public function dbGetReturning(): array|string|int|bool|null
	{
		return $this->dbGetInsertPK();
	}

	// end if db class
}

// __END__

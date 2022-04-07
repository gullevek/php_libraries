<?php

/*********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2003/04/09
* SHORT DESCRIPTION:
*   2018/3/23, the whole class system is transformed to namespaces
*   also all internal class calls are converted to camel case
*
* pgsql wrapper calls
*
* HISTORY:
* 2008/04/16 (cs) wrapper for pg escape string
* 2007/01/11 (cs) add prepare/execute for postgres
* 2006/09/12 (cs) in case db_query retuns false, save the query and
*                 run the query through the send/get procedure to get
*                 correct error data from the db
* 2006/06/26 (cs) added port for db connection
* 2006/04/03 (cs) added meta data for table
* 2005/07/25 (cs) removed the plural s remove, not needed and not 100% working
* 2005/07/07 (cs) the default it is table_name _ id
* 2005/01/19 (cs) changed the pgsql connect, so it dies if it can't connect to the DB
* 2004/09/30 (cs) layout cleanup
*
*
* collection of PostgreSQL wrappers
*
* pg_prepare
* pg_execute
* pg_num_rows
* pg_num_fields
* pg_field_name
* pg_affected_rows (*)
* pg_fetch_array
* pg_query
* pg_send_query
* pg_get_result
* pg_connection_busy
* pg_close
* pg_connect (*)
* pg_meta_data
* pg_escape_string
*
*/

declare(strict_types=1);

namespace CoreLibs\DB\SQL;

// below no ignore is needed if we want to use PgSql interface checks with PHP 8.0
// as main system. Currently all @var sets are written as object
/** @#phan-file-suppress PhanUndeclaredTypeProperty,PhanUndeclaredTypeParameter,PhanUndeclaredTypeReturnType */

class PgSQL implements \CoreLibs\DB\SQL\SqlInterface\SqlFunctions
{
	/** @var string */
	private $last_error_query;
	// NOTE for PHP 8.1 this is no longer a resource
	/** @var object|resource|bool */ // replace object with PgSql\Connection
	private $dbh;

	/**
	 * class constructor, empty does nothing
	 */
	// public function __construct()
	// {
	// }

	/**
	 * queries last error query and returns true or false if error was set
	 * @return bool true/false if last error is set
	 */
	public function __dbLastErrorQuery(): bool
	{
		if ($this->last_error_query) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * wrapper for pg_query, catches error and stores it in class var
	 * @param  string $query Query string
	 * @return object|resource|bool query result (PgSql\Result)
	 */
	public function __dbQuery(string $query)
	{
		$this->last_error_query = '';
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		// read out the query status and save the query if needed
		$result = pg_query($this->dbh, $query);
		if ($result === false) {
			$this->last_error_query = $query;
		}
		return $result;
	}

	/**
	 * Proposed
	 * wrapperf or pg_query_params for queries in the style of
	 * SELECT foo FROM bar WHERE foobar = $1
	 * @param  string       $query  Query string with placeholders $1, ..
	 * @param  array<mixed> $params Matching parameters for each placerhold
	 * @return object|resource|bool Query result (PgSql\Result)
	 */
	public function __dbQueryParams(string $query, array $params)
	{
		$this->last_error_query = '';
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		// parse query and get all $n entries
		// TODO count of $n must match params
		// read out the query status and save the query if needed
		$result = pg_query_params($this->dbh, $query, $params);
		if ($result === false) {
			$this->last_error_query = $query;
		}
		return $result;
	}

	/**
	 * sends an async query to the server
	 * @param  string $query query string
	 * @return bool          true/false if query was sent successful
	 */
	public function __dbSendQuery(string $query): bool
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		$result = pg_send_query($this->dbh, $query);
		return $result ? true : false;
	}

	/**
	 * wrapper for pg_get_result
	 * @return object|resource|bool resource handler or false for error (PgSql\Result)
	 */
	public function __dbGetResult()
	{
		$this->last_error_query = '';
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		$result = pg_get_result($this->dbh);
		if ($result === false) {
			return false;
		}
		if ($error = pg_result_error($result)) {
			$this->last_error_query = $error;
		}
		return $result;
	}

	/**
	 * wrapper for pg_close
	 * @return void has no return
	 */
	public function __dbClose(): void
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return;
		}
		if (pg_connection_status($this->dbh) === PGSQL_CONNECTION_OK) {
			// in 8.1 this throws an error, and we don't need that anyway
			// pg_close($this->dbh);
		}
	}

	/**
	 * wrapper for pg_prepare
	 * @param  string $name  statement name
	 * @param  string $query query string
	 * @return object|resource|bool prepare statement handler or false for error (PgSql\Result)
	 */
	public function __dbPrepare(string $name, string $query)
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		$result = pg_prepare($this->dbh, $name, $query);
		if (!$result) {
			$this->last_error_query = $query;
		}
		return $result;
	}

	/**
	 * wrapper for pg_execute for running a prepared statement
	 * @param  string        $name statement name
	 * @param  array<mixed>  $data data array
	 * @return object|resource|bool returns status or false for error (PgSql\Result)
	 */
	public function __dbExecute(string $name, array $data)
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		$result = pg_execute($this->dbh, $name, $data);
		if (!$result) {
			$this->last_error_query = $name;
		}
		return $result;
	}

	/**
	 * wrapper for pg_num_rows
	 * @param  object|resource|bool $cursor cursor PgSql\Result (former resource)
	 * @return int                  number of rows, -1 on error
	 */
	public function __dbNumRows($cursor): int
	{
		if ($cursor === false || is_bool($cursor)) {
			return -1;
		}
		return pg_num_rows($cursor);
	}

	/**
	 * wrapper for pg_num_fields
	 * @param  object|resource|bool $cursor cursor PgSql\Result (former resource)
	 * @return int                  number for fields in result, -1 on error
	 */
	public function __dbNumFields($cursor): int
	{
		if ($cursor === false || is_bool($cursor)) {
			return -1;
		}
		return pg_num_fields($cursor);
	}

	/**
	 * wrapper for pg_field_name
	 * @param  object|resource|bool $cursor cursor PgSql\Result (former resource)
	 * @param  int                  $i      field position
	 * @return string|bool          name or false on error
	 */
	public function __dbFieldName($cursor, int $i)
	{
		if ($cursor === false || is_bool($cursor)) {
			return false;
		}
		return pg_field_name($cursor, $i);
	}

	/**
	 * wrapper for pg_fetch_array
	 * if through/true false, use __dbResultType(true)
	 * @param  object|resource|bool $cursor      cursor PgSql\Result (former resource)
	 * @param  int                  $result_type result type as int number
	 * @return array<mixed>|bool    array result data or false on end/error
	 */
	public function __dbFetchArray($cursor, int $result_type = PGSQL_BOTH)
	{
		if ($cursor === false || is_bool($cursor)) {
			return false;
		}
		// result type is passed on as is [should be checked]
		return pg_fetch_array($cursor, null, $result_type);
	}

	/**
	 * simple match up between assoc true/false
	 * @param  bool $assoc_type true (default) for PGSQL_ASSOC, false for PGSQL_BOTH
	 * @return int              valid result type for fetch array
	 */
	public function __dbResultType(bool $assoc_type = true): int
	{
		if ($assoc_type == true) {
			return PGSQL_ASSOC;
		}
		// fallback to default
		return PGSQL_BOTH;
	}

	/**
	 * wrapper for pg_fetch_all
	 * @param  object|resource|bool $cursor cursor PgSql\Result (former resource)
	 * @return array<mixed>|bool    data array or false for end/error
	 */
	public function __dbFetchAll($cursor)
	{
		if ($cursor === false || is_bool($cursor)) {
			return false;
		}
		return pg_fetch_all($cursor);
	}

	/**
	 * wrapper for pg_affected_rows
	 * @param object|resource|bool $cursor cursor PgSql\Result (former resource)
	 * @return int                 affected rows, 0 for none, -1 for error
	 */
	public function __dbAffectedRows($cursor): int
	{
		if ($cursor === false || is_bool($cursor)) {
			return -1;
		}
		return pg_affected_rows($cursor);
	}

	/**
	 * reads the last inserted primary key for the query
	 * if there is no pk_name tries to auto built it from the table name
	 * this only works if db schema is after "no plural names. and pk name is table name + _id
	 * detects schema prefix in table name
	 * @param  string           $query   query string
	 * @param  string|null      $pk_name primary key name, if '' then auto detect
	 * @return string|int|false          primary key value
	 */
	public function __dbInsertId(string $query, ?string $pk_name)
	{
		// only if an insert has been done
		if (preg_match("/^insert /i", $query)) {
			$schema = '';
			// get table name from insert
			$array = explode(' ', $query);
			$_table = $array[2];
			// if there is a dot inside, we need to split
			if (strstr($_table, '.')) {
				list($schema, $table) = explode('.', $_table);
			} else {
				$table = $_table;
			}
			// no PK name given at all
			if (empty($pk_name)) {
				// if name is plurar, make it singular
				// if (preg_match("/.*s$/i", $table))
				// 	$table = substr($table, 0, -1);
				// set pk_name to "id"
				$pk_name = $table . "_id";
			}
			$seq = ($schema ? $schema . '.' : '') . $table . "_" . $pk_name . "_seq";
			$q = "SELECT CURRVAL('$seq') AS insert_id";
			// I have to do manually or I overwrite the original insert internal vars ...
			if ($q = $this->__dbQuery($q)) {
				if (is_array($res = $this->__dbFetchArray($q))) {
					list($id) = $res;
				} else {
					return false;
				}
			} else {
				$id = [-1, $q];
			}
			return $id;
		} else {
			//if not insert, return false
			return false;
		}
	}

	/**
	 * queries database for the primary key name to this table in the selected schema
	 * @param  string      $table  table name
	 * @param  string      $schema optional schema name, '' for default
	 * @return string|bool         primary key name or false if not found
	 */
	public function __dbPrimaryKey(string $table, string $schema = '')
	{
		if ($table) {
			// check if schema set is different from schema given,
			// only needed if schema is not empty
			$table_prefix = '';
			if ($schema) {
				$search_path = $this->__dbGetSchema();
				if ($search_path != $schema) {
					$table_prefix = $schema . '.';
				}
			}
			// read from table the PK name
			// faster primary key get
			$q = "SELECT pg_attribute.attname AS column_name, "
				. "format_type(pg_attribute.atttypid, pg_attribute.atttypmod) AS type "
				. "FROM pg_index, pg_class, pg_attribute ";
			if ($schema) {
				$q .= ", pg_namespace ";
			}
			$q .= "WHERE "
				// regclass translates the OID to the name
				. "pg_class.oid = '" . $table_prefix . $table . "'::regclass AND "
				. "indrelid = pg_class.oid AND ";
			if ($schema) {
				$q .= "nspname = '" . $schema . "' AND "
					. "pg_class.relnamespace = pg_namespace.oid AND ";
			}
			$q .= "pg_attribute.attrelid = pg_class.oid AND "
				. "pg_attribute.attnum = any(pg_index.indkey) "
				. "AND indisprimary";
			$cursor = $this->__dbQuery($q);
			if ($cursor !== false) {
				$__db_fetch_array = $this->__dbFetchArray($cursor);
				if (!is_array($__db_fetch_array)) {
					return false;
				}
				return $__db_fetch_array['column_name'] ?? false;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * wrapper for pg_connect, writes out failure to screen if error occurs (hidden var)
	 * @param  string  $db_host host name
	 * @param  string  $db_user user name
	 * @param  string  $db_pass password
	 * @param  string  $db_name databse name
	 * @param  integer $db_port port (int, 5432 is default)
	 * @param  string  $db_ssl  SSL (allow is default)
	 * @return object|resource|bool db handler PgSql\Connection or false on error
	 */
	public function __dbConnect(
		string $db_host,
		string $db_user,
		string $db_pass,
		string $db_name,
		int $db_port,
		string $db_ssl = 'allow'
	) {
		if (empty($db_name)) {
			return false;
		}
		// if there is no host, leave it empty, this will try default unix path
		// same for port (defaults to 5432 if not set)
		// must set is db name
		// if no user name, db name is used
		$connection_string = [];
		if (!empty($db_host)) {
			$connection_string[] = 'host=' . $db_host;
		}
		if (!empty($db_port)) {
			$connection_string[] = 'port=' . $db_port;
		}
		if (!empty($db_user)) {
			$connection_string[] = 'user=' . $db_user;
		}
		if (!empty($db_pass)) {
			$connection_string[] = 'password=' . $db_pass;
		}
		// we must have at least db name set
		$connection_string[] = 'dbname=' . $db_name;
		if (!empty($db_ssl)) {
			$connection_string[] = 'sslmode=' . $db_ssl;
		}
		// connect
		$this->dbh = pg_connect(join(' ', $connection_string));
		return $this->dbh;
	}

	/**
	 * reads the last error for this cursor and returns
	 * html formatted string with error name
	 * @param  bool|object|resource $cursor cursor PgSql\Result (former resource)
	 *                              or null
	 * @return string               error string
	 */
	public function __dbPrintError($cursor = false): string
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return '';
		}
		// run the query again for the error result here
		if (($cursor === false || is_bool($cursor)) && $this->last_error_query) {
			pg_send_query($this->dbh, $this->last_error_query);
			$this->last_error_query = '';
			$cursor = pg_get_result($this->dbh);
		}
		if ($cursor && !is_bool($cursor) && $error_str = pg_result_error($cursor)) {
			return '-PostgreSQL-Error- '
				. $error_str;
		} else {
			return '';
		}
	}

	/**
	 * wrapper for pg_meta_data
	 * @param  string $table     table name
	 * @param  bool   $extended  show extended info (default true)
	 * @return array<mixed>|bool array data for the table info or false on error
	 */
	public function __dbMetaData(string $table, $extended = true)
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		// needs to prefixed with @ or it throws a warning on not existing table
		return @pg_meta_data($this->dbh, $table, $extended);
	}

	/**
	 * wrapper for pg_escape_string
	 * @param  string|int|float|bool $string any string/int/float/bool
	 * @return string                excaped string
	 */
	public function __dbEscapeString($string): string
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return '';
		}
		return pg_escape_string($this->dbh, (string)$string);
	}

	/**
	 * wrapper for pg_escape_literal
	 * difference to escape string is that this one adds quotes ('') around
	 * the string too
	 * @param  string|int|float|bool $string any string/int/float/bool
	 * @return string                excaped string including quites
	 */
	public function __dbEscapeLiteral($string): string
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return (string)'';
		}
		// for phpstan, thinks this is string|false?
		return (string)pg_escape_literal($this->dbh, (string)$string);
	}

	/**
	 * wrapper for pg_escape_identifier
	 * Only used for table names, column names
	 * @param  string $string any string
	 * @return string         escaped string
	 */
	public function __dbEscapeIdentifier(string $string): string
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return '';
		}
		// for phpstan, thinks this is string|false?
		return (string)pg_escape_identifier($this->dbh, (string)$string);
	}

	/**
	 * wrapper for pg_escape_byte
	 * @param  string $bytea bytea data stream
	 * @return string        escaped bytea string
	 */
	public function __dbEscapeBytea(string $bytea): string
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return '';
		}
		return pg_escape_bytea($this->dbh, $bytea);
	}

	/**
	 * wrapper for pg_connection_busy
	 * @return bool True if connection is busy, False if not or no db connection at all
	 */
	public function __dbConnectionBusy(): bool
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		return pg_connection_busy($this->dbh);
	}

	/**
	 * Experimental wrapper with scoket timetout
	 * @param integer $timeout_seconds Wait how many seconds on timeout
	 * @return boolean                 True if connection is busy, or false on
	 *                                 not busy or no db connection at all
	 */
	public function __dbConnectionBusySocketWait(int $timeout_seconds = 3): bool
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		$busy = pg_connection_busy($this->dbh);
		/** @var array<resource>|null */
		$socket = [pg_socket($this->dbh)];
		$null = null;
		while ($busy) {
			// Will wait on that socket until that happens or the timeout is reached
			stream_select($socket, $null, $null, $timeout_seconds);
			$busy = pg_connection_busy($this->dbh);
		}
		return $busy;
	}

	/**
	 * extended wrapper for pg_version
	 * can return any setting in this array block
	 * If no connection, return empty string,
	 * if not in array return empty string
	 * On default 'version' will be stripped of any space attached info
	 * eg 13.5 (other info) will return only 13.5
	 * @param  string  $parameter Parameter string to extract from array
	 * @param  boolean $strip     If parameter is server strip out on default
	 *                            Set to false to get original string AS is
	 * @return string             The parameter value
	 */
	public function __dbVersionInfo(string $parameter, bool $strip = true): string
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return '';
		}
		// extract element
		$return_string = pg_version($this->dbh)[$parameter] ?? '';
		// for version, strip if requested
		if (
			in_array($parameter, ['server']) &&
			$strip === true
		) {
			$return_string = explode(' ', $return_string, 2)[0] ?? '';
		}
		return $return_string;
	}

	/**
	 * Returns all parameters that are possible from the db_version
	 * @return array<mixed> Parameter key names from pg_version
	 */
	public function __dbVersionInfoParameterList(): array
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return [];
		}
		return array_keys(pg_version($this->dbh));
	}

	/**
	 * wrapper for pg_version
	 * Note: this only returns server version
	 * not connection version OR client version
	 * @return string version string
	 */
	public function __dbVersion(): string
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return '';
		}
		// array has client, protocol, server, we just return server stripped
		return $this->__dbVersionInfo('server', true);
	}

	/**
	 * Returns a numeric version eg 90506 or 130006, etc
	 * Note that this calls a show command on the server
	 * Note:
	 * Old version is 9.5.6 where 9.5 is the major version
	 * Newer Postgresql (10 on) have only one major version so eg 13.5
	 * is returned as 130005
	 * @return integer Server version
	 */
	public function __dbVersionNumeric(): int
	{
		return (int)$this->__dbShow('server_version_num');
	}

	/**
	 * NOTE: it is recommended to convert arrays to json and parse the json in
	 * PHP itself, array_to_json(array)
	 * This is a fallback for old PostgreSQL versions
	 * postgresql array to php array
	 * https://stackoverflow.com/a/27964420
	 * @param  string   $array_text Array text from PostgreSQL
	 * @param  int      $start      Start string position
	 * @param  int|null $end        End string position from recursive call
	 * @return array<mixed>|null    PHP type array
	 */
	public function __dbArrayParse(
		string $array_text,
		int $start = 0,
		?int &$end = null
	): ?array {
		if (empty($array_text) || $array_text[0] != '{') {
			return null;
		}
		$return = [];
		$string = false;
		$quote = '';
		$len = strlen($array_text);
		$v = '';
		// start from offset
		for ($array_pos = $start + 1; $array_pos < $len; $array_pos += 1) {
			$ch = $array_text[$array_pos];
			// check wher ein the string are we
			// end, one down
			if (!$string && $ch == '}') {
				if ($v !== '' || !empty($return)) {
					$return[] = $v;
				}
				$end = $array_pos;
				break;
			// open new array, jump recusrive up
			} elseif (!$string && $ch == '{') {
				// full string + poff set and end
				$v = $this->__dbArrayParse($array_text, $array_pos, $array_pos);
			// next array element
			} elseif (!$string && $ch == ',') {
				$return[] = $v;
				$v = '';
			// flag that this is a string
			} elseif (!$string && ($ch == '"' || $ch == "'")) {
				$string = true;
				$quote = $ch;
			// quoted string
			} elseif ($string && $ch == $quote && $array_text[$array_pos - 1] == "\\") {
				$v = substr($v, 0, -1) . $ch;
			} elseif ($string && $ch == $quote && $array_text[$array_pos - 1] != "\\") {
				$string = false;
			} else {
				// build string
				$v .= $ch;
			}
		}

		return $return;
	}

	/**
	 * Returns any server setting
	 * if no connection or empty parameter or other error returns false
	 * else returns a string
	 * @param  string      $parameter Parameter to query
	 * @return string|bool            Settings value as string
	 */
	public function __dbParameter(string $parameter)
	{
		if ($this->dbh === false || is_bool($this->dbh)) {
			return false;
		}
		if (empty($parameter)) {
			return false;
		}
		return pg_parameter_status($this->dbh, $parameter);
	}

	/**
	 * wrapper for any SHOW data blocks
	 * eg search_path or client_encoding
	 * @param  string $show_string Part to show, if invalid will return empty string
	 * @return string              Found part as is
	 */
	public function __dbShow(string $show_string): string
	{
		// get search path
		$cursor = $this->__dbQuery("SHOW " . $this->__dbEscapeIdentifier($show_string));
		// abort on failure and return empty
		if ($cursor === false) {
			return '';
		}
		// get result
		$db_schema = $this->__dbFetchArray($cursor, PGSQL_ASSOC);
		/** @phpstan-ignore-next-line Cannot access offset string on array|bool */
		return $db_schema[$show_string] ?? '';
	}

	/**
	 * Sets a new database schema/search_path
	 * Checks if schema exits and if not aborts with error code 2
	 * @param  string $db_schema Schema to set
	 * @return int               Returns 0 if no error
	 *                           1 for check query failed
	 *                           2 for schema not found in database
	 */
	public function __dbSetSchema(string $db_schema): int
	{
		// check if schema actually exists
		$query = "SELECT EXISTS("
			. "SELECT 1 FROM information_schema.schemata "
			. "WHERE schema_name = " . $this->__dbEscapeLiteral($db_schema)
			. ")";
		$cursor = $this->__dbQuery($query);
		// abort if execution fails
		if ($cursor === false) {
			return 1;
		}
		// check if schema does not exists
		$row = $this->__dbFetchArray($cursor, PGSQL_ASSOC);
		/** @phpstan-ignore-next-line */
		if (empty($row['exists']) || $row['exists'] == 'f') {
			return 2;
		}
		$query = "SET search_path TO " . $this->__dbEscapeLiteral($db_schema);
		$this->__dbQuery($query);
		return 0;
	}

	/**
	 * Returns current set schema/search_path
	 * @return string Search Path as currently set in DB
	 */
	public function __dbGetSchema(): string
	{
		return $this->__dbShow('search_path');
	}

	/**
	 * set the client encoding
	 * Returns 0 on set ok, or 3 if the client encoding could not be set
	 * @param  string $db_encoding
	 * @return int    Returns 0 for no error
	 *                [not used] 1 for check query failed
	 *                [not used] 2 for invalid client encoding
	 *                3 client encoding could not be set
	 */
	public function __dbSetEncoding(string $db_encoding): int
	{
		// check if ecnoding is valid first
		// does not take into account aliases
		// TODO lookup with alisaes so eg ShiftJIS does not get a false
		// $query = "SELECT EXISTS("
		// 	. "SELECT pg_catalog.pg_encoding_to_char(conforencoding) "
		// 	. "FROM pg_catalog.pg_conversion "
		// 	. "WHERE pg_catalog.pg_encoding_to_char(conforencoding) = "
		// 	. $this->dbEscapeLiteral($db_encoding)
		// 	. ")";
		// $cursor = $this->__dbQuery($query);
		// if ($cursor === false) {
		// 	return 1;
		// }
		// $row = $this->__dbFetchArray($cursor, PGSQL_ASSOC);
		// if ($row['exists'] == 'f') {
		// 	return 2;
		// }
		$query = "SET client_encoding TO " . $this->__dbEscapeLiteral($db_encoding);
		if ($this->__dbQuery($query) === false) {
			return 3;
		}
		return 0;
	}

	/**
	 * Returns current set client encoding
	 * @return string Client encoding string, empty if not set
	 */
	public function __dbGetEncoding(): string
	{
		return $this->__dbShow('client_encoding');
	}
}

// __END__

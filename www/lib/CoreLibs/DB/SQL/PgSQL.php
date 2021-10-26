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
*  HISTORY:
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
* /

* collection of PostgreSQL wrappers
* REQUIRES 5.x PHP!!!
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

class PgSQL
{
	/** @var string */
	private $last_error_query;
	/** @var resource|bool */
	private $dbh;

	/**
	 * class constructor, empty does nothing
	 */
	public function __construct()
	{
	}

	/**
	 * queries last error query and returns true or false if error was set
	 * @return bool true/false if last error is set
	 */
	public function __dbLastErrorQuery()
	{
		if ($this->last_error_query) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * wrapper for gp_query, catches error and stores it in class var
	 * @param  string      $query query string
	 * @return resource|bool      query result
	 */
	public function __dbQuery(string $query)
	{
		$this->last_error_query = '';
		if (!is_resource($this->dbh)) {
			return false;
		}
		// read out the query status and save the query if needed
		$result = pg_query($this->dbh, $query);
		if (!$result) {
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
		if (!is_resource($this->dbh)) {
			return false;
		}
		$result = pg_send_query($this->dbh, $query);
		return $result ? true : false;
	}

	/**
	 * wrapper for pg_get_result
	 * @return resource|bool resource handler or false for error
	 */
	public function __dbGetResult()
	{
		$this->last_error_query = '';
		if (!is_resource($this->dbh)) {
			return false;
		}
		$result = pg_get_result($this->dbh);
		if (!is_resource($result)) {
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
		if (!is_resource($this->dbh)) {
			return;
		}
		if (pg_connection_status($this->dbh) === PGSQL_CONNECTION_OK) {
			pg_close($this->dbh);
		}
	}

	/**
	 * wrapper for pg_prepare
	 * @param  string $name  statement name
	 * @param  string $query query string
	 * @return resource|bool prepare statement handler or false for error
	 */
	public function __dbPrepare(string $name, string $query)
	{
		if (!is_resource($this->dbh)) {
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
	 * @return resource|bool returns status or false for error
	 */
	public function __dbExecute(string $name, array $data)
	{
		if (!is_resource($this->dbh)) {
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
	 * @param  resource $cursor cursor resource
	 * @return int              number of rows, -1 on error
	 */
	public function __dbNumRows($cursor): int
	{
		return pg_num_rows($cursor);
	}

	/**
	 * wrapper for pg_num_fields
	 * @param  resource $cursor cursor resource
	 * @return int              number for fields in result, -1 on error
	 */
	public function __dbNumFields($cursor): int
	{
		return pg_num_fields($cursor);
	}

	/**
	 * wrapper for pg_field_name
	 * @param  resource    $cursor cursor resource
	 * @param  int         $i      field position
	 * @return string|bool         name or false on error
	 */
	public function __dbFieldName($cursor, $i)
	{
		return pg_field_name($cursor, $i);
	}

	/**
	 * wrapper for pg_fetch_array
	 * if through/true false, use __dbResultType(true)
	 * @param  resource $cursor      cursor resource
	 * @param  int      $result_type result type as int number
	 * @return array<mixed>|bool     array result data or false on end/error
	 */
	public function __dbFetchArray($cursor, int $result_type = PGSQL_BOTH)
	{
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
	 * @param  resource   $cursor cursor resource
	 * @return array<mixed>|bool  data array or false for end/error
	 */
	public function __dbFetchAll($cursor)
	{
		return pg_fetch_all($cursor);
	}

	/**
	 * wrapper for pg_affected_rows
	 * @param  resource $cursor cursor resource
	 * @return int              affected rows, 0 for none
	 */
	public function __dbAffectedRows($cursor): int
	{
		return pg_affected_rows($cursor);
	}

	/**
	 * reads the last inserted primary key for the query
	 * if there is no pk_name tries to auto built it from the table name
	 * this only works if db schema is after "no plural names. and pk name is table name + _id
	 * detects schema prefix in table name
	 * @param  string           $query   query string
	 * @param  string           $pk_name primary key name, if '' then auto detect
	 * @return string|int|false          primary key value
	 */
	public function __dbInsertId(string $query, string $pk_name)
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
			if (!$pk_name) {
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
				// abort if this is not an resource
				if (!is_resource($q)) {
					return false;
				}
				list($id) = $this->__dbFetchArray($q) ?: [];
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
			// check if schema set is different from schema given, only needed if schema is not empty
			$table_prefix = '';
			if ($schema) {
				$q = "SHOW search_path";
				$cursor = $this->__dbQuery($q);
				if (!is_resource($cursor)) {
					return false;
				}
				$__db_fetch_array = $this->__dbFetchArray($cursor);
				if (!is_array($__db_fetch_array)) {
					return false;
				}
				$search_path = $__db_fetch_array['search_path'] ?? '';
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
			if (is_resource($cursor)) {
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
	 * @param  string        $db_host host name
	 * @param  string        $db_user user name
	 * @param  string        $db_pass password
	 * @param  string        $db_name databse name
	 * @param  integer       $db_port port (int, 5432 is default)
	 * @param  string        $db_ssl  SSL (allow is default)
	 * @return resource|bool          db handler resource or false on error
	 */
	public function __dbConnect(
		string $db_host,
		string $db_user,
		string $db_pass,
		string $db_name,
		int $db_port = 5432,
		string $db_ssl = 'allow'
	) {
		// to avoid empty db_port
		if (!$db_port) {
			$db_port = 5432;
		}
		$this->dbh = pg_connect("host=" . $db_host . " port=" . $db_port . " user="
			. $db_user . " password=" . $db_pass . " dbname=" . $db_name . " sslmode=" . $db_ssl);
		// if (!$this->dbh) {
		// 	die("<!-- Can't connect to database //-->");
		// }
		return $this->dbh;
	}

	/**
	 * reads the last error for this cursor and returns
	 * html formatted string with error name
	 * @param  ?resource $cursor cursor resource or null
	 * @return string            error string
	 */
	public function __dbPrintError($cursor = null): string
	{
		if (!is_resource($this->dbh)) {
			return '';
		}
		// run the query again for the error result here
		if (!$cursor && $this->last_error_query) {
			pg_send_query($this->dbh, $this->last_error_query);
			$this->last_error_query = '';
			$cursor = pg_get_result($this->dbh);
		}
		if ($cursor && pg_result_error($cursor)) {
			return "<span style=\"color: red;\"><b>-PostgreSQL-Error-></b> " . pg_result_error($cursor) . "</span><br>";
		} else {
			return '';
		}
	}

	/**
	 * wrapper for pg_meta_data
	 * @param  string $table     table name
	 * @param  bool   $extended  show extended info (default false)
	 * @return array<mixed>|bool array data for the table info or false on error
	 */
	public function __dbMetaData(string $table, $extended = false)
	{
		if (!is_resource($this->dbh)) {
			return false;
		}
		// needs to prefixed with @ or it throws a warning on not existing table
		return @pg_meta_data($this->dbh, $table, $extended);
	}

	/**
	 * wrapper for pg_escape_string
	 * @param  string|int|float|bool $string any string/int/float/bool
	 * @return string                        excaped string
	 */
	public function __dbEscapeString($string): string
	{
		if (!is_resource($this->dbh)) {
			return '';
		}
		return pg_escape_string($this->dbh, (string)$string);
	}

	/**
	 * wrapper for pg_escape_literal
	 * difference to escape string is that this one adds quotes ('') around
	 * the string too
	 * @param  string|int|float|bool $string any string/int/float/bool
	 * @return string                        excaped string including quites
	 */
	public function __dbEscapeLiteral($string): string
	{
		if (!is_resource($this->dbh)) {
			return '';
		}
		return pg_escape_string($this->dbh, (string)$string);
	}

	/**
	 * wrapper for pg_escape_byte
	 * @param  string $bytea bytea data stream
	 * @return string        escaped bytea string
	 */
	public function __dbEscapeBytea($bytea): string
	{
		if (!is_resource($this->dbh)) {
			return '';
		}
		return pg_escape_bytea($this->dbh, $bytea);
	}

	/**
	 * wrapper for pg_connection_busy
	 * @return bool true/false for busy connection
	 */
	public function __dbConnectionBusy(): bool
	{
		if (!is_resource($this->dbh)) {
			return false;
		}
		return pg_connection_busy($this->dbh);
	}

	/**
	 * wrapper for pg_version
	 * Note: this only returns server version
	 * not connection version OR client version
	 * @return string version string
	 */
	public function __dbVersion(): string
	{
		if (!is_resource($this->dbh)) {
			return '';
		}
		// array has client, protocol, server
		// we just need the server
		$v = pg_version($this->dbh);
		return $v['server'];
	}

	/**
	 * postgresql array to php array
	 * @param  string           $text   array text from PostgreSQL
	 * @param  array<mixed>     $output (internal) recursive pass on for nested arrays
	 * @param  bool|int         $limit  (internal) max limit to not overshoot
	 *                                  the end, start with false
	 * @param  integer          $offset (internal) shift offset for {}
	 * @return array<mixed>|int         converted PHP array, interal recusrive int position
	 */
	public function __dbArrayParse($text, &$output, $limit = false, $offset = 1)
	{
		if (false === $limit) {
			$limit = strlen($text) - 1;
			$output = [];
		}
		if ('{}' != $text) {
			do {
				if ('{' != $text[$offset]) {
					preg_match("/(\\{?\"([^\"\\\\]|\\\\.)*\"|[^,{}]+)+([,}]+)/", $text, $match, 0, $offset);
					$offset += strlen($match[0]);
					$output[] = ('"' != $match[1][0] ? $match[1] : stripcslashes(substr($match[1], 1, -1)));
					if ('},' == $match[3]) {
						return $offset;
					}
				} else {
					$offset = $this->__dbArrayParse($text, $output, $limit, $offset + 1);
				}
			} while ($limit > $offset);
		}
		return $output;
	}
}

// __END__

<?php
/*********************************************************************
* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
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
* 2006/09/12 (cs) in case db_query retuns false, save the query and run the query through the send/get procedure to get correct error data from the db
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

namespace CoreLibs\DB\SQL;

class PgSQL
{
	private $last_error_query;
	private $dbh;

	// METHOD: __construct
	// PARAMS: none
	// RETURN: none
	// DESC  : class constructor
	public function __construct()
	{
	}

	// METHOD: __dbLastErrorQuery
	// WAS   : _db_last_error_query
	// PARAMS: none
	// RETURN: true/false if last error is set
	// DESC  : queries last error query and returns true or false if error was set
	public function __dbLastErrorQuery()
	{
		if ($this->last_error_query) {
			return true;
		} else {
			return false;
		}
	}

	// METHOD: __dbQuery
	// WAS   : _db_query
	// PARAMS: query
	// RETURN: query result
	// DESC  : wrapper for gp_query, catches error and stores it in class var
	public function __dbQuery($query)
	{
		$this->last_error_query = '';
		// read out the query status and save the query if needed
		$result = pg_query($this->dbh, $query);
		if (!$result) {
			$this->last_error_query = $query;
		}
		return $result;
	}

	// METHOD: __dbSendQuery
	// WAS   : _db_send_query
	// PARAMS: query
	// RETURN: true/false if query was sent successful
	// DESC  : sends an async query to the server
	public function __dbSendQuery($query)
	{
		return pg_send_query($this->dbh, $query);
	}

	// METHOD: __dbGetResult
	// WAS   : _db_get_result
	// PARAMS: none
	// RETURN: resource handler
	// DESC  : wrapper for pg_get_result
	public function __dbGetResult()
	{
		$this->last_error_query = '';
		$result = pg_get_result($this->dbh);
		if ($error = pg_result_error($result)) {
			$this->last_error_query = $error;
		}
		return $result;
	}

	// METHOD: __dbClose
	// WAS   : _db_close
	// PARAMS: none
	// RETURN: none
	// DESC  : wrapper for pg_close
	public function __dbClose()
	{
		if (is_resource($this->dbh)) {
			if (pg_connection_status($this->dbh) === PGSQL_CONNECTION_OK) {
				pg_close($this->dbh);
			}
		}
	}

	// METHOD: __dbPrepare
	// WAS   : _db_prepare
	// PARAMS: prepare name, query
	// RETURN: prepared statement handler
	// DESC  : wrapper for pg_prepare
	public function __dbPrepare($name, $query)
	{
		$result = pg_prepare($this->dbh, $name, $query);
		if (!$result) {
			$this->last_error_query = $query;
		}
		return $result;
	}

	// METHOD: __dbExecute
	// WAS   : _db_execute
	// PARAMS: prepare name, data for query
	// RETURN: returns status
	// DESC  : wrapper for pg_execute for running a prepared statement
	public function __dbExecute($name, $data)
	{
		$result = pg_execute($this->dbh, $name, $data);
		if (!$result) {
			$this->last_error_query = $query;
		}
		return $result;
	}

	// METHOD: __dbNumRows
	// WAS   : _db_num_rows
	// PARAMS: cursor
	// RETURN: rows
	// DESC  : wrapper for pg_num_rows
	public function __dbNumRows($cursor)
	{
		return pg_num_rows($cursor);
	}

	// METHOD: __dbNumFields
	// WAS   : _db_num_fields
	// PARAMS: cursor
	// RETURN: number for fields in query
	// DESC  : wrapper for pg_num_fields
	public function __dbNumFields($cursor)
	{
		return pg_num_fields($cursor);
	}

	// METHOD: __dbFieldName
	// WAS   : _db_field_name
	// PARAMS: cursor, field position
	// RETURN: name of field
	// DESC  : wrapper for pg_field_name
	public function __dbFieldName($cursor, $i)
	{
		return pg_field_name($cursor, $i);
	}

	// METHOD: __dbFetchArray
	// WAS   : _db_fetch_array
	// PARAMS: cursor, opt result type
	// RETURN: row
	// DESC  : wrapper for pg_fetch_array
	public function __dbFetchArray($cursor, $result_type = '')
	{
		// result type is passed on as is [should be checked]
		if ($result_type) {
			return pg_fetch_array($cursor, null, $result_type);
		} else {
			return pg_fetch_array($cursor);
		}
	}

	// METHOD: __dbFetchAll
	// WAS   : _db_fetch_all
	// PARAMS: cursor
	// RETURN: all rows as array
	// DESC  : wrapper for pg_fetch_array
	public function __dbFetchAll($cursor)
	{
		return pg_fetch_all($cursor);
	}

	// METHOD: __dbAffectedRows
	// WAS   : _db_affected_rows
	// PARAMS: cursor
	// RETURN: number for rows
	// DESC  : wrapper for pg_affected_rows
	public function __dbAffectedRows($cursor)
	{
		return pg_affected_rows($cursor);
	}

	// METHOD: __dbInsertId
	// WAS   : _db_insert_id
	// PARAMS: query, primary key name
	// RETURN: last insert primary key
	// DESC  : reads the last inserted primary key for the query
	//         if ther is no pk_name tries to auto built it from the table name
	//         this only works if db schema is after "no plural names. and pk name is table name + _id
	//         detects schema prefix in table name
	public function __dbInsertId($query, $pk_name)
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
				$pk_name = $table."_id";
			}
			$seq = (($schema) ? $schema.'.' : '').$table."_".$pk_name."_seq";
			$q = "SELECT CURRVAL('$seq') AS insert_id";
			// I have to do manually or I overwrite the original insert internal vars ...
			if ($q = $this->__dbQuery($q)) {
				list($id) = $this->__dbFetchArray($q);
			} else {
				$id = array(-1, $q);
			}
			return $id;
		}
	}

	// METHOD: __dbPrimaryKey
	// WAS   : _db_primary_key
	// PARAMS: table and optional schema
	// RETURN: primary key name OR false if not possible
	// DESC  : queries database for the primary key name to this table in the selected schema
	public function __dbPrimaryKey($table, $schema = '')
	{
		if ($table) {
			// check if schema set is different from schema given, only needed if schema is not empty
			$table_prefix = '';
			if ($schema) {
				$q = "SHOW search_path";
				$cursor = $this->__dbQuery($q);
				$search_path = $this->__dbFetchArray($cursor)['search_path'];
				if ($search_path != $schema) {
					$table_prefix = $schema.'.';
				}
			}
			// read from table the PK name
			// faster primary key get
			$q = "SELECT pg_attribute.attname AS column_name, format_type(pg_attribute.atttypid, pg_attribute.atttypmod) AS type ";
			$q .= "FROM pg_index, pg_class, pg_attribute ";
			if ($schema) {
				$q .= ", pg_namespace ";
			}
			$q .= "WHERE ";
			// regclass translates the OID to the name
			$q .= "pg_class.oid = '".$table_prefix.$table."'::regclass AND ";
				$q .= "indrelid = pg_class.oid AND ";
			if ($schema) {
				$q .= "nspname = '".$schema."' AND ";
				$q .= "pg_class.relnamespace = pg_namespace.oid AND ";
			}
			$q .= "pg_attribute.attrelid = pg_class.oid AND ";
			$q .= "pg_attribute.attnum = any(pg_index.indkey) ";
			$q .= "AND indisprimary";
			$cursor = $this->__dbQuery($q);
			if ($cursor) {
				return $this->__dbFetchArray($cursor)['column_name'];
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	// METHOD: __dbConnect
	// WAS   : _db_connect
	// PARAMS: host name, user name, password, database name, optional port (defaults to default postgres port), optional ssl (default allow)
	// RETURN: database handler
	// DESC  : wrapper for pg_connect, writes out failure to screen if error occurs (hidden var)
	public function __dbConnect($db_host, $db_user, $db_pass, $db_name, $db_port = 5432, $db_ssl = 'allow')
	{
		// to avoid empty db_port
		if (!$db_port) {
			$db_port = 5432;
		}
		$this->dbh = pg_connect("host=".$db_host." port=".$db_port." user=".$db_user." password=".$db_pass." dbname=".$db_name." sslmode=".$db_ssl);
		if (!$this->dbh) {
			die("<!-- Can't connect [host=".$db_host." port=".$db_port." user=".$db_user." password=XXXX dbname=".$db_name." sslmode=".$db_ssl."] //-->");
		}
		return $this->dbh;
	}

	// METHOD: __dbPrintError
	// WAS   : _db_print_error
	// PARAMS: database handler, cursor
	// RETURN: error string (HTML)
	// DESC  : reads the last error for this cursor
	public function __dbPrintError($cursor = '')
	{
		// run the query again for the error result here
		if (!$cursor && $this->last_error_query) {
			pg_send_query($this->dbh, $this->last_error_query);
			$this->last_error_query = '';
			$cursor = pg_get_result($this->dbh);
		}
		if (pg_result_error($cursor)) {
			return "<span style=\"color: red;\"><b>-PostgreSQL-Error-></b> ".pg_result_error($cursor)."</span><br>";
		}
	}

	// METHOD: __dbMetaData
	// WAS   : _db_meta_data
	// PARAMS: table name
	// RETURN: array with table data
	// DESC  : wrapper for pg_emta_data
	public function __dbMetaData($table)
	{
		return pg_meta_data($this->dbh, $table);
	}

	// METHOD: __dbEscapeString
	// WAS   : _db_escape_string
	// PARAMS: string
	// RETURN: escaped string for postgres
	// DESC  : wrapper for pg_escape_string
	public function __dbEscapeString($string)
	{
		return pg_escape_string($this->dbh, $string);
	}

	// METHOD: __dbEscapeBytea
	// WAS   : _db_escape_bytea
	// PARAMS: string
	// RETURN: escape bytes for postgres
	// DESC  : wrapper for pg_escape_bytea
	public function __dbEscapeBytea($bytea)
	{
		return pg_escape_bytea($this->dbh, $bytea);
	}

	// METHOD: __dbConnectionBusy
	// WAS   : _db_connection_busy
	// PARAMS: none
	// RETURN: true/false for busy connection
	// DESC  : wrapper for pg_connection_busy
	public function __dbConnectionBusy()
	{
		return pg_connection_busy($this->dbh);
	}

	// METHOD: __dbVersion
	// WAS   : _db_version
	// PARAMS: none
	// RETURN: databse version
	// DESC  : wrapper for pg_version
	public function __dbVersion()
	{
		// array has client, protocol, server
		// we just need the server
		$v = pg_version($this->dbh);
		return $v['server'];
	}

	// METHOD: __dbArrayParse
	// WAS   : _db_array_parse
	// PARAMS: input text, output array [needed]
	//         [internal] limit: are we at the end of the parse
	//         [internal] offset: shift for {}
	// RETURN: array with the elements
	// DESC  : postgresql array to php array
	public function __dbArrayParse($text, &$output, $limit = false, $offset = 1)
	{
		if (false === $limit) {
			$limit = strlen($text) - 1;
			$output = array();
		}
		if ('{}' != $text) {
			do {
				if ('{' != $text{$offset}) {
					preg_match("/(\\{?\"([^\"\\\\]|\\\\.)*\"|[^,{}]+)+([,}]+)/", $text, $match, 0, $offset);
					$offset += strlen($match[0]);
					$output[] = ('"' != $match[1]{0} ? $match[1] : stripcslashes(substr($match[1], 1, -1)));
					if ('},' == $match[3]) {
						return $offset;
					}
				} else {
					$offset = pg_array_parse($text, $output[], $limit, $offset + 1);
				}
			} while ($limit > $offset);
		}
		return $output;
	}
}

# __END__

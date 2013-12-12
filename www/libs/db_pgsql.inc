<?
	/*********************************************************************
	* $HeadURL: svn://svn/development/core_data/php/www/libs/db_pgsql.inc $
	* $LastChangedBy: gullevek $
	* $LastChangedDate: 2013-12-12 18:53:03 +0900 (Thu, 12 Dec 2013) $
	* $LastChangedRevision: 4743 $
	*********************************************************************
	* AUTHOR: Clemens "Gullevek" Schwaighofer (www.gullevek.org)
	* CREATED: 2003/04/09 
	* SHORT DESCRIPTION:
	* pgsq; wrapper calls
	* HISTORY:
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

  /* collection of PostgreSQL wrappers
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

	class db_pgsql
	{
		private $last_error_query;
		private $dbh;
//		public $currval_query;

		// METHOD: __construct
		// PARAMS: none
		// RETURN: none
		// DESC  : class constructor
		public function __construct()
		{
		}

		public function _db_last_error_query()
		{
			if ($this->last_error_query)
				return true;
			else
				return false;
		}

		// METHOD: _db_query
		// PARAMS: query
		// RETURN: query result
		// DESC  : wrapper for gp_query, catches error and stores it in class var
		public function _db_query($query)
		{
			$this->last_error_query = '';
			// read out the query status and save the query if needed
			$result = @pg_query($this->dbh, $query);
			if (!$result)
				$this->last_error_query = $query;
			return $result;
		}

		// METHOD: _db_send_query
		// PARAMS: query
		// RETURN: true/false if query was sent successful
		// DESC  : sends an async query to the server
		public function _db_send_query($query)
		{
			return @pg_send_query($this->dbh, $query);
		}

		// METHOD: _db_get_result
		// PARAMS: none
		// RETURN: resource handler
		// DESC  : wrapper for pg_get_result
		public function _db_get_result()
		{
			$this->last_error_query = '';
			$result = pg_get_result($this->dbh);
			if (pg_result_error($result))
				$this->last_error_query = $query;
			return $result;
		}

		// METHOD: _db_close
		// PARAMS: optional database handler
		// RETURN: none
		// DESC  : wrapper for pg_close
		public function _db_close($dbh = '')
		{
			if (!$dbh)
				$dbh = $this->dbh;
			if (is_resource($dbh))
				if (@pg_connection_status($dbh) === PGSQL_CONNECTION_OK)
					@pg_close($dbh);
		}

		// METHOD: _db_prepare
		// PARAMS: prepare name, query
		// RETURN: prepared statement handler
		// DESC  : wrapper for pg_prepare
		public function _db_prepare($name, $query)
		{
			return @pg_prepare($this->dbh, $name, $query);
		}

		// METHOD: _db_execute
		// PARAMS: prepare name, data for query
		// RETURN: returns status
		// DESC  : wrapper for pg_execute for running a prepared statement
		public function _db_execute($name, $data)
		{
			return @pg_execute($this->dbh, $name, $data);
		}

		// METHOD: _db_num_rows
		// PARAMS: cursor
		// RETURN: rows
		// DESC  : wrapper for pg_num_rows
		public function _db_num_rows($cursor)
		{
			return pg_num_rows($cursor);
		}

		// METHOD: _db_num_fields
		// PARAMS: cursor
		// RETURN: number for fields in query
		// DESC  : wrapper for pg_num_fields
		public function _db_num_fields($cursor)
		{
			return pg_num_fields($cursor);
		}
 
		// METHOD: _db_field_name
		// PARAMS: cursor, field position
		// RETURN: name of field
		// DESC  : wrapper for pg_field_name
		public function _db_field_name($cursor, $i)
		{
			return pg_field_name($cursor, $i);
		}

		// METHOD: _db_fetch_array
		// PARAMS: cursor
		// RETURN: row
		// DESC  : wrapper for pg_fetch_array
		public function _db_fetch_array($cursor)
		{
			return pg_fetch_array($cursor);
		}

		// METHOD: _db_affected_ros
		// PARAMS: cursor
		// RETURN: number for rows
		// DESC  : wrapper for pg_affected_rows
		public function _db_affected_rows($cursor)
		{
			return pg_affected_rows($cursor); 
		}

		// METHOD: _db_insert_id
		// PARAMS: query, primary key name
		// RETURN: last insert primary key
		// DESC  : reads the last inserted primary key for the query
		//         if ther is no pk_name tries to auto built it from the table name
		//         this only works if db schema is after "no plural names. and pk name is table name + _id
		//         detects schema prefix in table name
		public function _db_insert_id($query, $pk_name)
		{
			// only if an insert has been done
			if (preg_match("/^insert /i", $query))
			{
				$schema = '';
				// get table name from insert
				$array = explode(' ', $query);
				$_table = $array[2];
				// if there is a dot inside, we need to split
				if (strstr($_table, '.'))
				   list($schema, $table) = explode('.', $_table);
				else
				   $table = $_table;
				// no PK name given at all
				if (!$pk_name)
				{
					// if name is plurar, make it singular
//					if (preg_match("/.*s$/i", $table))
//						$table = substr($table, 0, -1);
					// set pk_name to "id"
					$pk_name = $table."_id";
				}
				$seq = (($schema) ? $schema.'.' : '').$table."_".$pk_name."_seq";
				$q = "SELECT CURRVAL('$seq') AS insert_id";
//				$this->currval_query = $q;
//echo "Q: $q<Br>";
				// I have to do manually or I overwrite the original insert internal vars ...
				if ($q = $this->_db_query($q))
				{
					list($id) = pg_fetch_array($q);
				}
				else
				{
					$id = array(-1, $q);
				}
				return $id;
			}
		} 

		// METHOD: _db_primary_key
		// PARAMS: table and optional schema
		// RETURN: primary key name OR false if not possible
		// DESC  : queries database for the primary key name to this table in the selected schema
		public function _db_primary_key($table, $schema = '')
		{
			if ($table)
			{
				// read from table the PK name
				// slower version
/*				$q = "SELECT c.column_name ";
				$q .= "FROM information_schema.table_constraints tc ";
				$q .= "JOIN information_schema.constraint_column_usage AS ccu USING (constraint_schema, constraint_name) ";
				$q .= "JOIN information_schema.columns AS c ON c.table_schema = tc.constraint_schema AND tc.table_name = c.table_name AND ccu.column_name = c.column_name ";
				$q .= "WHERE constraint_type = 'PRIMARY KEY' and tc.table_name = '".$table."'";
				if ($schema)
					$q .= " AND c.table_schema = '".$schema."'"; */
				// faster primary key get
				$q = "SELECT pg_attribute.attname AS column_name, format_type(pg_attribute.atttypid, pg_attribute.atttypmod) AS type ";
				$q .= "FROM pg_index, pg_class, pg_attribute ";
				if ($schema)
					$q .= ", pg_namespace ";
				$q .= "WHERE ";
				// regclass translates the OID to the name
				$q .= "pg_class.oid = '".$table."'::regclass AND ";
					$q .= "indrelid = pg_class.oid AND ";
				if ($schema)
				{
					$q .= "nspname = '".$schema."' AND ";
					$q .= "pg_class.relnamespace = pg_namespace.oid AND ";
				}
				$q .= "pg_attribute.attrelid = pg_class.oid AND ";
				$q .= "pg_attribute.attnum = any(pg_index.indkey) ";
				$q .= "AND indisprimary";
				$cursor = $this->_db_query($q);
				return $this->_db_fetch_array($cursor)['column_name'];
			}
			else
			{
				return false;
			}
		}

		// METHOD: _db_connect
		// PARAMS: host name, user name, password, database name, optional port (defaults to default postgres port), optional ssl (default allow)
		// RETURN: database handler
		// DESC  : wrapper for pg_connect, writes out failure to screen if error occurs (hidden var)
		public function _db_connect($db_host, $db_user, $db_pass, $db_name, $db_port = 5432, $db_ssl = 'allow')
		{
			// to avoid empty db_port
			if (!$db_port)
			{
				$db_port = 5432;
			}
			$this->dbh = @pg_connect("host=".$db_host." port=".$db_port." user=".$db_user." password=".$db_pass." dbname=".$db_name." sslmode=".$db_ssl);
			if (!$this->dbh)
			{
				die("<!-- Can't connect [host=".$db_host." port=".$db_port." user=".$db_user." password=XXXX dbname=".$db_name." sslmode=".$db_ssl."] //-->");
			}
			return $this->dbh;
		}

		// METHOD: _db_print_error
		// PARAMS: database handler, cursor
		// RETURN: error string (HTML)
		// DESC  : reads the last error for this cursor
		public function _db_print_error($cursor = '')
		{
			// run the query again for the error result here
			if (!$cursor && $this->last_error_query)
			{
				pg_send_query($this->dbh, $this->last_error_query);
				$this->last_error_query = '';
				$cursor = pg_get_result($this->dbh);
			}
			if (pg_result_error($cursor))
				return "<span style=\"color: red;\"><b>-PostgreSQL-Error-></b> ".pg_result_error($cursor)."</span><br>";
		}

		// METHOD: _db_meta_data
		// PARAMS: table name
		// RETURN: array with table data
		// DESC  : wrapper for pg_emta_data
		public function _db_meta_data($table)
		{
			return @pg_meta_data($this->dbh, $table);
		}

		// METHOD: _db_escape_string
		// PARAMS: string
		// RETURN: escaped string for postgres
		// DESC  : wrapper for pg_escape_string
		public function _db_escape_string($string)
		{
			return pg_escape_string($this->dbh, $string);
		}

		// METHOD: _db_escape_bytea
		// PARAMS: string
		// RETURN: escape bytes for postgres
		// DESC  : wrapper for pg_escape_bytea
		public function _db_escape_bytea($bytea)
		{
			return pg_escape_bytea($this->dbh, $bytea);
		}

		// METHOD: _db_connection_busy
		// PARAMS: none
		// RETURN: true/false for busy connection
		// DESC  : wrapper for pg_connection_busy
		public function _db_connection_busy()
		{
			return pg_connection_busy($this->dbh);
		}
	
		// METHOD: _db_version
		// PARAMS: none
		// RETURN: databse version
		// DESC  : wrapper for pg_version
		public function _db_version()
		{   
			// array has client, protocol, server
			// we just need the server
			$v = pg_version($this->dbh);
			return $v['server'];
		}

		// METHOD: _db_array_parse
		// PARAMS: input text, output array [needed]
		//         [internal] limit: are we at the end of the parse
		//         [internal] offset: shift for {}
		// RETURN: array with the elements
		// DESC  : postgresql array to php array
		public function _db_array_parse($text, &$output, $limit = false, $offset = 1)
		{
			if (false === $limit)
			{
				$limit = strlen($text) - 1;
				$output = array();
			}
			if ('{}' != $text)
				do
				{
					if ('{' != $text{$offset})
					{
						preg_match("/(\\{?\"([^\"\\\\]|\\\\.)*\"|[^,{}]+)+([,}]+)/", $text, $match, 0, $offset);
						$offset += strlen($match[0]);
						$output[] = ('"' != $match[1]{0} ? $match[1] : stripcslashes(substr($match[1], 1, -1)));
						if ('},' == $match[3])
							return $offset;
					}
					else
						$offset = pg_array_parse($text, $output[], $limit, $offset + 1);
				}
				while ($limit > $offset);
			return $output;
		}
	}

	// $Id: db_pgsql.inc 4743 2013-12-12 09:53:03Z gullevek $
?>

<?
	/*********************************************************************
	* $HeadURL: svn://svn/development/core_data/php/www/libs/db_pgsql.inc $
	* $LastChangedBy: gullevek $
	* $LastChangedDate: 2013-02-06 10:26:17 +0900 (Wed, 06 Feb 2013) $
	* $LastChangedRevision: 4360 $
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
	* REQUIRES 5.4 PHP!!! (should do check for this)
	*
	* pg_prepare
	* pg_execute
	* pg_num_rows
	* pg_num_fields
	* pg_field_name
	* pg_affected_rows (*)
	* pg_fetch_array
	* pg_query
	* pg_close
	* pg_connect (*)
	* pg_meta_data
	* pg_escape_string
	*
	*/

	trait db_pgsql
	{
		private $last_error_query;
		private $currval_query;

		// METHOD: _db_query
		// PARAMS: query, database handler
		// RETURN: query result
		// DESC  : wrapper for gp_query, catches error and stores it in class var
		private function _db_query($query, $dbh)
		{
			// read out the query status and save the query if needed
			$result = @pg_query($dbh, $query);
			if (!$result)
			{
				$this->last_error_query = $query;
			}
			return $result;
		}

		// METHOD: _db_close
		// PARAMS: database handler
		// RETURN: none
		// DESC  : wrapper for pg_close
		private function _db_close($dbh)
		{
			pg_close($dbh);
		}

		// METHOD: _db_prepare
		// PARAMS: database handler, prepare name, query
		// RETURN: prepared statement handler
		// DESC  : wrapper for pg_prepare
		private function _db_prepare($dbh, $name, $query)
		{
			return @pg_prepare($dbh, $name, $query);
		}

		// METHOD: _db_execute
		// PARAMS: database handler, prepare name, data for query
		// RETURN: returns status
		// DESC  : wrapper for pg_execute for running a prepared statement
		private function _db_execute($dbh, $name, $data)
		{
			return @pg_execute($dbh, $name, $data);
		}

		// METHOD: _db_num_rows
		// PARAMS: cursor
		// RETURN: rows
		// DESC  : wrapper for pg_num_rows
		private function _db_num_rows($cursor)
		{
			return pg_num_rows($cursor);
		}

		// METHOD: _db_num_fields
		// PARAMS: cursor
		// RETURN: number for fields in query
		// DESC  : wrapper for pg_num_fields
		private function _db_num_fields($cursor)
		{
			return pg_num_fields($cursor);
		}
 
		// METHOD: _db_field_name
		// PARAMS: cursor, field position
		// RETURN: name of field
		// DESC  : wrapper for pg_field_name
		private function _db_field_name($cursor, $i)
		{
			return pg_field_name($cursor, $i);
		}

		// METHOD: _db_fetch_array
		// PARAMS: cursor
		// RETURN: row
		// DESC  : wrapper for pg_fetch_array
		private function _db_fetch_array($cursor)
		{
			return pg_fetch_array($cursor);
		}

		// METHOD: _db_affected_ros
		// PARAMS: database handler, cursor
		// RETURN: number for rows
		// DESC  : wrapper for pg_affected_rows
		private function _db_affected_rows($dbh, $cursor)
		{
			return pg_affected_rows($cursor); 
		}

		// METHOD: _db_insert_id
		// PARAMS: database handler, query, primary key name
		// RETURN: last insert primary key
		// DESC  : reads the last inserted primary key for the query
		//         if ther is no pk_name tries to auto built it from the table name
		//         this only works if db schema is after "no plural names. and pk name is table name + _id
		//         detects schema prefix in table name
		private function _db_insert_id($dbh, $query, $pk_name)
		{
			// only if an insert has been done
			if (preg_match("/^insert /i", $query))
			{
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
				$this->currval_query = $q;
//echo "Q: $q<Br>";
				// I have to do manually or I overwrite the original insert internal vars ...
				if ($q = @pg_query($dbh, $q))
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

		// METHOD: _db_connect
		// PARAMS: host name, user name, password, database name, optional port (defaults to default postgres port), optional ssl (default allow)
		// RETURN: database handler
		// DESC  : wrapper for pg_connect, writes out failure to screen if error occurs (hidden var)
		private function _db_connect($db_host, $db_user, $db_pass, $db_name, $db_port = 5432, $db_ssl = 'allow')
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
		private function _db_print_error($dbh, $cursor = '')
		{
			// run the query again for the error result here
			if (!$cursor && $this->last_error_query)
			{
				pg_send_query($dbh, $this->last_error_query);
				$this->last_error_query = "";
				$cursor = pg_get_result($dbh);
			}
			if (pg_result_error($cursor))
				return "<span style=\"color: red;\"><b>-PostgreSQL-Error-></b> ".pg_result_error($cursor)."</span><br>";
		}

		// METHOD: _db_meta_data
		// PARAMS: database handler, table name
		// RETURN: array with table data
		// DESC  : wrapper for pg_emta_data
		private function _db_meta_data($dbh, $table)
		{
			return @pg_meta_data($dbh, $table);
		}

		// METHOD: _db_escape_string
		// PARAMS: string
		// RETURN: escaped string for postgres
		// DESC  : wrapper for pg_escape_string
		private function _db_escape_string($string)
		{
			return pg_escape_string($this->dbh, $string);
		}

		// METHOD: _db_escape_bytea
		// PARAMS: string
		// RETURN: escape bytes for postgres
		// DESC  : wrapper for pg_escape_bytea
		private function _db_escape_bytea($bytea)
		{
			return pg_escape_bytea($this->dbh, $bytea);
		}

		// METHOD: _db_array_parse
		// PARAMS: input text, output array [needed]
		//         [internal] limit: are we at the end of the parse
		//         [internal] offset: shift for {}
		// RETURN: array with the elements
		// DESC  : postgresql array to php array
		private function _db_array_parse($text, &$output, $limit = false, $offset = 1)
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

	// $Id: db_pgsql.inc 4360 2013-02-06 01:26:17Z gullevek $
?>

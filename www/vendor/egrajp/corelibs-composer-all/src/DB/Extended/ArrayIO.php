<?php

/*********************************************************************
* AUTHOR: Clemens Schwaighofer
* CREATED: 2002/12/17
* VERSION: 1.0.0
* RELEASED LICENSE: GNU GPL 3
* SHORT DESC  :RIPTION:
*   DB Array IO Class:
*   writes, reads or deletes a complete array (one data set) in/out a
*   table from the connected DB.
*   you don't have to write any SQL queries, worry over update/insert
*
* HISTORY:
* 2019/9/11 (cs) error string 21->1021, 22->1022 for not overlapping with IO
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

declare(strict_types=1);

namespace CoreLibs\DB\Extended;

// subclass for one array handling
class ArrayIO extends \CoreLibs\DB\IO
{
	// main calss variables
	/** @var array<mixed> */
	private array $table_array = []; // the array from the table to work on
	/** @var string */
	private string $table_name = ''; // the table_name
	/** @var string */
	private string $pk_name = ''; // the primary key from this table
	/** @var int|string|null */
	private int|string|null $pk_id; // the PK id
	// security values
	/** @var int base acl for current page */
	private int $base_acl_level = 0;

	/**
	 * constructor for the array io class, set the
	 * primary key name automatically (from array)
	 *
	 * phpcs:ignore
	 * @param array{db_name:string,db_user:string,db_pass:string,db_host:string,db_port:int,db_schema:string,db_encoding:string,db_type:string,db_ssl:string,db_convert_type?:string[],db_convert_placeholder?:bool,db_convert_placeholder_target?:string,db_debug_replace_placeholder?:bool} $db_config db connection config
	 * @param array<mixed> $table_array    table array config
	 * @param string       $table_name     table name string
	 * @param \CoreLibs\Logging\Logging $log Logging class
	 * @param int          $base_acl_level Set base acl level, if needed
	 * @param int          $acl_admin      Flag if this is an admin ACL access level
	 * @throws \RuntimeException Missing table array or table name entry
	 */
	public function __construct(
		array $db_config,
		array $table_array,
		string $table_name,
		\CoreLibs\Logging\Logging $log,
		int $base_acl_level = 0,
		int $acl_admin = 0
	) {
		// instance db_io class
		parent::__construct($db_config, $log);
		// more error vars for this class
		$this->error_string['1998'] = 'No table name set';
		$this->error_string['1999'] = 'No table array set';
		$this->error_string['1021'] = 'No Primary Key given';
		$this->error_string['1022'] = 'Could not run Array Query';

		$this->setTableArray($table_array);
		$this->setTableName($table_name);

		// set primary key for given table_array
		foreach ($this->table_array as $key => $value) {
			if (empty($value['pk'])) {
				continue;
			}
			$this->setPkName($key);
			break;
		}
		$this->dbArrayIOSetAcl($base_acl_level, $acl_admin);
	}

	/**
	 * class deconstructor
	 */
	public function __destruct()
	{
		parent::__destruct();
	}

	/**
	 * Set the overall table array
	 *
	 * @param  array<mixed> $table_array
	 * @return void
	 * @throws \RuntimeException 1999 for empty table array
	 */
	public function setTableArray(array $table_array): void
	{
		$this->table_array = $table_array;
		if (empty($this->table_array)) {
			$this->__dbError(1999, false, 'MAJOR ERROR: Core settings missing: table_arrry');
			throw new \RuntimeException('MAJOR ERROR: Core settings missing: table_array', 1999);
		}
	}

	/**
	 * return full table array, or [] if empty
	 * of reset is set to true, will reset array first
	 *
	 * @param  bool         $reset [=false] run a reset before returning
	 * @return array<mixed>
	 */
	public function getTableArray(bool $reset = false): array
	{
		if (!$reset) {
			return $this->table_array;
		}
		$table_array = $this->table_array;
		reset($table_array);
		return $table_array;
	}

	/**
	 * get a table array entry under the key with element pos
	 *
	 * @param  string $key
	 * @param  string $pos
	 * @return mixed
	 */
	public function getTableArrayEntry(string $key, string $pos): mixed
	{
		return $this->table_array[$key][$pos] ?? null;
	}

	/**
	 * set a new value at key with pos
	 *
	 * @param  mixed  $value
	 * @param  string $key
	 * @param  string $pos
	 * @return void
	 */
	public function setTableArrayEntry(mixed $value, string $key, string $pos): void
	{
		$this->table_array[$key][$pos] = $value;
	}

	/**
	 * unset entry at key with pos
	 *
	 * @param  string $key
	 * @param  string $pos
	 * @return void
	 */
	public function unsetTableArrayEntry(string $key, string $pos): void
	{
		unset($this->table_array[$key][$pos]);
	}

	/**
	 * Set table name
	 *
	 * @param  string $table_name
	 * @return void
	 * @throws \RuntimeException 1998 for empty table name
	 */
	public function setTableName(string $table_name): void
	{
		$this->table_name = $table_name;
		if (empty($this->table_name)) {
			$this->__dbError(1998, false, 'MAJOR ERROR: Core settings missing: table_name');
			throw new \RuntimeException('MAJOR ERROR: Core settings missing: table_name', 1998);
		}
	}

	/**
	 * Return table name or empty string if not net
	 *
	 * @return string
	 */
	public function getTableName(): string
	{
		return $this->table_name;
	}

	/**
	 * Set primary key name
	 *
	 * @param  string $pk_name
	 * @return void
	 */
	public function setPkName(string $pk_name): void
	{
		$this->pk_name = $pk_name;
	}

	/**
	 * get primary key name
	 *
	 * @return string
	 */
	public function getPkName(): string
	{
		return $this->pk_name;
	}

	/**
	 * set primary key id, can be null for not yet set
	 *
	 * @param  int|string|null $pk_id
	 * @return void
	 */
	public function setPkId(int|string|null $pk_id): void
	{
		$this->pk_id = $pk_id;
	}

	/**
	 * return primary key id, or null if not set
	 *
	 * @return int|string|null
	 */
	public function getPkId(): int|string|null
	{
		return $this->pk_id ?? null;
	}

	/**
	 * set the base acl level and admin acl flag
	 * This is needed for table array ACL checks
	 * if not set I assume 0 (non write/non read/non admin)
	 *
	 * @param  int  $base_acl_level ACL Level from 0 to 100, -1 is not allowed
	 *                              Will sett 0 if invalid
	 * @param  int  $acl_admin      0 for non admin, 1 for admin (base acl is 100)
	 * @return void
	 */
	public function dbArrayIOSetAcl(int $base_acl_level, int $acl_admin): void
	{
		// default not allowed, must be 0 at least
		if ($base_acl_level < 0) {
			$base_acl_level = 0;
		}
		// only 0 or 1 allowed
		if (!in_array($acl_admin, [0, 1])) {
			$acl_admin = 0;
		}
		// if the user is admin flagged, auto set to 100, if not already set to 100
		if ($acl_admin == 1) {
			$base_acl_level = 100;
		}
		$this->base_acl_level = $base_acl_level;
	}

	/**
	 * changes all previously alterd HTML code into visible one,
	 * works for <b>,<i>, and <a> (thought <a> can be / or should
	 * be handled with the magic links functions
	 * used with the read function
	 *
	 * @param  string $text any html encoded string
	 * @return string       decoded html string
	 */
	public function convertData(string $text): string
	{
		$text = str_replace('&lt;b&gt;', '<b>', $text);
		$text = str_replace('&lt;/b&gt;', '</b>', $text);
		$text = str_replace('&lt;i&gt;', '<i>', $text);
		$text = str_replace('&lt;/i&gt;', '</i>', $text);
		// my need a change
		$text = str_replace('&lt;a href=&quot;', '<a target="_blank" href="', $text);
		$text = str_replace('&quot;&gt;', '">', $text);
		$text = str_replace('&lt;/a&gt;', '</a>', $text);
		return $text;
	}

	/**
	 * changeds all HTML entities into non HTML ones
	 *
	 * @param  string $text encoded html string
	 * @return string       decoded html string
	 */
	public function convertEntities(string $text): string
	{
		$text = str_replace('&lt;', '<', $text);
		$text = str_replace('&gt;', '>', $text);
		$text = str_replace('&amp;', '&', $text);
		$text = str_replace('&quot;', '"', $text);
		$text = str_replace('&#039;', "'", $text);
		return $text;
	}

	/**
	 * dumps the current data
	 *
	 * @param  bool   $write write to error message, default false
	 * @return string        the array data as html string entry
	 */
	public function dbDumpArray(bool $write = false): string
	{
		reset($this->table_array);
		$string = '';
		foreach ($this->table_array as $column => $data_array) {
			$string .= '<b>' . $column . '</b> -> ' . $data_array['value'] . '<br>';
		}
		// add output to internal error_msg
		if ($write === true) {
			$this->__dbDebug('dbArray', $string);
		}
		return $string;
	}

	/**
	 * checks if pk is set and if not, set from pk_id and
	 * if this also not set return 0
	 *
	 * @return bool true if pk value is set, else false
	 */
	public function dbCheckPkSet(): bool
	{
		// if pk_id is set, overrule ...
		if (!empty($this->getPkId())) {
			$this->table_array[$this->pk_name]['value'] = $this->getPkId();
		}
		// if not set ... produce error
		if (!$this->table_array[$this->pk_name]['value']) {
			// if no PK found, error ...
			$this->__dbError(1021);
			return false;
		} else {
			return true;
		}
	}

	/**
	 * resets the whole array values
	 * @param  bool $reset_pk true if we want to reset the pk too
	 * @return void              has no return
	 */
	public function dbResetArray(bool $reset_pk = false): void
	{
		reset($this->table_array);
		foreach (array_keys($this->table_array) as $column) {
			if (!$this->table_array[$column]['pk']) {
				unset($this->table_array[$column]['value']);
			} elseif ($reset_pk) {
				unset($this->table_array[$column]['value']);
			}
		}
	}

	/**
	 * deletes one dataset
	 *
	 * @param  array<mixed> $table_array optional override for table array set
	 *                                   set this as new table array too
	 * @param  bool      $acl_limit   [false], if set to true, well do ACL limit check
	 * @return array<mixed>              returns the table array that was deleted
	 */
	public function dbDelete(array $table_array = [], bool $acl_limit = false): array
	{
		// is array and has values, override set and set new
		if (count($table_array)) {
			$this->table_array = $table_array;
		}
		if (!$this->dbCheckPkSet()) {
			return $this->table_array;
		}
		if ($acl_limit === true && $this->base_acl_level < 100) {
			$this->log->error('DB DELETE ERROR: ACL Limit on, Delete, '
				. 'but base ACL level of 100 not met: ' . $this->base_acl_level);
			return $this->table_array;
		}
		// delete query
		$q = 'DELETE FROM ' . $this->table_name . ' WHERE ';
		$q .= $this->pk_name . ' = ' . $this->table_array[$this->pk_name]['value'] . ' ';
		// delete files and build FK query
		reset($this->table_array);
		$q_where = '';
		foreach (array_keys($this->table_array) as $column) {
			// suchen nach bildern und lschen ...
			if (
				!empty($this->table_array[$column]['file']) &&
				file_exists($this->table_array[$column]['url'] . $this->table_array[$column]['value'])
			) {
				if (file_exists($this->table_array[$column]['path'] . $this->table_array[$column]['value'])) {
					unlink($this->table_array[$column]['path'] . $this->table_array[$column]['value']);
				}
				$file_name = str_replace('_tn', '', $this->table_array[$column]['value']);
				if (file_exists($this->table_array[$column]['path'] . $file_name)) {
					unlink($this->table_array[$column]['path'] . $file_name);
				}
			}
			// if we have a foreign key
			if (!empty($this->table_array[$column]['fk'])) {
				// create FK constraint checks
				if ($q_where) {
					$q_where .= ' AND ';
				}
				$q_where .= $column . ' = ' . $this->table_array[$column]['value'];
			}
			// allgemeines zurcksetzen des arrays
			unset($this->table_array[$column]['value']);
		}

		// attach fk row if there ...
		if ($q_where) {
			$q .= ' AND ' . $q_where;
		}
		// if 0, error
		$this->setPkId(null);
		if (!$this->dbExec($q)) {
			$this->__dbError(1022);
		}
		return $this->table_array;
	}

	/**
	 * reads one row into the array
	 *
	 * @param  bool      $edit        on true convert data, else as is
	 * @param  array<mixed> $table_array optional table array, overwrites
	 *                                   internal set array
	 * @return array<mixed>              set table array with values
	 */
	public function dbRead(bool $edit = false, array $table_array = []): array
	{
		// if array give, overrules internal array
		if (count($table_array)) {
			$this->table_array = $table_array;
		}
		if (!$this->dbCheckPkSet()) {
			return $this->table_array;
		}
		reset($this->table_array);
		$q_select = '';
		$q_where = '';
		// create select part & addition FK part
		foreach ($this->table_array as $column => $data_array) {
			if ($q_select) {
				$q_select .= ', ';
			}
			if (
				!empty($data_array['type']) && $data_array['type'] == 'datetime' &&
				!empty($data_array['sql_read'])
			) {
				// convert tom different timestamp type
				$q_select .= "TO_CHAR($column, '" . $data_array['sql_read'] . "') AS $column";
			} else {
				$q_select .= $column;
			}

			// check FK ...
			if (
				isset($this->table_array[$column]['fk']) &&
				isset($this->table_array[$column]['value'])
			) {
				if (!empty($q_where)) {
					$q_where .= ' AND ';
				}
				$q_where .= $column .= ' = ' . $this->table_array[$column]['value'];
			}
		}

		$q = 'SELECT ';
		$q .= $q_select;
		$q .= ' FROM ' . $this->table_name . ' WHERE ';
		$q .= $this->pk_name . ' = ' . $this->table_array[$this->pk_name]['value'] . ' ';
		if ($q_where) {
			$q .= ' AND ' . $q_where;
		}

		// if query was executed okay, else set error
		if ($this->dbExec($q)) {
			if (is_array($res = $this->dbFetchArray())) {
				reset($this->table_array);
				foreach ($this->table_array as $column => $data_array) {
					// wenn "edit" dann gib daten wie in DB zurück, ansonten aufbereiten fr ausgabe
					// ?? sollte das nicht drauen ??? man weis ja net was da drin steht --> is noch zu berlegen
					// $this->log->debug('DB READ', 'EDIT: $edit | Spalte: $column | type: '
					//	.$this->table_array[$column]['type'].' | Res: '.$res[$column]);
					if ($edit) {
						$this->table_array[$column]['value'] = $res[$column];
						// if password, also write to hidden
						if (
							isset($this->table_array[$column]['type']) &&
							$this->table_array[$column]['type'] == 'password'
						) {
							$this->table_array[$column]['HIDDEN_value'] = $res[$column];
						}
					} else {
						$this->table_array[$column]['value'] = $this->convertData(nl2br($res[$column]));
						// had to put out the htmlentities from the line above as it breaks japanese characters
					}
				}
			}
			// possible dbFetchArray errors ...
			$this->setPkId($this->table_array[$this->pk_name]['value']);
		} else {
			$this->__dbError(1022);
		}
		return $this->table_array;
	}

	/**
	 * writes one set into DB or updates one set (if PK exists)
	 *
	 * @param  bool      $addslashes  old convert entities and set set escape
	 * @param  array<mixed> $table_array optional table array, overwrites internal one
	 * @param  bool      $acl_limit   [false], if set to true, well do ACL limit check
	 * @return array<mixed>              table array or null
	 */
	public function dbWrite(
		bool $addslashes = false,
		array $table_array = [],
		bool $acl_limit = false
	): array {
		if (count($table_array)) {
			$this->table_array = $table_array;
		}
		// checken ob PKs gesetzt, wenn alle -> update, wenn keiner -> insert, wenn ein paar -> ERROR!
		if (!$this->table_array[$this->pk_name]['value']) {
			$insert = 1;
		} else {
			$insert = 0;
		}
		// early abort for new write with not enough ACL level
		if ($insert && $acl_limit === true && $this->base_acl_level < 100) {
			$this->log->error('DB WRITE ERROR: ACL Limit on, Insert, '
				. 'but base ACL level of 100 not met: ' . $this->base_acl_level);
			return $this->table_array;
		}

		reset($this->table_array);
		$q_data = '';
		$q_vars = '';
		$q_where = '';
		foreach ($this->table_array as $column => $data_array) {
			/********************************* START FILE *************************************/
			// file upload
			if (isset($this->table_array[$column]['file'])) {
				// falls was im tmp drinnen, sprich ein upload, datei kopieren, Dateinamen in db schreiben
				// falls datei schon am server (physischer pfad), dann einfach url in db schreiben (update)
				// falls in 'delete' 'ja' dann loeschen (und gibts eh nur beim update)
				if ($this->table_array[$column]['delete']) {
					unset($this->table_array[$column]['delete']);
					if (file_exists($this->table_array[$column]['path'] . $this->table_array[$column]['value'])) {
						unlink($this->table_array[$column]['path'] . $this->table_array[$column]['value']);
					}
					$file_name = str_replace('_tn', '', $this->table_array[$column]['value']);
					if (file_exists($this->table_array[$column]['path'] . $file_name)) {
						unlink($this->table_array[$column]['path'] . $file_name);
					}
					$this->table_array[$column]['value'] = '';
				} else {
					if ($this->table_array[$column]['tmp'] != 'none' && $this->table_array[$column]['tmp']) {
						// mozilla, patch
						$fn_name = explode('/', $this->table_array[$column]['dn']);
						$this->table_array[$column]['dn'] = $fn_name[count($fn_name) - 1];
						$filename_parts = explode('.', $this->table_array[$column]['dn']);
						$ext = end($filename_parts);
						array_splice($filename_parts, -1, 1);
						$name = str_replace(' ', '_', implode('.', $filename_parts));
						$file_name = $name . '.' . $ext;
						//echo 'Dn: $file_name';
						copy($this->table_array[$column]['tmp'], $this->table_array[$column]['path'] . $file_name);
						// automatisch thumbnail generieren, geht nur mit convert (ImageMagic!!!), aber nur bei bild ..
						if (in_array(strtolower($ext), ['jpeg', 'jpg', 'gif', 'png'])) {
							$file_name_tn = $name . '_tn.' . $ext;
							$input = $this->table_array[$column]['path'] . $file_name;
							$output = $this->table_array[$column]['path'] . $file_name_tn;
							$com = 'convert -geometry 115 ' . $input . ' ' . $output;
							exec($com);
							$this->table_array[$column]['value'] = $file_name_tn;
						} else {
							$this->table_array[$column]['value'] = $file_name;
						}
					} elseif (file_exists($this->table_array[$column]['path'] . $this->table_array[$column]['value'])) {
						// mach gar nix, wenn bild schon da ???
					}
				} // delete or upload
			} // file IF
			/********************************* END FILE **************************************/

			// do not write 'pk' (primary key) or 'view' values
			// also do not write UPDATE for elements that are
			// acl flagged, not if we have an ACL limiter, don't insert
			// $this->log->debug('DB WRITE', 'C: ' . $column . ', '
			// 	. 'ACL Level ' . $this->log->prBl($acl_limit) . ', '
			// 	. 'TA ACL: ' . ($this->table_array[$column]['min_edit_acl'] ?? 100) . ', '
			// 	. 'Base ACL: ' . $this->base_acl_level);
			if (
				!isset($this->table_array[$column]['pk']) &&
				isset($this->table_array[$column]['type']) &&
				$this->table_array[$column]['type'] != 'view' &&
				strlen($column) > 0 &&
				// no acl limiter
				(
					$acl_limit === false ||
					// acl limit is true, min edit must be at larger than set
					$this->base_acl_level >=
						($this->table_array[$column]['min_edit_acl'] ?? 100)
				)
			) {
				// for password use hidden value if main is not set
				if (
					isset($this->table_array[$column]['type']) &&
					$this->table_array[$column]['type'] == 'password' &&
					empty($this->table_array[$column]['value'])
				) {
					$this->table_array[$column]['value'] = $this->table_array[$column]['HIDDEN_value'];
				}
				if (!$insert) {
					if (strlen($q_data)) {
						$q_data .= ', ';
					}
					$q_data .= $column . ' = ';
				} else {
					// this is insert
					if (strlen($q_data)) {
						$q_data .= ', ';
					}
					if (strlen($q_vars)) {
						$q_vars .= ', ';
					}
					$q_vars .= $column;
				}
				// integer is different
				if (isset($this->table_array[$column]['int']) || isset($this->table_array[$column]['int_null'])) {
					$this->log->debug('WRITE CHECK', '[' . $column . '][' . $this->table_array[$column]['value'] . ']'
						. '[' . $this->table_array[$column]['type'] . '] '
						. 'VALUE SET: ' . (string)isset($this->table_array[$column]['value'])
						. ' | INT NULL: ' . (string)isset($this->table_array[$column]['int_null']));
					if (
						isset($this->table_array[$column]['value']) &&
						!$this->table_array[$column]['value'] &&
						isset($this->table_array[$column]['int_null'])
					) {
						$_value = 'NULL';
					} elseif (
						!isset($this->table_array[$column]['value']) ||
						(isset($this->table_array[$column]['value']) && !$this->table_array[$column]['value'])
					) {
						$_value = 0;
					} else {
						$_value = $this->table_array[$column]['value'];
					}
					$q_data .= $_value;
				} elseif (isset($this->table_array[$column]['bool'])) {
					// bool storage (reverse check on ifset)
					$q_data .= "'" . $this->dbBoolean($this->table_array[$column]['value'], true) . "'";
				} elseif (
					isset($this->table_array[$column]['interval']) ||
					isset($this->table_array[$column]['date']) ||
					isset($this->table_array[$column]['datetime']) ||
					isset($this->table_array[$column]['emptynull'])
				) {
					// for interval we check if no value, then we set null
					if (
						!isset($this->table_array[$column]['value']) ||
						(isset($this->table_array[$column]['value']) && !$this->table_array[$column]['value'])
					) {
						$_value = 'NULL';
					} elseif (isset($this->table_array[$column]['value'])) {
						$_value = $this->dbEscapeLiteral($this->table_array[$column]['value']);
					} else {
						// fallback
						$_value = 'NULL';
					}
					$q_data .= $_value;
				} else {
					// if the error check is json, we set field to null if NOT set
					// else normal string write
					if (
						isset($this->table_array[$column]['error_check']) &&
						$this->table_array[$column]['error_check'] == 'json' &&
						(
							!isset($this->table_array[$column]['value']) ||
							(isset($this->table_array[$column]['value']) &&
							!$this->table_array[$column]['value'])
						)
					) {
						$q_data .= 'NULL';
					} else {
						// normal string
						$q_data .= "'";
						// if add slashes do convert & add slashes else write AS is
						if ($addslashes) {
							$q_data .= $this->dbEscapeString(
								$this->convertEntities($this->table_array[$column]['value'])
							);
						} else {
							$q_data .= $this->dbEscapeString($this->table_array[$column]['value']);
						}
						$q_data .= "'";
					}
				}
			}
		} // while ...

		if (empty($q_data)) {
			$this->log->error('DB WRITE ERROR: No data to write, possible through ACL');
			return $this->table_array;
		}

		// NOW get PK, and FK settings (FK only for update query)
		// get it at the end, cause now we can be more sure of no double IDs, etc
		reset($this->table_array);
		// create select part & addition FK part
		foreach (array_keys($this->table_array) as $column) {
			// check FK ...
			if (
				isset($this->table_array[$column]['fk']) &&
				isset($this->table_array[$column]['value'])
			) {
				if (!empty($q_where)) {
					$q_where .= ' AND ';
				}
				$q_where .= $column .= ' = ' . $this->table_array[$column]['value'];
			}
		}

		// if no PK set, then get max ID from DB
		if (!$this->table_array[$this->pk_name]['value']) {
			// max id, falls INSERT
			$q = 'SELECT MAX(' . $this->pk_name . ') + 1 AS pk_id FROM ' . $this->table_name;
			if (is_array($res = $this->dbReturnRow($q))) {
				$pk_id = $res['pk_id'];
			} else {
				$pk_id = 1;
			}
			$this->table_array[$this->pk_name]['value'] = $pk_id;
		}

		if (!$insert) {
			$q = 'UPDATE ' . $this->table_name . ' SET ';
			$q .= $q_data;
			$q .= ' WHERE ';
			$q .= $this->pk_name . ' = ' . $this->table_array[$this->pk_name]['value'] . ' ';
			if (!empty($q_where)) {
				$q .= ' AND ' . $q_where;
			}
			// set pk_id ... if it has changed or so
			$this->setPkId($this->table_array[$this->pk_name]['value']);
		} else {
			$q = 'INSERT INTO ' . $this->table_name . ' ';
			$q .= '(' . $q_vars . ') ';
			$q .= 'VALUES (' . $q_data . ')';
		}
		// return success or not
		if (!$this->dbExec($q)) {
			$this->__dbError(1022);
		}
		// set primary key
		if ($insert) {
			$insert_id = $this->dbGetInsertPK();
			if (is_array($insert_id)) {
				$insert_id = 0;
			}
			$this->table_array[$this->pk_name]['value'] = $insert_id;
			$this->setPkId($insert_id);
		}
		// return the table if needed
		return $this->table_array;
	}
	// end of class
}

// __END__

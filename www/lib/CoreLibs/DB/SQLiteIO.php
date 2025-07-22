<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2024/8/21
 * DESCRIPTION:
 * SQL Lite interface
 * Note: This is a very simple library and in future should perhaps merge with the master
 * CoreLibs SQL interface
 *
 * TODO: This should move to the CoreLibs\DB\IO class as a sub type for "sqlite" next to "pgsql"
*/

declare(strict_types=1);

namespace CoreLibs\DB;

use CoreLibs\Create\Hash;

class SqLite implements SQL\Interface\DatabaseInterface
{
	/** @var \CoreLibs\Logging\Logging logging */
	public \CoreLibs\Logging\Logging $log;

	/** @var string database connection string */
	private string $dsn;
	/** @var \PDO database handler */
	private \PDO $dbh;
	/** @var PDOStatement|false one cursor, for internal handling */
	// private \PDOStatement|false $cursor;
	/** @var array<string,mixed> extended cursoers string index with content */
	private array $cursor_ext = [];

	/**
	 * init database system
	 *
	 * @param  \CoreLibs\Logging\Logging $log
	 * @param  string                    $dsn
	 */
	public function __construct(
		\CoreLibs\Logging\Logging $log,
		string $dsn
	) {
		$this->log = $log;
		// open new connection
		if ($this->__connectToDB($dsn) === false) {
			throw new \ErrorException("Cannot load database: " . $dsn, 1);
		}
	}

	// *********************************************************************
	// MARK: PRIVATE METHODS
	// *********************************************************************

	/**
	 * Get a cursor dump with all info
	 *
	 * @param  \PDOStatement $cursor
	 * @return string|false
	 */
	private function __dbGetCursorDump(\PDOStatement $cursor): string|false
	{
		// get the cursor info
		ob_start();
		$cursor->debugDumpParams();
		$cursor_dump = ob_get_contents();
		ob_end_clean();
		return $cursor_dump;
	}

	/**
	 * fetch rows from a cursor (post execute)
	 *
	 * @param  \PDOStatement $cursor
	 * @return array<mixed>|false
	 */
	private function __dbFetchArray(\PDOStatement $cursor): array|false
	{
		try {
			// on empty array return false
			// TODO make that more elegant?
			return empty($row = $cursor->fetch(mode:\PDO::FETCH_NAMED)) ? false : $row;
		} catch (\PDOException $e) {
			$this->log->error(
				"Cannot fetch from cursor",
				[
					"dsn" => $this->dsn,
					"DumpParams" => $this->__dbGetCursorDump($cursor),
					"PDOException" => $e
				]
			);
			return false;
		}
	}

	// MARK: open database

	/**
	 * Open database
	 * reports errors for wrong DSN or failed connection
	 *
	 * @param  string $dsn
	 * @return bool
	 */
	private function __connectToDB(string $dsn): bool
	{
		// check if dsn starts with ":"
		if (!str_starts_with($dsn, "sqlite:")) {
			$this->log->error(
				"Invalid dsn string",
				[
					"dsn" => $dsn
				]
			);
			return false;
		}
		// TODO: if not ":memory:" check if path to file is writeable by system
		// avoid double open
		if (!empty($this->dsn) && $dsn == $this->dsn) {
			$this->log->info(
				"Connection already establisehd with this dsn",
				[
					"dsn" => $dsn,
				]
			);
			return true;
		}
		// TODO: check that folder is writeable
		// set DSN and open connection
		$this->dsn = $dsn;
		try {
			$this->dbh = new \PDO($this->dsn);
		} catch (\PDOException $e) {
			$this->log->error(
				"Cannot open database",
				[
					"dsn" => $this->dsn,
					"PDOException" => $e
				]
			);
			return false;
		}
		return true;
	}

	// *********************************************************************
	// MARK: PUBLIC METHODS
	// *********************************************************************

	// MARK: db meta data (table info)

	/**
	 * Table meta data
	 * Note that if columns have multi entries multiple lines are returned
	 * ?1 is equal to $1 in this query
	 *
	 * @param  string      $table
	 * @return array<array<string,mixed>>|false
	 */
	public function dbShowTableMetaData(string $table): array|false
	{
		$table_info = [];
		$query = <<<SQL
		SELECT
			ti.cid, ti.name, ti.type, ti.'notnull', ti.dflt_value, ti.pk,
			il_ii.idx_name, il_ii.idx_unique, il_ii.idx_origin, il_ii.idx_partial
		FROM
			sqlite_schema AS m,
			pragma_table_info(m.name) AS ti
			LEFT JOIN (
				SELECT
					il.name AS idx_name, il.'unique' AS idx_unique, il.origin AS idx_origin, il.partial AS idx_partial,
					ii.cid AS tbl_cid
				FROM
					sqlite_schema AS m,
					pragma_index_list(m.name) AS il,
					pragma_index_info(il.name) AS ii
				WHERE m.name = ?1
			) AS il_ii ON (ti.cid = il_ii.tbl_cid)
		WHERE
			m.name = ?1
		SQL;
		while (is_array($row = $this->dbReturnArray($query, [$table]))) {
			$table_info[] = [
				'cid' => $row['cid'],
				'name' => $row['name'],
				'type' => $row['type'],
				'notnull' => $row['notnull'],
				'dflt_value' => $row['dflt_value'],
				'pk' => $row['pk'],
				'idx_name' => $row['idx_name'],
				'idx_unique' => $row['idx_unique'],
				'idx_origin' => $row['idx_origin'],
				'idx_partial' => $row['idx_partial'],
			];
		}

		if (!$table_info) {
			return false;
		}
		return $table_info;
	}

	// MARK: db exec

	/**
	 * for reading or simple execution, no return data
	 *
	 * @param  string    $query
	 * @return int|false
	 */
	public function dbExec(string $query): int|false
	{
		try {
			return $this->dbh->exec($query);
		} catch (\PDOException $e) {
			$this->log->error(
				"Cannot execute query",
				[
					"dsn" => $this->dsn,
					"query" => $query,
					"PDOException" => $e
				]
			);
			return false;
		}
	}

	// MARK: db query

	/**
	 * Run a simple query and return its statement
	 *
	 * @param  string              $query
	 * @return \PDOStatement|false
	 */
	public function dbQuery(string $query): \PDOStatement|false
	{
		try {
			return $this->dbh->query($query, \PDO::FETCH_NAMED);
		} catch (\PDOException $e) {
			$this->log->error(
				"Cannot run query",
				[
					"dsn" => $this->dsn,
					"query" => $query,
					"PDOException" => $e
				]
			);
			return false;
		}
	}

	// MARK: db prepare & execute calls

	/**
	 * Execute one query with params
	 *
	 * @param  string       $query
	 * @param  array<mixed> $params
	 * @return \PDOStatement|false
	 */
	public function dbExecParams(string $query, array $params): \PDOStatement|false
	{
		// prepare query
		if (($cursor = $this->dbPrepare($query)) === false) {
			return false;
		}
		// execute the query, on failure return false
		if ($this->dbCursorExecute($cursor, $params) === false) {
			return false;
		}
		return $cursor;
	}

	/**
	 * Prepare query
	 *
	 * @param  string              $query
	 * @return \PDOStatement|false
	 */
	public function dbPrepare(string $query): \PDOStatement|false
	{
		try {
			// store query with cursor so we can reference?
			return $this->dbh->prepare($query);
		} catch (\PDOException $e) {
			$this->log->error(
				"Cannot open cursor",
				[
					"dsn" => $this->dsn,
					"query" => $query,
					"PDOException" => $e
				]
			);
			return false;
		}
	}

	/**
	 * execute a cursor
	 *
	 * @param  \PDOStatement $cursor
	 * @param  array<mixed>  $params
	 * @return bool
	 */
	public function dbCursorExecute(\PDOStatement $cursor, array $params): bool
	{
		try {
			return $cursor->execute($params);
		} catch (\PDOException $e) {
			// write error log
			$this->log->error(
				"Cannot execute prepared query",
				[
					"dsn" => $this->dsn,
					"params" => $params,
					"DumpParams" => $this->__dbGetCursorDump($cursor),
					"PDOException" => $e
				]
			);
			return false;
		}
	}

	// MARK: db return array

	/**
	 * Returns hash for query
	 * Hash is used in all internal storage systems for return data
	 *
	 * @param  string       $query  The query to create the hash from
	 * @param  array<mixed> $params If the query is params type we need params
	 *                              data to create a unique call one, optional
	 * @return string               Hash, as set by hash long
	 */
	public function dbGetQueryHash(string $query, array $params = []): string
	{
		return Hash::hashLong(
			$query . (
				$params !== [] ?
					'#' . json_encode($params) : ''
			)
		);
	}

	/**
	 * resets all data stored to this query
	 * @param  string       $query  The Query whose cache should be cleaned
	 * @param  array<mixed> $params If the query is params type we need params
	 *                              data to create a unique call one, optional
	 * @return bool                 False if query not found, true if success
	 */
	public function dbCacheReset(string $query, array $params = []): bool
	{
		$query_hash = $this->dbGetQueryHash($query, $params);
		// clears cache for this query
		if (empty($this->cursor_ext[$query_hash]['query'])) {
			$this->log->error('Cannot reset cursor_ext with given query and params', [
				"query" => $query,
				"params" => $params,
			]);
			return false;
		}
		unset($this->cursor_ext[$query_hash]);
		return true;
	}

	/**
	 * return array with data, when finshed return false
	 * also returns false on error
	 *
	 * TODO: This is currently a one time run
	 * if the same query needs to be run again, the cursor_ext must be reest
	 * with dbCacheReset
	 *
	 * @param  string             $query
	 * @param  array<mixed>       $params
	 * @return array<mixed>|false
	 */
	public function dbReturnArray(string $query, array $params = []): array|false
	{
		$query_hash = $this->dbGetQueryHash($query, $params);
		if (!isset($this->cursor_ext[$query_hash])) {
			$this->cursor_ext[$query_hash] = [
				// cursor null: unset, if set \PDOStatement
				'cursor' => null,
				// the query used in this call
				'query' => $query,
				// parameter
				'params' => $params,
				// how many rows have been read from db
				'read_rows' => 0,
				// when fetch array or cache read returns false
				// in loop read that means dbReturn retuns false without error
				'finished' => false,
			];
			if (!empty($params)) {
				if (($cursor = $this->dbExecParams($query, $params)) === false) {
					return false;
				}
			} else {
				if (($cursor = $this->dbQuery($query)) === false) {
					return false;
				}
			}
			$this->cursor_ext[$query_hash]['cursor'] = $cursor;
		}
		// flag finished if row is false
		$row = $this->__dbFetchArray($this->cursor_ext[$query_hash]['cursor']);
		if ($row === false) {
			$this->cursor_ext[$query_hash]['finished'] = true;
		} else {
			$this->cursor_ext[$query_hash]['read_rows']++;
		}
		return $row;
	}

	// MARK: other interface

	/**
	 * get current db handler
	 * this is for raw access
	 *
	 * @return \PDO
	 */
	public function getDbh(): \PDO
	{
		return $this->dbh;
	}
}

// __END__

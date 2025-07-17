<?php

/**
 * AUTHOR: Clemens Schwaighofer
 * CREATED: 2025/7/17
 * DESCRIPTION:
 * SQLite IO basic interface
*/

declare(strict_types=1);

namespace CoreLibs\DB\SQL\Interface;

interface DatabaseInterface
{
	/**
	 * Table meta data
	 * Note that if columns have multi
	 *
	 * @param  string      $table
	 * @return array<array<string,mixed>>|false
	 */
	public function dbShowTableMetaData(string $table): array|false;

	/**
	 * for reading or simple execution, no return data
	 *
	 * @param  string    $query
	 * @return int|false
	 */
	public function dbExec(string $query): int|false;

	/**
	 * Run a simple query and return its statement
	 *
	 * @param  string              $query
	 * @return \PDOStatement|false
	 */
	public function dbQuery(string $query): \PDOStatement|false;

	/**
	 * Execute one query with params
	 *
	 * @param  string       $query
	 * @param  array<mixed> $params
	 * @return \PDOStatement|false
	 */
	public function dbExecParams(string $query, array $params): \PDOStatement|false;

	/**
	 * Prepare query
	 *
	 * @param  string              $query
	 * @return \PDOStatement|false
	 */
	public function dbPrepare(string $query): \PDOStatement|false;

	/**
	 * execute a cursor
	 *
	 * @param  \PDOStatement $cursor
	 * @param  array<mixed>  $params
	 * @return bool
	 */
	public function dbCursorExecute(\PDOStatement $cursor, array $params): bool;

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
	public function dbReturnArray(string $query, array $params = []): array|false;

	/**
	 * get current db handler
	 * this is for raw access
	 *
	 * @return \PDO
	 */
	public function getDbh(): \PDO;
}

// __END__

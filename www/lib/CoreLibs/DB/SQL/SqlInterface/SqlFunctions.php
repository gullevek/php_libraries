<?php

/**
 * Intrface for all SQL\* functions
 */

declare(strict_types=1);

namespace CoreLibs\DB\SQL\SqlInterface;

interface SqlFunctions
{
	/**
	 * Undocumented function
	 *
	 * @return bool
	 */
	public function __dbLastErrorQuery(): bool;

	/**
	 * Undocumented function
	 *
	 * @param string $query
	 * @return object|resource|bool
	 */
	public function __dbQuery(string $query);

	/**
	 * Undocumented function
	 *
	 * @param string $query
	 * @param array<mixed> $params
	 * @return object|resource|bool
	 */
	public function __dbQueryParams(string $query, array $params);

	/**
	 * Undocumented function
	 *
	 * @param string $query
	 * @return boolean
	 */
	public function __dbSendQuery(string $query): bool;

	/**
	 * Undocumented function
	 *
	 * @return object|resource|bool
	 */
	public function __dbGetResult();

	/**
	 * Undocumented function
	 *
	 * @return void
	 */
	public function __dbClose(): void;

	/**
	 * Undocumented function
	 *
	 * @param string $name
	 * @param string $query
	 * @return object|resource|bool
	 */
	public function __dbPrepare(string $name, string $query);

	/**
	 * Undocumented function
	 *
	 * @param string $name
	 * @param array<mixed> $data
	 * @return object|resource|bool
	 */
	public function __dbExecute(string $name, array $data);

	/**
	 * Undocumented function
	 *
	 * @param object|resource|bool $cursor
	 * @return integer
	 */
	public function __dbNumRows($cursor): int;

	/**
	 * Undocumented function
	 *
	 * @param object|resource|bool $cursor
	 * @return integer
	 */
	public function __dbNumFields($cursor): int;

	/**
	 * Undocumented function
	 *
	 * @param object|resource|bool $cursor
	 * @param int $i
	 * @return string|bool
	 */
	public function __dbFieldName($cursor, int $i);

	/**
	 * Undocumented function
	 *
	 * @param object|resource|bool $cursor
	 * @param int $result_type
	 * @return array<mixed>|bool
	 */
	public function __dbFetchArray($cursor, int $result_type = PGSQL_BOTH);

	/**
	 * Undocumented function
	 *
	 * @param boolean $assoc_type
	 * @return integer
	 */
	public function __dbResultType(bool $assoc_type = true): int;

	/**
	 * Undocumented function
	 *
	 * @param object|resource|bool $cursor
	 * @return array<mixed>|bool
	 */
	public function __dbFetchAll($cursor);

	/**
	 * Undocumented function
	 *
	 * @param object|resource|bool $cursor
	 * @return integer
	 */
	public function __dbAffectedRows($cursor): int;

	/**
	 * Undocumented function
	 *
	 * @param string $query
	 * @param string|null $pk_name
	 * @return string|integer|false
	 */
	public function __dbInsertId(string $query, ?string $pk_name);

	/**
	 * Undocumented function
	 *
	 * @param string $table
	 * @param string $schema
	 * @return string|bool
	 */
	public function __dbPrimaryKey(string $table, string $schema = '');

	/**
	 * Undocumented function
	 *
	 * @param string $db_host
	 * @param string $db_user
	 * @param string $db_pass
	 * @param string $db_name
	 * @param integer $db_port
	 * @param string $db_ssl
	 * @return object|resource|bool
	 */
	public function __dbConnect(
		string $db_host,
		string $db_user,
		string $db_pass,
		string $db_name,
		int $db_port,
		string $db_ssl = 'allow'
	);

	/**
	 * Undocumented function
	 *
	 * @param object|resource|bool $cursor
	 * @return string
	 */
	public function __dbPrintError($cursor = false): string;

	/**
	 * Undocumented function
	 *
	 * @param string $table
	 * @param boolean $extended
	 * @return array<mixed>|bool
	 */
	public function __dbMetaData(string $table, $extended = true);

	/**
	 * Undocumented function
	 *
	 * @param string|int|float|bool $string
	 * @return string
	 */
	public function __dbEscapeString($string): string;

	/**
	 * Undocumented function
	 *
	 * @param string|int|float|bool $string
	 * @return string
	 */
	public function __dbEscapeLiteral($string): string;

	/**
	 * Undocumented function
	 *
	 * @param string $string
	 * @return string
	 */
	public function __dbEscapeIdentifier(string $string): string;

	/**
	 * Undocumented function
	 *
	 * @param string $bytea
	 * @return string
	 */
	public function __dbEscapeBytea(string $bytea): string;

	/**
	 * Undocumented function
	 *
	 * @return boolean
	 */
	public function __dbConnectionBusy(): bool;

	/**
	 * Undocumented function
	 *
	 * @param integer $timeout_seconds
	 * @return boolean
	 */
	public function __dbConnectionBusySocketWait(int $timeout_seconds = 3): bool;

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function __dbVersion(): string;

	/**
	 * Undocumented function
	 *
	 * @param string $array_text
	 * @param integer $start
	 * @param integer|null $end
	 * @return array<mixed>|null
	 */
	public function __dbArrayParse(
		string $array_text,
		int $start = 0,
		?int &$end = null
	): ?array;

	/**
	 * Undocumented function
	 *
	 * @param string $show_string
	 * @return string
	 */
	public function __dbShow(string $show_string): string;

	/**
	 * Undocumented function
	 *
	 * @param string $db_schema
	 * @return integer
	 */
	public function __dbSetSchema(string $db_schema): int;

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function __dbGetSchema(): string;

	/**
	 * Undocumented function
	 *
	 * @param string $db_encoding
	 * @return integer
	 */
	public function __dbSetEncoding(string $db_encoding): int;

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function __dbGetEncoding(): string;
}

// __END__

<?php

/**
 * Intrface for all SQL\* functions
 */

declare(strict_types=1);

namespace CoreLibs\DB\SQL\Interface;

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
	 * @return \PgSql\Result|false
	 */
	public function __dbQuery(string $query): \PgSql\Result|false;

	/**
	 * Undocumented function
	 *
	 * @param string $query
	 * @param array<mixed> $params
	 * @return \PgSql\Result|false
	 */
	public function __dbQueryParams(string $query, array $params): \PgSql\Result|false;

	/**
	 * Undocumented function
	 *
	 * @param string $query
	 * @return bool
	 */
	public function __dbSendQuery(string $query): bool;

	/**
	 * Undocumented function
	 *
	 * @param  string $query
	 * @param  array<mixed> $params
	 * @return bool
	 */
	public function __dbSendQueryParams(string $query, array $params): bool;

	/**
	 * Undocumented function
	 *
	 * @return \PgSql\Result|false
	 */
	public function __dbGetResult(): \PgSql\Result|false;

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
	 * @return \PgSql\Result|false
	 */
	public function __dbPrepare(string $name, string $query): \PgSql\Result|false;

	/**
	 * Undocumented function
	 *
	 * @param string $name
	 * @param array<mixed> $data
	 * @return \PgSql\Result|false
	 */
	public function __dbExecute(string $name, array $data): \PgSql\Result|false;

	/**
	 * Undocumented function
	 *
	 * @param  string $name
	 * @param  string $query
	 * @return bool
	 */
	public function __dbSendPrepare(string $name, string $query): bool;

	/**
	 * Undocumented function
	 *
	 * @param  string $name
	 * @param  array<mixed> $params
	 * @return bool
	 */
	public function __dbSendExecute(string $name, array $params): bool;

	/**
	 * Undocumented function
	 *
	 * @param \PgSql\Result|false $cursor
	 * @return int
	 */
	public function __dbNumRows(\PgSql\Result|false $cursor): int;

	/**
	 * Undocumented function
	 *
	 * @param \PgSql\Result|false $cursor
	 * @return int
	 */
	public function __dbNumFields(\PgSql\Result|false $cursor): int;

	/**
	 * Undocumented function
	 *
	 * @param \PgSql\Result|false $cursor
	 * @param int $i
	 * @return string|false
	 */
	public function __dbFieldName(\PgSql\Result|false $cursor, int $i): string|false;

	/**
	 * Undocumented function
	 *
	 * @param  \PgSql\Result|false $cursor
	 * @param  int $i
	 * @return string|false
	 */
	public function __dbFieldType(\PgSql\Result|false $cursor, int $i): string|false;

	/**
	 * Undocumented function
	 *
	 * @param \PgSql\Result|false $cursor
	 * @param int $result_type
	 * @return array<mixed>|bool
	 */
	public function __dbFetchArray(\PgSql\Result|false $cursor, int $result_type = PGSQL_BOTH);

	/**
	 * Undocumented function
	 *
	 * @param bool $assoc_type
	 * @return int
	 */
	public function __dbResultType(bool $assoc_type = true): int;

	/**
	 * Undocumented function
	 *
	 * @param \PgSql\Result|false $cursor
	 * @return array<mixed>|bool
	 */
	public function __dbFetchAll(\PgSql\Result|false $cursor): array|bool;

	/**
	 * Undocumented function
	 *
	 * @param \PgSql\Result|false $cursor
	 * @return int
	 */
	public function __dbAffectedRows(\PgSql\Result|false $cursor): int;

	/**
	 * Undocumented function
	 *
	 * @param string $query
	 * @param string|null $pk_name
	 * @return string|int|false
	 */
	public function __dbInsertId(string $query, ?string $pk_name): string|int|false;

	/**
	 * Undocumented function
	 *
	 * @param string $table
	 * @param string $schema
	 * @return string|bool
	 */
	public function __dbPrimaryKey(string $table, string $schema = ''): string|bool;

	/**
	 * Undocumented function
	 *
	 * @param string $db_host
	 * @param string $db_user
	 * @param string $db_pass
	 * @param string $db_name
	 * @param int $db_port
	 * @param string $db_ssl
	 * @return \PgSql\Connection|false
	 */
	public function __dbConnect(
		string $db_host,
		string $db_user,
		string $db_pass,
		string $db_name,
		int $db_port,
		string $db_ssl = 'allow'
	): \PgSql\Connection|false;

	/**
	 * Undocumented function
	 *
	 * @param \PgSql\Result|false $cursor
	 * @return string
	 */
	public function __dbPrintError(\PgSql\Result|false $cursor = false): string;

	/**
	 * Undocumented function
	 *
	 * @param string $table
	 * @param bool $extended
	 * @return array<mixed>|bool
	 */
	public function __dbMetaData(string $table, bool $extended = true): array|bool;

	/**
	 * Undocumented function
	 *
	 * @param string|int|float|bool $string
	 * @return string
	 */
	public function __dbEscapeString(string|int|float|bool $string): string;

	/**
	 * Undocumented function
	 *
	 * @param string|int|float|bool $string
	 * @return string
	 */
	public function __dbEscapeLiteral(string|int|float|bool $string): string;

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
	 * @param string $data
	 * @return string
	 */
	public function __dbEscapeBytea(string $data): string;

	/**
	 * Undocumented function
	 *
	 * @param  string $bytea
	 * @return string
	 */
	public function __dbUnescapeBytea(string $bytea): string;

	/**
	 * Undocumented function
	 *
	 * @return bool
	 */
	public function __dbConnectionBusy(): bool;

	/**
	 * Undocumented function
	 *
	 * @param int $timeout_seconds
	 * @return bool
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
	 * @param int $start
	 * @param int|null $end
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
	 * @return int
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
	 * @return int
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

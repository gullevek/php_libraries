<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

// turn on all error reporting
error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-db-query-placeholder';
ob_end_flush();

use CoreLibs\Debug\Support;
// use CoreLibs\DB\Support\ConvertPlaceholder;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$db->log->debug('START', '=============================>');

$PAGE_NAME = 'TEST CLASS: DB QUERY PLACEHOLDER';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "LOGFILE NAME: " . $db->log->getLogFile() . "<br>";
print "LOGFILE ID: " . $db->log->getLogFileId() . "<br>";
print "DBINFO: " . $db->dbInfo() . "<br>";
// DB client encoding
print "DB client encoding: " . $db->dbGetEncoding() . "<br>";
print "DB search path: " . $db->dbGetSchema() . "<br>";

$to_db_version = '15.2';
print "VERSION DB: " . $db->dbVersion() . "<br>";
print "SERVER ENCODING: " . $db->dbVersionInfo('server_encoding') . "<br>";
if (($dbh = $db->dbGetDbh()) instanceof \PgSql\Connection) {
	print "ALL OUTPUT [TEST]: <pre>" . print_r(pg_version($dbh), true) . "</pre><br>";
} else {
	print "NO DB HANDLER<br>";
}
// REGEX for placeholder count
print "Placeholder regex: <pre>" . CoreLibs\DB\Support\ConvertPlaceholder::REGEX_LOOKUP_PLACEHOLDERS . "</pre>";

// turn on debug replace for placeholders
$db->dbSetDebugReplacePlaceholder(true);

print "<b>TRUNCATE test_foo</b><br>";
$db->dbExec("TRUNCATE test_foo");

$uniqid = \CoreLibs\Create\Uids::uniqIdShort();
$binary_data = $db->dbEscapeBytea(file_get_contents('class_test.db.php') ?:  '');
$query_params = [
	$uniqid, // test
	true, // some_bool
	'STRING A', // string_a
	2, // number_a
	2.5, // numeric_a
	1, // smallint
	date('H:m:s'), // some_internval
	date('Y-m-d H:i:s'), // some_timestamp
	json_encode(['a' => 'string', 'b' => 1, 'c' => 1.5, 'f' => true, 'g' => ['a', 1, 1.5]]), // json_string
	null, // null_var
	'{"a", "b"}', // array_char_1
	'{1,2}', // array_int_1
	'{"(array Text A, 5, 8.8)","(array Text B, 10, 15.2)"}', // array_composite
	'("Text", 4, 6.3)', // composite_item
	$binary_data, // some_binary
	date('Y-m-d'), // some_date
	date('H:i:s'), // some_time
	'{"c", "d", "e"}', // array_char_2
	'{3,4,5}', // array_int_2
	12345667778818, // bigint
	1.56, // numbrer_real
	3.75, // number_double
	124.5, // numeric_3
	\CoreLibs\Create\Uids::uuidv4() // uuid_var
];

$query_insert = <<<SQL
INSERT INTO test_foo (
	-- row 1
	test, some_bool, string_a, number_a, numeric_a, smallint_a,
	-- row 2
	some_internval, some_timestamp, json_string, null_var,
	-- row 3
	array_char_1, array_int_1,
	-- row 4
	array_composite,
	-- row 5
	composite_item,
	-- row 6
	some_binary,
	-- row 7
	some_date, some_time,
	-- row 8
	array_char_2, array_int_2,
	-- row 9
	bigint_a, number_real, number_double, numeric_3,
	-- row 10
	uuid_var
) VALUES (
	-- row 1
	$1, $2, $3, $4, $5, $6,
	-- row 2
	$7, $8, $9, $10,
	-- row 3
	$11, $12,
	-- row 4
	$13,
	-- row 5
	$14,
	-- row 6
	$15,
	-- row 7
	$16, $17,
	-- row 8
	$18, $19,
	-- row 9
	$20, $21, $22, $23,
	-- row 10
	$24
)
RETURNING
	test_foo_id, number_serial, identity_always, identitiy_default, default_uuid,
	test, some_bool, string_a, number_a, numeric_a, smallint_a,
	some_internval, some_timestamp, json_string, null_var,
	array_char_1, array_int_1,
	array_composite,
	composite_item,
	some_binary,
	some_date,
	array_char_2, array_int_2,
	bigint_a, number_real, number_double, numeric_3,
	uuid_var
SQL;
$status = $db->dbExecParams($query_insert, $query_params);
echo "<b>*</b><br>";
echo "INSERT ALL COLUMN TYPES: "
	. Support::printToString($query_params) . " |<br>"
	. "QUERY: <pre>" . $db->dbGetQuery() . "</pre> |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " |<br>"
	. "RETURNING EXT: <pre>" . print_r($db->dbGetReturningExt(), true) . "</pre> |<br>"
	. "RETURNING RETURN: <pre>" . print_r($db->dbGetReturningArray(), true) . "<pre> |<br>"
	. "ERROR: " . $db->dbGetLastError(true) . "<br>";
echo "<hr>";

print "<b>ANY call</b><br>";
$query = <<<SQL
SELECT test
FROM test_foo
WHERE string_a = ANY($1)
SQL;
$query_value = '{'
	. join(',', ['STRING A'])
. '}';
while (is_array($res = $db->dbReturnParams($query, [$query_value]))) {
	print "Result: " . Support::prAr($res) . "<br>";
}

echo "<hr>";

echo "<b>CASE part</b><br>";
$query = <<<SQL
UPDATE
test_foo
SET
some_timestamp = NOW(),
	-- if not 1 set, else keep at one
	smallint_a = (CASE
			WHEN smallint_a <> 1 THEN $1
			ELSE 1::INT
	END)::INT
WHERE
	string_a = $2
SQL;
echo "QUERY: <pre>" . $query . "</pre>";
$res = $db->dbExecParams($query, [1, 'foobar']);
print "ERROR: " . $db->dbGetLastError(true) . "<br>";

echo "<hr>";

// test connectors: = , <> () for query detection

// convert placeholder tests
// ? -> $n
// :name -> $n

// other way around (just visual)
$test_queries = [
	'skip' => [
		'query' => <<<SQL
SELECT test, string_a, number_a
FROM test_foo
SQL,
		'params' => [],
		'direction' => 'pg',
	],
	'numbers' => [
		'query' => <<<SQL
SELECT test, string_a, number_a
FROM test_foo
WHERE
	foo = $1 AND bar = $1 AND foobar = $2
SQL,
		'params' => [\CoreLibs\Create\Uids::uniqIdShort(), 'string A-1', 1234],
		'direction' => 'pdo',
	],
	'a?' => [
		'query' => <<<SQL
INSERT INTO test_foo (
	test, string_a, number_a
) VALUES (
	?, ?, ?
)
SQL,
		'params' => [\CoreLibs\Create\Uids::uniqIdShort(), 'string A-1', 1234],
		'direction' => 'pg',
	],
	'b:' => [
		'query' => <<<SQL
INSERT INTO test_foo (
	test, string_a, number_a
) VALUES (
	:test, :string_a, :number_a
)
SQL,
		'params' => [
			':test' => \CoreLibs\Create\Uids::uniqIdShort(),
			':string_a' => 'string B-1',
			':number_a' => 5678
		],
		'direction' => 'pg',
	],
	'select, compare $' => [
		'query' => <<<SQL
		SELECT string_a
		FROM test_foo
		WHERE
			number_a >= $1 OR number_a <= $2 OR
			number_a > $3 OR number_a < $4
			OR number_a = $5 OR number_a <> $6
		SQL,
		'params' => [1, 2, 3, 4, 5, 6],
		'direction' => 'pg'
	],
];

$db->dbSetConvertPlaceholder(true);
foreach ($test_queries as $info => $data) {
	$query = $data['query'];
	$params = $data['params'];
	$direction = $data['direction'];
	// print "[$info] Convert: "
	// 	. Support::printAr(ConvertPlaceholder::convertPlaceholderInQuery($query, $params, $direction))
	// 	. "<br>";
	if ($db->dbCheckQueryForSelect($query)) {
		$row = $db->dbReturnRowParams($query, $params);
		print "<b>[$info]</b> SELECT: " . Support::prAr($row) . "<br>";
	} else {
		$db->dbExecParams($query, $params);
	}
	print "ERROR: " . $db->dbGetLastError(true) . "<br>";
	print "<b>[$info]</b> " . Support::printAr($db->dbGetPlaceholderConverted()) . "<br>";
	echo "<hr>";
}

echo "dbReturn read: <br>";
while (
	is_array($res = $db->dbReturnParams(
		<<<SQL
SELECT test, string_a, number_a
FROM test_foo
WHERE string_a = ?
SQL,
		['string A-1']
	))
) {
	print "<b>RES</b>: " . Support::prAr($res) . "<br>";
}
print "ERROR: " . $db->dbGetLastError(true) . "<br>";
echo "<hr>";

print "CursorExt: " . Support::prAr($db->dbGetCursorExt(<<<SQL
SELECT test, string_a, number_a
FROM test_foo
WHERE string_a = ?
SQL, ['string A-1']));
echo "<hr>";

// ERROR BELOW: missing params
$res = $db->dbReturnRowParams(<<<SQL
SELECT test, string_a, number_a
FROM test_foo
WHERE string_a = $1
SQL, []);
print "PL: " . Support::PrAr($db->dbGetPlaceholderConverted()) . "<br>";
print "ERROR: " . $db->dbGetLastError(true) . "<br>";
echo "<hr>";

// ERROR BELOW: LIKE cannot have placeholder
echo "dbReturn read LIKE: <br>";
while (
	is_array($res = $db->dbReturnParams(
		<<<SQL
SELECT test, string_a, number_a
FROM test_foo
WHERE string_a LIKE ?
SQL,
		['%A-1%']
	))
) {
	print "RES: " . Support::prAr($res) . "<br>";
}
print "ERROR: " . $db->dbGetLastError(true) . "<br>";

print "</body></html>";
$db->log->debug('DEBUGEND', '==================================== [END]');

// __END__

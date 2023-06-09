<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

// turn on all error reporting
error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-db-types';
ob_end_flush();

use CoreLibs\Debug\Support;
use CoreLibs\DB\Options\Convert;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$db->log->debug('START', '=============================>');

$PAGE_NAME = 'TEST CLASS: DB COLUMN TYPES';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
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

print "<b>TRUNCATE test_foo</b><br>";
$db->dbExec("TRUNCATE test_foo");

/* $q = <<<SQL
INSERT INTO test_foo (test, array_composite) VALUES ('C', '{"(a,1,1.5)","(b,2,2.5)"}')
SQL;
$db->dbExecParams($q);
pg_query($db->dbGetDbh(), $q);
$q = <<<SQL
INSERT INTO test_foo (test, array_composite) VALUES ($1, $2)
SQL;
// $db->dbExecParams($q, ['D', '{"(a b,1,1.5)","(c,3,4.5)"}']);
$db->dbExecParams($q, ['D', '{"(array Text A, 5, 8.8)","(array Text B, 10, 15.2)"}']);
 */
$uniqid = \CoreLibs\Create\Uids::uniqIdShort();
$binary_data = $db->dbEscapeBytea(file_get_contents('class_test.db.php') ?:  '');
$query_params = [
	$uniqid,
	true,
	'STRING A',
	2,
	2.5,
	1,
	date('H:m:s'),
	date('Y-m-d H:i:s'),
	json_encode(['a' => 'string', 'b' => 1, 'c' => 1.5, 'f' => true, 'g' => ['a', 1, 1.5]]),
	null,
	'{"a", "b"}',
	'{1,2}',
	'{"(array Text A, 5, 8.8)","(array Text B, 10, 15.2)"}',
	'("Text", 4, 6.3)',
	$binary_data
];

$query_insert = <<<SQL
INSERT INTO test_foo (
	test, some_bool, string_a, number_a, number_a_numeric, smallint_a,
	some_time, some_timestamp, json_string, null_var,
	array_char_1, array_int_1,
	array_composite,
	composite_item,
	some_binary
) VALUES (
	$1, $2, $3, $4, $5, $6,
	$7, $8, $9, $10,
	$11, $12,
	$13,
	$14,
	$15
)
RETURNING
	test_foo_id,
	test, some_bool, string_a, number_a, number_a_numeric, smallint_a,
	some_time, some_timestamp, json_string, null_var,
	array_char_1, array_int_1,
	array_composite,
	composite_item,
	some_binary
SQL;
$status = $db->dbExecParams($query_insert, $query_params);
echo "<b>*</b><br>";
echo "INSERT ALL COLUMN TYPES: "
	. Support::printToString($query_params) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " |<br>"
	. "RETURNING EXT: <pre>" . print_r($db->dbGetReturningExt(), true) . "</pre> |<br>"
	. "RETURNING RETURN: <pre>" . print_r($db->dbGetReturningArray(), true) . "<pre> |<br>"
	. "ERROR: " . $db->dbGetLastError(true) . "<br>";
echo "<hr>";

$query_select = <<<SQL
SELECT
	test_foo_id,
	test, some_bool, string_a, number_a, number_a_numeric, smallint_a,
	number_real, number_double, number_numeric_3, number_serial,
	some_time, some_timestamp, json_string, null_var,
	array_char_1, array_char_2, array_int_1, array_int_2, array_composite,
	composite_item, (composite_item).*,
	some_binary
FROM
	test_foo
WHERE
	test = $1;
SQL;
$res = $db->dbReturnRowParams($query_select, [$uniqid]);
// auto switch:
// ^int
// bool
// with flags:
// json(b) => array
// bytes => string? or resource?
// numeric => float (can have precision cut)
$pos = 0;
$name = '';
if (is_array($res)) {
	$cursor = $db->dbGetCursor();
	var_dump($res);
	print "Field Name/Types: <pre>" . print_r($db->dbGetFieldNameTypes(), true) . "</pre>";
	print "Get type for: 'number_a':" . $db->dbGetFieldType('number_a') . "<br>";
	print "Get type for: 0: " . $db->dbGetFieldType(0) . "<br>";
	print "Get name for: 0: " . $db->dbGetFieldName(0) . "<br>";
}

$db->dbSetConvertFlag(Convert::on);
$db->dbSetConvertFlag(Convert::json);
$db->dbSetConvertFlag(Convert::numeric);
$db->dbSetConvertFlag(Convert::bytea);
$res = $db->dbReturnRowParams($query_select, [$uniqid]);
if (is_array($res)) {
	var_dump($res);
}

print "</body></html>";

// __END__

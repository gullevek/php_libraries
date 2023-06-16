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
$LOG_FILE_ID = 'classTest-db-single';
ob_end_flush();

use CoreLibs\Debug\Support;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$db->log->debug('START', '=============================>');

$PAGE_NAME = 'TEST CLASS: DB SINGLE';
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

/**
 * Undocumented function
 *
 * @param  \CoreLibs\DB\IO $dbc
 * @return void
 */
function testDBS(\CoreLibs\DB\IO $dbc): void
{
	echo "Int call<br>";
	$dbc->dbReturnRow("SELECT test FROM test_foo LIMIT 1");
}

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
	date('Y-m-d H:m:s'),
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
SQL;
$status = $db->dbExecParams($query_insert, $query_params);
$query_select = <<<SQL
SELECT
	test_foo_id,
	test, some_bool, string_a, number_a, number_a_numeric, smallint_a,
	number_real, number_double, number_serial,
	some_time, some_timestamp, json_string, null_var,
	array_char_1, array_char_2, array_int_1, array_int_2, array_composite,
	composite_item, (composite_item).*
	some_binary
FROM
	test_foo
WHERE
	test = $1;
SQL;
$res = $db->dbReturnRowParams($query_select, [$uniqid]);
if (is_array($res)) {
	var_dump($res);
}

testDBS($db);

print "</body></html>";

// __END__

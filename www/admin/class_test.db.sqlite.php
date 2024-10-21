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
define('DATABASE', 'sqlite' . DIRECTORY_SEPARATOR);
// sample config
require 'config.php';
// define log file id
$LOG_FILE_ID = 'classTest-db';
ob_end_flush();

$sql_file = BASE . MEDIA . DATABASE . "class_test.db.sqlite.sq3";

use CoreLibs\DB\SqLite;
use CoreLibs\Debug\Support;
use CoreLibs\Convert\SetVarType;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\SqLite($log, "sqlite:" . $sql_file);
$db->log->debug('START', '=============================>');

$PAGE_NAME = 'TEST CLASS: DB: SqLite';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

print "<hr>";

echo "Create Tables on demand<br>";

$query = <<<SQL
CREATE TABLE IF NOT EXISTS test (
	test_id INTEGER PRIMARY KEY,
	c_text TEXT,
	c_integer INTEGER,
	c_integer_default INTEGER DEFAULT -1,
	c_bool BOOLEAN,
	c_datetime TEXT,
	c_datetime_microseconds TEXT,
	c_datetime_default TEXT DEFAULT CURRENT_TIMESTAMP,
	c_date TEXT,
	c_julian REAL,
	c_unixtime DATETIME,
	c_unixtime_alt DATETIME,
	c_numeric NUMERIC,
	c_real REAL,
	c_blob
)
SQL;
$db->dbExec($query);
// **********************
$query = <<<SQL
CREATE TABLE IF NOT EXISTS test_no_pk (
	c_text TEXT,
	c_integer INTEGER
)
SQL;
$db->dbExec($query);

print "<hr>";

$table = 'test';
echo "Table info for: " . $table . "<br>";

if (($table_info = $db->dbShowTableMetaData($table)) === false) {
	print "Read problem for: $table<br>";
} else {
	print "TABLE INFO: <pre>" . print_r($table_info, true) . "</pre><br>";
}

print "<hr>";

echo "Insert into 'test'<br>";

$query = <<<SQL
INSERT INTO test (
	c_text, c_integer, c_bool,
	c_datetime, c_datetime_microseconds, c_date,
	c_julian, c_unixtime, c_unixtime_alt,
	c_numeric, c_real, c_blob
) VALUES (
	?, ?, ?,
	?, ?, ?,
	julianday(?), ?, unixepoch(?),
	?, ?, ?
)
SQL;
$db->dbExecParams($query, [
	'test', rand(1, 100), true,
	date('Y-m-d H:i:s'), date_format(date_create("now"), 'Y-m-d H:i:s.u'), date('Y-m-d'),
	// julianday pass through
	date('Y-m-d H:i:s'),
	// use "U" if no unixepoch in query
	date('U'), date('Y-m-d H:i:s'),
	1.5, 10.5, 'Anything'
]);

print "<hr>";

echo "Insert into 'test_no_pk'<br>";

$query = <<<SQL
INSERT INTO test_no_pk (
	c_text, c_integer
) VALUES (
	?, ?
)
SQL;
$db->dbExecParams($query, ['test no pk', rand(100, 200)]);

print "<hr>";

$query = <<<SQL
SELECT test_id, c_text, c_integer, c_integer_default, c_datetime_default
FROM test
SQL;
while (is_array($row = $db->dbReturnArray($query))) {
	print "ROW: PK(test_id): " . $row["test_id"]
		. ", Text: " . $row["c_text"] . ", Int: " . $row["c_integer"]
		. ", Int Default: " . $row["c_integer_default"]
		. ", Date Default: " . $row["c_datetime_default"]
		. "<br>";
}

echo "<hr>";

$query = <<<SQL
SELECT rowid, c_text, c_integer
FROM test_no_pk
SQL;

while (is_array($row = $db->dbReturnArray($query))) {
	print "ROW[CURSOR]: PK(rowid): " . $row["rowid"]
		. ", Text: " . $row["c_text"] . ", Int: " . $row["c_integer"]
		. "<br>";
}

print "</body></html>";

// __END__

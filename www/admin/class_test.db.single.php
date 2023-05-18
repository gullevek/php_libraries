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
// override ECHO ALL FALSE
$ECHO_ALL = true;
// define log file id
$LOG_FILE_ID = 'classTest-db-single';
ob_end_flush();

use CoreLibs\Debug\Support as DgS;
use CoreLibs\DB\IO as DbIo;
use CoreLibs\Debug\Support;
use CoreLibs\Convert\SetVarType;

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL ?? true,
	'echo_all' => $ECHO_ALL,
	'print_all' => $PRINT_ALL ?? true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$db->log->debug('START', '=============================>');

$PAGE_NAME = 'TEST CLASS: DB SINGLE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><a href="class_test.db.dbReturn.php">Class Test DB dbReturn</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "LOGFILE NAME: " . $db->log->getSetting('log_file_name') . "<br>";
print "LOGFILE ID: " . $db->log->getSetting('log_file_id') . "<br>";
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

// params > 10 for debug
// error catcher
$query_insert = <<<SQL
INSERT INTO many_columns (
	col_01_int,
	col_01, col_02, col_03, col_04, col_05, col_06, col_07, col_08, col_09,
    col_10, col_11, col_12, col_02_int
) VALUES (
	1,
	$1, $2, $3, $4, $5, $6, $7, $8, $9,
	$10, $11, $12, $13
)
RETURNING
	many_columns_id,
	col_01_int,
	col_01, col_02, col_03, col_04, col_05, col_06, col_07, col_08, col_09,
    col_10, col_11, col_12, col_02_int
SQL;
$query_params = [
	'col 1', 'col 2', 'col 3', 'col 4', 'col 5', 'col 6', 'col 7', 'col 8',
	'col 9', 'col 10', 'col 11', 'col 12', null
];
$status = $db->dbExecParams($query_insert, $query_params);
echo "<b>*</b><br>";
echo "EOM STRING WITH MORE THAN 10 PARAMETERS: "
	. Support::printToString($query_params) . " |<br>"
	. " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	// . "RETURNING EXT: " . Support::printToString($db->dbGetReturningExt()) . " | "
	. "RETURNING RETURN: " . Support::printToString($db->dbGetReturningArray())
	. "ERROR: " . $db->dbGetLastError(true) . "<br>";
echo "<hr>";

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__

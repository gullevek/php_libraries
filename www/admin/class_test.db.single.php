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
$LOG_FILE_ID = 'classTest-db-query-placeholders';
ob_end_flush();

// use CoreLibs\Debug\Support;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$db->log->debug('START', '=============================>');

$PAGE_NAME = 'TEST CLASS: DB QUERY PLACEHOLDERS';
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

testDBS($db);

print "</body></html>";

// __END__

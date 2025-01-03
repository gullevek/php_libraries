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
$LOG_FILE_ID = 'classTest-db';
ob_end_flush();

use CoreLibs\Debug\Support;
use CoreLibs\Debug\RunningTime;
use CoreLibs\Convert\SetVarType;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$db->log->debug('START', '=============================>');

$PAGE_NAME = 'TEST CLASS: DB dbReturn';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><a href="class_test.db.php">Class Test DB</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "LOGFILE NAME: " . $db->log->getLogFile() . "<br>";
print "LOGFILE ID: " . $db->log->getLogFileId() . "<br>";
print "DBINFO: " . $db->dbInfo() . "<br>";

// DB client encoding
print "DB client encoding: " . $db->dbGetEncoding() . "<br>";
print "DB search path: " . $db->dbGetSchema() . "<br>";

// SELECT read tests with dbReturn and cache values
print "<br>";
print "<b>dbReturn CACHE tests</b><br>";
// DATA has two rows for reading
// delete and repare base data
$db->dbExec("DELETE FROM test_db_return");
$db->dbExec("INSERT INTO test_db_return (uid, data) VALUES ('A1', 'Test A'), ('B1', 'Test B')");
// read query to use
$q_db_ret = <<<SQL
SELECT * FROM test_db_return ORDER BY uid
SQL;

RunningTime::hrRunningTime();

$cache_flag = '[DEFAULT] NO_CACHE (0)';
print "dbReturn '" . $cache_flag . "'/Default: " . $q_db_ret . "<br>";
// Do twice
for ($i = 1; $i <= 6; $i++) {
	$res = $db->dbReturn($q_db_ret);
	print $i . ") " . $cache_flag . ": "
		. "res: " . (is_bool($res) ?
			"<b>Bool:</b> " . Support::prBl($res) :
			"Array: Yes"
		) . ", "
		. "cursor_ext: <pre>" . Support::printAr(
			SetVarType::setArray($db->dbGetCursorExt($q_db_ret))
		) . "</pre>";
	print "Run time: " .  RunningTime::hrRunningTime() . "<br>";
}
print "<hr>";

$cache_flag = 'USE_CACHE (0)';
print "dbReturn '" . $cache_flag . "'/Default: " . $q_db_ret . "<br>";
// SINGLE read on multi row return
// Do twice
for ($i = 1; $i <= 6; $i++) {
	$res = $db->dbReturn($q_db_ret, $db::USE_CACHE);
	print $i . ") " . $cache_flag . ": "
		. "res: " . (is_bool($res) ?
			"<b>Bool:</b> " . Support::prBl($res) :
			"Array: Yes"
		) . ", "
		. "cursor_ext: <pre>" . Support::printAr(
			SetVarType::setArray($db->dbGetCursorExt($q_db_ret))
		) . "</pre>";
	print "Run time: " .  RunningTime::hrRunningTime() . "<br>";
}
// reset all read data
$db->dbCacheReset($q_db_ret);
echo "<hr>";
$cache_flag = 'READ_NEW (1)';
print "dbReturn '" . $cache_flag . "': " . $q_db_ret . "<br>";
// NO CACHE
for ($i = 1; $i <= 6; $i++) {
	$res = $db->dbReturn($q_db_ret, $db::READ_NEW);
	print $i . ") " . $cache_flag . ": "
		. "res: " . (is_bool($res) ?
			"<b>Bool:</b> " . Support::prBl($res) :
			"Array: Yes"
		) . ", "
		. "cursor_ext: <pre>" . Support::printAr(
			SetVarType::setArray($db->dbGetCursorExt($q_db_ret))
		) . "</pre>";
	print "Run time: " .  RunningTime::hrRunningTime() . "<br>";
}
// reset all read data
$db->dbCacheReset($q_db_ret);
echo "<hr>";
$cache_flag = 'CLEAR_CACHE (2)';
print "dbReturn '" . $cache_flag . "': " . $q_db_ret . "<br>";
// NO CACHE
for ($i = 1; $i <= 6; $i++) {
	$res = $db->dbReturn($q_db_ret, $db::CLEAR_CACHE);
	print $i . ") " . $cache_flag . ": "
		. "res: " . (is_bool($res) ?
			"<b>Bool:</b> " . Support::prBl($res) :
			"Array: Yes"
		) . ", "
		. "cursor_ext: <pre>" . Support::printAr(
			SetVarType::setArray($db->dbGetCursorExt($q_db_ret))
		) . "</pre>";
	print "Run time: " .  RunningTime::hrRunningTime() . "<br>";
}
// reset all read data
$db->dbCacheReset($q_db_ret);
echo "<hr>";
$cache_flag = 'NO_CACHE (3)';
print "dbReturn '" . $cache_flag . "': " . $q_db_ret . "<br>";
// NO CACHE
for ($i = 1; $i <= 6; $i++) {
	$res = $db->dbReturn($q_db_ret, $db::NO_CACHE);
	print $i . ") " . $cache_flag . ": "
		. "res: " . (is_bool($res) ?
			"<b>Bool:</b> " . Support::prBl($res) :
			"Array: Yes"
		) . ", "
		. "cursor_ext: <pre>" . Support::printAr(
			SetVarType::setArray($db->dbGetCursorExt($q_db_ret))
		) . "</pre>";
	print "Run time: " .  RunningTime::hrRunningTime() . "<br>";
}
// reset all data
$db->dbCacheReset($q_db_ret);
print "<br>";
print "Overall Run time: " .  RunningTime::hrRunningTimeFromStart() . "<br>";

print "<br>";
print "PARAM TEST RUN<br>";
// PARAM
$q_db_ret = <<<SQL
SELECT * FROM test_db_return WHERE uid = $1
SQL;

while (is_array($res = $db->dbReturnParams($q_db_ret, ['A1'], $db::NO_CACHE, true))) {
	print "ROW: " . Support::printAr($res) . "<br>";
}

// __END__

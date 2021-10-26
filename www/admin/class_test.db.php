<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

$DEBUG_ALL_OVERRIDE = false; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = true;
$PRINT_ALL = true;
$DB_DEBUG = true;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// override ECHO ALL FALSE
$ECHO_ALL = true;
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-db';
ob_end_flush();

use CoreLibs\Debug\Support as DgS;

$db = $basic = new CoreLibs\Admin\Backend(DB_CONFIG);

// NEXT STEP
// $basic = new CoreLibs\Basic();
// change __construct
// add object $logger
// add $this->log = $logger;
// $db = new CoreLibs\DB\IO(DB_CONFIG, $basic->log);

print "<html><head><title>TEST CLASS: DB</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

print "DBINFO: " . $db->dbInfo() . "<br>";
echo "DB_CONFIG_SET constant: <pre>" . print_r(DB_CONFIG, true) . "</pre><br>";

// DB client encoding
print "DB Client encoding: " . $db->dbGetEncoding() . "<br>";

while (is_array($res = $db->dbReturn("SELECT * FROM max_test", 0, true))) {
	print "TIME: " . $res['time'] . "<br>";
}
print "CACHED DATA: <pre>" . print_r($db->cursor_ext, true) . "</pre><br>";
while (is_array($res = $db->dbReturn("SELECT * FROM max_test"))) {
	print "[CACHED] TIME: " . $res['time'] . "<br>";
}
// alternate check for valid data
// while (($res = $db->dbReturn("SELECT * FROM max_test")) !== false) {
// 	print "[CACHED] TIME: " . $res['time'] . "<br>";
// }

print "<pre>";
$status = $db->dbExec("INSERT INTO foo (test) VALUES ('FOO TEST " . time() . "') RETURNING test");
print "DIRECT INSERT STATUS: $status | "
	. "PRIMARY KEY: " . $db->dbGetInsertPK() . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

// should throw deprecated error
// $db->getReturningExt();
print "DIRECT INSERT PREVIOUS INSERTED: "
	. print_r($db->dbReturnRow("SELECT foo_id, test FROM foo WHERE foo_id = " . $db->dbGetInsertPK()), true) . "<br>";
$db->dbPrepare("ins_foo", "INSERT INTO foo (test) VALUES ($1)");
$status = $db->dbExecute("ins_foo", array('BAR TEST ' . time()));
print "PREPARE INSERT STATUS: $status | "
	. "PRIMARY KEY: " . $db->dbGetInsertPK() . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING RETURN: " . print_r($db->dbGetReturningArray(), true) . "<br>";

print "PREPARE INSERT PREVIOUS INSERTED: "
	. print_r($db->dbReturnRow("SELECT foo_id, test FROM foo WHERE foo_id = " . $db->dbGetInsertPK()), true) . "<br>";
// returning test with multiple entries
// $status = $db->db_exec(
// 	"INSERT INTO foo (test) VALUES "
// 	. "('BAR 1 " . time() . "'), "
// 	. "('BAR 2 " . time() . "'), "
// 	. "('BAR 3 " . time() . "') "
// 	. "RETURNING foo_id"
// );
$status = $db->dbExec(
	"INSERT INTO foo (test) VALUES "
	. "('BAR 1 " . time() . "'), "
	. "('BAR 2 " . time() . "'), "
	. "('BAR 3 " . time() . "') "
	. "RETURNING foo_id, test"
);
print "DIRECT MULTIPLE INSERT STATUS: $status | "
	. "PRIMARY KEYS: " . print_r($db->dbGetInsertPK(), true) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

// no returning, but not needed ;
$status = $db->dbExec("INSERT INTO foo (test) VALUES ('FOO; TEST " . time() . "');");
print "DIRECT INSERT STATUS: $status | "
	. "PRIMARY KEY: " . $db->dbGetInsertPK() . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

// UPDATE WITH RETURNING
$status = $db->dbExec("UPDATE foo SET test = 'SOMETHING DIFFERENT' WHERE foo_id = 3688452 RETURNING test");
print "UPDATE STATUS: $status | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";
print "</pre>";

// REEAD PREPARE
if (
	$db->dbPrepare(
		'sel_foo',
		"SELECT foo_id, test, some_bool, string_a, number_a, number_a_numeric, some_time "
		. "FROM foo ORDER BY foo_id DESC LIMIT 5"
	) === false
) {
	print "Error in sel_foo prepare<br>";
} else {
	$max_rows = 6;
	// do not run this in dbFetchArray directly as
	// dbFetchArray(dbExecute(...))
	// this will end in an endless loop
	$cursor = $db->dbExecute('sel_foo', []);
	$i = 1;
	while (($res = $db->dbFetchArray($cursor, true)) !== false) {
		print "DB PREP EXEC FETCH ARR: " . $i . ": <pre>" . print_r($res, true) . "</pre><br>";
		$i++;
	}
}


# db write class test
$table = 'foo';
print "TABLE META DATA: " . DgS::printAr($db->dbShowTableMetaData($table)) . "<br>";
$primary_key = ''; # unset
$db_write_table = array('test', 'string_a', 'number_a', 'some_bool');
//	$db_write_table = array('test');
$object_fields_not_touch = array();
$object_fields_not_update = array();
$data = array('test' => 'BOOL TEST SOMETHING ' . time(), 'string_a' => 'SOME TEXT', 'number_a' => 5);
$primary_key = $db->dbWriteDataExt(
	$db_write_table,
	$primary_key,
	$table,
	$object_fields_not_touch,
	$object_fields_not_update,
	$data
);
print "Wrote to DB tabel $table and got primary key $primary_key<br>";
$data = array('test' => 'BOOL TEST ON ' . time(), 'string_a' => '', 'number_a' => 0, 'some_bool' => 1);
$primary_key = $db->dbWriteDataExt(
	$db_write_table,
	$primary_key,
	$table,
	$object_fields_not_touch,
	$object_fields_not_update,
	$data
);
print "Wrote to DB tabel $table and got primary key $primary_key<br>";
$data = array('test' => 'BOOL TEST OFF ' . time(), 'string_a' => null, 'number_a' => null, 'some_bool' => 0);
$primary_key = $db->dbWriteDataExt(
	$db_write_table,
	$primary_key,
	$table,
	$object_fields_not_touch,
	$object_fields_not_update,
	$data
);
print "Wrote to DB tabel $table and got primary key $primary_key<br>";
$data = array('test' => 'BOOL TEST UNSET ' . time());
$primary_key = $db->dbWriteDataExt(
	$db_write_table,
	$primary_key,
	$table,
	$object_fields_not_touch,
	$object_fields_not_update,
	$data
);
print "Wrote to DB tabel $table and got primary key $primary_key<br>";

// return Array Test
$query = "SELECT type, sdate, integer FROM foobar";
$data = $db->dbReturnArray($query, true);
print "Full foobar list: <br><pre>" . print_r($data, true) . "</pre><br>";

# async test queries
/*	$db->dbExecAsync("SELECT test FROM foo, (SELECT pg_sleep(10)) as sub WHERE foo_id IN (27, 50, 67, 44, 10)");
echo "WAITING FOR ASYNC: ";
$chars = array('|', '/', '-', '\\');
while (($ret = $db->dbCheckAsync()) === true)
{
	if ((list($_, $char) = each($chars)) === FALSE)
	{
		reset($chars);
		list($_, $char) = each($chars);
	}
	print $char;
	sleep(1);
	flush();
}
print "<br>END STATUS: " . $ret . "<br>";
//	while ($res = $db->dbFetchArray($ret))
while ($res = $db->dbFetchArray())
{
	echo "RES: " . $res['test'] . "<br>";
}
# test async insert
$db->dbExecAsync("INSERT INTO foo (Test) VALUES ('ASYNC TEST " . time() . "')");
echo "WAITING FOR ASYNC INSERT: ";
while (($ret = $db->dbCheckAsync()) === true)
{
	print " . ";
	sleep(1);
	flush();
}
print "<br>END STATUS: " . $ret . " | PK: " . $db->insert_id . "<br>";
print "ASYNC PREVIOUS INSERTED: "
	. print_r($db->dbReturnRow("SELECT foo_id, test FROM foo WHERE foo_id = " . $db->insert_id), true) . "<br>"; */

$to_db_version = '9.1.9';
print "VERSION DB: " . $db->dbVersion() . "<br>";
print "DB Version smaller $to_db_version: " . $db->dbCompareVersion('<' . $to_db_version) . "<br>";
print "DB Version smaller than $to_db_version: " . $db->dbCompareVersion('<=' . $to_db_version) . "<br>";
print "DB Version equal $to_db_version: " . $db->dbCompareVersion('=' . $to_db_version) . "<br>";
print "DB Version bigger than $to_db_version: " . $db->dbCompareVersion('>=' . $to_db_version) . "<br>";
print "DB Version bigger $to_db_version: " . $db->dbCompareVersion('>' . $to_db_version) . "<br>";

/*	$q = "SELECT FOO FRO BAR";
// $q = "Select * from foo";
$foo = $db->dbExecAsync($q);
print "[ERR] Query: " . $q . "<br>";
print "[ERR] RESOURCE: $foo<br>";
while (($ret = $db->dbCheckAsync()) === true)
{
	print "[ERR]: $ret<br>";
	sleep(5);
} */

// search path check
$q = "SHOW search_path";
$cursor = $db->dbExec($q);
$data = $db->dbFetchArray($cursor)['search_path'];
print "RETURN DATA FOR search_path: " . $data . "<br>";
//	print "RETURN DATA FOR search_path: " . DgS::printAr($data) . "<br>";
// insert something into test.schema_test and see if we get the PK back
$status = $db->dbExec(
	"INSERT INTO test.schema_test (contents, id) VALUES "
	. "('TIME: " . time() . "', " . rand(1, 10) . ")"
);
print "OTHER SCHEMA INSERT STATUS: "
	. $status . " | PK NAME: " . $db->pk_name . ", PRIMARY KEY: " . $db->insert_id . "<br>";

print "<b>NULL TEST DB READ</b><br>";
$q = "SELECT uid, null_varchar, null_int FROM test_null_data WHERE uid = 'A'";
$res = $db->dbReturnRow($q);
var_dump($res);
print "RES: " . DgS::printAr($res) . "<br>";
print "ISSET: " . isset($res['null_varchar']) . "<br>";
print "EMPTY: " . empty($res['null_varchar']) . "<br>";

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__

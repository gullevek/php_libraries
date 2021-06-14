<?php declare(strict_types=1);
/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;

if ($DEBUG_ALL) {
	error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
}

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-db';
ob_end_flush();

use CoreLibs\Debug\Support as DgS;

$basic = new CoreLibs\Admin\Backend(DB_CONFIG);

print "<html><head><title>TEST CLASS: DB</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

print "DBINFO: ".$basic->dbInfo()."<br>";
echo "DB_CONFIG_SET constant: <pre>".print_r(DB_CONFIG, true)."</pre><br>";

// DB client encoding
print "DB Client encoding: ".$basic->dbGetEncoding()."<br>";

while ($res = $basic->dbReturn("SELECT * FROM max_test", 0, true)) {
	print "TIME: ".$res['time']."<br>";
}
print "CACHED DATA: <pre>".print_r($basic->cursor_ext, true)."</pre><br>";
while ($res = $basic->dbReturn("SELECT * FROM max_test")) {
	print "[CACHED] TIME: ".$res['time']."<br>";
}

print "<pre>";
$status = $basic->dbExec("INSERT INTO foo (test) VALUES ('FOO TEST ".time()."') RETURNING test");
print "DIRECT INSERT STATUS: $status | "
	."PRIMARY KEY: ".$basic->dbGetInsertPK()." | "
	."RETURNING EXT: ".print_r($basic->dbGetReturningExt(), true)." | "
	."RETURNING ARRAY: ".print_r($basic->dbGetReturningArray(), true)."<br>";

// should throw deprecated error
// $basic->getReturningExt();
print "DIRECT INSERT PREVIOUS INSERTED: ".print_r($basic->dbReturnRow("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->dbGetInsertPK()), true)."<br>";
$basic->dbPrepare("ins_foo", "INSERT INTO foo (test) VALUES ($1)");
$status = $basic->dbExecute("ins_foo", array('BAR TEST '.time()));
print "PREPARE INSERT STATUS: $status | "
	."PRIMARY KEY: ".$basic->dbGetInsertPK()." | "
	."RETURNING EXT: ".print_r($basic->dbGetReturningExt(), true)." | "
	."RETURNING RETURN: ".print_r($basic->dbGetReturningArray(), true)."<br>";

print "PREPARE INSERT PREVIOUS INSERTED: ".print_r($basic->dbReturnRow("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->dbGetInsertPK()), true)."<br>";
// returning test with multiple entries
//	$status = $basic->db_exec("INSERT INTO foo (test) values ('BAR 1 ".time()."'), ('BAR 2 ".time()."'), ('BAR 3 ".time()."') RETURNING foo_id");
$status = $basic->dbExec("INSERT INTO foo (test) values ('BAR 1 ".time()."'), ('BAR 2 ".time()."'), ('BAR 3 ".time()."') RETURNING foo_id, test");
print "DIRECT MULTIPLE INSERT STATUS: $status | "
	."PRIMARY KEYS: ".print_r($basic->dbGetInsertPK(), true)." | "
	."RETURNING EXT: ".print_r($basic->dbGetReturningExt(), true)." | "
	."RETURNING ARRAY: ".print_r($basic->dbGetReturningArray(), true)."<br>";

// no returning, but not needed ;
$status = $basic->dbExec("INSERT INTO foo (test) VALUES ('FOO; TEST ".time()."');");
print "DIRECT INSERT STATUS: $status | "
	."PRIMARY KEY: ".$basic->dbGetInsertPK()." | "
	."RETURNING EXT: ".print_r($basic->dbGetReturningExt(), true)." | "
	."RETURNING ARRAY: ".print_r($basic->dbGetReturningArray(), true)."<br>";

// UPDATE WITH RETURNING
$status = $basic->dbExec("UPDATE foo SET test = 'SOMETHING DIFFERENT' WHERE foo_id = 3688452 RETURNING test");
print "UPDATE STATUS: $status | "
	."RETURNING EXT: ".print_r($basic->dbGetReturningExt(), true)." | "
	."RETURNING ARRAY: ".print_r($basic->dbGetReturningArray(), true)."<br>";
print "</pre>";

// REEAD PREPARE
if ($basic->dbPrepare('sel_foo', "SELECT foo_id, test, some_bool, string_a, number_a, number_a_numeric, some_time FROM foo ORDER BY foo_id DESC LIMIT 5") === false) {
	print "Error in sel_foo prepare<br>";
} else {
	$max_rows = 6;
	// do not run this in dbFetchArray directly as
	// dbFetchArray(dbExecute(...))
	// this will end in an endless loop
	$cursor = $basic->dbExecute('sel_foo', []);
	$i = 1;
	while (($res = $basic->dbFetchArray($cursor, true)) !== false) {
		print "DB PREP EXEC FETCH ARR: ".$i.": <pre>".print_r($res, true)."</pre><br>";
		$i ++;
	}
}


# db write class test
$table = 'foo';
print "TABLE META DATA: ".DgS::printAr($basic->dbShowTableMetaData($table))."<br>";
$primary_key = ''; # unset
$db_write_table = array('test', 'string_a', 'number_a', 'some_bool');
//	$db_write_table = array('test');
$object_fields_not_touch = array();
$object_fields_not_update = array();
$data = array('test' => 'BOOL TEST SOMETHING '.time(), 'string_a' => 'SOME TEXT', 'number_a' => 5);
$primary_key = $basic->dbWriteDataExt($db_write_table, $primary_key, $table, $object_fields_not_touch, $object_fields_not_update, $data);
print "Wrote to DB tabel $table and got primary key $primary_key<br>";
$data = array('test' => 'BOOL TEST ON '.time(), 'string_a' => '', 'number_a' => 0, 'some_bool' => 1);
$primary_key = $basic->dbWriteDataExt($db_write_table, $primary_key, $table, $object_fields_not_touch, $object_fields_not_update, $data);
print "Wrote to DB tabel $table and got primary key $primary_key<br>";
$data = array('test' => 'BOOL TEST OFF '.time(), 'string_a' => null, 'number_a' => null, 'some_bool' => 0);
$primary_key = $basic->dbWriteDataExt($db_write_table, $primary_key, $table, $object_fields_not_touch, $object_fields_not_update, $data);
print "Wrote to DB tabel $table and got primary key $primary_key<br>";
$data = array('test' => 'BOOL TEST UNSET '.time());
$primary_key = $basic->dbWriteDataExt($db_write_table, $primary_key, $table, $object_fields_not_touch, $object_fields_not_update, $data);
print "Wrote to DB tabel $table and got primary key $primary_key<br>";

// return Array Test
$query = "SELECT type, sdate, integer FROM foobar";
$data = $basic->dbReturnArray($query, true);
print "Full foobar list: <br><pre>".print_r($data, true)."</pre><br>";

# async test queries
/*	$basic->dbExecAsync("SELECT test FROM foo, (SELECT pg_sleep(10)) as sub WHERE foo_id IN (27, 50, 67, 44, 10)");
echo "WAITING FOR ASYNC: ";
$chars = array('|', '/', '-', '\\');
while (($ret = $basic->dbCheckAsync()) === true)
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
print "<br>END STATUS: ".$ret."<br>";
//	while ($res = $basic->dbFetchArray($ret))
while ($res = $basic->dbFetchArray())
{
	echo "RES: ".$res['test']."<br>";
}
# test async insert
$basic->dbExecAsync("INSERT INTO foo (Test) VALUES ('ASYNC TEST ".time()."')");
echo "WAITING FOR ASYNC INSERT: ";
while (($ret = $basic->dbCheckAsync()) === true)
{
	print ".";
	sleep(1);
	flush();
}
print "<br>END STATUS: ".$ret." | PK: ".$basic->insert_id."<br>";
print "ASYNC PREVIOUS INSERTED: ".print_r($basic->dbReturnRow("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->insert_id), true)."<br>"; */

$to_db_version = '9.1.9';
print "VERSION DB: ".$basic->dbVersion()."<br>";
print "DB Version smaller $to_db_version: ".$basic->dbCompareVersion('<'.$to_db_version)."<br>";
print "DB Version smaller than $to_db_version: ".$basic->dbCompareVersion('<='.$to_db_version)."<br>";
print "DB Version equal $to_db_version: ".$basic->dbCompareVersion('='.$to_db_version)."<br>";
print "DB Version bigger than $to_db_version: ".$basic->dbCompareVersion('>='.$to_db_version)."<br>";
print "DB Version bigger $to_db_version: ".$basic->dbCompareVersion('>'.$to_db_version)."<br>";

/*	$q = "SELECT FOO FRO BAR";
// $q = "Select * from foo";
$foo = $basic->dbExecAsync($q);
print "[ERR] Query: ".$q."<br>";
print "[ERR] RESOURCE: $foo<br>";
while (($ret = $basic->dbCheckAsync()) === true)
{
	print "[ERR]: $ret<br>";
	sleep(5);
} */

// search path check
$q = "SHOW search_path";
$cursor = $basic->dbExec($q);
$data = $basic->dbFetchArray($cursor)['search_path'];
print "RETURN DATA FOR search_path: ".$data."<br>";
//	print "RETURN DATA FOR search_path: ".DgS::printAr($data)."<br>";
// insert something into test.schema_test and see if we get the PK back
$status = $basic->dbExec("INSERT INTO test.schema_test (contents, id) VALUES ('TIME: ".time()."', ".rand(1, 10).")");
print "OTHER SCHEMA INSERT STATUS: ".$status." | PK NAME: ".$basic->pk_name.", PRIMARY KEY: ".$basic->insert_id."<br>";

print "<b>NULL TEST DB READ</b><br>";
$q = "SELECT uid, null_varchar, null_int FROM test_null_data WHERE uid = 'A'";
$res = $basic->dbReturnRow($q);
var_dump($res);
print "RES: ".DgS::printAr($res)."<br>";
print "ISSET: ".isset($res['null_varchar'])."<br>";
print "EMPTY: ".empty($res['null_varchar'])."<br>";

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__

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

use CoreLibs\Debug\Support as DgS;
use CoreLibs\DB\IO as DbIo;
use CoreLibs\Debug\Support;
use CoreLibs\Convert\SetVarType;

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
// db connection and attach logger
$db = new CoreLibs\DB\IO(DB_CONFIG, $log);
$db->log->debug('START', '=============================>');

$PAGE_NAME = 'TEST CLASS: DB';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><a href="class_test.db.type.php">Class Test DB row type convert to PHP type</a></div>';
print '<div><a href="class_test.db.query-placeholder.php">Class Test DB Query Placeholder convert</a></div>';
print '<div><a href="class_test.db.dbReturn.php">Class Test DB dbReturn</a></div>';
print '<div><a href="class_test.db.single.php">Class Test DB Single Query tests</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "LOGFILE NAME: " . $db->log->getLogFile() . "<br>";
print "LOGFILE ID: " . $db->log->getLogFileId() . "<br>";
print "DBINFO: " . $db->dbInfo() . "<br>";
echo "DB_CONFIG_SET constant: <pre>" . print_r(DB_CONFIG, true) . "</pre><br>";

// DB client encoding
print "DB client encoding: " . $db->dbGetEncoding() . "<br>";
print "DB search path: " . $db->dbGetSchema() . "<br>";

$to_db_version = '15.2';
print "VERSION DB: " . $db->dbVersion() . "<br>";
print "VERSION LONG DB: " . $db->dbVersionInfo('server', false) . "<br>";
print "VERSION NUMERIC DB: " . $db->dbVersionNumeric() . "<br>";
print "SERVER ENCODING: " . $db->dbVersionInfo('server_encoding') . "<br>";
print "ALL PG VERSION PARAMETERS: <pre>" . print_r($db->dbVersionInfoParameters(), true) . "</pre><br>";
if (($dbh = $db->dbGetDbh()) instanceof \PgSql\Connection) {
	print "ALL OUTPUT [TEST]: <pre>" . print_r(pg_version($dbh), true) . "</pre><br>";
} else {
	print "NO DB HANDLER<br>";
}
print "DB Version smaller $to_db_version: " . $db->dbCompareVersion('<' . $to_db_version) . "<br>";
print "DB Version smaller than $to_db_version: " . $db->dbCompareVersion('<=' . $to_db_version) . "<br>";
print "DB Version equal $to_db_version: " . $db->dbCompareVersion('=' . $to_db_version) . "<br>";
print "DB Version bigger than $to_db_version: " . $db->dbCompareVersion('>=' . $to_db_version) . "<br>";
print "DB Version bigger $to_db_version: " . $db->dbCompareVersion('>' . $to_db_version) . "<br>";

$db->dbSetEncoding('SJIS');
print "ENCODING TEST: " . $db->dbVersionInfo('client_encoding') . "/" . $db->dbGetEncoding() . "<br>";
$db->dbResetEncoding();

// empty calls, none of the below should fail
//
$db->dbGetCursor();
//
$db->dbGetCursorExt();
//
$db->dbGetCursorPos('SELECT foo', ['bar']);
//
$db->dbGetCursorNumRows('SELECT foo', ['bar']);
//
$db->dbGetInsertPKName();
//
$db->dbGetInsertPK();
//
$db->dbGetReturningExt();
$db->dbGetReturningExt('foo');
$db->dbGetReturningExt('foo', 0);
$db->dbGetReturningExt(pos:0);
//
$db->dbGetReturningArray();
//
$db->dbGetNumRows();
//
$db->dbGetNumFields();
//
$db->dbGetFieldNames();
//
$db->dbGetFieldTypes();
//
$db->dbGetFieldNameTypes();
//
$db->dbGetFieldName(0);
//
$db->dbGetFieldType(0);
$db->dbGetFieldType('foo');
//
$db->dbGetPrepareCursorValue('foo', 'bar');

// TEST CACHE READS

$res = $db->dbReturn("SELECT * FROM max_test");
print "DB RETURN ROWS: " . $db->dbGetNumRows() . "<br>";

while (is_array($res = $db->dbReturn("SELECT * FROM max_test", DbIo::USE_CACHE, true))) {
	print "UUD/TIME: " . $res['uid'] . "/" . $res['time'] . "<br>";
}
print "CACHED DATA: <pre>" . print_r($db->dbGetCursorExt(), true) . "</pre><br>";
while (is_array($res = $db->dbReturn("SELECT * FROM max_test", DbIo::USE_CACHE))) {
	print "[CACHED] UID/TIME: " . $res['uid'] . "/" . $res['time'] . "<br>";
	// print "****RES: <pre>" . print_r($res, true) . "</pre><br>";
}
// print "CACHED REREAD DATA: <pre>" . print_r($db->dbGetCursorExt(), true) . "</pre><br>";
while (is_array($res = $db->dbReturn("SELECT * FROM max_test", DbIo::NO_CACHE))) {
	print "[NO CACHE] UID.TIME: " . $res['uid'] . "/" . $res['time'] . "<br>";
	// print "****RES: <pre>" . print_r($res, true) . "</pre><br>";
}
print "NO CACHED DATA: <pre>" . print_r($db->dbGetCursorExt(), true) . "</pre><br>";
// alternate check for valid data
// while (($res = $db->dbReturn("SELECT * FROM max_test")) !== false) {
// 	print "[CACHED] TIME: " . $res['time'] . "<br>";
// }
// while (is_array($res = $db->dbReturn("SELECT * FROM max_test", DbIo::USE_CACHE))) {
// 	print "UUD/TIME: " . $res['uid'] . "/" . $res['time'] . "<br>";
// }

// dbReturn tests on separate page
print "<br>";
print "<b>dbReturn CACHE tests</b><br>";
print '<a href="class_test.db.dbReturn.php">Class Test DB dbReturn</a><br>';
print "<br>";

print "<pre>";

if (($dbh = $db->dbGetDbh()) instanceof \PgSql\Connection) {
	print "SOCKET: " . pg_socket($dbh) . "<br>";
} else {
	print "NO SOCKET<br>";
}

// truncate test_foo table before testing
print "<b>TRUNCATE test_foo</b><br>";
$query = "TRUNCATE test_foo";
$db->dbExec($query);
print "<b>TRUNCATE test_foobar</b><br>";
$query = "TRUNCATE test_foobar";
$db->dbExec($query);

$status = $db->dbExec("INSERT INTO test_foo (test, number_a) VALUES "
	. "('FOO TEST " . time() . "', 1) RETURNING test, number_a");
print "DIRECT INSERT STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "DB OBJECT: <pre>" . print_r($status, true) . "</pre>| "
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING EXT[test]: " . print_r($db->dbGetReturningExt('test'), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

var_dump($db->dbGetReturningExt());

// same as above but use an EOM string
$some_time = time();
$query = <<<SQL
INSERT INTO test_foo (
	test, number_a
) VALUES (
	'EOM FOO TEST $some_time', 1
)
RETURNING test, number_a
SQL;
$status = $db->dbExec($query);
print "EOM STRING DIRECT INSERT STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "DB OBJECT: <pre>" . print_r($status, true) . "</pre>| "
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING EXT[test]: " . print_r($db->dbGetReturningExt('test'), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

var_dump($db->dbGetReturningExt());

// should throw deprecated error
// $db->getReturningExt();
$last_insert_pk = $db->dbGetInsertPK();
print "DIRECT INSERT PREVIOUS INSERTED: "
	. print_r($db->dbReturnRow("SELECT test_foo_id, test FROM test_foo "
		. "WHERE test_foo_id = " . (int)$last_insert_pk), true) . "<br>";
$__last_insert_pk = (int)$last_insert_pk;
$query = <<<SQL
SELECT
	test_foo_id, test
FROM test_foo
WHERE test_foo_id = $__last_insert_pk;
SQL;
print "EOM READ OF PREVIOUS INSERTED: " . print_r($db->dbReturnRow($query), true) . "<br>";
print "LAST ERROR: " . $db->dbGetLastError() . "<br>";
print "<br>";
$query = <<<SQL
SELECT
	test_foo_id, test
FROM test_foo
WHERE test_foo_id = $1;
SQL;
print "RETURN ROW PARAMS: " . print_r(
	$db->dbReturnRowParams(
		$query,
		[$__last_insert_pk]
	),
	true
) . "<br>";

// PREPARED INSERT
$db->dbPrepare("ins_test_foo", "INSERT INTO test_foo (test) VALUES ($1) RETURNING test");
$status = $db->dbExecute("ins_test_foo", ['BAR TEST ' . time()]);
print "PREPARE INSERT[ins_test_foo] STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . Support::printToString($db->dbGetPrepareCursorValue('ins_test_foo', 'query')) . " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING RETURN: " . print_r($db->dbGetReturningArray(), true) . "<br>";

print "PREPARE INSERT PREVIOUS INSERTED: "
	. print_r($db->dbReturnRow("SELECT test_foo_id, test FROM test_foo "
	. "WHERE test_foo_id = " . (int)$db->dbGetInsertPK()), true) . "<br>";

print "PREPARE CURSOR RETURN:<br>";
foreach (['pk_name', 'count', 'query', 'returning_id'] as $key) {
	print "KEY: " . $key . ': ' . Support::prAr($db->dbGetPrepareCursorValue('ins_test_foo', $key)) . "<br>";
}

$query = <<<SQL
INSERT INTO
	test_foo
(
	test, string_a
) VALUES (
	$1, '$2'
)
RETURNING test, string_a
SQL;
$db->dbPrepare("ins_test_foo_eom", $query);
$status = $db->dbExecute("ins_test_foo_eom", ['EOM BAR TEST ' . time()]);
print "EOM STRING PREPARE INSERT[ins_test_foo_eom] STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . Support::printToString($db->dbGetPrepareCursorValue('ins_test_foo_eom', 'query')) . " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING RETURN: " . print_r($db->dbGetReturningArray(), true) . "<br>";

echo "<b>*</b><br>";
$status = $db->dbExecParams($query, ['EOM BAR TEST PARAMS ' . time()]);
print "EOM STRING EXEC PARAMS INSERT STATUS: " . Support::printToString($status) . " |<br>"
	. " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING RETURN: " . print_r($db->dbGetReturningArray(), true) . "<br>";

// I/S Query
$query_insert = <<<SQL
INSERT INTO
	test_foo
(
	test, some_bool, string_a, number_a, number_a_numeric,
	some_time, some_timestamp, json_string
) VALUES (
	$1, $2, $3, $4, $5,
	$6, $7, $8
)
RETURNING test
SQL;
$query_select = <<<SQL
SELECT
	test, some_bool, string_a, number_a, number_a_numeric,
	some_time, some_time, some_timestamp, json_string
FROM
	test_foo
WHERE
	test_foo_id = $1
SQL;
// A
$status = $db->dbExecParams(
	$query_insert,
	[
		'EOM BAR TEST PARAMS MULTI ' . time(),
		true,
		'string a',
		1,
		1.5,
		'1h',
		'2023-01-01 12:12:12',
		json_encode(['a' => 'b', 'c' => 1])
	]
);
$__last_insert_id = $db->dbGetInsertPK();
echo "<b>*</b><br>";
print "EOM STRING EXEC PARAMS MULTI INSERT STATUS: " . Support::printToString($status) . " |<br>"
	. " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING RETURN: " . print_r($db->dbGetReturningArray(), true) . "<br>";

print "EOM STRING EXEC RETURN TEST: " . print_r(
	$db->dbReturnRowParams(
		$query_select,
		[$__last_insert_id]
	),
	true
) . "<br>";
// B
$status = $db->dbExecParams(
	$query_insert,
	[
		'EOM BAR TEST PARAMS MULTI NULL ' . time(),
		true,
		'string a',
		null,
		null,
		'1h',
		null,
		null
	]
);
$__last_insert_id = $db->dbGetInsertPK();
echo "<b>*</b><br>";
print "EOM STRING EXEC PARAMS MULTI NULL INSERT STATUS: "
	. Support::printToString($status) . " |<br>"
	. " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING RETURN: " . print_r($db->dbGetReturningArray(), true)
	. "ERROR: " . $db->dbGetLastError(true) . "<br>";
print "EOM STRING EXEC RETURN TEST: " . print_r(
	$db->dbReturnRowParams(
		$query_select,
		[$__last_insert_id]
	),
	true
) . "<br>";
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
// will fail with "NULL" string on int
$query_params = [
	'col 1', 'col 2', 'col 3', 'col 4', 'col 5', 'col 6', 'col 7', 'col 8',
	'col 9',
	'col 10', 'col 11', 'col 12', "NULL"
];
$status = $db->dbExecParams($query_insert, $query_params);
echo "<b>*</b><br>";
echo "EOM STRING WITH MORE THAN 10 PARAMETERS: "
	. Support::printToString($query_params) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " |<br>"
	. "RETURNING EXT: <pre>" . print_r($db->dbGetReturningExt(), true) . "</pre> |<br>"
	. "RETURNING RETURN: <pre>" . print_r($db->dbGetReturningArray(), true) . "</pre> |<br>"
	. "ERROR: " . $db->dbGetLastError(true) . "<br>";
echo "<hr>";
// binary insert tests
$filename = $db->dbEscapeLiteral('class_test.db.php');
$rand_bin_uid = $db->dbEscapeLiteral(\CoreLibs\Create\Uids::uniqIdShort());
$binary_data = $db->dbEscapeBytea(file_get_contents('class_test.db.php') ?:  '');
$query = <<<SQL
INSERT INTO binary_test (
	filename, uid, binary_data
) VALUES (
	$filename, $rand_bin_uid, '$binary_data'
)
SQL;
$status = $db->dbExec($query);
$__last_insert_id = $db->dbGetInsertPK();
print "BINARY DATA INSERT: "
	. Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " |<br>"
	. "RETURNING EXT: <pre>" . print_r($db->dbGetReturningExt(), true) . "</pre> |<br>"
	. "RETURNING RETURN: <pre>" . print_r($db->dbGetReturningArray(), true) . "</pre> |<br>"
	. "ERROR: " . $db->dbGetLastError(true) . "<br>";

echo "<b>*</b><br>";
$query = <<<SQL
INSERT INTO binary_test (
	filename, uid, binary_data
) VALUES (
	$1, $2, $3
)
SQL;
// $binary_data is dbEscapedBytea!
$uniqid = \CoreLibs\Create\Uids::uniqIdShort();
$status = $db->dbExecParams($query, [
	'class_test.db.php', $uniqid, $binary_data
]);
$__last_insert_id = $db->dbGetInsertPK();
print "BINARY DATA INSERT PARAMS: "
	. Support::printToString($status) . " |<br>"
	. " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING RETURN: " . print_r($db->dbGetReturningArray(), true)
	. "ERROR: " . $db->dbGetLastError(true) . "<br>";
print "BINARY READER: ";
$query = <<<SQL
SELECT
	filename, uid, binary_data
FROM
	binary_test
WHERE
	uid = $1
SQL;
$res = $db->dbReturnRowParams($query, [$uniqid]);
if (is_array($res)) {
	var_dump($res);
	$file = $db->dbUnescapeBytea($res['binary_data']);
	// var_dump($file);
} else {
	print "Execute error";
}
echo "<hr>";

// returning test with multiple entries
// $status = $db->db_exec(
// 	"INSERT INTO test_foo (test) VALUES "
// 	. "('BAR 1 " . time() . "'), "
// 	. "('BAR 2 " . time() . "'), "
// 	. "('BAR 3 " . time() . "') "
// 	. "RETURNING test_foo_id"
// );
$status = $db->dbExec(
	"INSERT INTO test_foo (test) VALUES "
	. "('BAR 1 " . time() . "'), "
	. "('BAR 2 " . time() . "'), "
	. "('BAR 3 " . time() . "') "
	. "RETURNING test_foo_id, test"
);
print "DIRECT MULTIPLE INSERT WITH RETURN STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "PRIMARY KEYS: " . print_r($db->dbGetInsertPK(), true) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING EXT[test]: " . print_r($db->dbGetReturningExt('test'), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

$t_1 = time();
$t_2 = time();
$t_3 = time();
$query = <<<SQL
INSERT INTO test_foo (
	test
) VALUES
('EOM BAR 1 $t_1'),
('EOM BAR 2 $t_2'),
('EOM BAR 3 $t_3')
RETURNING test_foo_id, test
SQL;
$status = $db->dbExec($query);
print "EOM STRING DIRECT MULTIPLE INSERT WITH RETURN STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "PRIMARY KEYS: " . print_r($db->dbGetInsertPK(), true) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING EXT[test]: " . print_r($db->dbGetReturningExt('test'), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

// no returning, but not needed ;
$status = $db->dbExec("INSERT INTO test_foo (test) VALUES ('FOO; TEST " . time() . "')");
print "DIRECT INSERT NO RETURN STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";
$last_insert_pk = $db->dbGetInsertPK();

// is_array read test
$q = "SELECT test_foo_id, test FROM test_foo WHERE test_foo_id = " . (int)$last_insert_pk;
if (is_array($s_res = $db->dbReturnRow($q)) && !empty($s_res['test'])) {
	print "WE HAVE DATA FOR: " . Support::printToString($last_insert_pk) . " WITH: " . $s_res['test'] . "<br>";
}

// UPDATE WITH RETURNING
$status = $db->dbExec("UPDATE test_foo SET test = 'SOMETHING DIFFERENT', string_a = '" . (string)rand(1, 100) . "' "
	. "WHERE test_foo_id = " . (int)$last_insert_pk . " RETURNING test_foo.test, string_a");
print "UPDATE WITH PK " . Support::printToString($last_insert_pk)
	. " RETURN STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";
// UPDATE BUT EOM STYLE
$status = $db->dbExecParams(
	<<<SQL
	UPDATE
		test_foo
	SET
		test = ?,
		string_a = ?
	WHERE
		tset_foo_id = ?
	RETURNING
		test_foo.test, string_a
	SQL,
	['SOMETHING DIFFERENT EOM', (string)rand(1, 100)]
);
print "UPDATE EOM WITH PK " . Support::printToString($last_insert_pk)
	. " RETURN STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

// a stand alone insert?
$db->dbExec("INSERT INTO test_foo (test) VALUES ('STAND ALONE')");

// INSERT WITH NO RETURNING
$status = $db->dbExec("INSERT INTO test_foobar (type, integer) VALUES ('WITHOUT DATA', 456)");
print "INSERT WITH NO PRIMARY KEY NO RETURNING STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

$status = $db->dbExec("INSERT INTO test_foobar (type, integer) VALUES ('WITH DATA', 123) RETURNING type, integer");
print "INSERT WITH NO PRIMARY KEY WITH RETURNING STATUS: " . Support::printToString($status) . " |<br>"
	. "QUERY: " . $db->dbGetQuery() . " |<br>"
	. "PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . " | "
	. "RETURNING EXT: " . print_r($db->dbGetReturningExt(), true) . " | "
	. "RETURNING ARRAY: " . print_r($db->dbGetReturningArray(), true) . "<br>";

print "</pre>";

print "<b>PREPARE QUERIES</b><br>";
// READ PREPARE
$q_prep = <<<SQL
SELECT test_foo_id, test, some_bool, string_a, number_a,
number_a_numeric, some_time
FROM test_foo
WHERE test = $1
ORDER BY test_foo_id DESC LIMIT 5
SQL;
if ($db->dbPrepare('sel_test_foo', $q_prep) === false) {
	print "Error in sel_test_foo prepare<br>";
} else {
	// do not run this in dbFetchArray directly as
	// dbFetchArray(dbExecute(...))
	// this will end in an endless loop
	$i = 1;
	$cursor = $db->dbExecute('sel_test_foo', ['SOMETHING DIFFERENT']);
	while (is_array($res = $db->dbFetchArray($cursor, true))) {
		print "DB PREP EXEC FETCH ARR: " . $i . ": <pre>" . print_r($res, true) . "</pre><br>";
		$i++;
	}
}
// prepre a second time on normal connection
if ($db->dbPrepare('sel_test_foo', $q_prep) === false) {
	print "Error prepareing<br>";
	print "ERROR (dbPrepare on same query): "
	. $db->dbGetLastError() . "/" . $db->dbGetLastWarning() . "/"
	. "<pre>" . print_r($db->dbGetCombinedErrorHistory(), true) . "</pre><br>";
}

// sel test with ANY () type
$q_prep = "SELECT test_foo_id, test, some_bool, string_a, number_a, "
	. "number_a_numeric, some_time "
	. "FROM test_foo "
	. "WHERE test = ANY($1) "
	. "ORDER BY test_foo_id DESC LIMIT 5";
if ($db->dbPrepare('sel_test_foo_any', $q_prep) === false) {
		print "Error in sel_test_foo_any prepare<br>";
} else {
	// do not run this in dbFetchArray directly as
	// dbFetchArray(dbExecute(...))
	// this will end in an endless loop
	$values = [
		'SOMETHING DIFFERENT',
		'STAND ALONE',
		'I DO NOT EXIST'
	];
	$query_value = '{'
		. join(',', $values)
		. '}';
	print "Read: $query_value<br>";
	$cursor = $db->dbExecute('sel_test_foo_any', [
		$query_value
	]);
	$i = 1;
	while (($res = $db->dbFetchArray($cursor, true)) !== false) {
		print "DB PREP EXEC FETCH ANY ARR: " . $i . ": <pre>" . print_r($res, true) . "</pre><br>";
		$i++;
	}
}

echo "<hr>";
print "EOM STYLE STRINGS<br>";
$test_bar = $db->dbEscapeLiteral('SOMETHING DIFFERENT');
// Test EOM block
$q = <<<SQL
SELECT test_foo_id, test, some_bool, string_a, number_a,
-- comment
number_a_numeric, some_time
FROM test_foo
WHERE test = $test_bar
ORDER BY test_foo_id DESC LIMIT 5
SQL;
while (is_array($res = $db->dbReturn($q))) {
	print "ROW: <pre>" . print_r($res, true) . "</pre><br>";
}
echo "<hr>";
print "DB RETURN PARAMS<br>";
$q = <<<SQL
SELECT test_foo_id, test, some_bool, string_a, number_a,
-- comment
number_a_numeric, some_time
FROM test_foo
WHERE test = $1
ORDER BY test_foo_id DESC LIMIT 5
SQL;
while (
	is_array($res = $db->dbReturnParams($q, ['SOMETHING DIFFERENT']))
) {
	print "ROW: <pre>" . print_r($res, true) . "</pre><br>";
}
echo "<hr>";

print "DB RETURN PARAMS LIKE<br>";
$q = <<<SQL
SELECT
	test_foo_id, test, some_bool, string_a, number_a, number_a_numeric
FROM test_foo
WHERE string_a LIKE $1;
SQL;
while (
	is_array($res = $db->dbReturnParams($q, ['%string%']))
) {
	print "ROW: <pre>" . print_r($res, true) . "</pre><br>";
}
echo "<hr>";

print "DB RETURN PARAMS ANY<br>";
$q = <<<SQL
SELECT
	test_foo_id, test, some_bool, string_a, number_a, number_a_numeric
FROM test_foo
WHERE string_a = ANY($1);
SQL;
$query_value = '{'
	. join(',', ['string a'])
. '}';
while (
	is_array($res = $db->dbReturnParams($q, [$query_value]))
) {
	print "ROW: <pre>" . print_r($res, true) . "</pre><br>";
}
echo "<hr>";

print "COMPOSITE ELEMENT READ<br>";
$res = $db->dbReturnRow("SELECT item, count, (item).name, (item).price, (item).supplier_id FROM on_hand");
print "ROW: <pre>" . print_r($res, true) . "</pre>";
var_dump($res);
print "Field Name/Types: <pre>" . print_r($db->dbGetFieldNameTypes(), true) . "</pre>";
echo "<hr>";

// NOTE: try to replacate connection still exists if script is run a second time
// open pg bouncer connection
$db_pgb = new CoreLibs\DB\IO($DB_CONFIG['test_pgbouncer'] ?? [], $log);
print "[PGB] DBINFO: " . $db_pgb->dbInfo() . "<br>";
if ($db->dbPrepare('pgb_sel_test_foo', $q_prep) === false) {
	print "[PGB] [1] Warning in pgb_sel_test_foo prepare<br>";
} else {
	print "[PGB] [1] pgb_sel_test_foo prepare OK<br>";
}
// second prepare
if ($db->dbPrepare('pgb_sel_test_foo', $q_prep) === false) {
	print "[PGB] [2] Warning in pgb_sel_test_foo prepare<br>";
} else {
	print "[PGB] [2] pgb_sel_test_foo prepare OK<br>";
}
// same statment name, different query
if (
	$db->dbPrepare('pgb_sel_test_foo', <<<SQL
	SELECT * FROM test_foo WHERE test = $1
	ORDER BY test_foo_id DESC LIMIT 5
	SQL) === false
) {
	print "[PGB] [3] Error in pgb_sel_test_foo prepare<br>";
} else {
	print "[PGB] [3] pgb_sel_test_foo prepare OK<br>";
}
$db_pgb->dbClose();

# db write class test
$table = 'test_foo';
print "TABLE META DATA: " . DgS::printAr(SetVarType::setArray(
	$db->dbShowTableMetaData($table)
)) . "<br>";
// insert first, then use primary key to update
$primary_key = ''; # unset
$db_write_table = ['test', 'string_a', 'number_a', 'some_bool'];
$object_fields_not_touch = [];
$object_fields_not_update = [];
$data = [
	'test' => 'dbWriteDataExt: BOOL TEST SOMETHING ' . time(), 'string_a' => 'SOME TEXT', 'number_a' => 5
];
$primary_key = $db->dbWriteDataExt(
	$db_write_table,
	$primary_key,
	$table,
	$object_fields_not_touch,
	$object_fields_not_update,
	$data
);
print "Wrote to DB tabel $table with data " . print_r($data, true) . " and got primary key $primary_key<br>";
$data = [
	'test' => 'dbWriteDataExt: BOOL TEST ON ' . time(), 'string_a' => '', 'number_a' => 0, 'some_bool' => 1
];
$primary_key = $db->dbWriteDataExt(
	$db_write_table,
	(int)$primary_key,
	$table,
	$object_fields_not_touch,
	$object_fields_not_update,
	$data
);
print "Wrote to DB tabel $table with data " . print_r($data, true) . " and got primary key $primary_key<br>";
$data = [
	'test' => 'dbWriteDataExt: BOOL TEST OFF ' . time(), 'string_a' => null, 'number_a' => null, 'some_bool' => 0
];
$primary_key = $db->dbWriteDataExt(
	$db_write_table,
	(int)$primary_key,
	$table,
	$object_fields_not_touch,
	$object_fields_not_update,
	$data
);
print "Wrote to DB tabel $table with data " . print_r($data, true) . " and got primary key $primary_key<br>";
$data = [
	'test' => 'dbWriteDataExt: BOOL TEST UNSET ' . time()
];
$primary_key = $db->dbWriteDataExt(
	$db_write_table,
	(int)$primary_key,
	$table,
	$object_fields_not_touch,
	$object_fields_not_update,
	$data
);
print "Wrote to DB tabel $table with data " . print_r($data, true) . " and got primary key $primary_key<br>";

// return Array Test
$query = "SELECT type, sdate, integer FROM foobar";
$data = $db->dbReturnArray($query, true);
print "RETURN ARRAY: " . $db->dbGetNumRows() . ", Full foobar list: <br><pre>" . print_r($data, true) . "</pre><br>";
$query = <<<SQL
SELECT
	type, sdate
FROM
	foobar
WHERE
	type = $1
SQL;
$data = $db->dbReturnArrayParams($query, ['schmalz'], true);
print "RETURN ARRAY PARAMS: " . $db->dbGetNumRows() . ", Full foobar list: <br><pre>"
	. print_r($data, true) . "</pre><br>";

// trigger a warning
print "<b>WARNING NEXT</b><br>";
// trigger an error
print "<b>ERROR NEXT</b><br>";
$query = "INSERT invalid FROM invalid";
$data = $db->dbReturnArray($query);
print "ERROR (INS ON dbExec): "
	. $db->dbGetLastError() . "/" . $db->dbGetLastWarning() . "/"
	. "<pre>" . print_r($db->dbGetCombinedErrorHistory(), true) . "</pre><br>";
$query = "SELECT invalid FROM invalid";
$data = $db->dbReturnArray($query);
print "ERROR (HARD ERROR): "
	. $db->dbGetLastError() . "/" . $db->dbGetLastWarning() . "/"
	. "<pre>" . print_r($db->dbGetCombinedErrorHistory(), true) . "</pre><br>";
// Now a good query will fail
$query = "SELECT type, sdate, integer FROM foobar";
$data = $db->dbReturnRow($query, true);
print "GOOD SELECT AFTER ERROR: "
	. $db->dbGetLastError() . "/" . $db->dbGetLastWarning() . "/"
	. "<pre>" . print_r($db->dbGetCombinedErrorHistory(), true) . "</pre><br>";
print "GOOD SELECT AFTER ERROR: <br><pre>" . print_r($data, true) . "</pre><br>";

/*
set error id in
dbPrepare
dbExecute
dbExecAsync
dbWriteDataExt
dbReturnArray
dbReturnRow
dbFetchArray (?)
dbExec (if not set before)
dbReturn
dbShowTableMetaData
*/

// how to handle HARD errors

# async test queries
/*
$db->dbExecAsync(
	"SELECT test FROM test_foo, (SELECT pg_sleep(10)) as sub "
		. "WHERE test_foo_id IN (27, 50, 67, 44, 10)"
);
echo "WAITING FOR ASYNC: ";
$chars = ['|', '/', '-', '\\'];
while (($ret = $db->dbCheckAsync()) === true) {
	if ((list($_, $char) = each($chars)) === FALSE) {
		reset($chars);
		list($_, $char) = each($chars);
	}
	print $char;
	sleep(1);
	flush();
}
print "<br>END STATUS: " . $ret . "<br>";
//	while ($res = $db->dbFetchArray($ret))
while ($res = $db->dbFetchArray()) {
	echo "RES: " . $res['test'] . "<br>";
}
# test async insert
$db->dbExecAsync("INSERT INTO test_foo (Test) VALUES ('ASYNC TEST " . time() . "')");
echo "WAITING FOR ASYNC INSERT: ";
while (($ret = $db->dbCheckAsync()) === true) {
	print " . ";
	sleep(1);
	flush();
}
print "<br>END STATUS: " . $ret . " | PK: " . $db->insert_id . "<br>";
print "ASYNC PREVIOUS INSERTED: "
	. print_r(
		$db->dbReturnRow("SELECT test_foo_id, test FROM test_foo WHERE test_foo_id = "
			. $db->insert_id),
		true
	) . "<br>";
*/

/*
$q = "Select * from test_foo";
$test_foo = $db->dbExecAsync($q);
print "[ERR] Query: " . $q . "<br>";
print "[ERR] RESOURCE: $test_foo<br>";
while (($ret = $db->dbCheckAsync()) === true)
{
	print "[ERR]: $ret<br>";
	sleep(5);
}
*/

// search path check
$q = "SHOW search_path";
$cursor = $db->dbExec($q);
$data = $db->dbFetchArray($cursor)['search_path'] ?? '';
print "RETURN DATA FOR search_path: " . $data . "<br>";
//	print "RETURN DATA FOR search_path: " . DgS::printAr($data) . "<br>";
// insert something into test.schema_test and see if we get the PK back
$status = $db->dbExec(
	"INSERT INTO test.schema_test (contents, id) VALUES "
	. "('TIME: " . (string)time() . "', " . (string)rand(1, 10) . ")"
);
print "OTHER SCHEMA INSERT STATUS: "
	. Support::printToString($status)
	. " | PK NAME: " . $db->dbGetInsertPKName()
	. ", PRIMARY KEY: " . Support::printToString($db->dbGetInsertPK()) . "<br>";

print "<b>NULL TEST DB READ</b><br>";
$q = "SELECT uid, null_varchar, null_int FROM test_null_data WHERE uid = 'A'";
$res = $db->dbReturnRow($q);
var_dump($res);
print "RES: " . DgS::printAr(SetVarType::setArray($res)) . "<br>";
print "ISSET: " . isset($res['null_varchar']) . "<br>";
print "EMPTY: " . empty($res['null_varchar']) . "<br>";

print "</body></html>";

// __END__

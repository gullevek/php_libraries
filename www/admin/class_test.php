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
$LOG_FILE_ID = 'classTest';

// init login & backend class
$login = new CoreLibs\ACL\Login(DB_CONFIG);
$basic = new CoreLibs\Admin\Backend(DB_CONFIG);
$basic->dbInfo(true);
ob_end_flush();

echo "DB_CONFIG_SET constant: <pre>".print_r(DB_CONFIG, true)."</pre><br>";

$basic->hrRunningTime();
$basic->runningTime();
echo "RANDOM KEY [50]: ".$basic->randomKeyGen(50)."<br>";
echo "TIMED [hr]: ".$basic->hrRunningTime()."<br>";
echo "TIMED [def]: ".$basic->runningTime()."<br>";
echo "TIMED [string]: ".$basic->runningtime_string."<br>";
$basic->hrRunningTime();
echo "RANDOM KEY [default]: ".$basic->randomKeyGen()."<br>";
echo "TIMED [hr]: ".$basic->hrRunningTime()."<br>";

// color
print "COLOR: -1, -1, -1: ".$basic->rgb2hex(-1, -1, -1)."<br>";
print "COLOR: 10, 20, 30: ".$basic->rgb2hex(10, 20, 30)."<br>";

// set + check edit access id
$edit_access_id = 3;
if (is_object($login) && isset($login->acl['unit'])) {
	print "ACL UNIT: ".print_r(array_keys($login->acl['unit']), true)."<br>";
	print "ACCESS CHECK: ".(string)$login->loginCheckEditAccess($edit_access_id)."<br>";
	if ($login->loginCheckEditAccess($edit_access_id)) {
		$basic->edit_access_id = $edit_access_id;
	} else {
		$basic->edit_access_id = $login->acl['unit_id'];
	}
} else {
	print "Something went wrong with the login<br>";
}

//	$basic->debug('SESSION', $basic->print_ar($_SESSION));

print "<html><head><title>TEST CLASS</title><head>";
print "<body>";
print '<form method="post" name="loginlogout">';
print '<a href="javascript:document.loginlogout.login_logout.value=\'Logou\';document.loginlogout.submit();">Logout</a>';
print '<input type="hidden" name="login_logout" value="">';
print '</form>';

// print the debug core vars
print "DEBUG OUT: ".$basic->debug_output."<br>";
print "ECHO OUT: ".$basic->echo_output."<br>";
print "PRINT OUT: ".$basic->print_output."<br>";
print "NOT DEBUG OUT: ".$basic->debug_output_not."<br>";
print "NOT ECHO OUT: ".$basic->echo_output_not."<br>";
print "NOT PRINT OUT: ".$basic->print_output_not."<br>";
print "DEBUG OUT ALL: ".$basic->debug_output_all."<br>";
print "ECHO OUT ALL: ".$basic->echo_output_all."<br>";
print "PRINT OUT ALL: ".$basic->print_output_all."<br>";

print "CALLER BACKTRACE: ".$basic->getCallerMethod()."<br>";
$basic->debug('SOME MARK', 'Some error output');

print "EDIT ACCESS ID: ".$basic->edit_access_id."<br>";
if (is_object($login)) {
	//	print "ACL: <br>".$basic->print_ar($login->acl)."<br>";
	$basic->debug('ACL', "ACL: ".$basic->printAr($login->acl));
	//	print "DEFAULT ACL: <br>".$basic->print_ar($login->default_acl_list)."<br>";
	//	print "DEFAULT ACL: <br>".$basic->print_ar($login->default_acl_list)."<br>";
	//	$result = array_flip(array_filter(array_flip($login->default_acl_list), function ($key) { if (is_numeric($key)) return $key; }));
	//	print "DEFAULT ACL: <br>".$basic->print_ar($result)."<br>";
	// DEPRICATED CALL
	//	$basic->adbSetACL($login->acl);
}

// DB client encoding
print "DB Client encoding: ".$basic->dbGetEncoding()."<br>";

while ($res = $basic->dbReturn("SELECT * FROM max_test", 0, true)) {
	print "TIME: ".$res['time']."<br>";
}
print "CACHED DATA: <pre>".print_r($basic->cursor_ext, true)."</pre><br>";
while ($res = $basic->dbReturn("SELECT * FROM max_test")) {
	print "[CACHED] TIME: ".$res['time']."<br>";
}

$status = $basic->dbExec("INSERT INTO foo (test) VALUES ('FOO TEST ".time()."') RETURNING test");
print "DIRECT INSERT STATUS: $status | PRIMARY KEY: ".$basic->dbGetInsertPK()." | PRIMARY KEY EXT: ".print_r($basic->insert_id_ext, true)." => ".print_r($basic->dbGetReturningExt(), true)."<br>";
// should throw deprecated error
// $basic->getReturningExt();
print "DIRECT INSERT PREVIOUS INSERTED: ".print_r($basic->dbReturnRow("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->dbGetInsertPK()), true)."<br>";
$basic->dbPrepare("ins_foo", "INSERT INTO foo (test) VALUES ($1)");
$status = $basic->dbExecute("ins_foo", array('BAR TEST '.time()));
print "PREPARE INSERT STATUS: $status | PRIMARY KEY: ".$basic->dbGetInsertPK()." | PRIMARY KEY EXT: ".print_r($basic->dbGetReturningExt(), true)."<br>";
print "PREPARE INSERT PREVIOUS INSERTED: ".print_r($basic->dbReturnRow("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->dbGetInsertPK()), true)."<br>";
// returning test with multiple entries
//	$status = $basic->db_exec("INSERT INTO foo (test) values ('BAR 1 ".time()."'), ('BAR 2 ".time()."'), ('BAR 3 ".time()."') RETURNING foo_id");
$status = $basic->dbExec("INSERT INTO foo (test) values ('BAR 1 ".time()."'), ('BAR 2 ".time()."'), ('BAR 3 ".time()."') RETURNING foo_id, test");
print "DIRECT MULTIPLE INSERT STATUS: $status | PRIMARY KEYS: ".print_r($basic->dbGetInsertPK(), true)." | PRIMARY KEY EXT: ".print_r($basic->dbGetReturningExt(), true)."<br>";
// no returning, but not needed ;
$status = $basic->dbExec("INSERT INTO foo (test) VALUES ('FOO; TEST ".time()."');");
print "DIRECT INSERT STATUS: $status | PRIMARY KEY: ".$basic->dbGetInsertPK()." | PRIMARY KEY EXT: ".print_r($basic->dbGetReturningExt(), true)."<br>";
// UPDATE WITH RETURNING
$status = $basic->dbExec("UPDATE foo SET test = 'SOMETHING DIFFERENT' WHERE foo_id = 3688452 RETURNING test");
print "UPDATE STATUS: $status | RETURNING EXT: ".print_r($basic->dbGetReturningExt(), true)."<br>";
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
print "TABLE META DATA: ".$basic->printAr($basic->dbShowTableMetaData($table))."<br>";
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
//	print "RETURN DATA FOR search_path: ".$basic->printAr($data)."<br>";
// insert something into test.schema_test and see if we get the PK back
$status = $basic->dbExec("INSERT INTO test.schema_test (contents, id) VALUES ('TIME: ".time()."', ".rand(1, 10).")");
print "OTHER SCHEMA INSERT STATUS: ".$status." | PK NAME: ".$basic->pk_name.", PRIMARY KEY: ".$basic->insert_id."<br>";

print "<b>NULL TEST DB READ</b><br>";
$q = "SELECT uid, null_varchar, null_int FROM test_null_data WHERE uid = 'A'";
$res = $basic->dbReturnRow($q);
var_dump($res);
print "RES: ".$basic->printAr($res)."<br>";
print "ISSET: ".isset($res['null_varchar'])."<br>";
print "EMPTY: ".empty($res['null_varchar'])."<br>";

// data read test

// time string thest
$timestamp = 5887998.33445;
$time_string = $basic->timeStringFormat($timestamp);
print "PLANE TIME STRING: ".$timestamp."<br>";
print "TIME STRING TEST: ".$time_string."<br>";
print "REVERSE TIME STRING: ".$basic->stringToTime($time_string)."<br>";
if (round($timestamp, 4) == $basic->stringToTime($time_string)) {
	print "REVERSE TIME STRING MATCH<br>";
} else {
	print "REVERSE TRIME STRING DO NOT MATCH<br>";
}
print "ZERO TIME STRING: ".$basic->timeStringFormat(0, true)."<br>";
print "ZERO TIME STRING: ".$basic->timeStringFormat(0.0, true)."<br>";
print "ZERO TIME STRING: ".$basic->timeStringFormat(1.005, true)."<br>";

echo "HTML ENT INT: ".$basic->htmlent(5)."<br>";
echo "HTML ENT STRING: ".$basic->htmlent('5<<>')."<br>";
echo "HTML ENT NULL: ".$basic->htmlent(null)."<br>";

// magic links test
print $basic->magicLinks('user@bubu.at').'<br>';
print $basic->magicLinks('http://test.com/foo/bar.php?foo=1').'<br>';

// compare date
$date_1 = '2017/1/5';
$date_2 = '2017-01-05';
print "COMPARE DATE: ".$basic->compareDate($date_1, $date_2)."<br>";

// recursive array search
$test_array = array(
	'foo' => 'bar',
	'input' => array(
		'element_a' => array(
			'type' => 'text'
		),
		'element_b' => array(
			'type' => 'email'
		),
		'element_c' => array(
			'type' => 'email'
		)
	)
);

echo "SOURCE ARRAY: ".$basic->printAr($test_array)."<br>";
echo "FOUND ELEMENTS [base]: ".$basic->printAr($basic->arraySearchRecursive('email', $test_array, 'type'))."<br>";
echo "FOUND ELEMENTS [input]: ".$basic->printAr($basic->arraySearchRecursive('email', $test_array['input'], 'type'))."<br>";

// *** BYTES TEST ***
$bytes = array(
	-123123123,
	999999, // KB-1
	999999999, // MB-1
	254779258, // MB-n
	999999999999999, // TB-1
	588795544887632, // TB-n
	999999999999999999, // PB-1
	9223372036854775807, // MAX INT
	999999999999999999999, // EB-1
);
print "<b>BYTE FORMAT TESTS</b><br>";
foreach ($bytes as $byte) {
	print '<div style="display: flex; border-bottom: 1px dashed gray;">';
	//
	print '<div style="width: 35%; text-align: right; padding-right: 2px;">';
	print "(".number_format($byte)."/".$byte.") bytes :";
	print '</div><div style="width: 40%;">';
	print $basic->humanReadableByteFormat($byte);
	print "</div>";
	//
	print "</div>";
	//
	print '<div style="display: flex; border-bottom: 1px dotted red;">';
	//
	print '<div style="width: 35%; text-align: right; padding-right: 2px;">';
	print "bytes [si]:";
	print '</div><div style="width: 40%;">';
	// print $basic->byteStringFormat($byte, true, false, true);
	print $basic->humanReadableByteFormat($byte, $basic::BYTE_FORMAT_SI);
	print "</div>";
	//
	print "</div>";
}


// *** IMAGE TESTS ***
echo "<hr>";
// image thumbnail
$images = array(
	// height bigger
	// 'no_picture.jpg',
	// 'no_picture.png',
	// width bigger
	// 'no_picture_width_bigger.jpg',
	// 'no_picture_width_bigger.png',
	// square
	// 'no_picture_square.jpg',
	// 'no_picture_square.png',
	// other sample images
	// '5c501af48da6c.jpg',
	// Apple HEIC files
	// 'img_2145.heic',
	// Photoshop
	'photoshop_test.psd',
);
$thumb_width = 250;
$thumb_height = 300;
// return mime type ala mimetype
$finfo = new finfo(FILEINFO_MIME_TYPE);
foreach ($images as $image) {
	$image = BASE.LAYOUT.CONTENT_PATH.IMAGES.$image;
	list ($height, $width, $img_type) = getimagesize($image);
	echo "<div>IMAGE INFO: ".$height."x".$width.", TYPE: ".$img_type." [".$finfo->file($image)."]</div>";
	// rotate image first
	$basic->correctImageOrientation($image);
	// thumbnail tests
	echo "<div>".basename($image).": WIDTH: $thumb_width<br><img src=".$basic->createThumbnailSimple($image, $thumb_width)."></div>";
	echo "<div>".basename($image).": HEIGHT: $thumb_height<br><img src=".$basic->createThumbnailSimple($image, 0, $thumb_height)."></div>";
	echo "<div>".basename($image).": WIDTH/HEIGHT: $thumb_width x $thumb_height<br><img src=".$basic->createThumbnailSimple($image, $thumb_width, $thumb_height)."></div>";
	// test with dummy
	echo "<div>".basename($image).": WIDTH/HEIGHT: $thumb_width x $thumb_height (+DUMMY)<br><img src=".$basic->createThumbnailSimple($image, $thumb_width, $thumb_height, null, true, false)."></div>";
	echo "<hr>";
}

// mime test
$mime = 'application/vnd.ms-excel';
print "App for mime: ".$basic->mimeGetAppName($mime)."<br>";
$basic->mimeSetAppName($mime, 'Microsoft Excel');
print "App for mime changed: ".$basic->mimeGetAppName($mime)."<br>";

// print error messages
// print $login->printErrorMsg();
print $basic->printErrorMsg();

print "</body></html>";

# __END__

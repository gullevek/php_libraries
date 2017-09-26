<?
	$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
	$DEBUG_ALL = 1;
	$PRINT_ALL = 1;
	$DB_DEBUG = 1;

	if ($DEBUG_ALL)
		error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

	define('USE_DATABASE', true);
	// sample config
	require("config.inc");
	// set session name
	DEFINE('SET_SESSION_NAME', EDIT_SESSION_NAME);
//	session_name(EDIT_SESSION_NAME);
//	session_start();
	// basic class test file
	foreach (array ('Login', 'Admin.Backend') as $class)
		_spl_autoload('Class.'.$class.'.inc');

	$lang = 'en_utf8';
	
	DEFINE('LOG_FILE_ID', 'classTest');
	$login = new login($DB_CONFIG[LOGIN_DB], $lang);
	// init with standard
//	$basic = new db_io($DB_CONFIG[MAIN_DB]);
	$basic = new AdminBackend($DB_CONFIG[MAIN_DB], $lang);
	$basic->db_info(1);
	
	// set + check edit access id
	$edit_access_id = 3;
	print "ACL UNIT: ".print_r(array_keys($login->acl['unit']), 1)."<br>";
	print "ACCESS CHECK: ".$login->login_check_edit_access($edit_access_id)."<br>";
	if ($login->login_check_edit_access($edit_access_id))
		$basic->edit_access_id = $edit_access_id;
	else
		$basic->edit_access_id = $login->acl['unit_id'];

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

	print "CALLER BACKTRACE: ".$basic->get_caller_method()."<br>";
	$basic->debug('SOME MARK', 'Some error output');

	print "EDIT ACCESS ID: ".$basic->edit_access_id."<br>";
//	print "ACL: <br>".$basic->print_ar($login->acl)."<br>";
	$basic->debug('ACL', "ACL: ".$basic->print_ar($login->acl));
//	print "DEFAULT ACL: <br>".$basic->print_ar($login->default_acl_list)."<br>";
//	print "DEFAULT ACL: <br>".$basic->print_ar($login->default_acl_list)."<br>";
//	$result = array_flip(array_filter(array_flip($login->default_acl_list), function ($key) { if (is_numeric($key)) return $key; }));
//	print "DEFAULT ACL: <br>".$basic->print_ar($result)."<br>";
	// DEPRICATED CALL
//	$basic->adbSetACL($login->acl);

	while ($res = $basic->db_return("SELECT * FROM max_test"))
	{
		print "TIME: ".$res['time']."<br>";
	}

	$status = $basic->db_exec("INSERT INTO foo (test) VALUES ('FOO TEST ".time()."') RETURNING test");
	print "DIRECT INSERT STATUS: $status | PRIMARY KEY: ".$basic->insert_id." | PRIMARY KEY EXT: ".print_r($basic->insert_id_ext, 1)."<br>";
	print "DIRECT INSERT PREVIOUS INSERTED: ".print_r($basic->db_return_row("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->insert_id), 1)."<br>";
	$basic->db_prepare("ins_foo", "INSERT INTO foo (test) VALUES ($1)");
	$status = $basic->db_execute("ins_foo", array('BAR TEST '.time()));
	print "PREPARE INSERT STATUS: $status | PRIMARY KEY: ".$basic->insert_id." | PRIMARY KEY EXT: ".print_r($basic->insert_id_ext, 1)."<br>";
	print "PREPARE INSERT PREVIOUS INSERTED: ".print_r($basic->db_return_row("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->insert_id), 1)."<br>";
	// returning test with multiple entries
//	$status = $basic->db_exec("INSERT INTO foo (test) values ('BAR 1 ".time()."'), ('BAR 2 ".time()."'), ('BAR 3 ".time()."') RETURNING foo_id");
	$status = $basic->db_exec("INSERT INTO foo (test) values ('BAR 1 ".time()."'), ('BAR 2 ".time()."'), ('BAR 3 ".time()."') RETURNING foo_id, test");
	print "DIRECT MULTIPLE INSERT STATUS: $status | PRIMARY KEYS: ".print_r($basic->insert_id, 1)." | PRIMARY KEY EXT: ".print_r($basic->insert_id_ext, 1)."<br>";

	# db write class test
	$table = 'foo';
	$primary_key = ''; # unset
	$db_write_table = array ('test', 'some_bool');
//	$db_write_table = array ('test');
	$object_fields_not_touch = array ();
	$object_fields_not_update = array ();
	$data = array ('test' => 'BOOL TEST SOMETHING '.time());
	$primary_key = $basic->db_write_data_ext($db_write_table, $primary_key, $table, $object_fields_not_touch, $object_fields_not_update, $data);
	print "Wrote to DB tabel $table and got primary key $primary_key<br>";
	$data = array ('test' => 'BOOL TEST OFF '.time(), 'some_bool' => 1);
	$primary_key = $basic->db_write_data_ext($db_write_table, $primary_key, $table, $object_fields_not_touch, $object_fields_not_update, $data);
	print "Wrote to DB tabel $table and got primary key $primary_key<br>";

	# async test queries
/*	$basic->db_exec_async("SELECT test FROM foo, (SELECT pg_sleep(10)) as sub WHERE foo_id IN (27, 50, 67, 44, 10)");
	echo "WAITING FOR ASYNC: ";
	$chars = array('|', '/', '-', '\\');
	while (($ret = $basic->db_check_async()) === true)
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
//	while ($res = $basic->db_fetch_array($ret))
	while ($res = $basic->db_fetch_array())
	{
		echo "RES: ".$res['test']."<br>";
	}
	# test async insert
	$basic->db_exec_async("INSERT INTO foo (Test) VALUES ('ASYNC TEST ".time()."')");
	echo "WAITING FOR ASYNC INSERT: ";
	while (($ret = $basic->db_check_async()) === true)
	{
		print ".";
		sleep(1);
		flush();
	}
	print "<br>END STATUS: ".$ret." | PK: ".$basic->insert_id."<br>";
	print "ASYNC PREVIOUS INSERTED: ".print_r($basic->db_return_row("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->insert_id), 1)."<br>"; */

	$to_db_version = '9.1.9';
	print "VERSION DB: ".$basic->db_version()."<br>";
	print "DB Version smaller $to_db_version: ".$basic->db_compare_version('<'.$to_db_version)."<br>";
	print "DB Version smaller than $to_db_version: ".$basic->db_compare_version('<='.$to_db_version)."<br>";
	print "DB Version equal $to_db_version: ".$basic->db_compare_version('='.$to_db_version)."<br>";
	print "DB Version bigger than $to_db_version: ".$basic->db_compare_version('>='.$to_db_version)."<br>";
	print "DB Version bigger $to_db_version: ".$basic->db_compare_version('>'.$to_db_version)."<br>";

/*	$q = "SELECT FOO FRO BAR";
//	$q = "Select * from foo";
	$foo = $basic->db_exec_async($q);
	print "[ERR] Query: ".$q."<br>";
	print "[ERR] RESOURCE: $foo<br>";
	while (($ret = $basic->db_check_async()) === true)
	{
		print "[ERR]: $ret<br>";
//		sleep(5);
	} */

	// search path check
	$q = "SHOW search_path";
	$cursor = $basic->db_exec($q);
	$data = $basic->db_fetch_array($cursor)['search_path'];
	print "RETURN DATA FOR search_path: ".$data."<br>";
//	print "RETURN DATA FOR search_path: ".$basic->print_ar($data)."<br>";
	// insert something into test.schema_test and see if we get the PK back
	$status = $basic->db_exec("INSERT INTO test.schema_test (contents, id) VALUES ('TIME: ".time()."', ".rand(1, 10).")");
	print "OTHER SCHEMA INSERT STATUS: ".$status." | PK NAME: ".$basic->pk_name.", PRIMARY KEY: ".$basic->insert_id."<br>";

	// time string thest
	$timestamp = 5887998.33445;
	$time_string = $basic->TimeStringFormat($timestamp);
	print "TIME STRING TEST: ".$time_string."<br>";
	print "REVERSE TIME STRING: ".$basic->StringToTime($time_string);

	// magic links test
	print $basic->magic_links('user@bubu.at').'<br>';
	print $basic->magic_links('http://test.com/foo/bar.php?foo=1').'<br>';

	// compare date
	$date_1 = '2017/1/5';
	$date_2 = '2017-01-05';
	print "COMPARE DATE: ".$basic->CompareDate($date_1, $date_2)."<br>";

	// print error messages
	print $basic->print_error_msg();

	print "</body></html>";
?>

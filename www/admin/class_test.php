<?
	// $Id: class_test.php 4831 2014-01-20 03:27:10Z gullevek $
	$DEBUG_ALL = 1;
	$PRINT_ALL = 1;
	$DB_DEBUG = 1;

	if ($DEBUG_ALL)
		error_reporting(E_ALL | E_STRICT |  E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);
	require('libs/Error.Handling.inc');

	define('USE_DATABASE', true);
	// sample config
	require("config.inc");
	// set session name
	DEFINE('SET_SESSION_NAME', EDIT_SESSION_NAME);
//	session_name(EDIT_SESSION_NAME);
//	session_start();
	// basic class test file
//	require(LIBS."Class.Basic.inc");
//	require(LIBS."Class.DB.IO.inc");
	require(LIBS."Class.Login.inc");
	require(LIBS."Class.Admin.Backend.inc");

	$lang = 'en_utf8';

	$login = new login($DB_CONFIG[LOGIN_DB], $lang);
	// init with standard
//	$basic = new db_io($DB_CONFIG[MAIN_DB]);
	$basic = new AdminBackend($DB_CONFIG[MAIN_DB], $lang);
	$basic->db_info(1);
	
	// set + check edit access id
	$edit_access_id = 3;
	if ($login->login_check_edit_access($edit_access_id))
		$basic->edit_access_id = $edit_access_id;
	else
		$basic->edit_access_id = $login->acl['info']['default_edit_access'];

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
	// file name (logging)
	print "FILENAME EXT: ".$basic->file_name_ext."<br>";
	print "MAX FILESIZE: ".$basic->max_filesize."<br>";

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
	print "DIRECT INSERT STATUS: $status | PRIMARY KEY: ".$basic->insert_id."<br>";
	print "DIRECT INSERT PREVIOUS INSERTED: ".print_r($basic->db_return_row("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->insert_id), 1)."<br>";
	$basic->db_prepare("ins_foo", "INSERT INTO foo (test) VALUES ($1)");
	$status = $basic->db_execute("ins_foo", array('BAR TEST '.time()));
	print "PREPARE INSERT STATUS: $status | PRIMARY KEY: ".$basic->insert_id."<br>";
	print "PREPARE INSERT PREVIOUS INSERTED: ".print_r($basic->db_return_row("SELECT foo_id, test FROM foo WHERE foo_id = ".$basic->insert_id), 1)."<br>";

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

	// search path check
	$q = "SHOW search_path";
	$cursor = $basic->db_exec($q);
	$data = $basic->db_fetch_array($cursor)['search_path'];
	print "RETURN DATA FOR search_path: ".$data."<br>";
//	print "RETURN DATA FOR search_path: ".$basic->print_ar($data)."<br>";
	// insert something into test.schema_test and see if we get the PK back
	$status = $basic->db_exec("INSERT INTO test.schema_test (contents, id) VALUES ('TIME: ".time()."', ".rand(1, 10).")");
	print "OTHER SCHEMA INSERT STATUS: ".$status." | PK NAME: ".$basic->pk_name.", PRIMARY KEY: ".$basic->insert_id."<br>";

	// print error messages
	print $basic->print_error_msg();

	print "</body></html>";
?>

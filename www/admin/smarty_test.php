<?
	$DEBGU_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
	$DEBUG_ALL = 1;
	$PRINT_ALL = 1;
	$DB_DEBUG = 1;

	if ($DEBUG_ALL)
		error_reporting(E_ALL | E_STRICT |  E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

	define('USE_DATABASE', true);
	require("config.inc");
	require("header.inc");
	$MASTER_TEMPLATE_NAME = 'main_body.tpl';
	$TEMPLATE_NAME = 'smarty_test.tpl';
	$PAGE_WIDTH = 750;
	require("set_paths.inc");

	// smarty test
	$cms->DATA['SMARTY_TEST'] = 'Test Data';

	require("smarty.inc");
	require("footer.inc");
?>

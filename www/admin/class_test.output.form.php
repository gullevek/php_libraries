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
$LOG_FILE_ID = 'classTest-form';
ob_end_flush();

// define an array for page use
$table_arrays = [];
$table_arrays[\CoreLibs\Get\System::getPageName(1)] = [
	// form fields mtaching up with db fields
	'table_array' => [
	],
	// laod query
	'load_query' => '',
	// database table to load from
	'table_name' => '',
	// for load dro pdown, format output
	'show_fields' => [
		[
			'name' => 'name'
		],
		[
			'name' => 'enabled',
			'binary' => ['Yes', 'No'],
			'before_value' => 'Enabled: '
		],
	],
	// a multi reference entry
	'element_list' => [
	]
];

$basic = new CoreLibs\Basic();
$form = new CoreLibs\Output\Form\Generate(DB_CONFIG);
// $db = new CoreLibs\DB\IO(DB_CONFIG, $basic->log);

print "<html><head><title>TEST CLASS: FORM GENERATE</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

print "MOBILE PHONE: " . $form->mobile_phone . "<br>";
// sets table array to include
print "MY PAGE NAME: " . $form->my_page_name . "<br>";

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__

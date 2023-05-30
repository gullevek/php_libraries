<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_STRICT | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// override ECHO ALL FALSE
$ECHO_ALL = true;
// define log file id
$LOG_FILE_ID = 'classTest-form';
ob_end_flush();

// start session, needed for Form\Generate
$SET_SESSION_NAME = EDIT_SESSION_NAME;
$session = new CoreLibs\Create\Session($SET_SESSION_NAME);

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

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$l10n = new CoreLibs\Language\L10n(
	SITE_LOCALE,
	SITE_DOMAIN,
	BASE . INCLUDES . LOCALE,
	SITE_ENCODING
);
$form = new CoreLibs\Output\Form\Generate(
	DB_CONFIG,
	$log,
	$l10n,
	[
		'base' => 10,
		'admin' => 0
	],
	table_arrays: $table_arrays
);

$PAGE_NAME = 'TEST CLASS: FORM GENERATE';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

print "MOBILE PHONE: " . $form->mobile_phone . "<br>";
// sets table array to include
print "MY PAGE NAME: " . $form->my_page_name . "<br>";

print "</body></html>";

// __END__

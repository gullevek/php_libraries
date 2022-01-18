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
// set language
$lang = 'en_utf8';
// sample config
require 'config.php';
// override ECHO ALL FALSE
$ECHO_ALL = true;
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-smarty';
ob_end_flush();

$log = new CoreLibs\Debug\Logging([
	'log_folder' => BASE . LOG,
	'file_id' => $LOG_FILE_ID,
	// add file date
	'print_file_date' => true,
	// set debug and print flags
	'debug_all' => $DEBUG_ALL ?? false,
	'echo_all' => $ECHO_ALL ?? false,
	'print_all' => $PRINT_ALL ?? false,
]);
$basic = new CoreLibs\Basic($log);
$smarty = new CoreLibs\Template\SmartyExtend();
// for testing with or without CMS
// $cms = new CoreLibs\Admin\Backend(DB_CONFIG);
$l = new CoreLibs\Language\L10n($lang);

print "<html><head><title>TEST CLASS: SMARTY</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

$smarty->DATA['JS_DEBUG'] = DEBUG;
$smarty->MASTER_TEMPLATE_NAME = 'main_body.tpl';
$smarty->TEMPLATE_NAME = 'smarty_test.tpl';
$smarty->CSS_SPECIAL_TEMPLATE_NAME = 'smart_test.css';
$smarty->USE_PROTOTYPE = false;
$smarty->USE_JQUERY = true;
$smarty->JS_DATEPICKR = false;
if ($smarty->USE_PROTOTYPE) {
	$smarty->ADMIN_JAVASCRIPT = 'edit.pt.js';
	$smarty->JS_SPECIAL_TEMPLATE_NAME = 'prototype.test.js';
} elseif ($smarty->USE_JQUERY) {
	$smarty->ADMIN_JAVASCRIPT = 'edit.jq.js';
	$smarty->JS_SPECIAL_TEMPLATE_NAME = 'jquery.test.js';
}
$smarty->PAGE_WIDTH = '100%';
// require BASE.INCLUDES.'admin_set_paths.php';
$smarty->setSmartyPaths();

// smarty test
$smarty->DATA['SMARTY_TEST'] = 'Test Data';
$smarty->DATA['TRANSLATE_TEST'] = $l->__('Are we translated?');
$smarty->DATA['TRANSLATE_TEST_SMARTY'] = $smarty->l10n->__('Are we translated?');

// drop down test with optgroups
$options = [
	'' => '選択してください',
	'4/25(木)' => [
		'4/25(木) 11:00-11:50' => '4/25(木) 11:00-11:50',
		'4/25(木) 12:20-13:00' => '4/25(木) 12:20-13:00'
	],
	'4/26(金)' => [
		'4/26(金) 11:00-11:50' => '4/26(金) 11:00-11:50',
		'4/26(金) 12:20-13:00' => '4/26(金) 12:20-13:00'
	],
	'4/27(土)' => [
		'4/27(土) 11:00-11:50' => '4/27(土) 11:00-11:50',
		'4/27(土) 12:20-13:00' => '4/27(土) 12:20-13:00'
	],
];

$smarty->DATA['drop_down_test'] = $options;
$smarty->DATA['drop_down_test_selected'] = '';
$smarty->DATA['loop_start'] = 2;
// require BASE.INCLUDES.'admin_smarty.php';
$smarty->setSmartyVarsAdmin();

// error message
print $log->printErrorMsg();

print "</body></html>";

// __END__

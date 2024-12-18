<?php // phpcs:ignore warning

/**
 * @phan-file-suppress PhanTypeSuspiciousStringExpression
 */

declare(strict_types=1);

error_reporting(E_ALL | E_ERROR | E_WARNING | E_PARSE | E_COMPILE_ERROR);

ob_start();

// basic class test file
define('USE_DATABASE', true);
// sample config
require 'config.php';
// override ECHO ALL FALSE
$ECHO_ALL = true;
// define log file id
$LOG_FILE_ID = 'classTest-smarty';
ob_end_flush();

$log = new CoreLibs\Logging\Logging([
	'log_folder' => BASE . LOG,
	'log_file_id' => $LOG_FILE_ID,
	'log_per_date' => true,
]);
$l10n = new \CoreLibs\Language\L10n(
	SITE_LOCALE,
	SITE_DOMAIN,
	BASE . INCLUDES . LOCALE,
	SITE_ENCODING
);
$smarty = new CoreLibs\Template\SmartyExtend(
	$l10n,
	CACHE_ID,
	COMPILE_ID,
);
$adm = new CoreLibs\Admin\Backend(
	new CoreLibs\DB\IO(DB_CONFIG, $log),
	$log,
	new CoreLibs\Create\Session(EDIT_SESSION_NAME),
	$l10n,
	80
);
$adm->DATA['adm_set'] = 'SET from admin class';

$PAGE_NAME = 'TEST CLASS: SMARTY';
print "<!DOCTYPE html>";
print "<html><head><title>" . $PAGE_NAME . "</title></head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';
print '<div><h1>' . $PAGE_NAME . '</h1></div>';

$smarty->DATA['JS_DEBUG'] = $log->loggingLevelIsDebug();
$smarty->MASTER_TEMPLATE_NAME = 'main_body.tpl';
$smarty->TEMPLATE_NAME = 'smarty_test.tpl';
$smarty->CSS_SPECIAL_TEMPLATE_NAME = 'smart_test.css';
$smarty->USE_PROTOTYPE = false;
$smarty->USE_JQUERY = true;
$smarty->JS_DATEPICKR = false;
if ($smarty->USE_PROTOTYPE) { /** @phpstan-ignore-line for debug purpose */
	$smarty->ADMIN_JAVASCRIPT = 'edit.pt.js';
	$smarty->JS_SPECIAL_TEMPLATE_NAME = 'prototype.test.js';
} elseif ($smarty->USE_JQUERY) {
	$smarty->ADMIN_JAVASCRIPT = 'edit.jq.js';
	$smarty->JS_SPECIAL_TEMPLATE_NAME = 'jquery.test.js';
}
$smarty->PAGE_WIDTH = '100%';
$smarty->setSmartyPaths(
	BASE . INCLUDES,
	BASE . INCLUDES . TEMPLATES . CONTENT_PATH,
	LAYOUT . JS,
	LAYOUT . CSS,
	LAYOUT . FONT,
	LAYOUT . IMAGES,
	LAYOUT . CACHE,
	ROOT . LAYOUT . CACHE,
	null // master template name optional
);

// smarty test
$smarty->DATA['SMARTY_TEST'] = 'Test Data';
$smarty->DATA['TRANSLATE_TEST'] = $l10n->__('Are we translated?');
$smarty->DATA['TRANSLATE_TEST_FUNCTION'] = _gettext('Are we translated?');
$smarty->DATA['TRANSLATE_TEST_SMARTY'] = $smarty->l10n->__('Are we translated?');
$smarty->DATA['replace'] = 'Replaced';
// variable variables
$smarty->DATA['test'] = 'foo';
$smarty->DATA['foo'] = 'bar';
// loop
$smarty->DATA['loop_start'] = 5;
// drop down test with optgroups
$smarty->DATA['drop_down_test'] = [
	'foo' => 'Foo',
	'bar' => 'Bar',
	'foobar' => 'Foo Bar',
];
$smarty->DATA['drop_down_test_selected'] = 'bar';
$smarty->DATA['drop_down_test_nested'] = [
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
$smarty->DATA['drop_down_test_nested_selected'] = '';
$smarty->DATA['radio_test'] = [
	'0' => 'On',
	'1' => 'Off',
	'-1' => 'Undefined'
];
$smarty->DATA['radio_test_selected'] = -1;
$smarty->DATA['checkbox_test'] = [
	'0' => 'On',
	'1' => 'Off',
	'-1' => 'Undefined'
];
$smarty->DATA['checkbox_test_pos'] = [
	'0' => 'A',
	'1' => 'B'
];
$smarty->DATA['checkbox_test_selected'] = ['1', '-1'];
$smarty->DATA['checkbox_test_pos_selected'] = ['0', '-1'];


$smarty->setSmartyVarsAdmin(
	[
		'compile_dir' => BASE . TEMPLATES_C,
		'cache_dir' => BASE . CACHE,
		'js' => JS,
		'css' => CSS,
		'font' => FONT,
		'g_title' => G_TITLE,
		'default_encoding' => DEFAULT_ENCODING,
		'admin_stylesheet' => ADMIN_STYLESHEET,
		'admin_javascript' => ADMIN_JAVASCRIPT,
		'page_width' => PAGE_WIDTH,
		'content_path' => CONTENT_PATH,
		'user_name' => $_SESSION['USER_NAME'] ?? ''
	],
	$adm
);

print "</body></html>";

// __END__

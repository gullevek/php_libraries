<?php declare(strict_types=1);
$ENABLE_ERROR_HANDLING = 0;
$DEBUG_ALL_OVERRIDE = 0; // set to 1 to debug on live/remote server locations
$DEBUG_ALL = 1;
$PRINT_ALL = 1;
$DB_DEBUG = 1;
$LOG_PER_RUN = 1;

define('USE_DATABASE', true);
define('USE_HEADER', true);
require 'config.php';
require BASE.INCLUDES.'admin_header.php';
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
$smarty->DATA['TRANSLATE_TEST'] = $cms->l->__('Are we translated?');

// drop down test with optgroups
$options = array (
	'' => '選択してください',
	'4/25(木)' => array (
		'4/25(木) 11:00-11:50' => '4/25(木) 11:00-11:50',
		'4/25(木) 12:20-13:00' => '4/25(木) 12:20-13:00'
	),
	'4/26(金)' => array (
		'4/26(金) 11:00-11:50' => '4/26(金) 11:00-11:50',
		'4/26(金) 12:20-13:00' => '4/26(金) 12:20-13:00'
	),
	'4/27(土)' => array (
		'4/27(土) 11:00-11:50' => '4/27(土) 11:00-11:50',
		'4/27(土) 12:20-13:00' => '4/27(土) 12:20-13:00'
	)
);

$smarty->DATA['drop_down_test'] = $options;

// require BASE.INCLUDES.'admin_smarty.php';
$smarty->setSmartyVarsAdmin();
require BASE.INCLUDES.'admin_footer.php';

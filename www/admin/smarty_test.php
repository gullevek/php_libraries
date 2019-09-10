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
require BASE.INCLUDES.'admin_header.inc';
$MASTER_TEMPLATE_NAME = 'main_body.tpl';
$TEMPLATE_NAME = 'smarty_test.tpl';
$CSS_NAME = 'smart_test.css';
$USE_PROTOTYPE = false;
$USE_JQUERY = true;
if ($USE_PROTOTYPE) {
	$ADMIN_JAVASCRIPT = 'edit.pt.js';
	$JS_NAME = 'prototype.test.js';
} elseif ($USE_JQUERY) {
	$ADMIN_JAVASCRIPT = 'edit.jq.js';
	$JS_NAME = 'jquery.test.js';
}
$PAGE_WIDTH = "100%";
require BASE.INCLUDES.'admin_set_paths.inc';

// smarty test
$cms->DATA['SMARTY_TEST'] = 'Test Data';

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

$cms->DATA['drop_down_test'] = $options;

require BASE.INCLUDES.'admin_smarty.inc';
require BASE.INCLUDES.'admin_footer.inc';

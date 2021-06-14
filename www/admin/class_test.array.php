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
define('USE_DATABASE', false);
// sample config
require 'config.php';
// set session name
if (!defined('SET_SESSION_NAME')) {
	define('SET_SESSION_NAME', EDIT_SESSION_NAME);
}
// define log file id
$LOG_FILE_ID = 'classTest-array';
ob_end_flush();

use CoreLibs\Combined\ArrayHandler;

$basic = new CoreLibs\Basic();
// $_array= new CoreLibs\Combined\ArrayHandler();
// $array_class = 'CoreLibs\Combination\ArrayHandler';

print "<html><head><title>TEST CLASS: ARRAY HANDLER</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

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
echo "FOUND ELEMENTS [base]: ".$basic->printAr(ArrayHandler::arraySearchRecursive('email', $test_array, 'type'))."<br>";
echo "FOUND ELEMENTS [input]: ".$basic->printAr(ArrayHandler::arraySearchRecursive('email', $test_array['input'], 'type'))."<br>";

$array_1 = [
	'foo' => 'bar'
];
$array_2 = [
	1, 2, 3
];
$array_3 = [
	'alpha' => [
		'beta' => 4
	]
];
print "ARRAYMERGERECURSIVE: ".$basic->printAr(ArrayHandler::arrayMergeRecursive($array_1, $array_2, $array_3))."<br>";

// DEPRECATED
// print "ARRAYMERGERECURSIVE: ".$basic->printAr($basic->arrayMergeRecursive($array_1, $array_2, $array_3))."<br>";

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__

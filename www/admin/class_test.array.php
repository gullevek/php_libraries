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
use CoreLibs\Debug\Support as DgS;

$basic = new CoreLibs\Basic();
// $_array= new CoreLibs\Combined\ArrayHandler();
// $array_class = 'CoreLibs\Combination\ArrayHandler';

print "<html><head><title>TEST CLASS: ARRAY HANDLER</title><head>";
print "<body>";
print '<div><a href="class_test.php">Class Test Master</a></div>';

// recursive array search
$test_array = [
	'foo' => 'bar',
	'input' => [
		'element_a' => [
			'type' => 'text'
		],
		'element_b' => [
			'type' => 'email'
		],
		'element_c' => [
			'type' => 'email'
		],
	],
];

echo "SOURCE ARRAY: ".DgS::printAr($test_array)."<br>";
// frist return
echo "ARRAYSEARCHRECURSIVE(email, [array], type): ".DgS::printAr(ArrayHandler::arraySearchRecursive('email', $test_array, 'type'))."<br>";
echo "ARRAYSEARCHRECURSIVE(email, [array]['input'], type): ".DgS::printAr(ArrayHandler::arraySearchRecursive('email', $test_array['input'], 'type'))."<br>";
// all return
echo "ARRAYSEARCHRECURSIVEALL(email, [array], type): ".Dgs::printAr((array)ArrayHandler::arraySearchRecursiveAll('email', $test_array, 'type'))."<br>";

// simple search
echo "ARRAYSEARCHSIMPLE([array], type, email): ".(string)ArrayHandler::arraySearchSimple($test_array, 'type', 'email')."<br>";

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
// recusrice merge
print "ARRAYMERGERECURSIVE: ".DgS::printAr(ArrayHandler::arrayMergeRecursive($array_1, $array_2, $array_3))."<br>";
// array difference
$array_left = [
	'same' => 'data',
	'left' => 'Has L'
];
$array_right = [
	'same' => 'data',
	'right' => 'has R'
];
print "ARRAYDIFF: ".DgS::printAr(ArrayHandler::arrayDiff($array_left, $array_right))."<br>";
// in array check
print "INARRAYANY([1,3], [array]): ".DgS::printAr(ArrayHandler::inArrayAny([1, 3], $array_2))."<br>";
// flatten array
print "FLATTENARRAY: ".DgS::printAr(ArrayHandler::flattenArray($test_array))."<br>";
print "FLATTENARRAYKEY: ".DgS::printAr(ArrayHandler::flattenArrayKey($test_array))."<br>";
// flatten for key set
print "ARRAYFLATFORKEY: ".DgS::printAr(ArrayHandler::arrayFlatForKey($test_array, 'type'))."<br>";

// DEPRECATED
// print "ARRAYMERGERECURSIVE: ".DgS::printAr($basic->arrayMergeRecursive($array_1, $array_2, $array_3))."<br>";

// error message
print $basic->log->printErrorMsg();

print "</body></html>";

// __END__
